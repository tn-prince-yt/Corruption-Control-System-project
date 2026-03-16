<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireOfficer();

// Get officer info — guard against missing officers row
$user_id = intval($_SESSION['user_id']);
$result = $conn->query("SELECT officer_id FROM officers WHERE user_id = $user_id");

if (!$result || $result->num_rows === 0) {
    // Officer user exists but has no officers record — show helpful error
    die('
    <!DOCTYPE html>
    <html><head><title>Setup Error</title>
    <link rel="stylesheet" href="../css/style.css"></head>
    <body><div class="container" style="margin-top:60px;">
    <div class="alert alert-danger">
        <strong>Account Setup Incomplete:</strong> Your user account is not linked to an officer profile.
        Please ask the administrator to add your account to the officers table.
        <br><br>
        <a href="../auth/logout.php" class="btn btn-secondary btn-sm">Logout</a>
    </div>
    </div></body></html>');
}

$officer_id = $result->fetch_assoc()['officer_id'];

// Get statistics safely
$active_cases    = 0;
$completed_cases = 0;

$r = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE assigned_officer_id = $officer_id AND status = 'under_investigation'");
if ($r) $active_cases = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE assigned_officer_id = $officer_id AND status = 'completed'");
if ($r) $completed_cases = $r->fetch_assoc()['c'];

$total_assigned = $active_cases + $completed_cases;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Investigation Officer</title>
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
                    <a href="dashboard.php" class="active">Dashboard</a>
                    <a href="view_complaints.php">Assigned Complaints</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <?php if (isset($_SESSION['form_success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['form_success']); unset($_SESSION['form_success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['form_error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['form_error']); unset($_SESSION['form_error']); ?></div>
                <?php endif; ?>

                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h2>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $total_assigned; ?></h3>
                        <p>Total Assigned</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $active_cases; ?></h3>
                        <p>Active Investigations</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $completed_cases; ?></h3>
                        <p>Completed Cases</p>
                    </div>
                </div>

                <div class="actions">
                    <a href="view_complaints.php" class="btn btn-primary">View Assigned Complaints</a>
                </div>

                <div class="info-box">
                    <h3>Investigation Process</h3>
                    <ol>
                        <li>View assigned complaints in the complaints list</li>
                        <li>Click on a complaint to see details and evidence</li>
                        <li>Conduct your investigation</li>
                        <li>Submit an investigation report with your findings</li>
                        <li>Mark the case as completed once the report is submitted</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
