<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosmax Activity Log - WordPress Plugin Demo</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f1f1f1;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.13);
        }
        
        .header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            color: #23282d;
            font-size: 23px;
            font-weight: 400;
        }
        
        .header p {
            margin: 10px 0 0;
            color: #666;
        }
        
        .plugin-info {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-left: 4px solid #0073aa;
            padding: 15px;
            margin-bottom: 30px;
        }
        
        .plugin-info h3 {
            margin-top: 0;
            color: #0073aa;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .feature {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 3px;
            border-left: 4px solid #46b450;
        }
        
        .feature h4 {
            margin-top: 0;
            color: #46b450;
        }
        
        .code-preview {
            background: #2d3748;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 3px;
            overflow-x: auto;
            margin: 20px 0;
        }
        
        .code-preview pre {
            margin: 0;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .file-structure {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 3px;
            margin: 20px 0;
        }
        
        .file-tree {
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .demo-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #fdcb6e;
            padding: 15px;
            margin: 20px 0;
        }
        
        .demo-note h4 {
            margin-top: 0;
            color: #d68910;
        }
        
        .button {
            display: inline-block;
            padding: 8px 16px;
            background: #0073aa;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            margin: 5px 5px 5px 0;
        }
        
        .button:hover {
            background: #005177;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dosmax Activity Log</h1>
            <p>Custom WordPress Plugin for Activity Logging with Role-Based Filtering</p>
        </div>
        
        <div class="plugin-info">
            <h3>Plugin Overview</h3>
            <p>The Dosmax Activity Log plugin provides a custom activity log display that integrates with WP Activity Log's database tables. It offers advanced role-based filtering to show only activities from specific user roles while completely hiding activities from other roles.</p>
        </div>
        
        <div class="features">
            <div class="feature">
                <h4>Role-Based Filtering</h4>
                <p>Displays events only for users with <strong>site-admin</strong> role and completely hides events from <strong>administrator</strong> users at the database query level for enhanced security.</p>
            </div>
            
            <div class="feature">
                <h4>Database Integration</h4>
                <p>Uses existing WP Activity Log tables (*_wsal_occurrences and *_wsal_metadata) with proper joins to display comprehensive event information including severity, date, user, IP, object, and event type.</p>
            </div>
            
            <div class="feature">
                <h4>Enhanced Performance</h4>
                <p>Includes database indexing on user_roles column for faster filtering and supports pagination and sorting for optimal performance with large datasets.</p>
            </div>
            
            <div class="feature">
                <h4>Detailed Event View</h4>
                <p>Features "More details..." links that load comprehensive event metadata via AJAX, showing additional information like post titles, URLs, user agents, and custom metadata.</p>
            </div>
        </div>
        
        <h3>Plugin Architecture</h3>
        <div class="file-structure">
            <div class="file-tree">
dosmax-activity-log/
├── dosmax-activity-log.php          # Main plugin file
├── includes/
│   ├── class-dosmax-activity-log.php    # Core plugin class
│   ├── class-dosmax-admin-page.php      # Admin interface
│   └── class-dosmax-database.php        # Database operations
├── templates/
│   └── admin-page.php                    # Admin page template
└── assets/
    ├── css/
    │   └── admin-style.css              # Admin styles
    └── js/
        └── admin-script.js              # AJAX interactions
            </div>
        </div>
        
        <h3>Key Features Implementation</h3>
        
        <h4>1. Role-Based Database Filtering</h4>
        <div class="code-preview">
            <pre>// Example query with role filtering
$role_conditions = array();
foreach ($allowed_roles as $role) {
    $role_conditions[] = $wpdb->prepare("user_roles LIKE %s", '%' . $role . '%');
}

$exclude_conditions = array();
foreach ($excluded_roles as $role) {
    $exclude_conditions[] = $wpdb->prepare("user_roles NOT LIKE %s", '%' . $role . '%');
}

$where_clause = 'WHERE (' . implode(' OR ', $role_conditions) . ')';
if (!empty($exclude_conditions)) {
    $where_clause .= ' AND (' . implode(' AND ', $exclude_conditions) . ')';
}</pre>
        </div>
        
        <h4>2. AJAX Detail Loading</h4>
        <div class="code-preview">
            <pre>// JavaScript AJAX call for loading detailed information
$.post(dosmax_ajax.ajax_url, {
    action: 'dosmax_get_log_details',
    occurrence_id: occurrenceId,
    nonce: dosmax_ajax.nonce
})
.done(function(response) {
    if (response.success && response.data) {
        $detailsContent.html(formatLogDetails(response.data));
    }
});</pre>
        </div>
        
        <h4>3. Database Structure Compatibility</h4>
        <div class="code-preview">
            <pre>// Sample database structure from WP Activity Log
CREATE TABLE `wp_wsal_occurrences` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `site_id` bigint(20) NOT NULL,
  `alert_id` bigint(20) NOT NULL,
  `created_on` double NOT NULL,
  `client_ip` varchar(255) NOT NULL,
  `severity` varchar(255) NOT NULL,
  `object` varchar(255) NOT NULL,
  `event_type` varchar(255) NOT NULL,
  `user_roles` varchar(255) NOT NULL,
  `username` varchar(60) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_roles` (`user_roles`)
);</pre>
        </div>
        
        <div class="demo-note">
            <h4>WordPress Installation Required</h4>
            <p>This plugin is designed to work within a WordPress environment. To see the full functionality:</p>
            <ol>
                <li>Install the plugin in a WordPress site with WP Activity Log active</li>
                <li>Navigate to <strong>Tools → Dosmax Activity Log</strong> in the WordPress admin</li>
                <li>The plugin will display filtered activity logs based on user roles</li>
                <li>Click "More details..." on any log entry to view comprehensive event information</li>
            </ol>
        </div>
        
        <h3>Sample Data Integration</h3>
        <p>The plugin is designed to work with the provided SQL sample data, which includes:</p>
        <ul>
            <li><strong>Event Types:</strong> Post modifications, plugin activations, user logins, page views</li>
            <li><strong>Severity Levels:</strong> 200 (Low), 250 (Medium), 400 (Critical)</li>
            <li><strong>User Roles:</strong> Administrator users (filtered out), site-admin users (displayed)</li>
            <li><strong>Metadata:</strong> Post titles, URLs, revision links, plugin data</li>
        </ul>
        
        <h3>Configuration Options</h3>
        <div class="code-preview">
            <pre>// Default plugin settings
add_option('dosmax_activity_log_excluded_roles', array('administrator'));
add_option('dosmax_activity_log_allowed_roles', array('site-admin'));

// Customizable role filtering
$allowed_roles = get_option('dosmax_activity_log_allowed_roles', array('site-admin'));
$excluded_roles = get_option('dosmax_activity_log_excluded_roles', array('administrator'));</pre>
        </div>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666;">
            <p>Dosmax Activity Log v1.0.0 - WordPress Plugin for Enhanced Activity Monitoring</p>
        </div>
    </div>
</body>
</html>