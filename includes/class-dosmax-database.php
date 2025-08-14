<?php
/**
 * Database operations class
 */
class Dosmax_Database {
    
    private $wpdb;
    private $external_db;
    private $occurrences_table;
    private $metadata_table;
    private $use_external_db;
    
    public function __construct() {
        global $wpdb;
        
        // Ensure wpdb is available
        if (!$wpdb && function_exists('wp_load_alloptions')) {
            // WordPress is loaded, but wpdb might not be set yet
            $wpdb = $GLOBALS['wpdb'] ?? null;
        }
        
        $this->wpdb = $wpdb;
        $this->use_external_db = function_exists('get_option') ? get_option('dosmax_activity_log_use_external_db', false) : false;
        
        // Initialize external database connection if needed
        if ($this->use_external_db) {
            $this->init_external_db();
        }
        
        // Get table names with custom prefix
        $prefix = function_exists('get_option') ? get_option('dosmax_activity_log_db_prefix', 'wp_') : 'wp_';
        
        if ($this->use_external_db) {
            // For external DB, use the configured prefix directly
            $this->occurrences_table = $prefix . 'wsal_occurrences';
            $this->metadata_table = $prefix . 'wsal_metadata';
        } else {
            // For current WordPress DB, support multisite
            $blog_id = function_exists('get_current_blog_id') ? get_current_blog_id() : 1;
            if ($blog_id > 1) {
                $this->occurrences_table = $wpdb->prefix . $blog_id . '_wsal_occurrences';
                $this->metadata_table = $wpdb->prefix . $blog_id . '_wsal_metadata';
            } else {
                $this->occurrences_table = $wpdb->prefix . 'wsal_occurrences';
                $this->metadata_table = $wpdb->prefix . 'wsal_metadata';
            }
        }
    }
    
