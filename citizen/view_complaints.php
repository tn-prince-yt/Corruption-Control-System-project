<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireCitizen();

$citizen_id = $_SESSION['user_id'];

// Get all complaints for this citizen
$query = "SELECT * FROM complaints WHERE citizen_id = $citizen_id ORDER BY created_at DESC";
$result = $conn->query($query);
$complaints = array();
while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - Citizen</title>
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
                    <a href="submit_complaint.php">Submit Complaint</a>
                    <a href="view_complaints.php" class="active">My Complaints</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <h2>My Complaints</h2>
                
                <?php if (count($complaints) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Evidence</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td>#<?php echo $complaint['complaint_id']; ?></td>
                                <td><?php echo $complaint['title']; ?></td>
                                <td><?php echo $complaint['category']; ?></td>
                                <td><span class="badge badge-<?php echo $complaint['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span></td>
                                <td><?php echo formatDate($complaint['complaint_date']); ?></td>
                                <td><?php echo $complaint['evidence_count']; ?></td>
                                <td><a href="view_complaint_detail.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-primary">View Details</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>You haven't submitted any complaints yet.</p>
                    <a href="submit_complaint.php" class="btn btn-primary">Submit Your First Complaint</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
