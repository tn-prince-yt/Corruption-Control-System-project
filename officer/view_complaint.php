<?php
/**
 * View Complaint (Officer)
 * Officer view of complaint details
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
    header("Location: assigned_complaints.php");
    exit();
}

// Get complaint details
$stmt = $conn->prepare("SELECT c.*, 
    u.name as citizen_name, u.email as citizen_email,
    i.name as investigator_name
FROM complaints c
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN users i ON c.assigned_investigator_id = i.id
WHERE c.id = ? AND c.assigned_officer_id = ?");
$stmt->bind_param("ii", $complaint_id, $user_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$complaint) {
    header("Location: assigned_complaints.php");
    exit();
}

// Get evidence
$stmt = $conn->prepare("SELECT * FROM evidence WHERE complaint_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$evidence = $stmt->get_result();
$stmt->close();

// Get FIR if exists
$stmt = $conn->prepare("SELECT * FROM fir WHERE complaint_id = ?");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$fir = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Complaint - <?php echo APP_NAME; ?></title>
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
                        <li><a href="assigned_complaints.php">Assigned Complaints</a></li>
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
            <a href="assigned_complaints.php" style="color: var(--secondary-color); margin-bottom: 1rem; display: inline-block;">← Back to Complaints</a>

            <div class="complaint-card" style="border-left: 4px solid var(--primary-color);">
                <div class="complaint-header">
                    <div>
                        <h2 class="complaint-title"><?php echo escapeHtml($complaint['title']); ?></h2>
                        <p class="complaint-ref">Reference: CCS-<?php echo str_pad($complaint['id'], 5, '0', STR_PAD_LEFT); ?></p>
                    </div>
                </div>

                <div class="complaint-meta">
                    <div class="complaint-meta-item">
                        <span class="complaint-meta-label">Status</span>
                        <span><?php echo formatStatus($complaint['status']); ?></span>
                    </div>
                    <div class="complaint-meta-item">
                        <span class="complaint-meta-label">Priority</span>
                        <span><?php echo formatPriority($complaint['priority']); ?></span>
                    </div>
                    <div class="complaint-meta-item">
                        <span class="complaint-meta-label">Category</span>
                        <span><?php echo escapeHtml($complaint['category']); ?></span>
                    </div>
                </div>

                <div style="margin-top: 1rem;">
                    <h3 style="margin-bottom: 0.5rem; color: var(--primary-color);">Description</h3>
                    <p><?php echo escapeHtml($complaint['description']); ?></p>
                </div>

                <div style="margin-top: 1rem; padding: 1rem; background-color: #f0f4f8; border-radius: var(--border-radius);">
                    <strong>Submitted by:</strong> <?php echo escapeHtml($complaint['citizen_name']); ?> (<?php echo escapeHtml($complaint['citizen_email']); ?>)
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($file = $evidence->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo APP_URL . '/uploads/complaints/' . $complaint_id . '/' . basename($file['file_path']); ?>" 
                                               target="_blank" 
                                               style="color: #2E86AB; font-weight: 600;">
                                                <?php echo escapeHtml($file['file_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo strtoupper($file['file_type']); ?></td>
                                        <td><?php echo formatFileSize($file['file_size']); ?></td>
                                        <td><?php echo formatDate($file['uploaded_at']); ?></td>
                                        <td>
                                            <a href="<?php echo APP_URL . '/uploads/complaints/' . $complaint_id . '/' . basename($file['file_path']); ?>" 
                                               download 
                                               style="background:#1B3A6B; color:white; padding:4px 10px; border-radius:4px; text-decoration:none; font-size:0.85rem;">
                                                ⬇ Download
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #999;">No evidence files uploaded.</p>
                <?php endif; ?>
            </div>

            <!-- FIR Section -->
            <?php if ($fir): ?>
                <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); margin-top: 2rem; box-shadow: var(--box-shadow);">
                    <h2 style="color: var(--primary-color); margin-bottom: 1rem;">First Information Report (FIR)</h2>
                    <div style="padding: 1rem; background-color: #f0f4f8; border-radius: var(--border-radius);">
                        <p><strong>FIR Number:</strong> <?php echo escapeHtml($fir['fir_number']); ?></p>
                        <p><strong>Status:</strong> <?php echo ucfirst($fir['status']); ?></p>
                        <p><strong>Filed by:</strong> <?php echo escapeHtml($complaint['investigator_name']); ?></p>
                    </div>
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
