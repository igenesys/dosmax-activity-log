<?php
/**
 * Settings page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$use_external_db = get_option('dosmax_activity_log_use_external_db', false);
$db_host = get_option('dosmax_activity_log_db_host', DB_HOST);
$db_name = get_option('dosmax_activity_log_db_name', DB_NAME);
$db_user = get_option('dosmax_activity_log_db_user', DB_USER);
$db_password = get_option('dosmax_activity_log_db_password', '');
$db_prefix = get_option('dosmax_activity_log_db_prefix', 'wp_');
$allowed_roles = get_option('dosmax_activity_log_allowed_roles', array('site-admin'));
$excluded_roles = get_option('dosmax_activity_log_excluded_roles', array('administrator'));

// Test database connection
$database = new Dosmax_Database();
$connection_test = $database->test_connection();
?>

<div class="wrap">
    <h1><?php _e('Dosmax Activity Log Settings', 'dosmax-activity-log'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('dosmax_activity_log_settings', 'dosmax_settings_nonce'); ?>
        
        <table class="form-table">
            <!-- Database Configuration -->
            <tr>
                <th scope="row"><?php _e('Database Configuration', 'dosmax-activity-log'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="use_external_db" value="1" <?php checked($use_external_db, true); ?> id="use_external_db">
                            <?php _e('Use external database', 'dosmax-activity-log'); ?>
                        </label>
                        <p class="description"><?php _e('Check this option to connect to an external database instead of the current WordPress database.', 'dosmax-activity-log'); ?></p>
                    </fieldset>
                </td>
            </tr>
            
            <tr id="external_db_settings" style="<?php echo $use_external_db ? '' : 'display: none;'; ?>">
                <th scope="row"><?php _e('External Database Details', 'dosmax-activity-log'); ?></th>
                <td>
                    <table class="widefat">
                        <tr>
                            <td><label for="db_host"><?php _e('Database Host:', 'dosmax-activity-log'); ?></label></td>
                            <td><input type="text" id="db_host" name="db_host" value="<?php echo esc_attr($db_host); ?>" class="regular-text" placeholder="localhost"></td>
                        </tr>
                        <tr>
                            <td><label for="db_name"><?php _e('Database Name:', 'dosmax-activity-log'); ?></label></td>
                            <td><input type="text" id="db_name" name="db_name" value="<?php echo esc_attr($db_name); ?>" class="regular-text" placeholder="database_name"></td>
                        </tr>
                        <tr>
                            <td><label for="db_user"><?php _e('Database User:', 'dosmax-activity-log'); ?></label></td>
                            <td><input type="text" id="db_user" name="db_user" value="<?php echo esc_attr($db_user); ?>" class="regular-text" placeholder="username"></td>
                        </tr>
                        <tr>
                            <td><label for="db_password"><?php _e('Database Password:', 'dosmax-activity-log'); ?></label></td>
                            <td><input type="password" id="db_password" name="db_password" value="<?php echo esc_attr($db_password); ?>" class="regular-text" placeholder="password"></td>
                        </tr>
                        <tr>
                            <td><label for="db_prefix"><?php _e('Table Prefix:', 'dosmax-activity-log'); ?></label></td>
                            <td><input type="text" id="db_prefix" name="db_prefix" value="<?php echo esc_attr($db_prefix); ?>" class="regular-text" placeholder="wp_"></td>
                        </tr>
                    </table>
                    <p class="description"><?php _e('Enter the connection details for your external database containing the WP Activity Log tables.', 'dosmax-activity-log'); ?></p>
                </td>
            </tr>
            
            <!-- Connection Status -->
            <tr>
                <th scope="row"><?php _e('Connection Status', 'dosmax-activity-log'); ?></th>
                <td>
                    <div class="connection-status">
                        <?php if ($connection_test['success']): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                            <span style="color: #46b450;"><?php echo esc_html($connection_test['message']); ?></span>
                        <?php else: ?>
                            <span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
                            <span style="color: #dc3232;"><?php echo esc_html($connection_test['message']); ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="description"><?php _e('This shows the current status of your database connection and table verification.', 'dosmax-activity-log'); ?></p>
                </td>
            </tr>
            
            <!-- Role Configuration -->
            <tr>
                <th scope="row"><?php _e('Allowed User Roles', 'dosmax-activity-log'); ?></th>
                <td>
                    <textarea name="allowed_roles[]" rows="3" class="large-text" placeholder="site-admin"><?php echo esc_textarea(implode("\n", $allowed_roles)); ?></textarea>
                    <p class="description"><?php _e('Enter user roles to display (one per line). Only activities from these roles will be shown.', 'dosmax-activity-log'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Excluded User Roles', 'dosmax-activity-log'); ?></th>
                <td>
                    <textarea name="excluded_roles[]" rows="3" class="large-text" placeholder="administrator"><?php echo esc_textarea(implode("\n", $excluded_roles)); ?></textarea>
                    <p class="description"><?php _e('Enter user roles to hide (one per line). Activities from these roles will be completely filtered out at the database level.', 'dosmax-activity-log'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Save Settings', 'dosmax-activity-log')); ?>
    </form>
    
    <!-- Documentation Section -->
    <div style="margin-top: 40px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 3px;">
        <h3><?php _e('Configuration Guide', 'dosmax-activity-log'); ?></h3>
        
        <h4><?php _e('External Database Setup', 'dosmax-activity-log'); ?></h4>
        <p><?php _e('When using an external database, ensure that:', 'dosmax-activity-log'); ?></p>
        <ul>
            <li><?php _e('The database contains the WP Activity Log tables (*_wsal_occurrences and *_wsal_metadata)', 'dosmax-activity-log'); ?></li>
            <li><?php _e('The specified user has SELECT permissions on these tables', 'dosmax-activity-log'); ?></li>
            <li><?php _e('The table prefix matches your actual table naming (e.g., "wp_" for wp_wsal_occurrences)', 'dosmax-activity-log'); ?></li>
            <li><?php _e('For multisite installations, include the site ID in the prefix (e.g., "wp_2_" for site ID 2)', 'dosmax-activity-log'); ?></li>
        </ul>
        
        <h4><?php _e('Role Filtering', 'dosmax-activity-log'); ?></h4>
        <p><?php _e('Role filtering is applied at the database level for security and performance:', 'dosmax-activity-log'); ?></p>
        <ul>
            <li><?php _e('Allowed roles: Activities from these roles will be displayed', 'dosmax-activity-log'); ?></li>
            <li><?php _e('Excluded roles: Activities from these roles will be hidden completely', 'dosmax-activity-log'); ?></li>
            <li><?php _e('If both allowed and excluded roles are specified, both conditions must be met', 'dosmax-activity-log'); ?></li>
            <li><?php _e('Use exact role names as they appear in your WordPress user roles', 'dosmax-activity-log'); ?></li>
        </ul>
        
        <h4><?php _e('Sample Table Prefixes', 'dosmax-activity-log'); ?></h4>
        <ul>
            <li><code>wp_</code> - <?php _e('Standard WordPress installation', 'dosmax-activity-log'); ?></li>
            <li><code>custom_</code> - <?php _e('Custom prefix installation', 'dosmax-activity-log'); ?></li>
            <li><code>hdvs_20_siteshowroom_nl_</code> - <?php _e('Complex multisite prefix (as in your sample data)', 'dosmax-activity-log'); ?></li>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#use_external_db').change(function() {
        if ($(this).is(':checked')) {
            $('#external_db_settings').show();
        } else {
            $('#external_db_settings').hide();
        }
    });
});
</script>

<style>
.connection-status {
    padding: 10px;
    border-radius: 3px;
    margin-bottom: 10px;
}

.connection-status .dashicons {
    vertical-align: middle;
    margin-right: 5px;
}

#external_db_settings table.widefat {
    max-width: 600px;
}

#external_db_settings table.widefat td {
    padding: 8px;
    border-bottom: 1px solid #ddd;
}

#external_db_settings table.widefat td:first-child {
    width: 150px;
    font-weight: 600;
}

code {
    background: #f1f1f1;
    padding: 2px 4px;
    border-radius: 2px;
    font-family: 'Courier New', monospace;
}
</style>