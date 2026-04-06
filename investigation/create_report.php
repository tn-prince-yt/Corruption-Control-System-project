<?php
/**
 * Create/Edit Investigation Report
 * Investigator creates and submits investigation report
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
$stmt = $conn->prepare("SELECT c.* FROM complaints c WHERE c.id = ? AND c.assigned_investigator_id = ?");
$stmt->bind_param("ii", $complaint_id, $user_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$complaint) {
    header("Location: my_cases.php");
    exit();
}

// Get existing report if any
$stmt = $conn->prepare("SELECT * FROM investigation_reports WHERE complaint_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

// Handle report submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $report_title = sanitizeInput($_POST['report_title'] ?? '');
    $report_text = sanitizeInput($_POST['report_text'] ?? '');
    $findings = sanitizeInput($_POST['findings'] ?? '');
    $recommendations = sanitizeInput($_POST['recommendations'] ?? '');
    
    if (empty($report_title) || empty($report_text)) {
        $error = 'Report title and content are required';
    } else {
        if ($report) {
            // Update existing report
            $stmt = $conn->prepare("UPDATE investigation_reports SET report_title = ?, report_text = ?, findings = ?, recommendations = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $report_title, $report_text, $findings, $recommendations, $report['id']);
            $stmt->execute();
            $stmt->close();
            $success = 'Report updated successfully!';
        } else {
            // Create new report
            $status = 'draft';
            $stmt = $conn->prepare("INSERT INTO investigation_reports (complaint_id, investigator_id, report_title, report_text, findings, recommendations, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $complaint_id, $user_id, $report_title, $report_text, $findings, $recommendations, $status);
            $stmt->execute();
            $stmt->close();
            $success = 'Report created successfully!';
            
            // Refresh report data
            $stmt = $conn->prepare("SELECT * FROM investigation_reports WHERE complaint_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param("i", $complaint_id);
            $stmt->execute();
            $report = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
        
        logActivity($user_id, $complaint_id, 'REPORT_' . ($report ? 'UPDATED' : 'CREATED'), 'Investigation report created/updated');
    }
}

// Handle report submission for approval
if (isset($_GET['action']) && $_GET['action'] === 'submit' && $report) {
    $status = 'submitted';
    $submitted_date = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE investigation_reports SET status = ?, submitted_date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $submitted_date, $report['id']);
    $stmt->execute();
    $stmt->close();
    
    logActivity($user_id, $complaint_id, 'REPORT_SUBMITTED', 'Investigation report submitted for approval');
    $success = 'Report submitted for approval!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Report - <?php echo APP_NAME; ?></title>
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
            <a href="view_case.php?id=<?php echo $complaint_id; ?>" style="color: var(--secondary-color); margin-bottom: 1rem; display: inline-block;">← Back to Case</a>

            <h1 style="color: var(--primary-color); margin-bottom: 2rem;"><?php echo $report ? 'Edit' : 'Create'; ?> Investigation Report</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo escapeHtml($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo escapeHtml($success); ?></div>
            <?php endif; ?>

            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <form method="POST">
                    <div class="form-group">
                        <label for="report_title">Report Title *</label>
                        <input type="text" id="report_title" name="report_title" value="<?php echo escapeHtml($report['report_title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="report_text">Investigation Report *</label>
                        <textarea id="report_text" name="report_text" rows="10" placeholder="Detailed investigation report..." required><?php echo escapeHtml($report['report_text'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="findings">Findings</label>
                        <textarea id="findings" name="findings" rows="6" placeholder="Key findings from the investigation..."><?php echo escapeHtml($report['findings'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="recommendations">Recommendations</label>
                        <textarea id="recommendations" name="recommendations" rows="6" placeholder="Recommendations based on investigation..."><?php echo escapeHtml($report['recommendations'] ?? ''); ?></textarea>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary"><?php echo $report ? 'Update' : 'Create'; ?> Report</button>
                        <a href="view_case.php?id=<?php echo $complaint_id; ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

                <?php if ($report && $report['status'] === 'draft'): ?>
                    <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #ddd;">
                    <div>
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Submit for Approval</h3>
                        <p style="margin-bottom: 1rem;">Once submitted, this report will be reviewed and approved for case closure.</p>
                        <a href="?id=<?php echo $complaint_id; ?>&action=submit" class="btn btn-success" onclick="return confirm('Submit this report for approval?');">Submit for Approval</a>
                    </div>
                <?php endif; ?>
            </div>
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
