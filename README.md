# Corruption Control System

A comprehensive full-stack web application for managing corruption complaints, investigations, and reports.

## Project Overview

The Corruption Control System is a complete platform that enables citizens to submit corruption complaints, anti-corruption officers to review and approve them, and investigation officers to conduct investigations and generate reports.

### Key Features

- **Citizen Portal**: Submit complaints with evidence, track status, view reports
- **Admin Dashboard**: Manage users and monitor all complaints
- **Anti-Corruption Officer Panel**: Review and approve/reject complaints
- **Investigation Officer Panel**: Handle approved cases, register FIRs, generate reports
- **Complete Tracking System**: Status updates throughout complaint lifecycle
- **Evidence Management**: Upload and manage evidence files
- **Report Generation**: Create and submit investigation reports
- **User Management**: Role-based access control

## Technology Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP (Pure PHP, no frameworks)
- **Database**: MySQL
- **Server**: Apache with mod_rewrite

## System Actors & Roles

1. **Citizen**: Submits complaints, uploads evidence, tracks progress
2. **Admin**: Manages users, monitors all complaints, generates reports
3. **Anti-Corruption Officer**: Reviews complaints, approves or rejects
4. **Investigation Officer**: Handles approved cases, files FIRs, creates reports

## Installation Instructions

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server with mod_rewrite enabled
- Composer (optional)

### Step 1: Database Setup

1. Open phpMyAdmin or MySQL command line
2. Create a new database (suggested name: `corruption_control_system`)
3. Import the schema file:

```bash
mysql -u root -p corruption_control_system < database/schema.sql
```

Or paste the contents of `database/schema.sql` in phpMyAdmin.

### Step 2: Configure Database Connection

Edit `config/database.php` and update the following credentials:

```php
define('DB_HOST', 'localhost');  // Your database host
define('DB_USER', 'root');       // Your MySQL username
define('DB_PASSWORD', '');       // Your MySQL password
define('DB_NAME', 'corruption_control_system');
```

### Step 3: Configure Application Settings

Edit `config/config.php` and update:

```php
define('APP_URL', 'http://localhost/corruptioncontrolsystem');
```

Replace with your actual application URL.

### Step 4: File Permissions

Make sure the uploads directory is writable:

```bash
chmod -R 755 uploads/
chmod -R 755 uploads/complaints/
chmod -R 755 uploads/reports/
```

### Step 5: Access the Application

Navigate to: `http://localhost/corruptioncontrolsystem/index.php`

## Default Credentials

### Admin Account
- **Email**: admin@corruptioncontrol.com
- **Password**: admin123@Ab (change this immediately!)

### Sample Officer Account
- **Email**: rajesh.officer@corruptioncontrol.com
- **Password**: admin123@Ab

### Sample Investigator Account
- **Email**: priya.investigator@corruptioncontrol.com
- **Password**: admin123@Ab

## Folder Structure

```
corruptioncontrolsystem/
├── config/                 # Configuration files
│   ├── database.php       # Database connection
│   ├── config.php         # App settings
│   ├── session.php        # Session management
│   └── helpers.php        # Helper functions
├── auth/                  # Authentication pages
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── citizen/               # Citizen module
│   ├── dashboard.php
│   ├── submit_complaint.php
│   ├── my_complaints.php
│   ├── view_complaint.php
│   └── profile.php
├── admin/                 # Admin module
│   ├── dashboard.php
│   ├── manage_users.php
│   ├── manage_complaints.php
│   ├── view_complaint.php
│   └── reports.php
├── officer/               # Anti-Corruption Officer module
│   ├── dashboard.php
│   ├── pending_complaints.php
│   ├── review_complaint.php
│   ├── assigned_complaints.php
│   └── view_complaint.php
├── investigation/         # Investigation Officer module
│   ├── dashboard.php
│   ├── pending_assignment.php
│   ├── take_case.php
│   ├── my_cases.php
│   ├── view_case.php
│   ├── create_report.php
│   └── close_case.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── main.js
│       └── validation.js
├── uploads/
│   ├── complaints/        # Complaint evidence storage
│   └── reports/           # Report storage
├── database/
│   └── schema.sql         # Database schema
├── index.php              # Landing page
└── README.md
```

