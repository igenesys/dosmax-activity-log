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
            $link.text('More details...');
            return;
        }
        
        // Show container and load details if not already loaded
        $detailsContainer.slideDown();
        $link.text('Hide details...');
        
        // Check if details are already loaded
        if ($detailsContent.find('.event-details-container').length > 0) {
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
        var html = '<div class="event-details-container">';
        
        // Basic event information in a structured format similar to the screenshot
        if (data.occurrence) {
            html += '<div class="details-section">';
            html += '<strong>EventType:</strong> ' + escapeHtml(data.occurrence.event_type || 'N/A') + '<br>';
            html += '<strong>PostTitle:</strong> ' + escapeHtml(data.metadata && data.metadata.PostTitle ? data.metadata.PostTitle : 'N/A') + '<br>';
            html += '<strong>PostUrl:</strong> ' + (data.metadata && data.metadata.PostUrl ? '<a href="' + escapeHtml(data.metadata.PostUrl) + '" target="_blank">' + escapeHtml(data.metadata.PostUrl) + '</a>' : 'N/A') + '<br>';
            html += '<strong>PostType:</strong> ' + escapeHtml(data.occurrence.post_type || 'N/A') + '<br>';
            html += '<strong>PostStatus:</strong> ' + escapeHtml(data.occurrence.post_status || 'N/A') + '<br>';
            html += '<strong>PostID:</strong> ' + escapeHtml(data.occurrence.post_id || 'N/A') + '<br>';
            
            // Add editor link if available
            if (data.metadata && data.metadata.EditorLinkPost) {
                html += '<strong>EditorLinkPost:</strong> <a href="' + escapeHtml(data.metadata.EditorLinkPost) + '" target="_blank">' + escapeHtml(data.metadata.EditorLinkPost) + '</a><br>';
            }
            
            html += '<strong>ClientIP:</strong> ' + escapeHtml(data.occurrence.client_ip || 'N/A') + '<br>';
            html += '<strong>Severity:</strong> ' + escapeHtml(data.occurrence.severity || 'N/A') + '<br>';
            html += '<strong>Object:</strong> ' + escapeHtml(data.occurrence.object || 'N/A') + '<br>';
            html += '<strong>EventType:</strong> ' + escapeHtml(data.occurrence.event_type || 'N/A') + '<br>';
            
            // Add user agent with word wrap
            html += '<strong>UserAgent:</strong> <span class="user-agent">' + escapeHtml(data.occurrence.user_agent || 'N/A') + '</span><br>';
            html += '<strong>Username:</strong> ' + escapeHtml(data.occurrence.username || 'N/A') + '<br>';
            html += '<strong>UserRoles:</strong> ' + escapeHtml(data.occurrence.user_roles || 'N/A') + '<br>';
            html += '<strong>SessionID:</strong> ' + escapeHtml(data.occurrence.session_id || 'N/A') + '<br>';
            
            // Add revision link if available
            if (data.metadata && data.metadata.RevisionLink) {
                html += '<strong>RevisionLink:</strong> <a href="' + escapeHtml(data.metadata.RevisionLink) + '" target="_blank">' + escapeHtml(data.metadata.RevisionLink) + '</a><br>';
            }
            
            // Add plugin data if available
            if (data.metadata && data.metadata.PluginFile) {
                html += '<strong>PluginFile:</strong> ' + escapeHtml(data.metadata.PluginFile) + '<br>';
            }
            
            if (data.metadata && data.metadata.PluginData) {
                html += '<strong>PluginData:</strong> <span class="plugin-data">' + escapeHtml(data.metadata.PluginData) + '</span><br>';
            }
            
            // Add any old/new title changes
            if (data.metadata && data.metadata.OldTitle) {
                html += '<strong>OldTitle:</strong> ' + escapeHtml(data.metadata.OldTitle) + '<br>';
            }
            
            if (data.metadata && data.metadata.NewTitle) {
                html += '<strong>NewTitle:</strong> ' + escapeHtml(data.metadata.NewTitle) + '<br>';
            }
            
            // Add post date if available
            if (data.metadata && data.metadata.PostDate) {
                html += '<strong>PostDate:</strong> ' + escapeHtml(data.metadata.PostDate) + '<br>';
            }
            
            html += '</div>';
        }
        
        html += '</div>';
        
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
