<?php
/**
 * Main plugin class
 */
class Dosmax_Activity_Log {
    
    private $admin_page;
    private $database;
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize components
        $this->database = new Dosmax_Database();
        $this->admin_page = new Dosmax_Admin_Page($this->database);
        
        // Add hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_dosmax_get_log_details', array($this, 'ajax_get_log_details'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_management_page(
            __('Dosmax Activity Log', 'dosmax-activity-log'),
            __('Dosmax Activity Log', 'dosmax-activity-log'),
            'manage_options',
            'dosmax-activity-log',
            array($this->admin_page, 'display_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'tools_page_dosmax-activity-log') {
            return;
        }
        
        wp_enqueue_style(
            'dosmax-activity-log-admin',
            DOSMAX_ACTIVITY_LOG_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            DOSMAX_ACTIVITY_LOG_VERSION
        );
        
        wp_enqueue_script(
            'dosmax-activity-log-admin',
            DOSMAX_ACTIVITY_LOG_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery'),
            DOSMAX_ACTIVITY_LOG_VERSION,
            true
        );
        
        wp_localize_script('dosmax-activity-log-admin', 'dosmax_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dosmax_activity_log_nonce')
        ));
    }
    
    /**
     * AJAX handler for getting log details
     */
    public function ajax_get_log_details() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'dosmax_activity_log_nonce')) {
            wp_die(__('Security check failed', 'dosmax-activity-log'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'dosmax-activity-log'));
        }
        
        $occurrence_id = intval($_POST['occurrence_id']);
        $details = $this->database->get_log_details($occurrence_id);
        
        wp_send_json_success($details);
    }
}
