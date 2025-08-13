<?php
// Basic WordPress configuration for demo
define('DB_NAME', 'dosmax_activity_log_demo');
define('DB_USER', 'demo');
define('DB_PASSWORD', 'demo');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

// WordPress Auth Keys (for demo only)
define('AUTH_KEY',         'demo-key');
define('SECURE_AUTH_KEY',  'demo-key');
define('LOGGED_IN_KEY',    'demo-key');
define('NONCE_KEY',        'demo-key');
define('AUTH_SALT',        'demo-salt');
define('SECURE_AUTH_SALT', 'demo-salt');
define('LOGGED_IN_SALT',   'demo-salt');
define('NONCE_SALT',       'demo-salt');

// WordPress table prefix
$table_prefix = 'wp_';

// WordPress debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Absolute path to WordPress directory
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// WordPress settings
require_once(ABSPATH . 'wp-settings.php');