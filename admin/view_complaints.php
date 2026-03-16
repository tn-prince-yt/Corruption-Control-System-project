<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

// Get filter parameter
$filter = isset($_GET['filter']) ? sanitizeInput($_GET['filter']) : 'pending';
$filter = $conn->real_escape_string($filter);

// Get complaints based on filter
if ($filter === 'all') {
    $query = "SELECT c.*, u.first_name, u.last_name, u.email FROM complaints c 
              JOIN users u ON c.citizen_id = u.user_id 
              ORDER BY c.created_at DESC";
} else {
    $query = "SELECT c.*, u.first_name, u.last_name, u.email FROM complaints c 
              JOIN users u ON c.citizen_id = u.user_id 
              WHERE c.status = '$filter' 
              ORDER BY c.created_at DESC";
}

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
    <title>Review Complaints - Admin</title>
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
                    <a href="view_complaints.php" class="active">Review Complaints</a>
                    <a href="manage_officers.php">Manage Officers</a>
                    <a href="../auth/logout.php">Logout</a>
                </nav>
            </div>

            <div class="main-content">
                <h2>Review Complaints</h2>
                
                <div class="filter-buttons">
                    <a href="view_complaints.php?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Pending</a>
                    <a href="view_complaints.php?filter=approved" class="btn <?php echo $filter === 'approved' ? 'btn-primary' : 'btn-secondary'; ?>">Approved</a>
                    <a href="view_complaints.php?filter=rejected" class="btn <?php echo $filter === 'rejected' ? 'btn-primary' : 'btn-secondary'; ?>">Rejected</a>
                    <a href="view_complaints.php?filter=under_investigation" class="btn <?php echo $filter === 'under_investigation' ? 'btn-primary' : 'btn-secondary'; ?>">Under Investigation</a>
                    <a href="view_complaints.php?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
                </div>

                <?php if (count($complaints) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Complainant</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Evidence</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td>#<?php echo $complaint['complaint_id']; ?></td>
                                <td><?php echo $complaint['first_name'] . ' ' . $complaint['last_name']; ?></td>
                                <td><?php echo $complaint['title']; ?></td>
                                <td><?php echo $complaint['category']; ?></td>
                                <td><span class="badge badge-<?php echo $complaint['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span></td>
                                <td><?php echo $complaint['evidence_count']; ?></td>
                                <td><?php echo formatDate($complaint['complaint_date']); ?></td>
                                <td><a href="approve_complaint.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-primary">Review</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>No complaints found in this category.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
