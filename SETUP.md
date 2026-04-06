# Quick Setup Guide

## 5-Minute Setup

### 1. Database Setup
```sql
CREATE DATABASE corruption_control_system;
USE corruption_control_system;
-- Import schema.sql file
```

### 2. Configuration
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'corruption_control_system');
```

### 3. Folder Permissions
```bash
chmod -R 755 uploads/
```

### 4. Initialize Users
After importing schema.sql, initialize the admin and test users with correct passwords:
```
http://localhost/corruptioncontrolsystem/setup.php
```
Visit this URL in your browser and click "Initialize Users" button.

**⚠️ IMPORTANT:** After setup completes, delete the `setup.php` file for security!

### 5. Access Application
Open browser: `http://localhost/corruptioncontrolsystem/auth/login.php`

## Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@corruptioncontrol.com | admin123@Ab |
| Officer | rajesh.officer@corruptioncontrol.com | admin123@Ab |
| Investigator | priya.investigator@corruptioncontrol.com | admin123@Ab |

## Create Test Citizen Account

1. Go to Register page
2. Fill in details:
   - Name: Test Citizen
   - Email: citizen@test.com
   - Phone: 9876543210
   - Password: TestPass@123
   - Role: Citizen

## Complete User Workflow Demo

### As Admin:
1. Login with admin credentials
2. Go to Manage Users to see all users
3. Go to Manage Complaints to view all submissions
4. Check Reports section for statistics

### As Citizen:
1. Register new account
2. Go to Submit Complaint
3. Fill complaint details and upload evidence
4. Check My Complaints to track status

### As Officer:
1. Login with officer account
2. Go to Pending Complaints
3. Review a complaint
4. Click Review and Approve/Reject
5. Check Assigned Complaints for review history

### As Investigator:
1. Login with investigator account
2. Go to Pending Assignment
3. Accept a case (auto-creates FIR)
4. Upload investigation evidence
5. Create investigation report
6. Close the case

## File Structure Quick Reference

- **Frontend Files**: `citizen/`, `admin/`, `officer/`, `investigation/`
- **Config**: `config/` - Database, session, helpers
- **Assets**: `assets/` - CSS and JavaScript
- **Database**: `database/schema.sql` - Database tables
- **Authentication**: `auth/` - Login, register, logout pages
- **Uploads**: `uploads/` - Evidence and report storage

## Troubleshooting

### White Blank Page?
- Check PHP error logs
- Verify database connection
- Ensure PHP MySQL extension is enabled

### Can't Login?
- Make sure you ran `setup.php` to initialize users
- Verify database has users table and data
- Check if `setup.php` was deleted (if so, run it again temporarily)
- Verify database credentials in `config/database.php`

### File Upload Fails?
- Check `uploads/` folder exists and is writable
- Verify file size is under 5MB
- Check allowed file extensions

## Performance Tips

1. Enable PHP opcache
2. Use database indexing on frequently queried columns
3. Clean up old activity logs periodically
4. Compress CSS and JavaScript in production

## Security Checklist

- [ ] Change default admin password
- [ ] Enable HTTPS in production
- [ ] Set strong PHP session timeout
- [ ] Restrict file upload directory access
- [ ] Regular database backups
- [ ] Monitor activity logs
- [ ] Update PHP and MySQL regularly

## Next Steps

1. **Customize**: Modify CSS in `assets/css/style.css`
2. **Add Features**: Create new pages in respective modules
3. **Scale**: Optimize database queries for large datasets
4. **Deploy**: Set up on production server with SSL

---

For detailed documentation, see [README.md](README.md)
