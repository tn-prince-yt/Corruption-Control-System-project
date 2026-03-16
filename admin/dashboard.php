<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

// Get statistics
$total_complaints = $conn->query("SELECT COUNT(*) as count FROM complaints")->fetch_assoc()['count'];
$pending_complaints = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'pending'")->fetch_assoc()['count'];
$approved_complaints = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'approved'")->fetch_assoc()['count'];
$under_investigation = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'under_investigation'")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'citizen'")->fetch_assoc()['count'];
$total_officers = $conn->query("SELECT COUNT(*) as count FROM officers")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
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
                    <p class="user-type">Administrator</p>
                </div>
                <nav class="sidebar-menu">
                    <a href="dashboard.php" class="active">Dashboard</a>
                    <a href="view_complaints.php">Review Complaints</a>
                    <a href="manage_officers.php">Manage Officers</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <h2>Admin Dashboard</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $total_complaints; ?></h3>
                        <p>Total Complaints</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $pending_complaints; ?></h3>
                        <p>Pending Review</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $approved_complaints; ?></h3>
                        <p>Approved</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $under_investigation; ?></h3>
                        <p>Under Investigation</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Citizens</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $total_officers; ?></h3>
                        <p>Officers</p>
                    </div>
                </div>

                <div class="actions">
                    <a href="view_complaints.php" class="btn btn-primary">Review Pending Complaints</a>
                    <a href="manage_officers.php" class="btn btn-secondary">Manage Officers</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
