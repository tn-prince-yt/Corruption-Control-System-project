<?php
/**
 * Login Page
 * Handles user authentication
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// If user is already logged in, redirect
if (isLoggedIn()) {
    header("Location: " . APP_URL . "/index.php");
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check if account is active
            if ($user['status'] != 'active') {
                $error = 'Your account is ' . $user['status'];
            } else if (verifyPassword($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Log activity
                logActivity($user['id'], null, 'LOGIN', 'User logged in');
                
                // Redirect based on role
                switch ($user['role']) {
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
                        $error = 'Invalid user role';
                        break;
                }
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style>
        .login-container-wrapper {
            display: flex;
            gap: 3rem;
            align-items: center;
            max-width: 1100px;
            width: 100%;
        }

        .login-hero {
            flex: 1;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 500px;
        }

        .login-hero-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .login-hero h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .login-hero p {
            font-size: 0.95rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            line-height: 1.6;
        }

        .login-features {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .login-features li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .login-features li::before {
            content: '✓';
            color: #4ade80;
            font-weight: bold;
            margin-right: 0.75rem;
            font-size: 1.3rem;
        }

        .login-form-section {
            flex: 0 0 420px;
        }

        .form-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .form-link a {
            color: var(--secondary-color);
            font-weight: 600;
            word-break: break-word;
        }

        .form-link a:hover {
            text-decoration: underline;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            color: #999;
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: #e0e0e0;
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        /* Password visibility toggle */
        .password-toggle-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle-wrapper input {
            width: 100%;
            padding-right: 2.5rem !important;
        }

        .password-toggle-btn {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 1.1rem;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle-btn:hover {
            color: var(--secondary-color);
        }

        @media (max-width: 768px) {
            .login-container-wrapper {
                flex-direction: column;
                gap: 2rem;
            }

            .login-hero {
                min-height: auto;
                margin-bottom: 1rem;
            }

            .login-hero h2 {
                font-size: 1.8rem;
            }

            .login-form-section {
                flex: 1;
                width: 100%;
            }

            .auth-box {
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="login-container-wrapper">
            <!-- Hero Section -->
            <div class="login-hero">
                <div class="login-hero-icon">🛡️</div>
                <h2>Corruption Control System</h2>
                <p>A comprehensive platform for managing corruption complaints, investigations, and reports with transparency and accountability.</p>
                
                <ul class="login-features">
                    <li>Secure complaint submission</li>
                    <li>Real-time status tracking</li>
                    <li>Professional investigations</li>
                    <li>Transparent reporting</li>
                </ul>
            </div>

            <!-- Login Form -->
            <div class="login-form-section">
                <div class="auth-box" style="max-width: none; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                    <div class="auth-logo">
                        <div class="auth-logo-icon">🔐</div>
                        <h1 class="auth-title">Welcome Back</h1>
                        <p class="auth-subtitle">Sign in to your account</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?php echo escapeHtml($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" onsubmit="return validateLoginForm();" id="loginForm">
                        <div class="form-group">
                            <label for="email">📧 Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">🔑 Password</label>
                            <div class="password-toggle-wrapper">
                                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                                <button type="button" class="password-toggle-btn" onclick="toggleLoginPassword()" tabindex="-1">👁️</button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.9rem; font-size: 1.05rem;">
                            🚀 Sign In
                        </button>
                    </form>
                    
                    <div class="divider">OR</div>

                    <div class="form-link">
                        <p style="margin-bottom: 0.75rem;">Don't have an account?</p>
                        <a href="<?php echo APP_URL; ?>/auth/register.php">Create Account</a>
                    </div>

                    <div class="form-link" style="margin-top: 1rem; border-top: 1px solid #e0e0e0; padding-top: 1rem;">
                        <a href="<?php echo APP_URL; ?>/index.php">← Back to Home</a>
                    </div>

                    <div class="demo-credentials">
                        <strong>🎯 Demo Account:</strong>
                        <div style="margin-top: 0.5rem;">
                            Email: <code>admin@corruptioncontrol.com</code><br/>
                            Password: <code>admin123@Ab</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo APP_URL; ?>/assets/js/validation.js"></script>
    <script>
        function toggleLoginPassword() {
            const passwordInput = document.getElementById('password');
            const btn = document.querySelector('.password-toggle-btn');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                btn.textContent = '👁️‍🗨️';
            } else {
                passwordInput.type = 'password';
                btn.textContent = '👁️';
            }
        }
    </script>
</body>
</html>
