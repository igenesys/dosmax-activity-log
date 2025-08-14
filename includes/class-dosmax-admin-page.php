<?php
/**
 * Admin page class
 */
class Dosmax_Admin_Page {
    
    private $database;
    private $per_page = 100;
    
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
     * Get severity level information (icon and color)
     */
    public function get_severity_level($severity) {
        $levels = array(
            '100' => array('icon' => 'warning', 'color' => '#d63384'),  // Critical
            '200' => array('icon' => 'yes', 'color' => '#00a32a'),      // Low  
            '300' => array('icon' => 'info', 'color' => '#0073aa'),     // Medium
            '400' => array('icon' => 'dismiss', 'color' => '#d63384'),  // High
            '500' => array('icon' => 'warning', 'color' => '#d63384'),  // Critical
        );
        
        return isset($levels[$severity]) ? $levels[$severity] : array('icon' => 'info', 'color' => '#0073aa');
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
        
        // Handle both Unix timestamp and MySQL datetime string
        if (is_numeric($timestamp)) {
            $unix_time = $timestamp;
        } else {
            $unix_time = strtotime($timestamp);
        }
        
        if ($unix_time === false) {
            return 'Invalid date';
        }
        
        // Format date as dd.mm.yyyy
        $formatted_date = date('d.m.Y', $unix_time);
        
        // Format time as h:mm:ss.000 am/pm
        $formatted_time = date('g:i:s', $unix_time) . '.000 ' . date('a', $unix_time);
        
        return $formatted_date . '<br>' . $formatted_time;
    }
    
