<?php
// Logout
include '../includes/config.php';
include '../includes/functions.php';

// Audit log
if (isset($_SESSION['user_id'])) {
    auditLog('LOGOUT', 'users', $_SESSION['user_id'], 'User logged out');
}

// Destroy session
session_destroy();

// Redirect to home
header("Location: " . SITE_URL . 'index.php');
exit();
