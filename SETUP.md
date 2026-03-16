# Quick Start Guide - Corruption Control System

## 5-Minute Setup Guide

### Prerequisites
- XAMPP/WAMP/LAMP installed with PHP 7.4+ and MySQL
- Web browser
- Text editor (optional)

### Steps

#### 1. Database Setup (2 minutes)

**Option A: Using phpMyAdmin**
1. Open `` in your browser
2. Click "New" or "Create Database"
3. Enter database name: `corruption_control_db`
4. Click "Create"
5. Select the new database
6. Go to "SQL" tab
7. Copy content from `db/schema.sql`
8. Paste into the SQL editor
9. Click "Go"

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p
CREATE DATABASE corruption_control_db;
USE corruption_control_db;
source db/schema.sql;
```

#### 2. File Placement (1 minute)

1. Extract project files to your web server root:
   - XAMPP: `C:\xampp\htdocs\corruption-control-system`
   - WAMP: `C:\wamp\www\corruption-control-system`
   - Linux: `/var/www/html/corruption-control-system`

2. Create the uploads directory structure:
   ```
   uploads/
   ├── evidence/
   └── documents/
   ```

3. Set folder permissions (Linux):
   ```bash
   chmod -R 755 uploads/
   chmod -R 777 uploads/evidence/
   chmod -R 777 uploads/documents/
   ```

#### 3. Configuration (1 minute)

Edit `includes/config.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Add your MySQL password if set
define('DB_NAME', 'corruption_control_db');
define('SITE_URL', 'http://localhost/corruption-control-system/');
```

#### 4. Start & Access (1 minute)

1. Start Apache and MySQL (XAMPP/WAMP control panel)
2. Open browser: `http://localhost/corruption-control-system/`
3. You should see the home page

### Test Accounts

**Admin**
- Email: `admin@corruption.com`
- Password: `admin123`

**Officer**
- Email: `officer@corruption.com`
- Password: `officer123`

**Create a Citizen Account**
1. Click "Register" on home page
2. Fill in details
3. Click "Register"
4. Login with new account

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Database connection failed | Check DB credentials in config.php, ensure MySQL is running |
| File upload not working | Set upload folder to 777 permissions, check PHP upload_max_filesize |
| Login fails | Verify email/password, check is_active in users table |
| Blank pages | Check Apache error logs, ensure PHP is enabled |
| CSS not loading | Clear browser cache, check file paths |

## Testing Checklist

- [ ] Home page loads
- [ ] Admin login works
- [ ] Can submit complaint (after creating citizen account)
- [ ] Can upload files with complaint
- [ ] Admin can review and approve complaints
- [ ] Officer can see assigned complaints
- [ ] Officer can submit investigation report
- [ ] Complaint status updates correctly

## What to Check First

1. **MySQL is running**: Open phpMyAdmin
2. **Database exists**: Check in phpMyAdmin
3. **Files are in right location**: Access index.php
4. **Permissions are set**: Can write to uploads folder
5. **config.php is updated**: Database credentials match

## Important Files

- `includes/config.php` - Database configuration (UPDATE THIS!)
- `db/schema.sql` - Database structure
- `css/style.css` - Styling
- `js/script.js` - JavaScript functionality

## Next Steps

1. Change default admin/officer passwords
2. Add your own officer accounts via database
3. Customize title and colors in CSS if desired
4. Test all user roles thoroughly
5. Backup database regularly
6. Monitor uploads folder size

---

If you encounter any issues, check the full README.md for detailed troubleshooting.
