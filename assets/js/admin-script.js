jQuery(document).ready(function($) {
    
    /**
     * Handle "More details..." toggle
     */
    $('.toggle-details').on('click', function(e) {
        e.preventDefault();
        
        var $link = $(this);
        var occurrenceId = $link.data('occurrence-id');
        var $detailsContainer = $('#details-' + occurrenceId);
        var $detailsContent = $detailsContainer.find('.details-content');
        
        // Toggle visibility
        if ($detailsContainer.is(':visible')) {
            $detailsContainer.slideUp();
            $link.text(dosmax_activity_log.strings.more_details);
            return;
        }
        
        // Show container and load details if not already loaded
        $detailsContainer.slideDown();
        $link.text(dosmax_activity_log.strings.hide_details || 'Hide details...');
        
        // Check if details are already loaded
        if ($detailsContent.find('.details-table').length > 0) {
            return;
        }
        
        // Load details via AJAX
        $detailsContent.html('<div class="loading">Loading...</div>');
        
        $.post(dosmax_ajax.ajax_url, {
            action: 'dosmax_get_log_details',
            occurrence_id: occurrenceId,
            nonce: dosmax_ajax.nonce
        })
        .done(function(response) {
            if (response.success && response.data) {
                $detailsContent.html(formatLogDetails(response.data));
            } else {
                $detailsContent.html('<div class="error">Failed to load details.</div>');
            }
        })
        .fail(function() {
            $detailsContent.html('<div class="error">Failed to load details.</div>');
        });
    });
    
    /**
     * Format log details for display
     */
    function formatLogDetails(data) {
        var html = '<h4>Event Details</h4>';
        html += '<table class="details-table">';
        
        // Basic event information
        if (data.occurrence) {
            html += '<tr><th>Event ID</th><td>' + escapeHtml(data.occurrence.alert_id) + '</td></tr>';
            html += '<tr><th>Severity</th><td>' + escapeHtml(data.occurrence.severity) + '</td></tr>';
            html += '<tr><th>Date</th><td>' + formatDate(data.occurrence.created_on) + '</td></tr>';
            html += '<tr><th>User</th><td>' + escapeHtml(data.occurrence.username || 'Unknown') + '</td></tr>';
            html += '<tr><th>User ID</th><td>' + escapeHtml(data.occurrence.user_id || 'N/A') + '</td></tr>';
            html += '<tr><th>User Roles</th><td>' + escapeHtml(data.occurrence.user_roles || 'N/A') + '</td></tr>';
            html += '<tr><th>IP Address</th><td>' + escapeHtml(data.occurrence.client_ip) + '</td></tr>';
            html += '<tr><th>User Agent</th><td>' + escapeHtml(data.occurrence.user_agent) + '</td></tr>';
            html += '<tr><th>Object</th><td>' + escapeHtml(data.occurrence.object) + '</td></tr>';
            html += '<tr><th>Event Type</th><td>' + escapeHtml(data.occurrence.event_type) + '</td></tr>';
            html += '<tr><th>Session ID</th><td>' + escapeHtml(data.occurrence.session_id) + '</td></tr>';
            
            if (data.occurrence.post_id && data.occurrence.post_id != '0') {
                html += '<tr><th>Post ID</th><td>' + escapeHtml(data.occurrence.post_id) + '</td></tr>';
                html += '<tr><th>Post Type</th><td>' + escapeHtml(data.occurrence.post_type) + '</td></tr>';
                html += '<tr><th>Post Status</th><td>' + escapeHtml(data.occurrence.post_status) + '</td></tr>';
            }
        }
        
        html += '</table>';
        
        // Metadata information
        if (data.metadata && Object.keys(data.metadata).length > 0) {
            html += '<h4>Additional Information</h4>';
            html += '<table class="details-table">';
            
            for (var key in data.metadata) {
                if (data.metadata.hasOwnProperty(key)) {
                    var value = data.metadata[key];
                    
                    // Format specific metadata types
                    if (key.toLowerCase().includes('url') || key.toLowerCase().includes('link')) {
                        value = '<a href="' + escapeHtml(value) + '" target="_blank">' + escapeHtml(value) + '</a>';
                    } else if (key.toLowerCase().includes('date') && /^\d{4}-\d{2}-\d{2}/.test(value)) {
                        value = formatDateString(value);
                    } else {
                        value = escapeHtml(value);
                    }
                    
                    html += '<tr><th>' + escapeHtml(formatMetadataKey(key)) + '</th><td>' + value + '</td></tr>';
                }
            }
            
            html += '</table>';
        }
        
        return html;
    }
    
    /**
     * Format metadata key for display
     */
    function formatMetadataKey(key) {
        return key.replace(/([A-Z])/g, ' $1')
                  .replace(/^./, function(str) { return str.toUpperCase(); });
    }
    
    /**
     * Format timestamp to readable date
     */
    function formatDate(timestamp) {
        var date = new Date(timestamp * 1000);
        return date.toLocaleString();
    }
    
    /**
     * Format date string
     */
    function formatDateString(dateString) {
        var date = new Date(dateString);
        return date.toLocaleString();
    }
    
    /**
     * Escape HTML entities
     */
    function escapeHtml(text) {
        if (typeof text !== 'string') {
            return text;
        }
        
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    /**
     * Handle table sorting
     */
    $('.wp-list-table th a').on('click', function() {
        // Add loading indicator
        var $table = $('#dosmax-activity-log-table');
        $table.addClass('loading');
    });
    
});

// Add loading styles
jQuery(document).ready(function($) {
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .wp-list-table.loading tbody {
                opacity: 0.5;
                pointer-events: none;
            }
            
            .details-table a {
                color: #0073aa;
                text-decoration: none;
            }
            
            .details-table a:hover {
                text-decoration: underline;
            }
            
            .error {
                color: #dc3232;
                text-align: center;
                padding: 10px;
            }
        `)
        .appendTo('head');
});
