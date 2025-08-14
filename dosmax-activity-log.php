<?php
/**
 * Plugin Name: Dosmax Activity Log
 * Plugin URI: https://example.com/dosmax-activity-log
 * Description: Custom activity log display with role-based filtering using existing WP Activity Log database tables.
 * Version: 1.0.0
 * Author: Dosmax
 * License: GPL v2 or later
 * Text Domain: dosmax-activity-log
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DOSMAX_ACTIVITY_LOG_VERSION', '1.0.0');
define('DOSMAX_ACTIVITY_LOG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOSMAX_ACTIVITY_LOG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once DOSMAX_ACTIVITY_LOG_PLUGIN_DIR . 'includes/class-dosmax-activity-log.php';
require_once DOSMAX_ACTIVITY_LOG_PLUGIN_DIR . 'includes/class-dosmax-admin-page.php';
require_once DOSMAX_ACTIVITY_LOG_PLUGIN_DIR . 'includes/class-dosmax-database.php';

/**
 * Main plugin class initialization
 */
function dosmax_activity_log_init() {
    $plugin = new Dosmax_Activity_Log();
    $plugin->init();
}

// Initialize plugin
add_action('plugins_loaded', 'dosmax_activity_log_init');

/**
 * Plugin activation hook
 */
function dosmax_activity_log_activate() {
    try {
        // Set default options
        add_option('dosmax_activity_log_version', DOSMAX_ACTIVITY_LOG_VERSION);
        add_option('dosmax_activity_log_excluded_roles', array('administrator'));
        add_option('dosmax_activity_log_allowed_roles', array('site-admin'));
        
        // Database configuration options
        add_option('dosmax_activity_log_db_host', defined('DB_HOST') ? DB_HOST : 'localhost');
        add_option('dosmax_activity_log_db_name', defined('DB_NAME') ? DB_NAME : '');
        add_option('dosmax_activity_log_db_user', defined('DB_USER') ? DB_USER : '');
        add_option('dosmax_activity_log_db_password', defined('DB_PASSWORD') ? DB_PASSWORD : '');
        add_option('dosmax_activity_log_db_prefix', 'wp_');
        add_option('dosmax_activity_log_use_external_db', false);
        
        // Check if WP Activity Log tables exist (only if using current DB)
        if (!get_option('dosmax_activity_log_use_external_db', false)) {
            global $wpdb;
            
            // Check if $wpdb is available
            if (!isset($wpdb)) {
                return; // Gracefully skip table check during activation
            }
            
            $table_name = $wpdb->prefix . 'wsal_occurrences';
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            
            // Only show warning if tables don't exist, don't block activation
            if (!$table_exists) {
                // Store a notice to show later instead of blocking activation
                add_option('dosmax_activity_log_activation_notice', 'WP Activity Log tables not found. Please install WP Activity Log plugin or configure external database settings.');
            } else {
                // Try to add index, ignore errors if index already exists
                $wpdb->query("ALTER TABLE {$table_name} ADD INDEX IF NOT EXISTS idx_user_roles (user_roles)");
            }
        }
        
        // Flush rewrite rules
        if (function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules();
        }
        
    } catch (Exception $e) {
        // Log error but don't block activation
        error_log('Dosmax Activity Log activation error: ' . $e->getMessage());
    }
}

/**
 * Plugin deactivation hook
 */
function dosmax_activity_log_deactivate() {
    // Clean up if needed
    delete_option('dosmax_activity_log_version');
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'dosmax_activity_log_activate');
register_deactivation_hook(__FILE__, 'dosmax_activity_log_deactivate');
