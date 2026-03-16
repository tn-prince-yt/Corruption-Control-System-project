<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

// Get all officers
$query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone, COUNT(c.complaint_id) as cases_assigned
          FROM officers o 
          JOIN users u ON o.user_id = u.user_id 
          LEFT JOIN complaints c ON o.officer_id = c.assigned_officer_id AND c.status = 'under_investigation'
          GROUP BY o.officer_id
          ORDER BY u.first_name";
$result = $conn->query($query);
$officers = array();
while ($row = $result->fetch_assoc()) {
    $officers[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Officers - Admin</title>
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
                </div>
                <nav class="sidebar-menu">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="view_complaints.php">Review Complaints</a>
                    <a href="manage_officers.php" class="active">Manage Officers</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <h2>Manage Officers</h2>
                
                <?php if (count($officers) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Badge #</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Active Cases</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($officers as $officer): ?>
                            <tr>
                                <td><?php echo $officer['first_name'] . ' ' . $officer['last_name']; ?></td>
                                <td><?php echo $officer['badge_number']; ?></td>
                                <td><?php echo $officer['department']; ?></td>
                                <td><?php echo $officer['email']; ?></td>
                                <td><?php echo $officer['phone']; ?></td>
                                <td><?php echo $officer['cases_assigned']; ?></td>
                                <td>
                                    <?php 
                                    if ($officer['is_available']) {
                                        echo '<span class="badge badge-success">Available</span>';
                                    } else {
                                        echo '<span class="badge badge-danger">Unavailable</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="toggle_officer_status.php?id=<?php echo $officer['officer_id']; ?>" class="btn btn-sm btn-secondary">
                                        <?php echo $officer['is_available'] ? 'Mark Unavailable' : 'Mark Available'; ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>No officers found.</p>
                </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
