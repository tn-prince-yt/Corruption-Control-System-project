<?php
// Login Processing
include '../includes/config.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $user_type = sanitizeInput($_POST['user_type']);
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        // Check user in database
        $email = $conn->real_escape_string($email);
        $user_type = $conn->real_escape_string($user_type);
        
        $query = "SELECT * FROM users WHERE email = '$email' AND user_type = '$user_type' AND is_active = TRUE";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (verifyPassword($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Audit log
                auditLog('LOGIN', 'users', $user['user_id'], 'User logged in');
                
                // Redirect based on user type
                if ($user_type === 'admin') {
                    redirect(SITE_URL . 'admin/dashboard.php');
                } elseif ($user_type === 'officer') {
                    redirect(SITE_URL . 'officer/dashboard.php');
                } else {
                    redirect(SITE_URL . 'citizen/dashboard.php');
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "User not found or account is inactive.";
        }
    }
} else {
    $error = null;
}

// Redirect to login page with error if any
if (isset($error)) {
    $_SESSION['login_error'] = $error;
    redirect(SITE_URL . 'auth/login.php');
}
