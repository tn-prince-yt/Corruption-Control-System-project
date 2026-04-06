<?php
/**
 * My Complaints Page
 * Displays all complaints submitted by the citizen
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require citizen login
requireRole('citizen');

$user_id = getUserId();
$user = getCurrentUser();

// Get filter
$status_filter = sanitizeInput($_GET['status'] ?? '');

// Build query
$query = "SELECT c.*, 
    (SELECT COUNT(*) FROM evidence WHERE complaint_id = c.id) as evidence_count,
    u.name as officer_name
FROM complaints c
LEFT JOIN users u ON c.assigned_officer_id = u.id
WHERE c.user_id = ?";

$params = [$user_id];
$types = "i";

if (!empty($status_filter)) {
    $query .= " AND c.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$complaints = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo"><?php echo APP_NAME; ?></div>
                <nav>
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="submit_complaint.php">Submit Complaint</a></li>
                        <li><a href="my_complaints.php">My Complaints</a></li>
                        <li class="user-menu">
                            <span class="user-info"><?php echo escapeHtml($user['name']); ?></span>
                            <a href="../auth/logout.php" class="logout-btn">Logout</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: var(--primary-color);">My Complaints</h1>
                <a href="submit_complaint.php" class="btn btn-primary">+ New Complaint</a>
            </div>

            <!-- Filter -->
            <div style="background-color: white; padding: 1.5rem; border-radius: var(--border-radius); margin-bottom: 2rem; display: flex; gap: 1rem;">
                <label for="statusFilter">Filter by Status:</label>
                <select id="statusFilter" onchange="window.location.href='?status=' + this.value;">
                    <option value="">All Statuses</option>
                    <option value="submitted" <?php echo $status_filter === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                    <option value="under_review" <?php echo $status_filter === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="investigation" <?php echo $status_filter === 'investigation' ? 'selected' : ''; ?>>Investigation</option>
                    <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>

            <!-- Complaints List -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <?php if ($complaints->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Evidence</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($complaint = $complaints->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>CCS-<?php echo str_pad($complaint['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo escapeHtml(substr($complaint['title'], 0, 25)) . (strlen($complaint['title']) > 25 ? '...' : ''); ?></td>
                                        <td><?php echo escapeHtml($complaint['category']); ?></td>
                                        <td><?php echo formatStatus($complaint['status']); ?></td>
                                        <td><?php echo formatPriority($complaint['priority']); ?></td>
                                        <td><span class="badge badge-info"><?php echo $complaint['evidence_count']; ?> file(s)</span></td>
                                        <td><?php echo formatDate($complaint['created_at']); ?></td>
                                        <td>
                                            <a href="view_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-primary" style="font-size: 0.85rem;">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 2rem;">No complaints found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
</body>
</html>