    /**
     * Initialize external database connection
     */
    private function init_external_db() {
        if (!function_exists('get_option')) return;
        
        $db_host = get_option('dosmax_activity_log_db_host', defined('DB_HOST') ? DB_HOST : 'localhost');
        $db_name = get_option('dosmax_activity_log_db_name', defined('DB_NAME') ? DB_NAME : 'wordpress');
        $db_user = get_option('dosmax_activity_log_db_user', defined('DB_USER') ? DB_USER : 'root');
        $db_password = get_option('dosmax_activity_log_db_password', defined('DB_PASSWORD') ? DB_PASSWORD : '');
        
        try {
            $this->external_db = new wpdb($db_user, $db_password, $db_name, $db_host);
            
            // Test the connection
            $result = $this->external_db->get_var("SELECT 1");
            if ($result !== '1') {
                throw new Exception('Database connection test failed');
            }
        } catch (Exception $e) {
            // Fall back to WordPress database
            $this->use_external_db = false;
            $this->external_db = null;
            
            // Log error if WordPress debug is enabled
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Dosmax Activity Log: External database connection failed - ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Get the appropriate database connection
     */
    private function get_db() {
        $db = $this->use_external_db && $this->external_db ? $this->external_db : $this->wpdb;
        
        // Ensure we have a valid database connection
        if (!$db) {
            global $wpdb;
            $db = $wpdb;
        }
        
        return $db;
    }
    
    /**
     * Build WHERE clause with role filtering and additional filters
     */
    private function build_where_clause($filters = array()) {
        $db = $this->get_db();
        $where_conditions = array();
        
        // Get allowed and excluded roles
        $allowed_roles = get_option('dosmax_activity_log_allowed_roles', array('site-admin'));
        $excluded_roles = get_option('dosmax_activity_log_excluded_roles', array('administrator'));
        
        // Role filtering
        $role_conditions = array();
        foreach ($allowed_roles as $role) {
            $role_conditions[] = $db->prepare("user_roles LIKE %s", '%' . $role . '%');
        }
        
        $exclude_conditions = array();
        foreach ($excluded_roles as $role) {
            $exclude_conditions[] = $db->prepare("user_roles NOT LIKE %s", '%' . $role . '%');
        }
        
        if (!empty($role_conditions)) {
            $role_filter = '(' . implode(' OR ', $role_conditions) . ')';
            if (!empty($exclude_conditions)) {
                $role_filter .= ' AND (' . implode(' AND ', $exclude_conditions) . ')';
            }
            $where_conditions[] = $role_filter;
        } elseif (!empty($exclude_conditions)) {
            $where_conditions[] = '(' . implode(' AND ', $exclude_conditions) . ')';
        }
        
        // User filter
        if (!empty($filters['user'])) {
            $where_conditions[] = $db->prepare("username = %s", $filters['user']);
        }
        
        // Object filter
        if (!empty($filters['object'])) {
            $where_conditions[] = $db->prepare("object = %s", $filters['object']);
        }
        
        // IP Address filter
        if (!empty($filters['ip'])) {
            $where_conditions[] = $db->prepare("client_ip = %s", $filters['ip']);
        }
        
        // Date filters
        if (!empty($filters['date_type']) && !empty($filters['date'])) {
            $date_condition = '';
            switch ($filters['date_type']) {
                case 'before':
                    $date_condition = $db->prepare("DATE(created_on) < %s", $filters['date']);
                    break;
                case 'after':
                    $date_condition = $db->prepare("DATE(created_on) > %s", $filters['date']);
                    break;
                case 'on':
                    $date_condition = $db->prepare("DATE(created_on) = %s", $filters['date']);
                    break;
            }
            if ($date_condition) {
                $where_conditions[] = $date_condition;
            }
        }
        
        return !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    }
    
    /**
     * Get filtered activity logs
     */
    public function get_filtered_logs($page = 1, $per_page = 25, $orderby = 'created_on', $order = 'DESC', $filters = array()) {
        $db = $this->get_db();
        $offset = ($page - 1) * $per_page;
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        
        // Sanitize order parameters
        $allowed_orderby = array('id', 'created_on', 'alert_id', 'severity', 'username', 'client_ip', 'object', 'event_type');
        $orderby = in_array($orderby, $allowed_orderby) ? $orderby : 'created_on';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        $query = "
            SELECT *
            FROM {$this->occurrences_table}
            {$where_clause}
            ORDER BY {$orderby} {$order}
            LIMIT %d OFFSET %d
        ";
        
        $results = $db->get_results(
            $db->prepare($query, $per_page, $offset),
            'ARRAY_A'
        );
        
        // Get metadata for each occurrence
        foreach ($results as &$result) {
            $result['metadata'] = $this->get_occurrence_metadata($result['id']);
        }
        
        return $results;
    }
    
    /**
     * Get total count of filtered logs
     */
    public function get_total_log_count($filters = array()) {
        $db = $this->get_db();
        
        // Build WHERE clause
        $where_clause = $this->build_where_clause($filters);
        
        $query = "SELECT COUNT(*) FROM {$this->occurrences_table} {$where_clause}";
        
        return intval($db->get_var($query));
    }
    
    /**
     * Get available users for filter dropdown
     */
    public function get_available_users() {
        $db = $this->get_db();
        
        // Get allowed and excluded roles for filtering
        $allowed_roles = get_option('dosmax_activity_log_allowed_roles', array('site-admin'));
        $excluded_roles = get_option('dosmax_activity_log_excluded_roles', array('administrator'));
        
        // Build role filtering
        $role_conditions = array();
        foreach ($allowed_roles as $role) {
            $role_conditions[] = $db->prepare("user_roles LIKE %s", '%' . $role . '%');
        }
        
        $exclude_conditions = array();
        foreach ($excluded_roles as $role) {
            $exclude_conditions[] = $db->prepare("user_roles NOT LIKE %s", '%' . $role . '%');
        }
        
        $where_clause = '';
        if (!empty($role_conditions)) {
            $where_clause = 'WHERE (' . implode(' OR ', $role_conditions) . ')';
            if (!empty($exclude_conditions)) {
                $where_clause .= ' AND (' . implode(' AND ', $exclude_conditions) . ')';
            }
        } elseif (!empty($exclude_conditions)) {
            $where_clause = 'WHERE (' . implode(' AND ', $exclude_conditions) . ')';
        }
        
        $query = "SELECT DISTINCT username FROM {$this->occurrences_table} {$where_clause} AND username IS NOT NULL AND username != '' ORDER BY username";
        
        $results = $db->get_col($query);
        return array_filter($results);
    }
    
    /**
     * Get available objects for filter dropdown
     */
    public function get_available_objects() {
        $db = $this->get_db();
        
        // Get allowed and excluded roles for filtering
        $allowed_roles = get_option('dosmax_activity_log_allowed_roles', array('site-admin'));
        $excluded_roles = get_option('dosmax_activity_log_excluded_roles', array('administrator'));
        
        // Build role filtering
        $role_conditions = array();
        foreach ($allowed_roles as $role) {
            $role_conditions[] = $db->prepare("user_roles LIKE %s", '%' . $role . '%');
        }
        
        $exclude_conditions = array();
        foreach ($excluded_roles as $role) {
            $exclude_conditions[] = $db->prepare("user_roles NOT LIKE %s", '%' . $role . '%');
        }
        
        $where_clause = '';
        if (!empty($role_conditions)) {
            $where_clause = 'WHERE (' . implode(' OR ', $role_conditions) . ')';
            if (!empty($exclude_conditions)) {
                $where_clause .= ' AND (' . implode(' AND ', $exclude_conditions) . ')';
            }
        } elseif (!empty($exclude_conditions)) {
            $where_clause = 'WHERE (' . implode(' AND ', $exclude_conditions) . ')';
        }
        
        $query = "SELECT DISTINCT object FROM {$this->occurrences_table} {$where_clause} AND object IS NOT NULL AND object != '' ORDER BY object";
        
        $results = $db->get_col($query);
        return array_filter($results);
    }
    
    /**
     * Get occurrence metadata
     */
    public function get_occurrence_metadata($occurrence_id) {
        $db = $this->get_db();
        
        $query = $db->prepare(
            "SELECT name, value FROM {$this->metadata_table} WHERE occurrence_id = %d",
            $occurrence_id
        );
        
        $results = $db->get_results($query, 'ARRAY_A');
        
        $metadata = array();
        foreach ($results as $result) {
            $metadata[$result['name']] = $result['value'];
        }
        
        return $metadata;
    }
    
    /**
     * Get log details for AJAX request
     */
    public function get_log_details($occurrence_id) {
        $db = $this->get_db();
        
        // Get occurrence data
        $occurrence_query = $db->prepare(
            "SELECT * FROM {$this->occurrences_table} WHERE id = %d",
            $occurrence_id
        );
        
        $occurrence = $db->get_row($occurrence_query, 'ARRAY_A');
        
        if (!$occurrence) {
            return array('error' => 'Occurrence not found');
        }
        
        // Get metadata
        $metadata = $this->get_occurrence_metadata($occurrence_id);
        
        return array(
            'occurrence' => $occurrence,
            'metadata' => $metadata
        );
    }
    
    /**
     * Get occurrence details by ID
     */
    public function get_occurrence_details($occurrence_id) {
        $db = $this->get_db();
        
        $query = $db->prepare(
            "SELECT * FROM {$this->occurrences_table} WHERE id = %d",
            $occurrence_id
        );
        
        return $db->get_row($query, 'ARRAY_A');
    }
    
    /**
     * Get detailed log information for AJAX requests
     */
    public function get_log_details($occurrence_id) {
        $db = $this->get_db();
        
        // Get occurrence data
        $occurrence = $db->get_row(
            $db->prepare("SELECT * FROM {$this->occurrences_table} WHERE id = %d", $occurrence_id),
            'ARRAY_A'
        );
        
        if (!$occurrence) {
            return array('error' => 'Log entry not found');
        }
        
        // Get metadata
        $metadata = $this->get_occurrence_metadata($occurrence_id);
        
        return array(
            'occurrence' => $occurrence,
            'metadata' => $metadata
        );
    }
    
    /**
     * Test database connection
     */
    public function test_connection() {
        try {
            $db = $this->get_db();
            
            // Test basic connection
            $result = $db->get_var("SELECT 1");
            if ($result !== '1') {
                return array('status' => 'error', 'message' => 'Database connection test failed');
            }
            
            // Test if tables exist
            $occurrences_exists = $db->get_var("SHOW TABLES LIKE '{$this->occurrences_table}'");
            $metadata_exists = $db->get_var("SHOW TABLES LIKE '{$this->metadata_table}'");
            
            if (!$occurrences_exists || !$metadata_exists) {
                return array(
                    'status' => 'warning', 
                    'message' => 'Connected but WP Activity Log tables not found'
                );
            }
            
            // Test data access
            $count = $db->get_var("SELECT COUNT(*) FROM {$this->occurrences_table} LIMIT 1");
            
            return array(
                'status' => 'success', 
                'message' => 'Connection successful. Found ' . intval($count) . ' log entries.'
            );
            
        } catch (Exception $e) {
            return array('status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage());
        }
    }
}