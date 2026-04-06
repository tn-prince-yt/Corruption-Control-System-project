# Deployment Guide

## Pre-Deployment Checklist

- [ ] Test all features locally
- [ ] Review and update all credentials
- [ ] Optimize database
- [ ] Backup source code
- [ ] Create database backup plan
- [ ] Configure server environment
- [ ] Review security settings
- [ ] Test SSL/HTTPS
- [ ] Update DNS records if needed
- [ ] Review file permissions
- [ ] Test file uploads on server
- [ ] Configure email notifications
- [ ] Set up database replication/backup
- [ ] Monitor performance metrics
- [ ] Create rollback plan

## Step 1: Server Setup

### Requirements
- PHP 7.4+ (preferred: 8.0+)
- MySQL 5.7+ (preferred: 8.0+)
- Apache 2.4+ with mod_rewrite enabled
- OpenSSL for HTTPS
- At least 1GB disk space

### Enable Required Apache Modules
```bash
# On Linux
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod deflate
sudo systemctl restart apache2

# On Windows (modify Apache httpd.conf)
# Uncomment: LoadModule rewrite_module modules/mod_rewrite.so
```

### Create Directory Structure
```bash
# Create project directory
mkdir -p /var/www/html/corruptioncontrolsystem
cd /var/www/html/corruptioncontrolsystem

# Copy all project files
# Set proper permissions
chmod -R 755 /var/www/html/corruptioncontrolsystem
chmod -R 755 /var/www/html/corruptioncontrolsystem/uploads
chmod -R 755 /var/www/html/corruptioncontrolsystem/logs

# Create logs directory if not exists
mkdir -p logs
chmod 777 logs
```

## Step 2: Database Setup

### Create Database
```sql
CREATE DATABASE corruption_control_system 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE corruption_control_system;
-- Import schema.sql
SOURCE /path/to/database/schema.sql;

-- Create backup user (optional but recommended)
CREATE USER 'ccs_backup'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT ON corruption_control_system.* TO 'ccs_backup'@'localhost';
FLUSH PRIVILEGES;
```

### Create Database User (Separate from root)
```sql
CREATE USER 'ccs_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON corruption_control_system.* TO 'ccs_user'@'localhost';
FLUSH PRIVILEGES;
```

### Create Indexes for Performance
```sql
-- These should be in schema.sql but verify they exist
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_user_role ON users(role);
CREATE INDEX idx_complaint_status ON complaints(status);
CREATE INDEX idx_complaint_user ON complaints(user_id);
CREATE INDEX idx_complaint_date ON complaints(created_at);
```

## Step 3: Application Configuration

### Update config/database.php
```php
<?php
// Database configuration for production
define('DB_HOST', 'localhost');
define('DB_USER', 'ccs_user');  // NOT root
define('DB_PASSWORD', 'secure_password_from_above');
define('DB_NAME', 'corruption_control_system');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Error handling for production
define('DB_ERROR_LOG', '/var/log/php/db_errors.log');
?>
```

### Update config/config.php
```php
<?php
// Production settings
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('APP_URL', 'https://yourdomain.com/corruptioncontrolsystem');

// Session security
define('SESSION_TIMEOUT', 1800);  // 30 minutes
define('SESSION_REGENERATE', true);

// File upload
define('UPLOADS_DIR', '/var/www/html/corruptioncontrolsystem/uploads');
define('MAX_FILE_SIZE', 5242880);  // 5MB

// Enable HTTPS
define('FORCE_HTTPS', true);
?>
```

### Create .env file (Production)
```
DB_HOST=localhost
DB_USER=ccs_user
DB_PASSWORD=secure_password
DB_NAME=corruption_control_system

APP_URL=https://yourdomain.com
APP_ENV=production
APP_DEBUG=false

FORCE_HTTPS=true
SESSION_TIMEOUT=1800
```

## Step 4: Apache Virtual Host Configuration

### Create Virtual Host File
File: `/etc/apache2/sites-available/corruptioncontrolsystem.conf`
```apache
<VirtualHost *:80>
    ServerName corruptioncontrolsystem.com
    ServerAlias www.corruptioncontrolsystem.com
    DocumentRoot /var/www/html/corruptioncontrolsystem
    
    # Redirect HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName corruptioncontrolsystem.com
    ServerAlias www.corruptioncontrolsystem.com
    DocumentRoot /var/www/html/corruptioncontrolsystem
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
    SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/ccs_error.log
    CustomLog ${APACHE_LOG_DIR}/ccs_access.log combined
    
    # PHP Configuration
    <Directory /var/www/html/corruptioncontrolsystem>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Enable Virtual Host
```bash
sudo a2ensite corruptioncontrolsystem.conf
sudo apache2ctl configtest  # Should return "Syntax OK"
sudo systemctl restart apache2
```

## Step 5: SSL/HTTPS Configuration

### Use Let's Encrypt (Free SSL)
```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renew certificates
sudo systemctl enable certbot.timer
```

### Alternative: Self-Signed Certificate (Testing Only)
```bash
sudo openssl req -x509 -nodes -days 365 \
    -newkey rsa:2048 \
    -keyout /etc/ssl/private/corruptioncontrol.key \
    -out /etc/ssl/certs/corruptioncontrol.crt
```

## Step 6: PHP Security Configuration

### Update php.ini
```ini
; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; File uploads
upload_max_filesize = 20M
post_max_size = 20M
file_uploads = On
upload_tmp_dir = /tmp

; Session security
session.cookie_httponly = On
session.cookie_secure = On
session.cookie_samesite = "Strict"
session.gc_maxlifetime = 1800

