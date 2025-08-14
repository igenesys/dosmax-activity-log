<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosmax Activity Log - Admin Interface Demo</title>
    
    <!-- WordPress Admin CSS -->
    <link rel="stylesheet" href="/assets/css/admin-style.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            margin: 0;
            padding: 20px;
            background: #f1f1f1;
            font-size: 13px;
            line-height: 1.4em;
        }
        
        .wrap {
            background: white;
            margin: 0 auto;
            max-width: 1200px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.13);
        }
        
        h1 {
            padding: 23px 20px;
            margin: 0;
            background: white;
            border-bottom: 1px solid #ddd;
            font-size: 23px;
            font-weight: 400;
            line-height: 29px;
        }
        
        .dashicons {
            font-family: dashicons;
            display: inline-block;
            line-height: 1;
            font-weight: normal;
            font-style: normal;
            speak: none;
            text-decoration: inherit;
            text-transform: none;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            width: 20px;
            height: 20px;
            font-size: 20px;
            vertical-align: top;
            text-align: center;
        }
        
        .dashicons-yes:before { content: "\f147"; }
        
        .back-link {
            display: inline-block;
            margin: 20px 0;
            color: #0073aa;
            text-decoration: none;
        }
        
        .back-link:hover {
            color: #005177;
        }
    </style>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <a href="/" class="back-link">← Back to Plugin Overview</a>
    
    <div class="wrap">
        <h1>Dosmax Activity Log</h1>
        
        <div class="dosmax-activity-log-container">
            
            <!-- Filters Section -->
            <div class="dosmax-filters-container">
                <form method="get" id="dosmax-filters-form">
                    <input type="hidden" name="page" value="dosmax-activity-log" />
                    
                    <div class="filters-row">
                        <!-- User Filter -->
                        <div class="filter-group">
                            <label for="filter-user">User:</label>
                            <select name="filter_user" id="filter-user">
                                <option value="">All Users</option>
                                <option value="jarik">jarik</option>
                            </select>
                        </div>
                        
                        <!-- Object Filter -->
                        <div class="filter-group">
                            <label for="filter-object">Object:</label>
                            <select name="filter_object" id="filter-object">
                                <option value="">All Objects</option>
                                <option value="post">Post</option>
                            </select>
                        </div>
                        
                        <!-- IP Address Filter -->
                        <div class="filter-group">
                            <label for="filter-ip">IP Address:</label>
                            <input type="text" name="filter_ip" id="filter-ip" value="" placeholder="Enter IP address" />
                        </div>
                    </div>
                    
                    <div class="filters-row">
                        <!-- Date Filter -->
                        <div class="filter-group">
                            <label for="filter-date-type">Date Filter:</label>
                            <select name="filter_date_type" id="filter-date-type">
                                <option value="">All Dates</option>
                                <option value="before">Before</option>
                                <option value="after">After</option>
                                <option value="on">On</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="filter-date">Date:</label>
                            <input type="date" name="filter_date" id="filter-date" value="" />
                        </div>
                        
                        <div class="filter-group">
                            <input type="submit" name="apply_filters" class="button button-primary" value="Apply Filters" />
                            <a href="?" class="button">Clear Filters</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Active Filters Display -->
            <div class="active-filters-display" style="display: none;">
                <strong>Active Filters:</strong>
                <div class="filter-tags"></div>
            </div>
            
            <!-- Results Table -->
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-severity sortable desc">
                            <a href="#"><span>Severity</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" class="manage-column column-date sortable asc">
                            <a href="#"><span>Date</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" class="manage-column column-user">
                            <a href="#"><span>User</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" class="manage-column column-ip">
                            <a href="#"><span>IP Address</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" class="manage-column column-object">
                            <a href="#"><span>Object</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" class="manage-column column-event-type">
                            <a href="#"><span>Event Type</span><span class="sorting-indicator"></span></a>
                        </th>
                        <th scope="col" class="manage-column column-message">
                            <span>Message</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-occurrence-id="1">
                        <td class="column-severity">
                            <span class="dashicons dashicons-yes" title="Low" style="color: #00a32a;"></span>
                            <span class="severity-label">Low</span>
                        </td>
                        <td class="column-date">
                            14.08.2025<br>8:09:29.000 am
                        </td>
                        <td class="column-user">
                            jarik
                            <div class="user-roles">site_admin</div>
                        </td>
                        <td class="column-ip">
                            46.243.189.112
                        </td>
                        <td class="column-object">
                            post
                        </td>
                        <td class="column-event-type">
                            opened
                        </td>
                        <td class="column-message">
                            <div class="message-content">
                                User opened a post in the editor: <strong>Seizoensallergieën bij huisdieren: waar moet je op letten?</strong>
                            </div>
                            <div class="row-actions">
                                <span class="more-details">
                                    <a href="#" class="toggle-details" data-occurrence-id="1">
                                        More details...
                                    </a>
                                </span>
                            </div>
                            <div class="details-container" id="details-1" style="display: none;">
                                <div class="details-content">
                                    <!-- Details will be loaded via AJAX -->
                                    <div class="loading">Loading...</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr data-occurrence-id="2">
                        <td class="column-severity">
                            <span class="dashicons dashicons-yes" title="Low" style="color: #00a32a;"></span>
                            <span class="severity-label">Low</span>
                        </td>
                        <td class="column-date">
                            14.08.2025<br>8:08:15.000 am
                        </td>
                        <td class="column-user">
                            jarik
                            <div class="user-roles">site_admin</div>
                        </td>
                        <td class="column-ip">
                            46.243.189.112
                        </td>
                        <td class="column-object">
                            post
                        </td>
                        <td class="column-event-type">
                            viewed
                        </td>
                        <td class="column-message">
                            <div class="message-content">
                                User viewed a post: <strong>Home</strong>
                            </div>
                            <div class="row-actions">
                                <span class="more-details">
                                    <a href="#" class="toggle-details" data-occurrence-id="2">
                                        More details...
                                    </a>
                                </span>
                            </div>
                            <div class="details-container" id="details-2" style="display: none;">
                                <div class="details-content">
                                    <!-- Details will be loaded via AJAX -->
                                    <div class="loading">Loading...</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                    <select name="action2" id="bulk-action-selector-bottom">
                        <option value="-1">Bulk Actions</option>
                    </select>
                    <input type="submit" id="doaction2" class="button action" value="Apply">
                </div>
                <div class="tablenav-pages">
                    <span class="displaying-num">2 items</span>
                    <span class="pagination-links">
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
                        <span class="paging-input">
                            <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                            <input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
                            <span class="tablenav-paging-text"> of <span class="total-pages">1</span></span>
                        </span>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
                        <span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Load Admin JavaScript -->
    <script src="/assets/js/admin-script.js"></script>
</body>
</html>