## Database Schema

### Tables

- **users**: User accounts (citizens, officers, admins, investigators)
- **complaints**: Corruption complaints
- **evidence**: Evidence files attached to complaints
- **fir**: First Information Reports
- **investigation_reports**: Investigation findings and recommendations
- **activity_log**: Track all user actions
- **comments**: Internal notes on complaints

## User Workflow

### Citizen Flow
1. Register/Login
2. Submit complaint with evidence
3. Receive complaint reference number
4. Track complaint status
5. View final report when available

### Admin Flow
1. Login to dashboard
2. View all users and complaints
3. Manage user accounts
4. Generate system reports

### Officer Flow
1. Login to dashboard
2. Review pending complaints
3. Approve or reject complaints
4. Assign to investigation officers

### Investigator Flow
1. Accept assigned cases
2. Register FIR
3. Upload investigation evidence
4. Create investigation reports
5. Close cases

## Security Features

- Bcrypt password hashing
- SQL injection prevention (prepared statements)
- Session-based authentication
- Role-based access control
- XSS prevention (HTMLspecialchars escaping)
- File upload validation
- Password strength requirements

## Features Description

### Complaint Management
- Citizens can submit detailed complaints
- Multiple file uploads as evidence
- Status tracking in real-time
- Priority classification (low, medium, high, critical)
- Category classification (Bribery, Embezzlement, Fraud, etc.)

### Investigation System
- Officers review and approve/reject complaints
- Investigators take case assignment
- Automatic FIR generation with unique numbers
- Evidence management throughout investigation
- Investigation report generation with findings and recommendations

### User Management
- Admin can activate/suspend/delete users
- Different roles with specific permissions
- User profile management
- Activity logging for audit trail

### Reporting
- Complaint statistics dashboard
- Category-wise distribution
- Priority-wise analysis
- Average resolution time tracking
- Export-ready data

## Password Policy

- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one digit
- At least one special character (@$!%*?&)

Example valid password: `SecurePass123!`

## Common Issues & Solutions

### Issue: Database connection fails
**Solution**: 
- Check MySQL credentials in `config/database.php`
- Ensure MySQL server is running
- Verify database name exists

### Issue: File upload not working
**Solution**:
- Check folder permissions on `uploads/` directory
- Ensure PHP has write permissions
- Verify PHP upload_max_filesize setting

### Issue: Session not persisting
**Solution**:
- Ensure cookies are enabled in browser
- Check PHP session.save_path is writable
- Verify SESSION_TIMEOUT setting in config.php

### Issue: Login redirects to home page
**Solution**:
- Check if passwords are hashing correctly
- Verify user exists in database
- Check user status is 'active'

## Feature Roadmap

- [ ] Email notifications
- [ ] SMS alerts
- [ ] Dashboard charts and analytics
- [ ] Advanced search and filtering
- [ ] Batch complaint uploads
- [ ] API integration
- [ ] Mobile application
- [ ] Two-factor authentication
- [ ] Audit log download
- [ ] Complaint templates

## API Documentation

The system uses standard HTTP methods (GET, POST) with session-based authentication. No separate API endpoints are exposed currently.

## Support & Maintenance

### Regular Maintenance Tasks
- Backup database weekly
- Review activity logs monthly
- Update user credentials quarterly
- Clean up old temporary files

### Database Backup
```bash
mysqldump -u root -p corruption_control_system > backup_$(date +%Y%m%d).sql
```

## License

This project is proprietary and confidential.

## Contact

For issues or questions, please contact the system administrator.

---

**Version**: 1.0.0  
**Last Updated**: 2024  
**Status**: Production Ready
