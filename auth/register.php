<?php
/**
 * Register Page
 * Handles user registration
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

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $role = sanitizeInput($_POST['role'] ?? '');
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($phone) || empty($role)) {
        $error = 'All fields are required';
    } else if (!validateEmail($email)) {
        $error = 'Invalid email format';
    } else if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else if (!validatePhone($phone)) {
        $error = 'Invalid phone number (must be 10 digits)';
    } else if (!in_array($role, ['citizen', 'officer', 'investigator'])) {
        $error = 'Invalid role selected';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered';
            $stmt->close();
        } else {
            $stmt->close();
            
            // Hash password
            $hashed_password = hashPassword($password);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role, department, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
            
            $department = ($role === 'officer') ? 'Anti-Corruption' : (($role === 'investigator') ? 'Investigation' : '');
            
            $stmt->bind_param("sssssss", $name, $email, $hashed_password, $phone, $address, $role, $department);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! Please login to your account.';
                $stmt->close();
                
                // Redirect to login after 2 seconds
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "' . APP_URL . '/auth/login.php";
                    }, 2000);
                </script>';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style>
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .register-wrapper {
            width: 100%;
            max-width: 500px;
        }

        .form-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .form-link a {
            color: var(--secondary-color);
            font-weight: 600;
            transition: all 0.3s ease;
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

        .form-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .form-section:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .form-section-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .password-requirements {
            background-color: #fff3cd;
            border-left: 3px solid #f39c12;
            padding: 0.75rem;
            border-radius: 4px;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #856404;
        }

        .password-requirements ul {
            list-style: none;
            margin: 0.5rem 0 0 0;
            padding: 0;
        }

        .password-requirements li {
            display: flex;
            align-items: center;
            margin: 0.3rem 0;
            font-size: 0.8rem;
        }

        .password-requirements li::before {
            content: '○';
            margin-right: 0.5rem;
            color: #f39c12;
            font-size: 1rem;
            font-weight: bold;
        }

        .password-requirements li.met {
            color: #27ae60;
        }

        .password-requirements li.met::before {
            content: '✓';
            color: #27ae60;
            font-weight: bold;
        }

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
            transition: color 0.3s ease;
        }

        .password-toggle-btn:hover {
            color: var(--secondary-color);
        }

        .role-info {
            background-color: #e3f2fd;
            border-left: 3px solid var(--secondary-color);
            padding: 0.75rem;
            border-radius: 4px;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #1565c0;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .register-wrapper {
                max-width: 100%;
            }

            .form-section-title {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container register-container">
        <div class="register-wrapper">
            <!-- Registration Form -->
            <div class="auth-box">
                <div class="auth-logo">
                    <div class="auth-logo-icon">✍️</div>
                    <h1 class="auth-title">Join Us</h1>
                    <p class="auth-subtitle">Create your account in minutes</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Registration Error:</strong> <?php echo escapeHtml($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <strong>✓ Success!</strong> <?php echo escapeHtml($success); ?>
                        <p style="margin-top: 0.5rem;">Redirecting to login...</p>
                    </div>
                <?php else: ?>
                    <form method="POST" onsubmit="return validateRegisterForm();" id="registerForm">
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <div class="form-section-title">👤 Personal Information</div>
                            
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">📧 Email Address</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">📱 Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="10-digit mobile number" maxlength="10" pattern="[0-9]{10}" required>
                                <small style="color: #999; display: block; margin-top: 0.25rem;">Format: 10 digits (e.g., 9876543210)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">📍 Address</label>
                                <textarea id="address" name="address" placeholder="Enter your address" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Account Setup Section -->
                        <div class="form-section">
                            <div class="form-section-title">🔐 Account Setup</div>
                            
                            <div class="form-group">
                                <label for="role">👨‍💼 Select Your Role</label>
                                <select id="role" name="role" onchange="updateRoleInfo()" required>
                                    <option value="">-- Choose Your Role --</option>
                                    <option value="citizen">Citizen - Submit Complaints</option>
                                    <option value="officer">Anti-Corruption Officer - Review & Approve</option>
                                    <option value="investigator">Investigation Officer - Investigate & Report</option>
                                </select>
                                <div id="roleInfo" class="role-info" style="display: none;"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">🔑 Password</label>
                                <div class="password-toggle-wrapper">
                                    <input type="password" id="password" name="password" placeholder="Create a strong password" oninput="checkPasswordStrength()" required>
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword('password')">👁️</button>
                                </div>
                                <div class="password-requirements">
                                    <strong style="color: #333;">Password must include:</strong>
                                    <ul>
                                        <li id="req-length">At least 8 characters</li>
                                        <li id="req-upper">One uppercase letter (A-Z)</li>
                                        <li id="req-lower">One lowercase letter (a-z)</li>
                                        <li id="req-digit">One number (0-9)</li>
                                        <li id="req-special">One special character (@$!%*?&)</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">✓ Confirm Password</label>
                                <div class="password-toggle-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                                    <button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password')">👁️</button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.9rem; font-size: 1.05rem;">
                            🚀 Create Account
                        </button>
                    </form>
                    
                    <div class="form-link">
                        <p style="margin-bottom: 0.75rem;">Already have an account?</p>
                        <a href="<?php echo APP_URL; ?>/auth/login.php">Sign In</a>
                    </div>
                <?php endif; ?>
                
                <div class="form-link mt-3" style="border-top: 1px solid #e0e0e0; padding-top: 1rem;">
                    <a href="<?php echo APP_URL; ?>/index.php">← Back to Home</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo APP_URL; ?>/assets/js/validation.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const btn = event.target.closest('.password-toggle-btn');
            
            if (field.type === 'password') {
                field.type = 'text';
                btn.textContent = '👁️‍🗨️';
            } else {
                field.type = 'password';
                btn.textContent = '👁️';
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const requirements = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password),
                lower: /[a-z]/.test(password),
                digit: /\d/.test(password),
                special: /[@$!%*?&]/.test(password)
            };

            updateRequirement('length', requirements.length);
            updateRequirement('upper', requirements.upper);
            updateRequirement('lower', requirements.lower);
            updateRequirement('digit', requirements.digit);
            updateRequirement('special', requirements.special);
        }

        function updateRequirement(id, met) {
            const elem = document.getElementById('req-' + id);
            if (met) {
                elem.classList.add('met');
            } else {
                elem.classList.remove('met');
            }
        }

        function updateRoleInfo() {
            const role = document.getElementById('role').value;
            const roleInfo = document.getElementById('roleInfo');
            const infoMap = {
                'citizen': '📢 As a citizen, you can submit corruption complaints and track their status in real-time.',
                'officer': '🔍 As an officer, you\'ll review complaints and decide whether to approve or reject them.',
                'investigator': '📋 As an investigator, you\'ll handle cases, create FIRs, and generate investigation reports.'
            };

            if (role && infoMap[role]) {
                roleInfo.textContent = infoMap[role];
                roleInfo.style.display = 'block';
            } else {
                roleInfo.style.display = 'none';
            }
        }
    </script>
</body>
</html>
