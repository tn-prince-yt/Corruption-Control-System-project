<?php
/**
 * General Configuration File
 * Application-wide settings and constants
 */

// Application settings
define('APP_NAME', 'Corruption Control System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/corruptioncontrolsystem');

// Session settings
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_NAME', 'corruption_control_session');

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/corruptioncontrolsystem/uploads/');
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip']);

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REGEX', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/');

// Complaint statuses
define('COMPLAINT_STATUSES', [
    'submitted' => 'Submitted',
    'under_review' => 'Under Review',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
    'investigation' => 'Investigation',
    'closed' => 'Closed'
]);

// User roles
define('USER_ROLES', [
    'citizen' => 'Citizen',
    'admin' => 'Administrator',
    'officer' => 'Anti-Corruption Officer',
    'investigator' => 'Investigation Officer'
]);

// Priority levels
define('PRIORITY_LEVELS', [
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High',
    'critical' => 'Critical'
]);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/corruptioncontrolsystem/logs/error.log');

?>
