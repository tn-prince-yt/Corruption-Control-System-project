<?php
/**
 * Review Complaint
 * Officer reviews and approves/rejects complaints
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require officer login
requireRole('officer');

$user_id = getUserId();
$user = getCurrentUser();

// Get complaint ID from URL
$complaint_id = intval($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    header("Location: pending_complaints.php");
    exit();
}

// Get complaint details
$stmt = $conn->prepare("SELECT c.*, u.name as citizen_name FROM complaints c 
LEFT JOIN users u ON c.user_id = u.id 
WHERE c.id = ? AND c.status = 'submitted'");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$complaint) {
    header("Location: pending_complaints.php");
    exit();
}

// Get evidence
$stmt = $conn->prepare("SELECT * FROM evidence WHERE complaint_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$evidence = $stmt->get_result();
$stmt->close();

$error = '';
$success = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = sanitizeInput($_POST['action'] ?? '');
    $comments = sanitizeInput($_POST['comments'] ?? '');
    
    if ($action === 'approve') {
        $status = 'approved';
        $stmt = $conn->prepare("UPDATE complaints SET status = ?, assigned_officer_id = ?, assigned_investigator_id = NULL WHERE id = ?");
        $stmt->bind_param("sii", $status, $user_id, $complaint_id);
        $stmt->execute();
        $stmt->close();
        
        logActivity($user_id, $complaint_id, 'COMPLAINT_APPROVED', 'Complaint approved by officer. ' . $comments);
        
        // Notify citizen
        createNotification($conn, $complaint['user_id'],
            'Your Complaint is Under Investigation',
            'An FIR has been filed for your complaint.',
            'info',
            APP_URL . '/citizen/view_complaint.php?id=' . $complaint_id
        );
        
        // Notify all admins
        $admins = $conn->query("SELECT id FROM users WHERE role='admin' AND status='active'");
        while ($admin = $admins->fetch_assoc()) {
            createNotification($conn, $admin['id'],
                'FIR Filed',
                'Officer filed FIR for complaint ID: ' . $complaint_id,
                'success',
                APP_URL . '/admin/view_complaint.php?id=' . $complaint_id
            );
        }
        
        $success = 'Complaint approved successfully!';
    } else if ($action === 'reject') {
        $status = 'rejected';
        $stmt = $conn->prepare("UPDATE complaints SET status = ?, assigned_officer_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $status, $user_id, $complaint_id);
        $stmt->execute();
        $stmt->close();
        
        logActivity($user_id, $complaint_id, 'COMPLAINT_REJECTED', 'Complaint rejected. Reason: ' . $comments);
        
        // Notify citizen about rejection
        createNotification($conn, $complaint['user_id'],
            'Complaint Status Updated',
            'Your complaint status has changed to: Rejected',
            'warning',
            APP_URL . '/citizen/view_complaint.php?id=' . $complaint_id
        );
        
        $success = 'Complaint rejected!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Complaint - <?php echo APP_NAME; ?></title>
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
                        <li><a href="pending_complaints.php">Pending Complaints</a></li>
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
            <a href="pending_complaints.php" style="color: var(--secondary-color); margin-bottom: 1rem; display: inline-block;">← Back to Pending</a>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo escapeHtml($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo escapeHtml($success); ?></div>
                <a href="pending_complaints.php" class="btn btn-primary">Back to Pending List</a>
            <?php else: ?>
                <!-- Complaint Details -->
                <div class="complaint-card" style="border-left: 4px solid var(--primary-color);">
                    <div class="complaint-header">
                        <div>
                            <h2 class="complaint-title"><?php echo escapeHtml($complaint['title']); ?></h2>
                            <p class="complaint-ref">Reference: CCS-<?php echo str_pad($complaint['id'], 5, '0', STR_PAD_LEFT); ?></p>
                        </div>
                    </div>

                    <div class="complaint-meta">
                        <div class="complaint-meta-item">
                            <span class="complaint-meta-label">Priority</span>
                            <span><?php echo formatPriority($complaint['priority']); ?></span>
                        </div>
                        <div class="complaint-meta-item">
                            <span class="complaint-meta-label">Category</span>
                            <span><?php echo escapeHtml($complaint['category']); ?></span>
                        </div>
                        <div class="complaint-meta-item">
                            <span class="complaint-meta-label">Location</span>
                            <span><?php echo escapeHtml($complaint['location']); ?></span>
                        </div>
                        <div class="complaint-meta-item">
                            <span class="complaint-meta-label">Submitted By</span>
                            <span><?php echo escapeHtml($complaint['citizen_name']); ?></span>
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <h3 style="margin-bottom: 0.5rem; color: var(--primary-color);">Description</h3>
                        <p><?php echo escapeHtml($complaint['description']); ?></p>
                    </div>
                </div>

                <!-- Evidence -->
                <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); margin-top: 2rem; box-shadow: var(--box-shadow);">
                    <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Evidence Files</h2>
                    <?php if ($evidence->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Uploaded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($file = $evidence->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo escapeHtml($file['file_name']); ?></td>
                                            <td><?php echo strtoupper($file['file_type']); ?></td>
                                            <td><?php echo formatFileSize($file['file_size']); ?></td>
                                            <td><?php echo formatDate($file['uploaded_at']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="color: #999;">No evidence files uploaded.</p>
                    <?php endif; ?>
                </div>

                <!-- Review Form -->
                <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); margin-top: 2rem; box-shadow: var(--box-shadow);">
                    <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">Review & Decision</h2>

                    <form method="POST">
                        <div class="form-group">
                            <label for="comments">Comments / Reason *</label>
                            <textarea id="comments" name="comments" rows="5" placeholder="Enter your review comments..." required></textarea>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" name="action" value="approve" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this complaint?');">Approve Complaint</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this complaint?');">Reject Complaint</button>
                            <a href="pending_complaints.php" class="btn btn-secondary">Cancel</a>
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

    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
</body>
</html>
