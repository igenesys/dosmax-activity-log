<?php
/**
 * Admin page template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Dosmax Activity Log', 'dosmax-activity-log'); ?></h1>
    
    <div class="dosmax-activity-log-container">
        
        <!-- Filters Section -->
        <div class="dosmax-filters-container">
            <form method="get" id="dosmax-filters-form">
                <input type="hidden" name="page" value="dosmax-activity-log" />
                
                <div class="filters-row">
                    <!-- User Filter -->
                    <div class="filter-group">
                        <label for="filter-user"><?php _e('User:', 'dosmax-activity-log'); ?></label>
                        <select name="filter_user" id="filter-user">
                            <option value=""><?php _e('All Users', 'dosmax-activity-log'); ?></option>
                            <?php foreach ($available_users as $username) : ?>
                                <option value="<?php echo esc_attr($username); ?>" <?php selected($filter_user, $username); ?>>
                                    <?php echo esc_html($username); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Object Filter -->
                    <div class="filter-group">
                        <label for="filter-object"><?php _e('Object:', 'dosmax-activity-log'); ?></label>
                        <select name="filter_object" id="filter-object">
                            <option value=""><?php _e('All Objects', 'dosmax-activity-log'); ?></option>
                            <?php foreach ($available_objects as $object) : ?>
                                <option value="<?php echo esc_attr($object); ?>" <?php selected($filter_object, $object); ?>>
                                    <?php echo esc_html(ucfirst($object)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- IP Address Filter -->
                    <div class="filter-group">
                        <label for="filter-ip"><?php _e('IP Address:', 'dosmax-activity-log'); ?></label>
                        <input type="text" name="filter_ip" id="filter-ip" value="<?php echo esc_attr($filter_ip); ?>" placeholder="<?php _e('Enter IP address', 'dosmax-activity-log'); ?>" />
                    </div>
                </div>
                
                <div class="filters-row">
                    <!-- Date Filter Type -->
                    <div class="filter-group">
                        <label for="date-filter-type"><?php _e('Date Filter:', 'dosmax-activity-log'); ?></label>
                        <select name="date_filter_type" id="date-filter-type">
                            <option value=""><?php _e('All Dates', 'dosmax-activity-log'); ?></option>
                            <option value="before" <?php selected($date_filter_type, 'before'); ?>><?php _e('Before Date', 'dosmax-activity-log'); ?></option>
                            <option value="after" <?php selected($date_filter_type, 'after'); ?>><?php _e('After Date', 'dosmax-activity-log'); ?></option>
                            <option value="on" <?php selected($date_filter_type, 'on'); ?>><?php _e('On Date', 'dosmax-activity-log'); ?></option>
                        </select>
                    </div>
                    
                    <!-- Date Picker -->
                    <div class="filter-group">
                        <label for="filter-date"><?php _e('Date:', 'dosmax-activity-log'); ?></label>
                        <input type="date" name="filter_date" id="filter-date" value="<?php echo esc_attr($filter_date); ?>" />
                    </div>
                    
                    <!-- Filter Actions -->
                    <div class="filter-group filter-actions">
                        <input type="submit" class="button" value="<?php _e('Apply Filters', 'dosmax-activity-log'); ?>" />
                        <a href="<?php echo admin_url('admin.php?page=dosmax-activity-log'); ?>" class="button"><?php _e('Clear Filters', 'dosmax-activity-log'); ?></a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Results Summary -->
        <?php if ($has_active_filters) : ?>
        <div class="filter-summary">
            <p><strong><?php _e('Active Filters:', 'dosmax-activity-log'); ?></strong>
                <?php if ($filter_user) : ?>
                    <span class="filter-tag"><?php printf(__('User: %s', 'dosmax-activity-log'), $filter_user); ?></span>
                <?php endif; ?>
                <?php if ($filter_object) : ?>
                    <span class="filter-tag"><?php printf(__('Object: %s', 'dosmax-activity-log'), $filter_object); ?></span>
                <?php endif; ?>
                <?php if ($filter_ip) : ?>
                    <span class="filter-tag"><?php printf(__('IP: %s', 'dosmax-activity-log'), $filter_ip); ?></span>
                <?php endif; ?>
                <?php if ($date_filter_type && $filter_date) : ?>
                    <span class="filter-tag"><?php printf(__('Date: %s %s', 'dosmax-activity-log'), ucfirst($date_filter_type), $filter_date); ?></span>
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Pagination top -->
        <div class="tablenav top">
            <?php echo $this->pagination_links($current_page, $total_pages, $total_items); ?>
        </div>
        
        <!-- Activity log table -->
        <table class="wp-list-table widefat fixed striped" id="dosmax-activity-log-table">
            <thead>
                <tr>
                    <th scope="col" class="column-severity">
                        <a href="<?php echo add_query_arg(array('orderby' => 'severity', 'order' => $order === 'ASC' ? 'DESC' : 'ASC')); ?>">
                            <?php _e('Severity', 'dosmax-activity-log'); ?>
                            <?php if ($orderby === 'severity') : ?>
                                <span class="sorting-indicator <?php echo $order === 'ASC' ? 'asc' : 'desc'; ?>"></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th scope="col" class="column-date">
                        <a href="<?php echo add_query_arg(array('orderby' => 'created_on', 'order' => $order === 'ASC' ? 'DESC' : 'ASC')); ?>">
                            <?php _e('Date', 'dosmax-activity-log'); ?>
                            <?php if ($orderby === 'created_on') : ?>
                                <span class="sorting-indicator <?php echo $order === 'ASC' ? 'asc' : 'desc'; ?>"></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th scope="col" class="column-user">
                        <a href="<?php echo add_query_arg(array('orderby' => 'username', 'order' => $order === 'ASC' ? 'DESC' : 'ASC')); ?>">
                            <?php _e('User', 'dosmax-activity-log'); ?>
                            <?php if ($orderby === 'username') : ?>
                                <span class="sorting-indicator <?php echo $order === 'ASC' ? 'asc' : 'desc'; ?>"></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th scope="col" class="column-ip">
                        <a href="<?php echo add_query_arg(array('orderby' => 'client_ip', 'order' => $order === 'ASC' ? 'DESC' : 'ASC')); ?>">
                            <?php _e('IP', 'dosmax-activity-log'); ?>
                            <?php if ($orderby === 'client_ip') : ?>
                                <span class="sorting-indicator <?php echo $order === 'ASC' ? 'asc' : 'desc'; ?>"></span>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th scope="col" class="column-object">
                        <?php _e('Object', 'dosmax-activity-log'); ?>
                    </th>
                    <th scope="col" class="column-event-type">
                        <?php _e('Event Type', 'dosmax-activity-log'); ?>
                    </th>
                    <th scope="col" class="column-message">
                        <?php _e('Message', 'dosmax-activity-log'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)) : ?>
                    <?php foreach ($logs as $log) : ?>
                    <tr data-occurrence-id="<?php echo esc_attr($log['id']); ?>">
                        <td class="column-severity">
                            <?php 
                            $severity_level = $this->get_severity_level($log['severity']); 
                            $severity_label = $this->get_severity_label($log['severity']); 
                            ?>
                            <span class="dashicons dashicons-<?php echo $severity_level['icon']; ?>" title="<?php echo esc_attr($severity_label); ?>" style="color: <?php echo $severity_level['color']; ?>;"></span>
                            <span class="severity-label"><?php echo esc_html($severity_label); ?></span>
                        </td>
                        <td class="column-date">
                            <?php echo $this->format_custom_date($log['created_on']); ?>
                        </td>
                        <td class="column-user">
                            <?php echo esc_html($log['username']); ?>
                            <div class="user-roles"><?php echo esc_html($log['user_roles']); ?></div>
                        </td>
                        <td class="column-ip">
                            <?php echo esc_html($log['client_ip']); ?>
                        </td>
                        <td class="column-object">
                            <?php echo esc_html($log['object']); ?>
                        </td>
                        <td class="column-event-type">
                            <?php echo esc_html($log['event_type']); ?>
                        </td>
                        <td class="column-message">
                            <div class="message-content">
                                <?php echo $this->format_event_message($log['alert_id'], array()); ?>
                            </div>
                            <div class="row-actions">
                                <span class="more-details">
                                    <a href="#" class="toggle-details" data-occurrence-id="<?php echo esc_attr($log['id']); ?>">
                                        More details...
                                    </a>
                                </span>
                            </div>
                            <div class="details-container" id="details-<?php echo esc_attr($log['id']); ?>" style="display: none;">
                                <div class="details-content">
                                    <!-- Details will be loaded via AJAX -->
                                    <div class="loading">Loading...</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">
                            <p><?php _e('No activity logs found matching the current filters.', 'dosmax-activity-log'); ?></p>
                            <?php if ($has_active_filters) : ?>
                                <p><a href="<?php echo admin_url('admin.php?page=dosmax-activity-log'); ?>"><?php _e('Clear all filters', 'dosmax-activity-log'); ?></a></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination bottom -->
        <div class="tablenav bottom">
            <?php echo $this->pagination_links($current_page, $total_pages, $total_items); ?>
        </div>
        
    </div>
</div>