; Error logging
display_errors = Off
error_log = /var/log/php/error.log
log_errors = On

; Error reporting
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
```

## Step 7: Database Backup Strategy

### Automated Daily Backup (Linux Cron)
```bash
# Edit crontab
crontab -e

# Add daily backup at 2 AM
0 2 * * * /usr/bin/mysqldump -u ccs_backup -ppassword corruption_control_system | gzip > /backups/ccs_backup_$(date +\%Y\%m\%d_\%H\%M\%S).sql.gz

# Keep only last 7 days
0 3 * * * find /backups -name "ccs_backup_*.sql.gz" -mtime +7 -delete
```

### Manual Backup Command
```bash
mysqldump -u ccs_user -p corruption_control_system > backup_$(date +%Y%m%d_%H%M%S).sql
gzip backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore from Backup
```bash
gunzip backup.sql.gz
mysql -u ccs_user -p corruption_control_system < backup.sql
```

## Step 8: Monitoring and Logging

### Set Up Application Logs
```bash
# Create log directory
mkdir -p /var/log/corruptioncontrol
chmod 755 /var/log/corruptioncontrol

# Create log rotation config
cat > /etc/logrotate.d/corruptioncontrol << 'EOF'
/var/log/corruptioncontrol/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload apache2 > /dev/null 2>&1 || true
    endscript
}
EOF
```

### Monitor Server Health
```bash
# Check disk space
df -h

# Check MySQL status
sudo systemctl status mysql

# Check Apache status
sudo systemctl status apache2

# Check logs
tail -f /var/log/apache2/ccs_error.log
tail -f /var/log/php/error.log
```

## Step 9: Post-Deployment Testing

### Run Test Suite
1. **User Authentication**
   - Create new account
   - Login with credentials
   - Logout and re-login
   
2. **Complaint Submission**
   - Submit complaint with evidence
   - Verify file uploads work
   - Check database record created
   
3. **Officer Workflow**
   - Login as officer
   - Review and approve complaint
   - Verify status updated
   
4. **Investigator Workflow**
   - Accept case assignment
   - Register FIR
   - Upload evidence
   - Create report
   
5. **Admin Functions**
   - Manage users
   - View reports
   - Check analytics

### Performance Testing
```bash
# Monitor during test
top  # Shows CPU/Memory
iotop  # Shows I/O usage
htop  # Better than top

# Test database query performance
EXPLAIN SELECT * FROM complaints WHERE status='approved';
```

### Security Testing
- [ ] Try SQL injection in login form
- [ ] Test CSRF protection (should fail cross-site requests)
- [ ] Verify HTTPS is enforced
- [ ] Check sensitive files aren't accessible (config files, database backups)
- [ ] Test file upload with malicious files (should be blocked)

## Step 10: Performance Optimization

### Enable PHP Caching
```ini
; In php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### Enable MySQL Query Cache
```sql
SET GLOBAL query_cache_type = ON;
SET GLOBAL query_cache_size = 268435456;  # 256MB
```

### Optimize Database Tables
```bash
# Connect to MySQL and optimize
mysql -u ccs_user -p corruption_control_system
ANALYZE TABLE users;
ANALYZE TABLE complaints;
OPTIMIZE TABLE users;
OPTIMIZE TABLE complaints;
```

## Step 11: Security Hardening

### File Permissions
```bash
# Web server owns uploads
chown -R www-data:www-data /var/www/html/corruptioncontrolsystem/uploads

# Restrict config files
chmod 640 /var/www/html/corruptioncontrolsystem/config/database.php
chown www-data:www-data /var/www/html/corruptioncontrolsystem/config/database.php

# Database writes
chmod 755 /var/www/html/corruptioncontrolsystem/uploads
```

### Firewall Configuration
```bash
# Allow only necessary ports
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw allow 3306/tcp # MySQL (only from localhost)
```

### Update System
```bash
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install unattended-upgrades
```

## Step 12: Monitoring and Maintenance

### Weekly Tasks
- [ ] Check disk space usage
- [ ] Review error logs
- [ ] Verify backups completed successfully
- [ ] Monitor application performance

### Monthly Tasks
- [ ] Review user accounts for inactive users
- [ ] Optimize database indexes
- [ ] Check for security updates
- [ ] Review access logs for suspicious activity

### Quarterly Tasks
- [ ] Load test the application
- [ ] Test disaster recovery procedure
- [ ] Review and update security policies
- [ ] Database integrity check

## Troubleshooting Deployment

### 500 Internal Server Error
```bash
# Check Apache error log
tail -f /var/log/apache2/error.log

# Check PHP error log
tail -f /var/log/php/error.log

# Verify file permissions
ls -la /var/www/html/corruptioncontrolsystem/
```

### Database Connection Failed
```bash
# Test MySQL connection
mysql -u ccs_user -p -e "SELECT 1"

# Check MySQL is running
sudo systemctl status mysql

# Verify credentials in config/database.php
```

### Can't Upload Files
```bash
# Check directory permissions
ls -la /var/www/html/corruptioncontrolsystem/uploads

# Make writable
chmod 777 /var/www/html/corruptioncontrolsystem/uploads

# Check Apache user
ps aux | grep apache
```

## Rollback Plan

If deployment fails:
1. Stop Apache: `sudo systemctl stop apache2`
2. Restore code from backup: `git checkout <previous-tag>`
3. Restore database: `mysql -u user -p db < backup.sql`
4. Restart Apache: `sudo systemctl start apache2`
5. Run tests again

---

**Status**: Production Ready
**Last Updated**: 2024
**Support**: See TROUBLESHOOTING.md for common issues
