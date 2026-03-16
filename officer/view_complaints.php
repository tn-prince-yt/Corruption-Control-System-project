<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireOfficer();

// Get officer info safely
$user_id = intval($_SESSION['user_id']);
$result = $conn->query("SELECT officer_id FROM officers WHERE user_id = $user_id");

if (!$result || $result->num_rows === 0) {
    die('<!DOCTYPE html><html><head><title>Error</title><link rel="stylesheet" href="../css/style.css"></head>
    <body><div class="container" style="margin-top:60px;">
    <div class="alert alert-danger"><strong>Account Setup Incomplete:</strong> Your account is not linked to an officer profile. Contact the administrator.
    <br><br><a href="../auth/logout.php" class="btn btn-secondary btn-sm">Logout</a></div>
    </div></body></html>');
}
$officer_id = $result->fetch_assoc()['officer_id'];

// Get assigned complaints
$complaints = array();
$r = $conn->query("SELECT c.*, u.first_name, u.last_name, u.email 
                   FROM complaints c 
                   JOIN users u ON c.citizen_id = u.user_id 
                   WHERE c.assigned_officer_id = $officer_id 
                   ORDER BY c.created_at DESC");
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $complaints[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Complaints - Investigation Officer</title>
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
                <h2>Assigned Complaints</h2>

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
                                <td><?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                                <td><?php echo htmlspecialchars($complaint['category']); ?></td>
                                <td><span class="badge badge-<?php echo $complaint['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span></td>
                                <td><?php echo intval($complaint['evidence_count']); ?></td>
                                <td><?php echo formatDate($complaint['complaint_date']); ?></td>
                                <td><a href="view_complaint_detail.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-primary">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>No complaints assigned to you yet.</p>
                    <p style="font-size:13px;">Once the admin approves a complaint and assigns you, it will appear here.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
