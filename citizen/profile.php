<?php
/**
 * Citizen Profile Page
 * Displays and allows editing of citizen profile
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require citizen login
requireRole('citizen');

$user_id = getUserId();
$user = getCurrentUser();
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($name) || empty($phone)) {
        $error = 'Name and phone are required';
    } else if (!validatePhone($phone)) {
        $error = 'Invalid phone number';
    } else {
        // Update basic info
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $address, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Update password if provided
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $error = 'Please enter current password to change password';
            } else if (!verifyPassword($current_password, $user['password'])) {
                $error = 'Current password is incorrect';
            } else if ($new_password !== $confirm_password) {
                $error = 'New passwords do not match';
            } else if (strlen($new_password) < 8) {
                $error = 'New password must be at least 8 characters';
            } else {
                $hashed_password = hashPassword($new_password);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        if (empty($error)) {
            $success = 'Profile updated successfully!';
            $_SESSION['name'] = $name;
            $user = getCurrentUser();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo"><?php echo APP_NAME; ?></div>
                <nav>
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="submit_complaint.php">Submit Complaint</a></li>
                        <li><a href="my_complaints.php">My Complaints</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li class="user-menu">
                            <span class="user-info"><?php echo escapeHtml($user['name']); ?></span>
                            <a href="../auth/logout.php" class="logout-btn">Logout</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container" style="max-width: 600px;">
            <h1 style="color: var(--primary-color); margin-bottom: 2rem;">My Profile</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo escapeHtml($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo escapeHtml($success); ?></div>
            <?php endif; ?>

            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <form method="POST">
                    <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">Personal Information</h2>

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo escapeHtml($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo escapeHtml($user['email']); ?>" disabled style="background-color: #f5f5f5; cursor: not-allowed;">
                        <small style="color: #999; display: block; margin-top: 0.5rem;">Email cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo escapeHtml($user['phone']); ?>" maxlength="10" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo escapeHtml($user['address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <input type="text" id="role" name="role" value="<?php echo getRoleName($user['role']); ?>" disabled style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>

                    <hr style="margin: 2rem 0; border: none; border-top: 1px solid #ddd;">

                    <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">Change Password</h2>

                    <div class="form-group">
                        <label for="current_password">Current Password (leave blank to keep current)</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Leave blank if not changing password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Leave blank if not changing password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Leave blank if not changing password">
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?php echo APP_URL; ?>/assets/js/validation.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
</body>
</html>
