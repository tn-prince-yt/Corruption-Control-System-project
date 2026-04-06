<?php
/**
 * Session Management File
 * Handles session initialization and validation
 */

session_start();

// Check if session exists and is valid
if (isset($_SESSION['user_id'])) {
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        // Session expired
        session_destroy();
        header("Location: " . APP_URL . "/index.php?error=Session%20expired");
        exit();
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

/**
 * Debug: Get current user info (used in templates)
 */
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        $stmt->close();
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Check if user has any of the given roles
 */
function hasAnyRole($roles) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    return in_array($_SESSION['role'], $roles);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . APP_URL . "/index.php");
        exit();
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header("Location: " . APP_URL . "/index.php?error=Unauthorized%20access");
        exit();
    }
}

/**
 * Require any of the given roles
 */
function requireAnyRole($roles) {
    requireLogin();
    if (!hasAnyRole($roles)) {
        header("Location: " . APP_URL . "/index.php?error=Unauthorized%20access");
        exit();
    }
}

/**
 * Get logged in user ID
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get logged in user role
 */
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

?>
