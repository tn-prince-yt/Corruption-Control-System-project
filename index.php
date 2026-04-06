<?php
/**
 * Main Index Page
 * Landing page and authentication redirect
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'config/session.php';
require_once 'config/helpers.php';

// If user is already logged in, redirect to their dashboard
if (isLoggedIn()) {
    $role = getUserRole();
    
    switch ($role) {
        case 'citizen':
            header("Location: " . APP_URL . "/citizen/dashboard.php");
            break;
        case 'admin':
            header("Location: " . APP_URL . "/admin/dashboard.php");
            break;
        case 'officer':
            header("Location: " . APP_URL . "/officer/dashboard.php");
            break;
        case 'investigator':
            header("Location: " . APP_URL . "/investigation/dashboard.php");
            break;
        default:
            session_destroy();
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Home</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title"><?php echo APP_NAME; ?></h1>
            <p class="auth-subtitle">Report Corruption and Track Progress</p>
            
            <?php
            // Display error message if present
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger">' . escapeHtml($_GET['error']) . '</div>';
            }
            ?>
            
            <div class="text-center mb-4">
                <p style="color: #666; margin-bottom: 1.5rem; line-height: 1.6;">
                    Welcome to the Corruption Control System. Please log in to your account or create a new one.
                </p>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-primary" style="flex: 1; text-align: center;">Login</a>
                <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Register</a>
            </div>
            
            <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #ddd;">
            
            <div style="background-color: #f9f9f9; padding: 1rem; border-radius: 6px; font-size: 0.9rem;">
                <h4 style="margin-bottom: 0.5rem; color: var(--primary-color);">About This System</h4>
                <ul style="list-style: disc; padding-left: 1.5rem; color: #666;">
                    <li>Submit corruption complaints with evidence</li>
                    <li>Track complaint status in real-time</li>
                    <li>Review investigation reports</li>
                    <li>Admin dashboard for system management</li>
                </ul>
            </div>
            
            <div style="text-align: center; margin-top: 2rem; font-size: 0.8rem; color: #999;">
                <p>&copy; 2024 Corruption Control System. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
