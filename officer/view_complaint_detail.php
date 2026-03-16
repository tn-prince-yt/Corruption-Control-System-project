<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireOfficer();

// Get officer info
$user_id = $_SESSION['user_id'];
$query = "SELECT o.officer_id FROM officers WHERE user_id = $user_id";
$result = $conn->query($query);
if ($result->num_rows === 0) {
    redirect(SITE_URL . 'index.php');
}
$officer = $result->fetch_assoc();
$officer_id = $officer['officer_id'];

$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get complaint details (only if assigned to this officer)
$query = "SELECT c.*, u.first_name, u.last_name, u.email, u.phone FROM complaints c 
          JOIN users u ON c.citizen_id = u.user_id 
          WHERE c.complaint_id = $complaint_id AND c.assigned_officer_id = $officer_id";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    redirect(SITE_URL . 'officer/view_complaints.php');
}

$complaint = $result->fetch_assoc();

// Get evidence files
$evidence_query = "SELECT * FROM evidence WHERE complaint_id = $complaint_id";
$evidence_result = $conn->query($evidence_query);
$evidence_files = array();
while ($row = $evidence_result->fetch_assoc()) {
    $evidence_files[] = $row;
}

// Get existing report if any
$report_query = "SELECT * FROM reports WHERE complaint_id = $complaint_id";
$report_result = $conn->query($report_query);
$report = $report_result->num_rows > 0 ? $report_result->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Details - Investigation Officer</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/navbar.php'; ?>

        <div class="dashboard">
            <div class="sidebar">
                <div class="user-profile">
                    <h3><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h3>
                    <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p class="user-type">Investigation Officer</p>
                </div>
                <nav class="sidebar-menu">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="view_complaints.php" class="active">Assigned Complaints</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <?php if (isset($_SESSION['form_error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['form_error']); unset($_SESSION['form_error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['form_success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['form_success']); unset($_SESSION['form_success']); ?></div>
                <?php endif; ?>

                <div class="detail-header">
                    <h2>Complaint #<?php echo $complaint['complaint_id']; ?></h2>
                    <span class="badge badge-<?php echo $complaint['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span>
                </div>

                <div class="detail-box">
                    <h3>Complaint Information</h3>
                    <div class="info-row">
                        <span class="info-label">Complainant:</span>
                        <span class="info-value"><?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?><br><small><?php echo htmlspecialchars($complaint['email']); ?> | <?php echo htmlspecialchars($complaint['phone']); ?></small></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Title:</span>
                        <span class="info-value"><?php echo htmlspecialchars($complaint['title']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Category:</span>
                        <span class="info-value"><?php echo htmlspecialchars($complaint['category']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Location:</span>
                        <span class="info-value"><?php echo htmlspecialchars($complaint['location']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date of Incident:</span>
                        <span class="info-value"><?php echo formatDate($complaint['complaint_date']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Description:</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></span>
                    </div>
                </div>

                <?php if (count($evidence_files) > 0): ?>
                <div class="detail-box">
                    <h3>Evidence Files (<?php echo count($evidence_files); ?>)</h3>
                    <div class="evidence-list">
                        <?php foreach ($evidence_files as $file): ?>
                        <div class="evidence-item">
                            <p><strong><?php echo htmlspecialchars($file['file_name']); ?></strong> (<?php echo round($file['file_size'] / 1024); ?> KB)</p>
                            <p class="text-muted">Uploaded: <?php echo formatDateTime($file['uploaded_at']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($report): ?>
                <div class="detail-box">
                    <h3>Investigation Report</h3>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value"><span class="badge badge-<?php echo $report['status']; ?>"><?php echo ucfirst($report['status']); ?></span></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Findings:</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($report['findings'])); ?></span>
                    </div>
                    <?php if ($report['recommendations']): ?>
                    <div class="info-row">
                        <span class="info-label">Recommendations:</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($report['recommendations'])); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span class="info-label">Created:</span>
                        <span class="info-value"><?php echo formatDateTime($report['created_at']); ?></span>
                    </div>
                    <?php if ($report['submitted_date']): ?>
                    <div class="info-row">
                        <span class="info-label">Submitted:</span>
                        <span class="info-value"><?php echo formatDateTime($report['submitted_date']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!$report || $report['status'] === 'draft'): ?>
                <div class="detail-box">
                    <h3><?php echo $report ? 'Update' : 'Submit'; ?> Investigation Report</h3>
                    <p class="text-muted" style="margin-bottom:15px;">Document your findings and recommendations for this case.</p>
                    <a href="submit_report.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-primary">
                        <?php echo $report ? 'Edit Report' : 'Create Report'; ?>
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($complaint['status'] === 'under_investigation'): ?>
                <div class="detail-box">
                    <h3>Close Case</h3>
                    <p class="text-muted" style="margin-bottom:15px;">A submitted report is required before marking the case as completed.</p>
                    <a href="mark_completed.php?id=<?php echo $complaint['complaint_id']; ?>" 
                       class="btn btn-success"
                       onclick="return confirm('Mark this case as completed? This cannot be undone.')">
                        Mark as Completed
                    </a>
                </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="view_complaints.php" class="btn btn-secondary">Back to Complaints</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
