# Troubleshooting Guide

## Common Issues and Solutions

### Database Issues

#### Error: "No such file or directory" when connecting to database
**Symptoms**: Cannot connect to MySQL, blank page or connection error
**Solution**:
1. Verify MySQL server is running
2. Check database connection credentials in `config/database.php`
3. Ensure MySQL socket path is correct
4. Try connecting via command line: `mysql -h localhost -u root`

#### Error: "Access denied for user 'root'@'localhost'"
**Symptoms**: Login fails with authentication error
**Solution**:
1. Check MySQL password is correct
2. Verify user has proper privileges: `GRANT ALL PRIVILEGES ON corruption_control_system.* TO 'root'@'localhost';`
3. Flush privileges: `FLUSH PRIVILEGES;`

#### Error: "Unknown database 'corruption_control_system'"
**Symptoms**: Database selection fails
**Solution**:
1. Create database: `CREATE DATABASE corruption_control_system;`
2. Import schema: `mysql -u root -p corruption_control_system < database/schema.sql`
3. Verify tables exist: `SHOW TABLES;`

---

### Authentication Issues

#### Can't login even with correct credentials
**Symptoms**: "Invalid email or password" message repeatedly
**Solution**:
1. Verify user exists in database: `SELECT * FROM users WHERE email='admin@corruptioncontrol.com';`
2. Check password was hashed correctly (should start with `$2`)
3. Manually update password: 
```sql
UPDATE users SET password='$2y$10$...' WHERE email='admin@corruptioncontrol.com';
```
4. Verify user status is 'active': `SELECT status FROM users WHERE email='...';`

#### Redirects to home page after login
**Symptoms**: Login appears successful but redirects to index.php
**Solution**:
1. Check user role is set correctly
2. Verify respective dashboard file exists (e.g., `citizen/dashboard.php`)
3. Check file permissions on dashboard files
4. Verify `config/session.php` is included on all pages

#### Session expires too quickly
**Symptoms**: Logged out while still active
**Solution**:
1. Increase SESSION_TIMEOUT in `config/config.php` (in seconds)
2. Check server's PHP session timeout isn't shorter
3. Verify cookies are enabled in browser
4. Check disk space for session storage

#### Page shows "Access Denied" after login
**Symptoms**: Can login but get access denied on specific pages
**Solution**:
1. Verify user role in database matches page role requirement
2. Check `requireRole()` function in `config/session.php`
3. Verify user hasn't been suspended/deactivated
4. Check browser cookies are enabled

---

### File Upload Issues

#### File upload fails silently
**Symptoms**: File upload form submitted but files don't appear
**Solution**:
1. Check `uploads/` directory permissions: `chmod -R 755 uploads/`
2. Verify PHP `upload_max_filesize` setting (should be larger than 5MB)
3. Check `posts_max_size` setting in php.ini (should be larger than `upload_max_filesize`)
4. Verify disk space available on server
5. Check `uploadFile()` function return value

#### Error: "File too large"
**Symptoms**: Upload fails with size error
**Solution**:
1. Current max is 5MB, defined in `config/config.php`
2. To increase: Modify `MAX_FILE_SIZE` constant
3. Also increase PHP settings in php.ini:
```ini
upload_max_filesize = 20M
post_max_size = 20M
```
4. Restart Apache/PHP

#### Error: "Invalid file type"
**Symptoms**: File format not accepted
**Solution**:
1. Check allowed extensions in `config/config.php` (pdf, jpg, jpeg, png, doc, docx, txt)
2. To add more: Modify `ALLOWED_FILE_EXTENSIONS` array
3. Note: Check both extension AND MIME type
4. Virus scan if available

#### Files uploaded but can't be downloaded
**Symptoms**: Upload works but download returns 404
**Solution**:
1. Verify file actually exists in `uploads/` directory
2. Check file path is stored correctly in database
3. Verify file permissions (644 or 755)
4. Check web server can read the directory

---

### Performance Issues

#### Application runs slowly
**Symptoms**: Pages take long time to load
**Solution**:
1. Check number of complaints in database (large datasets slow queries)
2. Verify database indexes are created: `SHOW INDEXES FROM complaints;`
3. Enable PHP OpCache if available
4. Check database query execution time
5. Reduce items per page in table displays

#### Out of Memory errors
**Symptoms**: "Allowed memory size exhausted" error
**Solution**:
1. Increase PHP memory limit in php.ini: `memory_limit = 256M`
2. Optimize database queries to return fewer rows
3. Implement pagination if not already done
4. Check for memory leaks in loops

---

### Display Issues

#### Styles not loading (page looks broken)
**Symptoms**: Page displays but styling is missing
**Solution**:
1. Check if CSS file loads: Open browser DevTools (F12)
2. Verify `assets/css/style.css` file path is correct
3. Check .htaccess isn't blocking CSS access
4. Clear browser cache (Ctrl+Shift+Delete)
5. Test in private/incognito window

