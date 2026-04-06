<?php
/**
 * Admin Reports
 * System reports and statistics
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require admin login
requireRole('admin');

$user_id = getUserId();
$user = getCurrentUser();

// Get complaint statistics by status
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM complaints GROUP BY status");
$status_stats = [];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $status_stats[$row['status']] = $row['count'];
    }
    $stmt->close();
}

// Get complaint statistics by category
$stmt = $conn->prepare("SELECT category, COUNT(*) as count FROM complaints GROUP BY category");
$category_stats = [];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $category_stats[$row['category']] = $row['count'];
    }
    $stmt->close();
}

// Get priority statistics
$stmt = $conn->prepare("SELECT priority, COUNT(*) as count FROM complaints GROUP BY priority");
$priority_stats = [];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $priority_stats[$row['priority']] = $row['count'];
    }
    $stmt->close();
}

// Get average resolution time
$avg_resolution = 0;
$stmt = $conn->prepare("SELECT AVG(DATEDIFF(CASE WHEN status='closed' THEN updated_at ELSE NOW() END, created_at)) as avg_days FROM complaints");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $avg_resolution = $row['avg_days'] ?? 0;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo APP_NAME; ?></title>
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
                        <li><a href="manage_users.php">Manage Users</a></li>
                        <li><a href="manage_complaints.php">Manage Complaints</a></li>
                        <li><a href="reports.php">Reports</a></li>
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
            <h1 style="color: var(--primary-color); margin-bottom: 2rem;">System Reports</h1>

            <!-- Statistics -->
            <div class="dashboard">
                <div class="card">
                    <div class="card-header">Avg Resolution Time</div>
                    <div class="card-value"><?php echo round($avg_resolution ?? 0); ?></div>
                    <div class="card-subtitle">days</div>
                </div>
            </div>

            <!-- Status Distribution -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 2rem;">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Complaints by Status</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <?php foreach ($status_stats as $status => $count): ?>
                        <div style="text-align: center; padding: 1rem; background-color: #f9f9f9; border-radius: var(--border-radius);">
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--secondary-color);"><?php echo $count; ?></div>
                            <div style="color: #666; margin-top: 0.5rem;"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Category Distribution -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 2rem;">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Complaints by Category</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = array_sum($category_stats);
                            foreach ($category_stats as $category => $count): 
                                $percentage = $total > 0 ? round(($count / $total) * 100, 2) : 0;
                            ?>
                                <tr>
                                    <td><?php echo escapeHtml($category); ?></td>
                                    <td><?php echo $count; ?></td>
                                    <td>
                                        <div style="width: 100%; background-color: #f0f0f0; border-radius: 4px; overflow: hidden;">
                                            <div style="width: <?php echo $percentage; ?>%; background-color: var(--secondary-color); padding: 0.5rem; color: white; text-align: center;">
                                                <?php echo $percentage; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Priority Distribution -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Complaints by Priority</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <?php foreach ($priority_stats as $priority => $count): ?>
                        <div style="text-align: center; padding: 1rem; background-color: #f9f9f9; border-radius: var(--border-radius);">
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--secondary-color);"><?php echo $count; ?></div>
                            <div style="color: #666; margin-top: 0.5rem;"><?php echo ucfirst($priority); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
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