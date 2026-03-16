<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireOfficer();

// Get officer info safely
$user_id = intval($_SESSION['user_id']);
$result = $conn->query("SELECT officer_id FROM officers WHERE user_id = $user_id");

if (!$result || $result->num_rows === 0) {
    redirect(SITE_URL . 'officer/view_complaints.php');
}
$officer_id = $result->fetch_assoc()['officer_id'];

$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verify complaint is assigned to this officer
$check = $conn->query("SELECT complaint_id FROM complaints WHERE complaint_id = $complaint_id AND assigned_officer_id = $officer_id");
if (!$check || $check->num_rows === 0) {
    redirect(SITE_URL . 'officer/view_complaints.php');
}

// Get existing report if any
$report = null;
$report_result = $conn->query("SELECT * FROM reports WHERE complaint_id = $complaint_id");
if ($report_result && $report_result->num_rows > 0) {
    $report = $report_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $report ? 'Update' : 'Submit'; ?> Report - Investigation Officer</title>
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
                <h2><?php echo $report ? 'Update' : 'Submit'; ?> Investigation Report</h2>
                <p style="color:var(--text-muted); margin-bottom:24px;">Complaint #<?php echo $complaint_id; ?></p>

                <?php if (isset($_SESSION['form_error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['form_error']); unset($_SESSION['form_error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['form_success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['form_success']); unset($_SESSION['form_success']); ?></div>
                <?php endif; ?>

                <form method="POST" action="process_report.php" class="form">
                    <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">
                    <input type="hidden" name="officer_id" value="<?php echo $officer_id; ?>">

                    <div class="form-group">
                        <label for="findings">Findings <span style="color:var(--accent)">*</span></label>
                        <textarea id="findings" name="findings" rows="7" required placeholder="Describe your investigation findings in detail..."><?php echo $report ? htmlspecialchars($report['findings']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="recommendations">Recommendations</label>
                        <textarea id="recommendations" name="recommendations" rows="4" placeholder="Optional: add recommendations based on your findings..."><?php echo ($report && $report['recommendations']) ? htmlspecialchars($report['recommendations']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="status">Report Status</label>
                        <select id="status" name="status" required>
                            <option value="draft" <?php echo (!$report || $report['status'] === 'draft') ? 'selected' : ''; ?>>Save as Draft</option>
                            <option value="submitted" <?php echo ($report && $report['status'] === 'submitted') ? 'selected' : ''; ?>>Submit for Review</option>
                        </select>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">Save Report</button>
                        <a href="view_complaint_detail.php?id=<?php echo $complaint_id; ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
