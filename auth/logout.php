<?php
/**
 * Logout Page
 * Handles user logout
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Log the logout action
if (isLoggedIn()) {
    logActivity(getUserId(), null, 'LOGOUT', 'User logged out');
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: " . APP_URL . "/index.php?message=You%20have%20been%20logged%20out");
exit();
?>