#### JavaScript not working
**Symptoms**: Interactive features don't work (modals, alerts)
**Solution**:
1. Check browser console for JavaScript errors (F12 → Console)
2. Verify `assets/js/main.js` is included on page
3. Check for syntax errors in custom JavaScript
4. Verify jQuery or other dependencies are loaded
5. Check Content Security Policy headers

#### Page layout broken on mobile
**Symptoms**: Content overlaps or is cut off on phones
**Solution**:
1. Check viewport meta tag exists in HTML head
2. Verify responsive CSS media queries are applied
3. Test in different browsers (Chrome mobile emulator)
4. Check form inputs aren't too wide
5. Verify images scale properly

---

### Email/Notification Issues

#### Emails not sending
**Symptoms**: No email received after user action
**Solution**:
1. Email functionality not yet implemented (feature for v2.0)
2. To implement:
   - Configure SMTP settings in `.env`
   - Install PHPMailer or SwiftMailer via Composer
   - Add email sending in appropriate workflow pages
   - Configure server's mail_from address

---

### Data Issues

#### Complaint created but doesn't show in dashboard
**Symptoms**: Submit successful but complaint missing from list
**Solution**:
1. Check if status is correct: `SELECT * FROM complaints WHERE id=X;`
2. Check assigned_officer_id matches current user if filtered
3. Refresh page or clear cache
4. Check SQL query in dashboard.php for filtering issues
5. Verify user_id matches current logged-in user

#### Activity log not recording actions
**Symptoms**: No entries appearing in activity_log table
**Solution**:
1. Verify `logActivity()` is called in action pages
2. Check database connection is active when logging
3. Verify activity_log table exists
4. Check for SQL errors in log query
5. Ensure sufficient disk space for database

#### Users can see other users' complaints
**Symptoms**: Privacy concern - citizens seeing each other's data
**Solution**:
1. Check citizen dashboard filters by `user_id`
2. Verify SQL WHERE clause includes: `WHERE user_id = ?`
3. Review all queries to ensure proper user filtering
4. Check admin can see all (intended behavior)

---

### Browser-Specific Issues

#### Issues only in Internet Explorer
**Symptoms**: Features work in Chrome but not IE
**Solution**:
1. IE is deprecated, use modern browser (Chrome, Firefox, Safari, Edge)
2. If must support IE, add compatibility head tags:
```html
<meta http-equiv="X-UA-Compatible" content="IE=edge">
```
3. Avoid modern JavaScript features (arrow functions, etc.)

#### Issues on Safari
**Symptoms**: Features work in Chrome but not Safari
**Solution**:
1. Check for vendor prefixes in CSS
2. Verify file upload works (different handling in Safari)
3. Check date formatting compatibility
4. Test with Latest Safari version

---

### Security Issues

#### SQL Injection concerns
**Symptoms**: Worry about database security
**Solution**:
1. Verify all queries use prepared statements with `?` placeholders
2. Never concatenate user input into queries
3. Example safe query:
```php
$stmt = $db_conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
```

#### XSS (Cross-Site Scripting) concerns
**Symptoms**: Injected JavaScript executes on page
**Solution**:
1. Always use `htmlspecialchars()` when displaying user input
2. Use `sanitizeInput()` helper when receiving data
3. Never use `eval()` or equivalent
4. Example safe output:
```php
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

---

## Getting Help

If issue persists:
1. **Check logs**: Look in PHP error logs and MySQL error logs
2. **Enable debugging**: Set `APP_DEBUG=true` in config.php
3. **Test with dummy data**: Manually insert test records
4. **Search documentation**: Review README.md and SETUP.md
5. **Check browser console**: F12 → Console/Network tabs for errors

---

## Debug Tips

### Enable PHP Error Logging
In `index.php` at top of file (development only):
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
log_errors(1);
error_log('/path/to/error.log');
```

### Exit and Debug Database Queries
```php
// Temporarily stop execution and show query
var_dump($query);
die();

// Check if prepared statement worked
if (!$stmt) {
    die('Error: ' . $db_conn->error);
}
```

### Check Session Variables
```php
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
```

### View Database Records
```sql
-- Check all users
SELECT id, name, email, role, status FROM users;

-- Check specific complaint
SELECT * FROM complaints WHERE id = 123;

-- View recent activity
SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 10;
```

### Clear Browser Cache
**Chrome**: Ctrl+Shift+Delete or Cmd+Shift+Delete
**Firefox**: Shift+Ctrl+Delete or Cmd+Shift+Delete
**Safari**: Develop → Empty Caches

---

**Last Updated**: 2024
**For detailed features documentation**: See README.md and SETUP.md
