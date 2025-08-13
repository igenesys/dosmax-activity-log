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
        $this->wpdb = $wpdb;
        $this->use_external_db = get_option('dosmax_activity_log_use_external_db', false);
        
        // Initialize external database connection if needed
        if ($this->use_external_db) {
            $this->init_external_db();
        }
        
        // Get table names with custom prefix
        $prefix = get_option('dosmax_activity_log_db_prefix', 'wp_');
        
        if ($this->use_external_db) {
            // For external DB, use the configured prefix directly
            $this->occurrences_table = $prefix . 'wsal_occurrences';
            $this->metadata_table = $prefix . 'wsal_metadata';
        } else {
            // For current WordPress DB, support multisite
            $blog_id = get_current_blog_id();
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
        $db_host = get_option('dosmax_activity_log_db_host', DB_HOST);
        $db_name = get_option('dosmax_activity_log_db_name', DB_NAME);
        $db_user = get_option('dosmax_activity_log_db_user', DB_USER);
        $db_password = get_option('dosmax_activity_log_db_password', DB_PASSWORD);
        
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
        return $this->use_external_db && $this->external_db ? $this->external_db : $this->wpdb;
    }
    
    /**
     * Get filtered activity logs
     */
    public function get_filtered_logs($page = 1, $per_page = 25, $orderby = 'created_on', $order = 'DESC') {
        $db = $this->get_db();
        $offset = ($page - 1) * $per_page;
        
        // Get allowed and excluded roles
        $allowed_roles = get_option('dosmax_activity_log_allowed_roles', array('site-admin'));
        $excluded_roles = get_option('dosmax_activity_log_excluded_roles', array('administrator'));
        
        // Build WHERE clause for role filtering
        $role_conditions = array();
        
        // Include allowed roles
        foreach ($allowed_roles as $role) {
            $role_conditions[] = $db->prepare("user_roles LIKE %s", '%' . $role . '%');
        }
        
        // Exclude forbidden roles
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
        
        // Sanitize order parameters
        $allowed_orderby = array('id', 'created_on', 'alert_id', 'severity', 'username', 'client_ip');
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
            ARRAY_A
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
    public function get_total_log_count() {
        $db = $this->get_db();
        
        // Get allowed and excluded roles
        $allowed_roles = get_option('dosmax_activity_log_allowed_roles', array('site-admin'));
        $excluded_roles = get_option('dosmax_activity_log_excluded_roles', array('administrator'));
        
        // Build WHERE clause for role filtering
        $role_conditions = array();
        
        // Include allowed roles
        foreach ($allowed_roles as $role) {
            $role_conditions[] = $db->prepare("user_roles LIKE %s", '%' . $role . '%');
        }
        
        // Exclude forbidden roles
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
        
        $query = "SELECT COUNT(*) FROM {$this->occurrences_table} {$where_clause}";
        
        return (int) $db->get_var($query);
    }
    
    /**
     * Get metadata for a specific occurrence
     */
    public function get_occurrence_metadata($occurrence_id) {
        $db = $this->get_db();
        $metadata = array();
        
        $results = $db->get_results(
            $db->prepare(
                "SELECT name, value FROM {$this->metadata_table} WHERE occurrence_id = %d",
                $occurrence_id
            ),
            ARRAY_A
        );
        
        foreach ($results as $row) {
            $metadata[$row['name']] = $row['value'];
        }
        
        return $metadata;
    }
    
    /**
     * Get detailed information for a specific log entry
     */
    public function get_log_details($occurrence_id) {
        $db = $this->get_db();
        
        $occurrence = $db->get_row(
            $db->prepare(
                "SELECT * FROM {$this->occurrences_table} WHERE id = %d",
                $occurrence_id
            ),
            ARRAY_A
        );
        
        if (!$occurrence) {
            return false;
        }
        
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
        $db = $this->get_db();
        
        try {
            // Test basic connection
            $result = $db->get_var("SELECT 1");
            if ($result !== '1') {
                return array('success' => false, 'message' => 'Database connection test failed');
            }
            
            // Test if tables exist
            $occurrences_exists = $db->get_var($db->prepare("SHOW TABLES LIKE %s", $this->occurrences_table));
            $metadata_exists = $db->get_var($db->prepare("SHOW TABLES LIKE %s", $this->metadata_table));
            
            if (!$occurrences_exists) {
                return array('success' => false, 'message' => "Occurrences table '{$this->occurrences_table}' not found");
            }
            
            if (!$metadata_exists) {
                return array('success' => false, 'message' => "Metadata table '{$this->metadata_table}' not found");
            }
            
            // Test table structure
            $columns = $db->get_results("DESCRIBE {$this->occurrences_table}", ARRAY_A);
            $required_columns = array('id', 'user_roles', 'created_on', 'severity', 'username', 'client_ip');
            $table_columns = array_column($columns, 'Field');
            
            foreach ($required_columns as $column) {
                if (!in_array($column, $table_columns)) {
                    return array('success' => false, 'message' => "Required column '{$column}' not found in occurrences table");
                }
            }
            
            return array('success' => true, 'message' => 'Database connection and tables verified successfully');
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Database error: ' . $e->getMessage());
        }
    }
}
