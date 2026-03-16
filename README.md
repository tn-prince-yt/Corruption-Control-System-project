# Corruption Control System

A full-stack web application for managing corruption complaints online. Citizens can submit complaints with evidence, admins can review and approve them, and investigation officers can conduct investigations and submit reports.

## Features

- **Citizen Module**
  - User registration and login
  - Submit corruption complaints with title, category, location, date, and description
  - Upload evidence files (images, documents)
  - View complaint status in real-time
  - Track investigation progress

- **Admin Module**
  - Review pending complaints
  - Approve or reject complaints with reasons
  - Assign investigation officers to approved complaints
  - Manage officer availability
  - View all complaints with filtering options

- **Officer Module**
  - View assigned complaints
  - Access complainant information and uploaded evidence
  - Submit investigation reports with findings and recommendations
  - Mark cases as completed
  - Track case status

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP (Core PHP, no frameworks)
- **Database**: MySQL
- **Server**: Apache (or compatible web server)

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- Minimum 10MB disk space

## Installation & Setup

### Step 1: Extract Files

Extract the project files to your web server's root directory (usually `htdocs` for XAMPP or `www` for WAMP).

### Step 2: Create Database

1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Create a new database called `corruption_control_db`
3. Import the database schema:
   - Open the `db/schema.sql` file
   - Copy and paste the SQL content into the SQL query window in phpMyAdmin
   - Click "Go" to execute

Alternatively, use MySQL command line:
```bash
mysql -u root -p < db/schema.sql
```

### Step 3: Configure Database Connection

Edit `includes/config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'corruption_control_db');
```

### Step 4: Create Upload Directories

Ensure the `uploads` directory and its subdirectories have write permissions:

```bash
chmod -R 777 uploads/
```

For Windows, ensure the folder permissions allow the web server user to write files.

### Step 5: Access the Application

Open your browser and navigate to:
```
http://localhost/corruption-control-system/
```

Or if installed in the root:
```
http://localhost/
```

## Default Credentials

### Admin Account
- **Email**: admin@corruption.com
- **Password**: admin123

### Sample Officer Account
- **Email**: officer@corruption.com
- **Password**: officer123

**Note**: Change these passwords immediately in production!

## Folder Structure

```
corruption-control-system/
├── admin/              # Admin module files
│   ├── dashboard.php
│   ├── view_complaints.php
│   ├── approve_complaint.php
│   ├── process_approval.php
│   ├── manage_officers.php
│   └── toggle_officer_status.php
├── citizen/            # Citizen module files
│   ├── dashboard.php
│   ├── submit_complaint.php
│   ├── process_complaint.php
│   ├── view_complaints.php
│   └── view_complaint_detail.php
├── officer/            # Officer module files
│   ├── dashboard.php
│   ├── view_complaints.php
│   ├── view_complaint_detail.php
│   ├── submit_report.php
│   ├── process_report.php
│   └── mark_completed.php
├── auth/               # Authentication files
│   ├── login.php
│   ├── register.php
│   ├── process_login.php
│   ├── process_register.php
│   └── logout.php
├── css/                # Stylesheets
│   └── style.css
├── js/                 # JavaScript files
│   └── script.js
├── includes/           # PHP includes
│   ├── config.php
│   ├── functions.php
│   └── navbar.php
├── db/                 # Database files
│   └── schema.sql
├── uploads/            # User uploads directory
│   ├── evidence/
│   └── documents/
└── index.php           # Home page
```

## Database Tables

### users
- `user_id` - Primary key
- `email` - User email (unique)
- `password` - Hashed password
- `first_name` - First name
- `last_name` - Last name
- `phone` - Phone number
- `user_type` - citizen, admin, or officer
- `is_active` - Account status

### complaints
- `complaint_id` - Primary key
- `citizen_id` - Citizen who filed complaint (FK to users)
- `title` - Complaint title
- `description` - Detailed description
- `category` - Corruption category
- `location` - Location of incident
- `complaint_date` - Date of incident
- `evidence_count` - Number of evidence files
- `status` - pending, approved, rejected, under_investigation, completed
- `assigned_officer_id` - Assigned officer (FK to officers)
- `rejection_reason` - If rejected

### evidence
- `evidence_id` - Primary key
- `complaint_id` - Related complaint (FK)
- `file_name` - Original filename
- `file_type` - File extension
- `file_size` - File size in bytes
- `file_path` - Path to file
- `uploaded_by` - User who uploaded (FK to users)

### officers
- `officer_id` - Primary key
- `user_id` - Officer user account (FK to users)
- `department` - Department name
- `badge_number` - Unique badge number
- `designation` - Job title
- `is_available` - Availability status

### reports
- `report_id` - Primary key
- `complaint_id` - Related complaint (FK)
- `officer_id` - Officer who submitted (FK to officers)
- `findings` - Investigation findings
- `recommendations` - Recommendations
- `status` - draft or submitted
- `submitted_date` - Submission timestamp

## User Workflows

### Citizen Workflow
1. Register new account
2. Login with credentials
3. Submit complaint with details and evidence
4. View complaint status
5. Track investigation progress
6. View investigation report when completed

### Admin Workflow
1. Login with admin credentials
2. View pending complaints
3. Read complaint details and evidence
4. Approve complaint and assign officer, or reject with reason
5. Manage officer availability
6. Monitor all complaints

### Officer Workflow
1. Login with officer credentials
2. View assigned complaints
3. Review complaint details and evidence
4. Conduct investigation
5. Submit investigation report (can save as draft)
6. Submit report for approval
7. Mark case as completed

## Security Features

- Password hashing using PHP's password_hash()
- Session-based authentication
- Input sanitization to prevent SQL injection
- File upload validation
- User role-based access control
- Audit logging for all actions

## File Upload

- Maximum file size: 5MB
- Allowed formats: jpg, jpeg, png, gif, pdf, doc, docx
- Files stored in `uploads/evidence/` directory
- Automatic filename generation to prevent conflicts

## Responsive Design

The application is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones (iOS and Android)

## API Documentation

All interactions are form-based POST requests. No REST API is provided in this version.

## Troubleshooting

### Database Connection Error
- Check if MySQL service is running
- Verify credentials in `includes/config.php`
- Ensure database `corruption_control_db` exists

### File Upload Not Working
- Check `uploads/` folder permissions (should be 777)
- Verify PHP upload_max_filesize setting
- Check server error logs

### Login Not Working
- Verify email and password are correct
- Ensure user account is active (is_active = TRUE)
- Check browser cookies are enabled

### Pages Not Loading
- Ensure PHP is installed and configured
- Check Apache error logs
- Verify file permissions

## Performance Considerations

- Database indexes are created on frequently searched columns
- Sessions are used for client-side state management
- No external APIs or dependencies
- Lightweight CSS and JavaScript

## Future Enhancements

- Email notifications for complaint updates
- Advanced search and filtering
- Export reports to PDF
- Complaint categories dashboard
- Case statistics and analytics
- Two-factor authentication
- File versioning for reports
- Bulk complaint import

## License

This project is provided as-is for educational and organizational use.

## Support

For issues and questions, please contact the system administrator.

---

**Version**: 1.0  
**Last Updated**: March 2026
