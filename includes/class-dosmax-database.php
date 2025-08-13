<?php
/**
 * Database operations class
 */
class Dosmax_Database {
    
    private $wpdb;
    private $occurrences_table;
    private $metadata_table;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        // Get table names (support multisite)
        $blog_id = get_current_blog_id();
        if ($blog_id > 1) {
            $this->occurrences_table = $wpdb->prefix . $blog_id . '_wsal_occurrences';
            $this->metadata_table = $wpdb->prefix . $blog_id . '_wsal_metadata';
        } else {
            $this->occurrences_table = $wpdb->prefix . 'wsal_occurrences';
            $this->metadata_table = $wpdb->prefix . 'wsal_metadata';
        }
    }
    
    /**
     * Get filtered activity logs
     */
    public function get_filtered_logs($page = 1, $per_page = 25, $orderby = 'created_on', $order = 'DESC') {
        $offset = ($page - 1) * $per_page;
        
        // Get allowed and excluded roles
        $allowed_roles = get_option('dosmax_activity_log_allowed_roles', array('site-admin'));
        $excluded_roles = get_option('dosmax_activity_log_excluded_roles', array('administrator'));
        
        // Build WHERE clause for role filtering
        $role_conditions = array();
        
        // Include allowed roles
        foreach ($allowed_roles as $role) {
            $role_conditions[] = $this->wpdb->prepare("user_roles LIKE %s", '%' . $role . '%');
        }
        
        // Exclude forbidden roles
        $exclude_conditions = array();
        foreach ($excluded_roles as $role) {
            $exclude_conditions[] = $this->wpdb->prepare("user_roles NOT LIKE %s", '%' . $role . '%');
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
        
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($query, $per_page, $offset),
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
        // Get allowed and excluded roles
        $allowed_roles = get_option('dosmax_activity_log_allowed_roles', array('site-admin'));
        $excluded_roles = get_option('dosmax_activity_log_excluded_roles', array('administrator'));
        
        // Build WHERE clause for role filtering
        $role_conditions = array();
        
        // Include allowed roles
        foreach ($allowed_roles as $role) {
            $role_conditions[] = $this->wpdb->prepare("user_roles LIKE %s", '%' . $role . '%');
        }
        
        // Exclude forbidden roles
        $exclude_conditions = array();
        foreach ($excluded_roles as $role) {
            $exclude_conditions[] = $this->wpdb->prepare("user_roles NOT LIKE %s", '%' . $role . '%');
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
        
        return (int) $this->wpdb->get_var($query);
    }
    
    /**
     * Get metadata for a specific occurrence
     */
    public function get_occurrence_metadata($occurrence_id) {
        $metadata = array();
        
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
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
        $occurrence = $this->wpdb->get_row(
            $this->wpdb->prepare(
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
}
