<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireCitizen();

$citizen_id = $_SESSION['user_id'];

// Get complaint statistics
$query = "SELECT status, COUNT(*) as count FROM complaints WHERE citizen_id = $citizen_id GROUP BY status";
$result = $conn->query($query);
$stats = array();
while ($row = $result->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Citizen</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/navbar.php'; ?>

        <div class="dashboard">
            <div class="sidebar">
                <div class="user-profile">
                    <h3><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h3>
                    <p><?php echo $_SESSION['email']; ?></p>
                    <p class="user-type">Citizen</p>
                </div>
                <nav class="sidebar-menu">
                    <a href="dashboard.php" class="active">Dashboard</a>
                    <a href="submit_complaint.php">Submit Complaint</a>
                    <a href="view_complaints.php">My Complaints</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <h2>Welcome, <?php echo $_SESSION['first_name']; ?>!</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo isset($stats['pending']) ? $stats['pending'] : 0; ?></h3>
                        <p>Pending</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo isset($stats['approved']) ? $stats['approved'] : 0; ?></h3>
                        <p>Approved</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo isset($stats['under_investigation']) ? $stats['under_investigation'] : 0; ?></h3>
                        <p>Under Investigation</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo isset($stats['completed']) ? $stats['completed'] : 0; ?></h3>
                        <p>Completed</p>
                    </div>
                </div>

                <div class="actions">
                    <a href="submit_complaint.php" class="btn btn-primary">Submit New Complaint</a>
                    <a href="view_complaints.php" class="btn btn-secondary">View All Complaints</a>
                </div>

                <div class="info-box">
                    <h3>How to Submit a Complaint</h3>
                    <ol>
                        <li>Click on "Submit New Complaint"</li>
                        <li>Fill in the complaint details</li>
                        <li>Upload supporting evidence (images, documents)</li>
                        <li>Submit for review</li>
                        <li>Admin will approve or reject your complaint</li>
                        <li>Once approved, an investigation officer will be assigned</li>
                        <li>Track the progress in "My Complaints"</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
