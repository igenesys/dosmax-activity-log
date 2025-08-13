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
        
        <!-- Pagination top -->
        <div class="tablenav top">
            <?php echo $this->pagination_links($current_page, $total_pages); ?>
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
                <?php if (empty($logs)) : ?>
                    <tr>
                        <td colspan="7" class="no-items">
                            <?php _e('No activity logs found for site-admin users.', 'dosmax-activity-log'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($logs as $log) : ?>
                        <tr data-occurrence-id="<?php echo esc_attr($log['id']); ?>">
                            <td class="column-severity">
                                <span class="dashicons <?php echo $this->get_severity_icon($log['severity']); ?>" title="<?php echo esc_attr($this->get_severity_label($log['severity'])); ?>"></span>
                                <span class="severity-label"><?php echo esc_html($this->get_severity_label($log['severity'])); ?></span>
                            </td>
                            <td class="column-date">
                                <?php echo date('Y-m-d H:i:s', $log['created_on']); ?>
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
                                    <?php echo esc_html($this->format_event_message($log['alert_id'], $log['metadata'])); ?>
                                </div>
                                <div class="row-actions">
                                    <span class="more-details">
                                        <a href="#" class="toggle-details" data-occurrence-id="<?php echo esc_attr($log['id']); ?>">
                                            <?php _e('More details...', 'dosmax-activity-log'); ?>
                                        </a>
                                    </span>
                                </div>
                                <div class="details-container" id="details-<?php echo esc_attr($log['id']); ?>" style="display: none;">
                                    <div class="details-content">
                                        <!-- Details will be loaded via AJAX -->
                                        <div class="loading"><?php _e('Loading...', 'dosmax-activity-log'); ?></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination bottom -->
        <div class="tablenav bottom">
            <?php echo $this->pagination_links($current_page, $total_pages); ?>
        </div>
        
    </div>
</div>
