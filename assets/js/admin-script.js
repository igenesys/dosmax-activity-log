jQuery(document).ready(function($) {
    
    // Check if dosmax_ajax is defined
    if (typeof dosmax_ajax === 'undefined') {
        console.error('dosmax_ajax object not found');
        return;
    }
    
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
        
        console.log('Making AJAX request for occurrence ID:', occurrenceId);
        
        $.post(dosmax_ajax.ajax_url, {
            action: 'dosmax_get_log_details',
            occurrence_id: occurrenceId,
            nonce: dosmax_ajax.nonce
        })
        .done(function(response) {
            console.log('AJAX response:', response);
            if (response.success && response.data) {
                $detailsContent.html(window.formatLogDetails(response.data));
            } else {
                $detailsContent.html('<div class="error">Failed to load details: ' + (response.data || 'Unknown error') + '</div>');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('AJAX failed:', status, error);
            $detailsContent.html('<div class="error">Failed to load details: ' + error + '</div>');
        });
    });
    
});

/**
 * Format log details for display - matches WP Activity Log format from screenshot
 */
window.formatLogDetails = function(data) {
        var html = '<div class="event-details-container">';
        
        // Display the main message (as shown in screenshot)
        if (data.message) {
            html += '<div class="event-message">' + data.message + '</div>';
        }
        
        // For product events, show additional structured metadata (matching screenshot format)
        if ((data.event_id === '2101' || data.event_id === '2100') && data.metadata) {
            html += '<div class="metadata-details">';
            
            // Show Post ID
            if (data.metadata.PostID) {
                html += 'Post ID: <strong>' + escapeHtml(data.metadata.PostID) + '</strong><br>';
            }
            
            // Show Post type
            if (data.metadata.PostType || data.object) {
                html += 'Post type: <strong>' + escapeHtml(data.metadata.PostType || data.object) + '</strong><br>';
            }
            
            // Show Post status
            if (data.metadata.PostStatus) {
                html += 'Post status: <strong>' + escapeHtml(data.metadata.PostStatus) + '</strong><br>';
            }
            
            // Show URL if available
            if (data.metadata.PostUrl) {
                html += 'URL: <a href="' + escapeHtml(data.metadata.PostUrl) + '" target="_blank" class="view-link">' + escapeHtml(data.metadata.PostUrl) + '</a><br>';
            }
            
            // Show editor link
            if (data.metadata.PostID) {
                var editUrl = '/wp-admin/post.php?post=' + data.metadata.PostID + '&action=edit';
                html += '<a href="' + editUrl + '" target="_blank" class="view-post-link">View the post in editor</a><br>';
            }
            
            html += '</div>';
        } else if (data.event_id === '6023') {
            // Access denied event
            html += '<div class="access-denied-details">';
            html += 'Was denied access to the page <strong>' + escapeHtml(data.metadata && data.metadata.RequestedURL ? data.metadata.RequestedURL : 'admin.php?page=dosmax-activity-log-settings') + '</strong>.';
            html += '</div>';
        } else {
            // For other events, show basic metadata
            html += '<div class="basic-metadata">';
            
            if (data.metadata && data.metadata.PostID) {
                html += 'Post ID: <strong>' + escapeHtml(data.metadata.PostID) + '</strong><br>';
            }
            
            if (data.metadata && data.metadata.PostType) {
                html += 'Post type: <strong>' + escapeHtml(data.metadata.PostType) + '</strong><br>';
            }
            
            if (data.metadata && data.metadata.PostStatus) {
                html += 'Post status: <strong>' + escapeHtml(data.metadata.PostStatus) + '</strong><br>';
            }
            
            if (data.metadata && data.metadata.PostUrl) {
                html += 'URL: <a href="' + escapeHtml(data.metadata.PostUrl) + '" target="_blank" class="view-link">' + escapeHtml(data.metadata.PostUrl) + '</a><br>';
            }
            
            if (data.metadata && data.metadata.PostID) {
                var editUrl = '/wp-admin/post.php?post=' + data.metadata.PostID + '&action=edit';
                html += '<a href="' + editUrl + '" target="_blank" class="view-post-link">View the post in editor</a><br>';
            }
            
            html += '</div>';
        }
        
        html += '</div>';
        
        return html;
    };
    
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
