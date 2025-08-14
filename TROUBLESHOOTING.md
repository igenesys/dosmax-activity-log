# WordPress Plugin Troubleshooting Guide

## "More Details" Not Working - Debugging Steps

### Issue: JavaScript Errors
**Problem**: dosmax_ajax variable not defined
**Solution**: Check that the script localization is working properly

### Debug Steps:

1. **Check Browser Console**:
   - Open browser Developer Tools (F12)
   - Look for JavaScript errors
   - Check if `dosmax_ajax` variable is defined

2. **Verify Script Loading**:
   - Go to WordPress admin: Activity Log page
   - Check if `admin-script.js` is loaded in the Network tab
   - Verify `dosmax_ajax` variable is available in Console

3. **Test AJAX Manually**:
   ```javascript
   // In browser console:
   console.log(dosmax_ajax); // Should show ajax_url and nonce
   
   // Test AJAX call:
   jQuery.post(dosmax_ajax.ajax_url, {
       action: 'dosmax_get_log_details',
       occurrence_id: 1,
       nonce: dosmax_ajax.nonce
   }).done(function(response) {
       console.log(response);
   });
   ```

4. **Check WordPress AJAX Hook**:
   - Verify `wp_ajax_dosmax_get_log_details` action is registered
   - Check if nonce verification is working
   - Ensure user has proper permissions

## Common Solutions

### Fix 1: Clear Cache
- Clear WordPress cache if using caching plugins
- Clear browser cache and hard refresh (Ctrl+F5)

### Fix 2: Check Plugin Activation
- Ensure plugin is properly activated
- Check for any PHP errors in WordPress debug log

### Fix 3: Verify Database Connection
- Go to Settings → Dosmax Activity Log
- Test database connection
- Ensure WP Activity Log tables exist

### Fix 4: Check User Permissions
- Ensure current user has 'manage_options' capability
- Verify user is not blocked by role restrictions

## Expected Behavior

When "More details" is working correctly:
1. Click "More details..." link
2. Container expands with loading message
3. AJAX request loads detailed information
4. Two-level expandable content appears matching WP Activity Log format

## Files to Check

- `assets/js/admin-script.js` - JavaScript functionality
- `includes/class-dosmax-activity-log.php` - AJAX handler and script enqueuing
- `includes/class-dosmax-database.php` - Database queries
- `templates/admin-page.php` - HTML structure for details containers