    /**
     * Generate pagination links
     */
    public function pagination_links($current_page, $total_pages, $total_items = null) {
        if ($total_pages <= 1) {
            return '';
        }
        
        $pagination = '<div class="tablenav-pages">';
        $pagination .= '<span class="displaying-num">' . sprintf(__('%d items', 'dosmax-activity-log'), $total_items ?: $this->database->get_total_log_count()) . '</span>';
        
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
    
    /**
     * Format detailed message for the Message column - shows all essential info by default
     */
    public function format_detailed_message_for_column($log) {
        // Get metadata for this log entry
        $metadata = $this->database->get_occurrence_metadata($log['id']);
        
        $message_parts = array();
        
        // Main event description based on alert_id - comprehensive coverage
        switch ($log['alert_id']) {
            // Post/Page Related Events
            case '2101': // Post viewed
                if (isset($metadata['PostTitle'])) {
                    $message_parts[] = 'Viewed the post ' . esc_html($metadata['PostTitle']) . '.';
                } else {
                    $message_parts[] = 'User viewed a post.';
                }
                break;
                
            case '2100': // Post opened in editor
                if (isset($metadata['PostTitle'])) {
                    $message_parts[] = 'Opened the post ' . esc_html($metadata['PostTitle']) . ' in the editor.';
                } else {
                    $message_parts[] = 'User opened a post in the editor.';
                }
                break;
                
            case '2065': // Post modified
                if (isset($metadata['PostTitle'])) {
                    $message_parts[] = 'Modified the post ' . esc_html($metadata['PostTitle']) . '.';
                } else {
                    $message_parts[] = 'User modified a post.';
                }
                break;
                
            case '2086': // Post title changed
                if (isset($metadata['OldTitle']) && isset($metadata['NewTitle'])) {
                    $message_parts[] = 'Changed post title from "' . esc_html($metadata['OldTitle']) . '" to "' . esc_html($metadata['NewTitle']) . '".';
                } elseif (isset($metadata['PostTitle'])) {
                    $message_parts[] = 'Changed post title: ' . esc_html($metadata['PostTitle']) . '.';
                } else {
                    $message_parts[] = 'User changed post title.';
                }
                break;
                
            case '2002': // Post revision created
                if (isset($metadata['PostTitle'])) {
                    $message_parts[] = 'Created a revision of the post ' . esc_html($metadata['PostTitle']) . '.';
                    if (isset($metadata['RevisionLink'])) {
                        $message_parts[] = '<a href="' . esc_url($metadata['RevisionLink']) . '" target="_blank" style="color: #0073aa; text-decoration: none;">View revision</a>';
                    }
                } else {
                    $message_parts[] = 'User created a post revision.';
                }
                break;
                
            // File Upload Events
            case '2010': // File uploaded
                if (isset($metadata['FileName'])) {
                    $message_parts[] = 'Uploaded a file called ' . esc_html($metadata['FileName']) . '.';
                    if (isset($metadata['FilePath'])) {
                        $message_parts[] = '<strong>Directory:</strong> ' . esc_html($metadata['FilePath']);
                    }
                    if (isset($metadata['AttachmentURL'])) {
                        $message_parts[] = '<a href="' . esc_url($metadata['AttachmentURL']) . '" target="_blank" style="color: #0073aa; text-decoration: none;">View attachment page</a>';
                    }
                } else {
                    $message_parts[] = 'User uploaded a file.';
                }
                break;
                
            // Plugin Events
            case '5001': // Plugin activated
                if (isset($metadata['PluginData'])) {
                    $plugin_data = is_string($metadata['PluginData']) ? unserialize($metadata['PluginData']) : $metadata['PluginData'];
                    if (is_array($plugin_data) && isset($plugin_data['Name'])) {
                        $message_parts[] = 'Activated the plugin ' . esc_html($plugin_data['Name']) . '.';
                        if (isset($plugin_data['Version'])) {
                            $message_parts[] = '<strong>Version:</strong> ' . esc_html($plugin_data['Version']);
                        }
                    } else {
                        $message_parts[] = 'Activated a plugin.';
                    }
                    if (isset($metadata['PluginFile'])) {
                        $message_parts[] = '<strong>Install location:</strong> ' . esc_html($metadata['PluginFile']);
                    }
                } else {
                    $message_parts[] = 'User activated a plugin.';
                }
                break;
                
            case '5002': // Plugin deactivated
                if (isset($metadata['PluginData'])) {
                    $plugin_data = is_string($metadata['PluginData']) ? unserialize($metadata['PluginData']) : $metadata['PluginData'];
                    if (is_array($plugin_data) && isset($plugin_data['Name'])) {
                        $message_parts[] = 'Deactivated the plugin ' . esc_html($plugin_data['Name']) . '.';
                    } else {
                        $message_parts[] = 'Deactivated a plugin.';
                    }
                } else {
                    $message_parts[] = 'User deactivated a plugin.';
                }
                break;
                
            case '5010': // Plugin updated
                if (isset($metadata['PluginData'])) {
                    $plugin_data = is_string($metadata['PluginData']) ? unserialize($metadata['PluginData']) : $metadata['PluginData'];
                    if (is_array($plugin_data) && isset($plugin_data['Name'])) {
                        $message_parts[] = 'Updated the plugin ' . esc_html($plugin_data['Name']) . '.';
                        if (isset($metadata['OldVersion']) && isset($plugin_data['Version'])) {
                            $message_parts[] = '<strong>Version:</strong> ' . esc_html($metadata['OldVersion']) . ' → ' . esc_html($plugin_data['Version']);
                        }
                    } else {
                        $message_parts[] = 'Updated a plugin.';
                    }
                } else {
                    $message_parts[] = 'User updated a plugin.';
                }
                break;
                
            // User Events
            case '1000': // User logged in
                $message_parts[] = 'User logged in.';
                break;
                
            case '1001': // User logged out
                $message_parts[] = 'User logged out.';
                break;
                
            case '1002': // Failed login
                if (isset($metadata['Users'])) {
                    $message_parts[] = 'Failed login attempt for user "' . esc_html($metadata['Users']) . '".';
                    if (isset($metadata['Attempts'])) {
                        $message_parts[] = '<strong>Attempts:</strong> ' . esc_html($metadata['Attempts']);
                    }
                } else {
                    $message_parts[] = 'Failed login attempt.';
                }
                break;
                
            case '4000': // New user registered
                if (isset($metadata['NewUserData'])) {
                    $user_data = is_string($metadata['NewUserData']) ? unserialize($metadata['NewUserData']) : $metadata['NewUserData'];
                    if (is_array($user_data) && isset($user_data['Username'])) {
                        $message_parts[] = 'New user "' . esc_html($user_data['Username']) . '" was registered.';
                        if (isset($user_data['FirstName']) && isset($user_data['LastName'])) {
                            $message_parts[] = '<strong>Name:</strong> ' . esc_html($user_data['FirstName'] . ' ' . $user_data['LastName']);
                        }
                        if (isset($user_data['Email'])) {
                            $message_parts[] = '<strong>Email:</strong> ' . esc_html($user_data['Email']);
                        }
                        if (isset($user_data['Roles'])) {
                            $message_parts[] = '<strong>Role:</strong> ' . esc_html($user_data['Roles']);
                        }
                    } else {
                        $message_parts[] = 'A new user was registered.';
                    }
                } else {
                    $message_parts[] = 'A new user was registered.';
                }
                break;
                
            case '4001': // User profile updated
                if (isset($metadata['TargetUsername'])) {
                    $message_parts[] = 'Updated profile for user "' . esc_html($metadata['TargetUsername']) . '".';
                    if (isset($metadata['custom_field_name']) && isset($metadata['new_value'])) {
                        $message_parts[] = '<strong>Field:</strong> ' . esc_html($metadata['custom_field_name']) . ' = ' . esc_html($metadata['new_value']);
                    }
                } else {
                    $message_parts[] = 'Updated a user profile.';
                }
                break;
                
            // Admin Page Events
            case '6023': // Admin page access denied
                if (isset($metadata['URL'])) {
                    $message_parts[] = 'Was denied access to admin page "' . esc_html($metadata['URL']) . '".';
                } else {
                    $message_parts[] = 'Was denied access to an admin page.';
                }
                break;
                
            case '6000': // Admin page visited
                if (isset($metadata['URL'])) {
                    $message_parts[] = 'Visited admin page "' . esc_html($metadata['URL']) . '".';
                } else {
                    $message_parts[] = 'Visited an admin page.';
                }
                break;
                
            // IP Address Events  
            case '6008': // Blocked IP attempt
                if (isset($metadata['IPAddress'])) {
                    $ip_data = is_string($metadata['IPAddress']) ? unserialize($metadata['IPAddress']) : $metadata['IPAddress'];
                    if (is_array($ip_data) && !empty($ip_data)) {
                        $message_parts[] = 'Blocked access attempt from IP: ' . esc_html(implode(', ', $ip_data)) . '.';
                    } else {
                        $message_parts[] = 'Blocked access attempt.';
                    }
                } else {
                    $message_parts[] = 'Blocked access attempt.';
                }
                break;
                
            default:
                // Generic fallback with better handling
                $base_messages = array(
                    '2003' => 'User created a post',
                    '2004' => 'User published a post', 
                    '2005' => 'User moved post to trash',
                    '2031' => 'User created a page',
                    '2032' => 'User published a page',
                    '2034' => 'User moved page to trash',
                    '9999' => 'Activity logged'
                );
                
                $base_message = isset($base_messages[$log['alert_id']]) ? $base_messages[$log['alert_id']] : 'Activity logged';
                
                if (isset($metadata['PostTitle'])) {
                    $message_parts[] = $base_message . ': ' . esc_html($metadata['PostTitle']) . '.';
                } elseif (isset($metadata['FileName'])) {
                    $message_parts[] = $base_message . ': ' . esc_html($metadata['FileName']) . '.';
                } else {
                    $message_parts[] = $base_message . '.';
                }
                break;
        }
        
        // Add essential details for post-related activities
        if (in_array($log['alert_id'], array('2100', '2101', '2065', '2086', '2002', '2003', '2004', '2005', '2031', '2032', '2034'))) {
            if (isset($metadata['PostID'])) {
                $message_parts[] = '<strong>Post ID:</strong> ' . esc_html($metadata['PostID']);
            }
            if (isset($metadata['PostType'])) {
                $message_parts[] = '<strong>Post type:</strong> ' . esc_html($metadata['PostType']);
            } else if (!empty($log['object'])) {
                $message_parts[] = '<strong>Post type:</strong> ' . esc_html($log['object']);
            }
            if (isset($metadata['PostStatus'])) {
                $message_parts[] = '<strong>Post status:</strong> ' . esc_html($metadata['PostStatus']);
            }
            
            // Add links
            if (isset($metadata['PostUrl'])) {
                $message_parts[] = '<a href="' . esc_url($metadata['PostUrl']) . '" target="_blank" style="color: #0073aa; text-decoration: none;">URL</a>';
            }
            if (isset($metadata['EditorLinkPost'])) {
                $message_parts[] = '<a href="' . esc_url($metadata['EditorLinkPost']) . '" target="_blank" style="color: #0073aa; text-decoration: none;">View the post in editor</a>';
            } else if (isset($metadata['PostID'])) {
                $edit_url = admin_url('post.php?post=' . $metadata['PostID'] . '&action=edit');
                $message_parts[] = '<a href="' . esc_url($edit_url) . '" target="_blank" style="color: #0073aa; text-decoration: none;">View the post in editor</a>';
            }
        }
        
        return implode('<br>', $message_parts);
    }
}
