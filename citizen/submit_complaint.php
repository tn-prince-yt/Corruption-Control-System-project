<?php
/**
 * Submit Complaint Page
 * Allows citizens to submit new complaints with evidence
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $priority = sanitizeInput($_POST['priority'] ?? 'medium');
    
    // Validate inputs
    if (empty($title) || empty($description) || empty($category) || empty($location)) {
        $error = 'All fields are required';
    } else if (strlen($description) < 50) {
        $error = 'Description must be at least 50 characters';
    } else {
        // Insert complaint
        $status = 'submitted';
        $stmt = $conn->prepare("INSERT INTO complaints (user_id, title, description, category, location, status, priority) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $title, $description, $category, $location, $status, $priority);
        
        if ($stmt->execute()) {
            $complaint_id = $stmt->insert_id;
            $stmt->close();
            
            // Handle file uploads
            if (isset($_FILES['evidence']) && is_array($_FILES['evidence']['name'])) {
                $upload_dir = UPLOAD_DIR . 'complaints/' . $complaint_id . '/';
                
                for ($i = 0; $i < count($_FILES['evidence']['name']); $i++) {
                    $file = [
                        'name' => $_FILES['evidence']['name'][$i],
                        'tmp_name' => $_FILES['evidence']['tmp_name'][$i],
                        'size' => $_FILES['evidence']['size'][$i]
                    ];
                    
                    $upload_result = uploadFile($file, $upload_dir);
                    
                    if ($upload_result['success']) {
                        $stmt = $conn->prepare("INSERT INTO evidence (complaint_id, uploaded_by, file_name, file_path, file_type, file_size, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $file_type = pathinfo($upload_result['original_name'], PATHINFO_EXTENSION);
                        $desc = "Initial evidence file";
                        $stmt->bind_param("iisssis", $complaint_id, $user_id, $upload_result['original_name'], $upload_result['filepath'], $file_type, $upload_result['size'], $desc);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
            
            logActivity($user_id, $complaint_id, 'COMPLAINT_SUBMITTED', 'New complaint submitted');
            
            // Notify all admins
            $admins = $conn->query("SELECT id FROM users WHERE role='admin' AND status='active'");
            while ($admin = $admins->fetch_assoc()) {
                createNotification($conn, $admin['id'],
                    'New Complaint Submitted',
                    'A new complaint has been submitted: ' . $title,
                    'info',
                    APP_URL . '/admin/view_complaint.php?id=' . $complaint_id
                );
            }
            
            // Notify all officers
            $officers = $conn->query("SELECT id FROM users WHERE role='officer' AND status='active'");
            while ($officer = $officers->fetch_assoc()) {
                createNotification($conn, $officer['id'],
                    'New Complaint Pending Review',
                    'Complaint pending your review: ' . $title,
                    'warning',
                    APP_URL . '/officer/view_complaint.php?id=' . $complaint_id
                );
            }
            
            $success = 'Complaint submitted successfully! Reference ID: CCS-' . str_pad($complaint_id, 5, '0', STR_PAD_LEFT);
        } else {
            $error = 'Failed to submit complaint. Please try again.';
            $stmt->close();
        }
    }
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint - <?php echo APP_NAME; ?></title>
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
                        <?php renderNotificationBell($conn, $user_id); ?>
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
        <div class="container">
            <h1 style="color: var(--primary-color); margin-bottom: 2rem;">Submit New Complaint</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo escapeHtml($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo escapeHtml($success); ?></div>
                <a href="my_complaints.php" class="btn btn-primary">View My Complaints</a>
            <?php else: ?>
                <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                    <form method="POST" enctype="multipart/form-data" onsubmit="return validateComplaintForm();" id="complaintForm">
                        <div class="form-group">
                            <label for="title">Complaint Title*</label>
                            <input type="text" id="title" name="title" placeholder="Brief title of the complaint" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description*</label>
                            <textarea id="description" name="description" placeholder="Provide detailed description of the issue (minimum 50 characters)" required></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                            <div class="form-group">
                                <label for="category">Category*</label>
                                <select id="category" name="category" required>
                                    <option value="">-- Select Category --</option>
                                    <option value="Bribery">Bribery</option>
                                    <option value="Embezzlement">Embezzlement</option>
                                    <option value="Fraud">Fraud</option>
                                    <option value="Extortion">Extortion</option>
                                    <option value="Nepotism">Nepotism</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select id="priority" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="location">Location of Incident*</label>
                            <input type="text" id="location" name="location" placeholder="Where did the incident occur?" required>
                        </div>

                        <div class="form-group">
                            <label for="evidence">Upload Evidence Files</label>
                            <input type="file" id="evidence" name="evidence[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.txt,.zip">
                            <small style="color: #999; display: block; margin-top: 0.5rem;">
                                Accepted formats: PDF, DOC, DOCX, JPG, PNG, GIF, TXT, ZIP (Max 5MB per file)
                            </small>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">Submit Complaint</button>
                            <a href="my_complaints.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
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
