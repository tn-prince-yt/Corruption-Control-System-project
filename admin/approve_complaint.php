<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get complaint details
$query = "SELECT c.*, u.first_name, u.last_name, u.email 
          FROM complaints c 
          JOIN users u ON c.citizen_id = u.user_id 
          WHERE c.complaint_id = $complaint_id";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    redirect(SITE_URL . 'admin/view_complaints.php');
}

$complaint = $result->fetch_assoc();

// Get available officers
$officers_query = "SELECT o.officer_id, u.first_name, u.last_name, o.badge_number 
                   FROM officers o 
                   JOIN users u ON o.user_id = u.user_id 
                   WHERE o.is_available = TRUE
                   ORDER BY u.first_name";
$officers_result = $conn->query($officers_query);
$officers = array();
while ($row = $officers_result->fetch_assoc()) {
    $officers[] = $row;
}

// Get evidence files
$evidence_query = "SELECT * FROM evidence WHERE complaint_id = $complaint_id";
$evidence_result = $conn->query($evidence_query);
$evidence_files = array();
while ($row = $evidence_result->fetch_assoc()) {
    $evidence_files[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Complaint - Admin</title>
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
                    <p class="user-type">Administrator</p>
                </div>
                <nav class="sidebar-menu">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="view_complaints.php" class="active">Review Complaints</a>
                    <a href="manage_officers.php">Manage Officers</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <?php if (isset($_SESSION['form_error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['form_error']); unset($_SESSION['form_error']); ?></div>
                <?php endif; ?>

                <div class="detail-header">
                    <h2>Complaint #<?php echo $complaint['complaint_id']; ?></h2>
                    <span class="badge badge-<?php echo $complaint['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span>
                </div>

                <div class="detail-box">
                    <h3>Complaint Information</h3>
                    <div class="info-row">
                        <span class="info-label">Complainant:</span>
                        <span class="info-value"><?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?> (<?php echo htmlspecialchars($complaint['email']); ?>)</span>
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

                <?php if ($complaint['status'] === 'pending') : ?>
                <div class="detail-box">
                    <h3>Action</h3>
                    <form method="POST" action="process_approval.php">
                        <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
                        
                        <div class="form-group">
                            <label for="action">Approval Status</label>
                            <select id="action" name="action" required>
                                <option value="">Select Action</option>
                                <option value="approve">Approve &amp; Assign Officer</option>
                                <option value="reject">Reject</option>
                            </select>
                        </div>

                        <div class="form-group" id="rejection-reason" style="display: none;">
                            <label for="rejection_reason">Rejection Reason</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="3" placeholder="Provide reason for rejection..."></textarea>
                        </div>

                        <div class="form-group" id="officer-selection" style="display: none;">
                            <label for="officer_id">Assign Investigation Officer</label>
                            <select id="officer_id" name="officer_id">
                                <option value="">-- Select Available Officer --</option>
                                <?php foreach ($officers as $officer): ?>
                                <option value="<?php echo $officer['officer_id']; ?>">
                                    <?php echo htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']); ?> (Badge: <?php echo htmlspecialchars($officer['badge_number']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (count($officers) === 0): ?>
                            <small class="text-muted">No available officers at this time.</small>
                            <?php endif; ?>
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary">Submit Decision</button>
                            <a href="view_complaints.php?filter=<?php echo $complaint['status']; ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>

                <script>
                document.getElementById('action').addEventListener('change', function() {
                    var rejectionReason = document.getElementById('rejection-reason');
                    var officerSelection = document.getElementById('officer-selection');
                    if (this.value === 'reject') {
                        rejectionReason.style.display = 'block';
                        officerSelection.style.display = 'none';
                    } else if (this.value === 'approve') {
                        rejectionReason.style.display = 'none';
                        officerSelection.style.display = 'block';
                    } else {
                        rejectionReason.style.display = 'none';
                        officerSelection.style.display = 'none';
                    }
                });
                </script>
                <?php else: ?>
                <div class="alert alert-info">
                    <p>This complaint status is already: <strong><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></strong></p>
                </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="view_complaints.php?filter=<?php echo $complaint['status']; ?>" class="btn btn-secondary">Back to Complaints</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
