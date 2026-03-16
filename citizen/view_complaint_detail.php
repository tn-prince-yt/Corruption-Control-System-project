<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireCitizen();

$citizen_id = $_SESSION['user_id'];
$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get complaint details
$query = "SELECT c.*, u.first_name as officer_first_name, u.last_name as officer_last_name 
          FROM complaints c 
          LEFT JOIN officers o ON c.assigned_officer_id = o.officer_id 
          LEFT JOIN users u ON o.user_id = u.user_id 
          WHERE c.complaint_id = $complaint_id AND c.citizen_id = $citizen_id";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    redirect(SITE_URL . 'citizen/view_complaints.php');
}

$complaint = $result->fetch_assoc();

// Get evidence files
$evidence_query = "SELECT * FROM evidence WHERE complaint_id = $complaint_id";
$evidence_result = $conn->query($evidence_query);
$evidence_files = array();
while ($row = $evidence_result->fetch_assoc()) {
    $evidence_files[] = $row;
}

// Get investigation report if exists
$report_query = "SELECT * FROM reports WHERE complaint_id = $complaint_id";
$report_result = $conn->query($report_query);
$report = $report_result->num_rows > 0 ? $report_result->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Details - Citizen</title>
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
                    <p class="user-type">Citizen</p>
                </div>
                <nav class="sidebar-menu">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="submit_complaint.php">Submit Complaint</a>
                    <a href="view_complaints.php" class="active">My Complaints</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <div class="detail-header">
                    <h2>Complaint #<?php echo $complaint['complaint_id']; ?></h2>
                    <span class="badge badge-<?php echo $complaint['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span>
                </div>

                <div class="detail-box">
                    <h3>Complaint Information</h3>
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
                        <span class="info-label">Submitted On:</span>
                        <span class="info-value"><?php echo formatDateTime($complaint['created_at']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Description:</span>
                        <span class="info-value"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></span>
                    </div>
                </div>

                <?php if ($complaint['status'] === 'rejected'): ?>
                <div class="alert alert-danger">
                    <strong>Rejection Reason:</strong>
                    <p><?php echo htmlspecialchars($complaint['rejection_reason']); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($complaint['assigned_officer_id']): ?>
                <div class="detail-box">
                    <h3>Assigned Officer</h3>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($complaint['officer_first_name'] . ' ' . $complaint['officer_last_name']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

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
                        <span class="info-label">Submitted On:</span>
                        <span class="info-value"><?php echo formatDateTime($report['submitted_date']); ?></span>
                    </div>
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
