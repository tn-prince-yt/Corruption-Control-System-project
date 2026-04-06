<?php
/**
 * View Case (Investigator)
 * Detailed view and management of investigation case
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require investigator login
requireRole('investigator');

$user_id = getUserId();
$user = getCurrentUser();

// Get complaint ID from URL
$complaint_id = intval($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    header("Location: my_cases.php");
    exit();
}

// Get complaint details
$stmt = $conn->prepare("SELECT c.*, u.name as citizen_name FROM complaints c 
LEFT JOIN users u ON c.user_id = u.id 
WHERE c.id = ? AND c.assigned_investigator_id = ?");
$stmt->bind_param("ii", $complaint_id, $user_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$complaint) {
    header("Location: my_cases.php");
    exit();
}

// Get evidence
$stmt = $conn->prepare("SELECT * FROM evidence WHERE complaint_id = ? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$evidence = $stmt->get_result();
$stmt->close();

// Get FIR
$stmt = $conn->prepare("SELECT * FROM fir WHERE complaint_id = ?");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$fir = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get report if exists
$stmt = $conn->prepare("SELECT * FROM investigation_reports WHERE complaint_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();
$stmt->close();

$upload_error = '';
$upload_success = '';

// Handle evidence upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['evidence'])) {
    $upload_dir = UPLOAD_DIR . 'complaints/' . $complaint_id . '/';
    
    $upload_result = uploadFile($_FILES['evidence'], $upload_dir);
    
    if ($upload_result['success']) {
        $stmt = $conn->prepare("INSERT INTO evidence (complaint_id, uploaded_by, file_name, file_path, file_type, file_size, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $file_type = pathinfo($upload_result['original_name'], PATHINFO_EXTENSION);
        $desc = "Investigation evidence";
        $stmt->bind_param("iisssii", $complaint_id, $user_id, $upload_result['original_name'], $upload_result['filepath'], $file_type, $upload_result['size'], $desc);
        $stmt->execute();
        $stmt->close();
        
        logActivity($user_id, $complaint_id, 'EVIDENCE_ADDED', 'Investigation evidence uploaded');
        $upload_success = 'Evidence uploaded successfully!';
        
        // Refresh page
        header("Location: view_case.php?id=$complaint_id");
    } else {
        $upload_error = $upload_result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Case - <?php echo APP_NAME; ?></title>
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
                        <li><a href="my_cases.php">My Cases</a></li>
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
            <a href="my_cases.php" style="color: var(--secondary-color); margin-bottom: 1rem; display: inline-block;">← Back to Cases</a>

            <!-- Case Details -->
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
            </div>

            <!-- FIR Section -->
            <?php if ($fir): ?>
                <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); margin-top: 2rem; box-shadow: var(--box-shadow);">
                    <h2 style="color: var(--primary-color); margin-bottom: 1rem;">First Information Report (FIR)</h2>
                    <div style="padding: 1rem; background-color: #f0f4f8; border-radius: var(--border-radius);">
                        <p><strong>FIR Number:</strong> <?php echo escapeHtml($fir['fir_number']); ?></p>
                        <p><strong>Status:</strong> <?php echo ucfirst($fir['status']); ?></p>
                        <p><strong>Date:</strong> <?php echo formatDate($fir['fir_date']); ?></p>
                        <?php if ($fir['description']): ?>
                            <p><strong>Description:</strong></p>
                            <p><?php echo nl2br(escapeHtml($fir['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Evidence Section -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); margin-top: 2rem; box-shadow: var(--box-shadow);">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Evidence Files</h2>

                <?php if ($upload_error): ?>
                    <div class="alert alert-danger"><?php echo escapeHtml($upload_error); ?></div>
                <?php endif; ?>

                <?php if ($upload_success): ?>
                    <div class="alert alert-success"><?php echo escapeHtml($upload_success); ?></div>
                <?php endif; ?>

                <?php if ($complaint['status'] !== 'closed'): ?>
                    <form method="POST" enctype="multipart/form-data" style="margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="evidence">Add Evidence File</label>
                            <input type="file" id="evidence" name="evidence" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.txt,.zip">
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Evidence</button>
                    </form>
                <?php endif; ?>

                <?php if ($evidence->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($file = $evidence->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo escapeHtml($file['file_name']); ?></td>
                                        <td><?php echo strtoupper($file['file_type']); ?></td>
                                        <td><?php echo formatFileSize($file['file_size']); ?></td>
                                        <td><?php 
                                            $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                                            $stmt->bind_param("i", $file['uploaded_by']);
                                            $stmt->execute();
                                            $uploader = $stmt->get_result()->fetch_assoc();
                                            $stmt->close();
                                            echo escapeHtml($uploader['name'] ?? 'Unknown');
                                        ?></td>
                                        <td><?php echo formatDate($file['uploaded_at']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #999;">No evidence files yet.</p>
                <?php endif; ?>
            </div>

            <!-- Report Section -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); margin-top: 2rem; box-shadow: var(--box-shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 style="color: var(--primary-color); margin: 0;">Investigation Report</h2>
                    <?php if ($complaint['status'] !== 'closed'): ?>
                        <a href="create_report.php?id=<?php echo $complaint_id; ?>" class="btn btn-primary" style="font-size: 0.9rem;">Create/Edit Report</a>
                    <?php endif; ?>
                </div>

                <?php if ($report): ?>
                    <div style="padding: 1rem; background-color: #f0f4f8; border-radius: var(--border-radius);">
                        <p><strong>Title:</strong> <?php echo escapeHtml($report['report_title']); ?></p>
                        <p><strong>Status:</strong> <span class="badge badge-info"><?php echo ucfirst($report['status']); ?></span></p>
                        <p><strong>Submitted:</strong> <?php echo formatDate($report['submitted_date'] ?? $report['created_at']); ?></p>
                        <p style="margin-top: 1rem;"><strong>Report Content:</strong></p>
                        <div style="background-color: white; padding: 1rem; border-radius: var(--border-radius); max-height: 400px; overflow-y: auto;">
                            <?php echo nl2br(escapeHtml($report['report_text'])); ?>
                        </div>
                        <?php if ($report['findings']): ?>
                            <p style="margin-top: 1rem;"><strong>Findings:</strong></p>
                            <p><?php echo nl2br(escapeHtml($report['findings'])); ?></p>
                        <?php endif; ?>
                        <?php if ($report['recommendations']): ?>
                            <p style="margin-top: 1rem;"><strong>Recommendations:</strong></p>
                            <p><?php echo nl2br(escapeHtml($report['recommendations'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #999;">No report submitted yet.</p>
                <?php endif; ?>
            </div>

            <!-- Close Case -->
            <?php if ($complaint['status'] !== 'closed'): ?>
                <div style="background:white; padding:2rem; border-radius:var(--border-radius); margin-top:2rem; box-shadow:var(--box-shadow); border-left:4px solid #e74c3c;">
                    <h2 style="color:#e74c3c; margin-bottom:1rem;">&#x1F512; Close Case</h2>
                    <form action="close_case.php" method="POST" onsubmit="return confirm('Are you sure you want to close this case? This cannot be undone.');">
                        <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">
                        <div class="form-group" style="margin-bottom:1rem;">
                            <label for="close_reason" style="font-weight:600; display:block; margin-bottom:0.5rem;">&#x1F4DD; Closing Remarks *</label>
                            <textarea name="close_reason" id="close_reason" required
                                placeholder="Enter the reason for closing this case..."
                                style="width:100%; padding:0.75rem; border:1px solid #ddd; border-radius:6px; min-height:100px; font-size:0.95rem;"></textarea>
                        </div>
                        <button type="submit" style="background:#e74c3c; color:white; padding:0.75rem 2rem; border:none; border-radius:6px; cursor:pointer; font-size:1rem; font-weight:600;">
                            &#x1F512; Close Case
                        </button>
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