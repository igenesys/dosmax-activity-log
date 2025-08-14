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
        add_action('admin_init', array($this, 'restrict_settings_access'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_dosmax_get_log_details', array($this, 'ajax_get_log_details'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Add main menu page
        add_menu_page(
            __('Dosmax Activity Log', 'dosmax-activity-log'),
            __('Activity Log', 'dosmax-activity-log'),
            'manage_options',
            'dosmax-activity-log',
            array($this->admin_page, 'display_page'),
            'dashicons-list-view',
            25
        );
        
        // Add submenu pages
        add_submenu_page(
            'dosmax-activity-log',
            __('Activity Logs', 'dosmax-activity-log'),
            __('View Logs', 'dosmax-activity-log'),
            'manage_options',
            'dosmax-activity-log',
            array($this->admin_page, 'display_page')
        );
        
        // Only show settings to users who are not site-admin
        if (!$this->is_site_admin_only()) {
            add_submenu_page(
                'dosmax-activity-log',
                __('Activity Log Settings', 'dosmax-activity-log'),
                __('Settings', 'dosmax-activity-log'),
                'activate_plugins', // Higher capability required
                'dosmax-activity-log-settings',
                array($this, 'display_settings_page')
            );
        }
    }
    
    /**
     * Restrict settings page access for site-admin users
     */
    public function restrict_settings_access() {
        // Check if we're on the settings page
        if (isset($_GET['page']) && $_GET['page'] === 'dosmax-activity-log-settings') {
            // Block access for site-admin only users
            if ($this->is_site_admin_only() || !current_user_can('activate_plugins')) {
                wp_die(__('You do not have sufficient permissions to access this page.', 'dosmax-activity-log'), 403);
            }
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Load scripts for main page for all users, settings page only for admins
        if ($hook !== 'toplevel_page_dosmax-activity-log' && 
            ($hook !== 'activity-log_page_dosmax-activity-log-settings' || $this->is_site_admin_only())) {
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
        
        // Get log details (includes occurrence and metadata)
        $log_data = $this->database->get_log_details($occurrence_id);
        
        if (isset($log_data['error'])) {
            wp_send_json_error($log_data['error']);
        }
        
        if (!isset($log_data['occurrence']) || !$log_data['occurrence']) {
            wp_send_json_error('Occurrence not found');
        }
        
        $occurrence = $log_data['occurrence'];
        $metadata = $log_data['metadata'] ?? array();
        
        // Format details for display with enhanced product information
        $details = array(
            'date' => $this->admin_page->format_custom_date($occurrence['created_on']),
            'user' => $occurrence['username'],
            'user_roles' => $occurrence['user_roles'],
            'ip' => $occurrence['client_ip'],
            'event_id' => $occurrence['alert_id'],
            'severity' => $occurrence['severity'],
            'object' => $occurrence['object'],
            'event_type' => $occurrence['event_type'],
            'site' => $occurrence['site_id'],
            'message' => $this->format_detailed_event_message($occurrence['alert_id'], $metadata, $occurrence),
            'metadata' => $metadata
        );
        
        wp_send_json_success($details);
    }
    
    /**
     * Format detailed event message with product information
     */
    private function format_detailed_event_message($alert_id, $metadata, $occurrence) {
        $message_parts = array();
        
        // Main event description
        switch ($alert_id) {
            case '2101':
                if (isset($metadata['PostTitle'])) {
                    $message_parts[] = 'Viewed the product <strong>' . esc_html($metadata['PostTitle']) . '</strong> page.';
                    
                    if (isset($metadata['PostID'])) {
                        $message_parts[] = 'Product ID: <strong>' . esc_html($metadata['PostID']) . '</strong>';
                    }
                    
                    if (isset($metadata['ProductSKU'])) {
                        $sku_value = $metadata['ProductSKU'] !== '' ? $metadata['ProductSKU'] : 'Not provided';
                        $message_parts[] = 'Product SKU: <strong>' . esc_html($sku_value) . '</strong>';
                    } else {
                        $message_parts[] = 'Product SKU: <strong>Not provided</strong>';
                    }
                    
                    if (isset($metadata['PostStatus'])) {
                        $message_parts[] = 'Product status: <strong>' . esc_html($metadata['PostStatus']) . '</strong>';
                    }
                    
                    if (isset($metadata['PostID'])) {
                        $edit_url = admin_url('post.php?post=' . $metadata['PostID'] . '&action=edit');
                        $message_parts[] = '<a href="' . esc_url($edit_url) . '" class="view-product-link">View product in editor</a>';
                    }
                }
                break;
                
            case '2100':
                if (isset($metadata['PostTitle'])) {
                    $message_parts[] = 'Opened the product <strong>' . esc_html($metadata['PostTitle']) . '</strong> in the editor.';
                    
                    if (isset($metadata['PostID'])) {
                        $message_parts[] = 'Product ID: <strong>' . esc_html($metadata['PostID']) . '</strong>';
                    }
                    
                    if (isset($metadata['ProductSKU'])) {
                        $sku_value = $metadata['ProductSKU'] !== '' ? $metadata['ProductSKU'] : 'Not provided';
                        $message_parts[] = 'Product SKU: <strong>' . esc_html($sku_value) . '</strong>';
                    } else {
                        $message_parts[] = 'Product SKU: <strong>Not provided</strong>';
                    }
                    
                    if (isset($metadata['PostStatus'])) {
                        $message_parts[] = 'Product status: <strong>' . esc_html($metadata['PostStatus']) . '</strong>';
                    }
                    
                    if (isset($metadata['PostID'])) {
                        $edit_url = admin_url('post.php?post=' . $metadata['PostID'] . '&action=edit');
                        $message_parts[] = '<a href="' . esc_url($edit_url) . '" class="view-product-link">View product in editor</a>';
                    }
                }
                break;
                
            case '6023':
                $message_parts[] = 'Was denied access to the page <strong>' . esc_html($metadata['RequestedURL'] ?? 'admin.php?page=dosmax-activity-log-settings') . '</strong>.';
                break;
                
            default:
                // Default message formatting
                $base_messages = array(
                    '1000' => 'User logged in',
                    '1001' => 'User logged out',
                    '2002' => 'User created a post revision',
                    '2065' => 'User modified a post',
                    '2086' => 'User changed post title',
                    '5001' => 'User activated a plugin',
                    '5002' => 'User deactivated a plugin',
                );
                
                $base_message = isset($base_messages[$alert_id]) ? $base_messages[$alert_id] : 'Activity logged';
                
                if (isset($metadata['PostTitle'])) {
                    $message_parts[] = $base_message . ': <strong>' . esc_html($metadata['PostTitle']) . '</strong>';
                } else {
                    $message_parts[] = $base_message;
                }
                break;
        }
        
        return implode('<br>', $message_parts);
    }
    
    /**
     * Check if current user is site-admin only
     */
    private function is_site_admin_only() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $user = wp_get_current_user();
        $user_roles = $user->roles;
        
        // Check if user has site-admin role and no other administrative roles
        // Also check if they don't have activate_plugins capability
        return (in_array('site-admin', $user_roles) && !in_array('administrator', $user_roles) && !in_array('super_admin', $user_roles)) 
               || !current_user_can('activate_plugins');
    }
    
    /**
     * Display settings page
     */
    public function display_settings_page() {
        // Check if user should have access to settings
        if ($this->is_site_admin_only() || !current_user_can('activate_plugins')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'dosmax-activity-log'));
        }
        
        // Handle form submission
        if ($_POST && wp_verify_nonce($_POST['dosmax_settings_nonce'], 'dosmax_activity_log_settings')) {
            $this->save_settings();
        }
        
        include DOSMAX_ACTIVITY_LOG_PLUGIN_DIR . 'templates/settings-page.php';
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        // Database settings
        if (isset($_POST['use_external_db'])) {
            update_option('dosmax_activity_log_use_external_db', true);
            update_option('dosmax_activity_log_db_host', sanitize_text_field($_POST['db_host']));
            update_option('dosmax_activity_log_db_name', sanitize_text_field($_POST['db_name']));
            update_option('dosmax_activity_log_db_user', sanitize_text_field($_POST['db_user']));
            update_option('dosmax_activity_log_db_password', $_POST['db_password']); // Don't sanitize password
            update_option('dosmax_activity_log_db_prefix', sanitize_text_field($_POST['db_prefix']));
        } else {
            update_option('dosmax_activity_log_use_external_db', false);
        }
        
        // Role settings
        $allowed_roles = array();
        if (isset($_POST['allowed_roles']) && is_array($_POST['allowed_roles'])) {
            foreach ($_POST['allowed_roles'] as $role) {
                $allowed_roles[] = sanitize_text_field($role);
            }
        }
        update_option('dosmax_activity_log_allowed_roles', $allowed_roles);
        
        $excluded_roles = array();
        if (isset($_POST['excluded_roles']) && is_array($_POST['excluded_roles'])) {
            foreach ($_POST['excluded_roles'] as $role) {
                $excluded_roles[] = sanitize_text_field($role);
            }
        }
        update_option('dosmax_activity_log_excluded_roles', $excluded_roles);
        
        add_action('admin_notices', array($this, 'settings_saved_notice'));
    }
    
    /**
     * Show settings saved notice
     */
    public function settings_saved_notice() {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully!', 'dosmax-activity-log') . '</p></div>';
    }
}
