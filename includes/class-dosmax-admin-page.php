<?php
/**
 * Admin page class
 */
class Dosmax_Admin_Page {
    
    private $database;
    private $per_page = 25;
    
    public function __construct($database) {
        $this->database = $database;
    }
    
    /**
     * Display the admin page
     */
    public function display_page() {
        // Get current page and sorting parameters
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'created_on';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        
        // Get filter parameters
        $filters = $this->get_filter_parameters();
        $filter_user = $filters['user'];
        $filter_object = $filters['object'];
        $filter_ip = $filters['ip'];
        $date_filter_type = $filters['date_type'];
        $filter_date = $filters['date'];
        $has_active_filters = $filters['has_active'];
        
        // Get available filter options
        $available_users = $this->database->get_available_users();
        $available_objects = $this->database->get_available_objects();
        
        // Get log entries with filters
        $logs = $this->database->get_filtered_logs($current_page, $this->per_page, $orderby, $order, $filters);
        $total_items = $this->database->get_total_log_count($filters);
        $total_pages = ceil($total_items / $this->per_page);
        
        // Include template
        include DOSMAX_ACTIVITY_LOG_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Get filter parameters from request
     */
    private function get_filter_parameters() {
        $filter_user = isset($_GET['filter_user']) ? sanitize_text_field($_GET['filter_user']) : '';
        $filter_object = isset($_GET['filter_object']) ? sanitize_text_field($_GET['filter_object']) : '';
        $filter_ip = isset($_GET['filter_ip']) ? sanitize_text_field($_GET['filter_ip']) : '';
        $date_filter_type = isset($_GET['date_filter_type']) ? sanitize_text_field($_GET['date_filter_type']) : '';
        $filter_date = isset($_GET['filter_date']) ? sanitize_text_field($_GET['filter_date']) : '';
        
        // Validate date filter type
        if (!in_array($date_filter_type, array('before', 'after', 'on'))) {
            $date_filter_type = '';
        }
        
        // Validate date format
        if ($filter_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
            $filter_date = '';
        }
        
        // Check if any filters are active
        $has_active_filters = !empty($filter_user) || !empty($filter_object) || 
                             !empty($filter_ip) || (!empty($date_filter_type) && !empty($filter_date));
        
        return array(
            'user' => $filter_user,
            'object' => $filter_object,
            'ip' => $filter_ip,
            'date_type' => $date_filter_type,
            'date' => $filter_date,
            'has_active' => $has_active_filters
        );
    }
    
    /**
     * Get severity icon
     */
    public function get_severity_icon($severity) {
        $icons = array(
            '100' => 'dashicons-info',      // Informational
            '200' => 'dashicons-yes',       // Low
            '250' => 'dashicons-warning',   // Medium
            '300' => 'dashicons-warning',   // High
            '400' => 'dashicons-dismiss',   // Critical
        );
        
        return isset($icons[$severity]) ? $icons[$severity] : 'dashicons-marker';
    }
    
    /**
     * Get severity label
     */
    public function get_severity_label($severity) {
        $labels = array(
            '100' => __('Info', 'dosmax-activity-log'),
            '200' => __('Low', 'dosmax-activity-log'),
            '250' => __('Medium', 'dosmax-activity-log'),
            '300' => __('High', 'dosmax-activity-log'),
            '400' => __('Critical', 'dosmax-activity-log'),
        );
        
        return isset($labels[$severity]) ? $labels[$severity] : __('Unknown', 'dosmax-activity-log');
    }
    
    /**
     * Format event message
     */
    public function format_event_message($alert_id, $metadata) {
        // Basic message formatting based on common alert IDs
        $messages = array(
            '1000' => __('User logged in', 'dosmax-activity-log'),
            '1001' => __('User logged out', 'dosmax-activity-log'),
            '2002' => __('User created a post revision', 'dosmax-activity-log'),
            '2065' => __('User modified a post', 'dosmax-activity-log'),
            '2086' => __('User changed post title', 'dosmax-activity-log'),
            '2100' => __('User opened a post in the editor', 'dosmax-activity-log'),
            '2101' => __('User viewed a post', 'dosmax-activity-log'),
            '5001' => __('User activated a plugin', 'dosmax-activity-log'),
            '5002' => __('User deactivated a plugin', 'dosmax-activity-log'),
        );
        
        $base_message = isset($messages[$alert_id]) ? $messages[$alert_id] : __('Activity logged', 'dosmax-activity-log');
        
        // Add post title if available
        if (isset($metadata['PostTitle'])) {
            $base_message .= ': ' . esc_html($metadata['PostTitle']);
        }
        
        return $base_message;
    }
    
    /**
     * Format date according to custom format (dd.mm.yyyy h:mm:ss.000 am/pm)
     */
    public function format_custom_date($timestamp) {
        // Use WordPress date functions for better compatibility
        if (empty($timestamp)) {
            return '';
        }
        
        // Format date as dd.mm.yyyy
        $formatted_date = date('d.m.Y', $timestamp);
        
        // Format time as h:mm:ss.000 am/pm
        $formatted_time = date('g:i:s', $timestamp) . '.000 ' . date('a', $timestamp);
        
        return $formatted_date . '<br>' . $formatted_time;
    }
    
    /**
     * Generate pagination links
     */
    public function pagination_links($current_page, $total_pages) {
        if ($total_pages <= 1) {
            return '';
        }
        
        $pagination = '<div class="tablenav-pages">';
        $pagination .= '<span class="displaying-num">' . sprintf(__('%d items', 'dosmax-activity-log'), $this->database->get_total_log_count()) . '</span>';
        
        if ($current_page > 1) {
            $pagination .= '<a class="prev-page button" href="' . add_query_arg('paged', $current_page - 1) . '">&lsaquo;</a>';
        }
        
        $pagination .= '<span class="paging-input">';
        $pagination .= '<span class="current-page">' . $current_page . '</span>';
        $pagination .= ' of ';
        $pagination .= '<span class="total-pages">' . $total_pages . '</span>';
        $pagination .= '</span>';
        
        if ($current_page < $total_pages) {
            $pagination .= '<a class="next-page button" href="' . add_query_arg('paged', $current_page + 1) . '">&rsaquo;</a>';
        }
        
        $pagination .= '</div>';
        
        return $pagination;
    }
}
