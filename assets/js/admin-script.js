jQuery(document).ready(function($) {
    
    // WordPress AJAX setup
    console.log('Dosmax Activity Log initialized');
    
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
                response.data.occurrence_id = occurrenceId; // Add occurrence ID for nested "More details"
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
 * Format log details for display - matches WP Activity Log format exactly
 */
window.formatLogDetails = function(data) {
        var html = '<div class="event-details-container">';
        
        // Display the main message
        if (data.message) {
            html += '<div class="event-message">' + data.message + '</div>';
        }
        
        // Show detailed metadata in WP Activity Log format
        html += '<div class="metadata-details">';
        
        // Show Post ID
        if (data.metadata && data.metadata.PostID) {
            html += 'Post ID: <strong>' + escapeHtml(data.metadata.PostID) + '</strong><br>';
        }
        
        // Show Post type
        if (data.metadata && data.metadata.PostType) {
            html += 'Post type: <strong>' + escapeHtml(data.metadata.PostType) + '</strong><br>';
        } else if (data.object) {
            html += 'Post type: <strong>' + escapeHtml(data.object) + '</strong><br>';
        }
        
        // Show Post status
        if (data.metadata && data.metadata.PostStatus) {
            html += 'Post status: <strong>' + escapeHtml(data.metadata.PostStatus) + '</strong><br>';
        }
        
        // Show editor link
        if (data.metadata && data.metadata.EditorLinkPost) {
            html += '<a href="' + escapeHtml(data.metadata.EditorLinkPost) + '" target="_blank" class="view-post-link">View the post in editor</a><br>';
        } else if (data.metadata && data.metadata.PostID) {
            var editUrl = '/wp-admin/post.php?post=' + data.metadata.PostID + '&action=edit';
            html += '<a href="' + editUrl + '" target="_blank" class="view-post-link">View the post in editor</a><br>';
        }
        
        // Show URL if available
        if (data.metadata && data.metadata.PostUrl) {
            html += '<a href="' + escapeHtml(data.metadata.PostUrl) + '" target="_blank" class="view-link">URL</a><br>';
        }
        
        html += '</div>';
        
        // Add "More details..." section for additional metadata (like screenshot 3)
        html += '<div class="additional-details" style="margin-top: 15px;">';
        html += '<a href="#" class="show-more-details" data-occurrence-id="' + data.occurrence_id + '">More details...</a>';
        html += '<div class="full-metadata" style="display: none; margin-top: 10px; font-family: monospace; font-size: 11px; background: #f9f9f9; padding: 10px; border: 1px solid #ddd;">';
        
        // Display all metadata in technical format (like screenshot 3)
        if (data.metadata && data.metadata.EditorLinkPost) {
            html += '<strong>EditorLinkPost:</strong> ' + escapeHtml(data.metadata.EditorLinkPost) + '<br>';
        }
        if (data.metadata && data.metadata.NewTitle) {
            html += '<strong>NewTitle:</strong> ' + escapeHtml(data.metadata.NewTitle) + '<br>';
        }
        if (data.metadata && data.metadata.OldTitle) {
            html += '<strong>OldTitle:</strong> ' + escapeHtml(data.metadata.OldTitle) + '<br>';
        }
        if (data.metadata && data.metadata.PostDate) {
            html += '<strong>PostDate:</strong> ' + escapeHtml(data.metadata.PostDate) + '<br>';
        }
        if (data.metadata && data.metadata.PostTitle) {
            html += '<strong>PostTitle:</strong> ' + escapeHtml(data.metadata.PostTitle) + '<br>';
        }
        if (data.metadata && data.metadata.PostUrl) {
            html += '<strong>PostUrl:</strong> ' + escapeHtml(data.metadata.PostUrl) + '<br>';
        }
        
        // Add core event data
        html += '<strong>ClientIP:</strong> ' + escapeHtml(data.ip || 'N/A') + '<br>';
        html += '<strong>Severity:</strong> ' + escapeHtml(data.severity || 'N/A') + ' (informational)<br>';
        html += '<strong>Object:</strong> ' + escapeHtml(data.object || 'N/A') + '<br>';
        html += '<strong>EventType:</strong> ' + escapeHtml(data.event_type || 'modified') + '<br>';
        
        // Add user agent if available
        if (data.metadata && data.metadata.UserAgent) {
            html += '<strong>UserAgent:</strong> ' + escapeHtml(data.metadata.UserAgent) + '<br>';
        }
        
        // Add user info
        html += '<strong>CurrentUserRoles:</strong> ' + escapeHtml(data.user_roles || 'N/A') + '<br>';
        html += '<strong>Username:</strong> ' + escapeHtml(data.user || 'N/A') + '<br>';
        
        // Add session ID if available
        if (data.metadata && data.metadata.SessionID) {
            html += '<strong>SessionID:</strong> ' + escapeHtml(data.metadata.SessionID) + '<br>';
        }
        
        // Add post status and type
        if (data.metadata && data.metadata.PostStatus) {
            html += '<strong>PostStatus:</strong> ' + escapeHtml(data.metadata.PostStatus) + '<br>';
        }
        if (data.metadata && data.metadata.PostType) {
            html += '<strong>PostType:</strong> ' + escapeHtml(data.metadata.PostType) + '<br>';
        }
        if (data.metadata && data.metadata.PostID) {
            html += '<strong>PostID:</strong> ' + escapeHtml(data.metadata.PostID) + '<br>';
        }
        
        html += '</div>';
        html += '</div>';
        
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
    
    // Handle nested "More details..." toggle
    $(document).on('click', '.show-more-details', function(e) {
        e.preventDefault();
        
        var $link = $(this);
        var $fullMetadata = $link.siblings('.full-metadata');
        
        if ($fullMetadata.is(':visible')) {
            $fullMetadata.slideUp();
            $link.text('More details...');
        } else {
            $fullMetadata.slideDown();
            $link.text('Hide details...');
        }
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
