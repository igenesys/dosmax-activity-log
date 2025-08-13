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
        
        // Get log entries
        $logs = $this->database->get_filtered_logs($current_page, $this->per_page, $orderby, $order);
        $total_items = $this->database->get_total_log_count();
        $total_pages = ceil($total_items / $this->per_page);
        
        // Include template
        include DOSMAX_ACTIVITY_LOG_PLUGIN_DIR . 'templates/admin-page.php';
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
