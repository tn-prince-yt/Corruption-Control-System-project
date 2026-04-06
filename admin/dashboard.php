<?php
/**
 * Admin Dashboard
 * Main dashboard for administrators
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require admin login
requireRole('admin');
$user_id = getUserId();

$user = getCurrentUser();

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM complaints");
$stmt->execute();
$total_complaints = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM complaints WHERE status = 'under_review'");
$stmt->execute();
$pending_complaints = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM fir");
$stmt->execute();
$total_firs = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get role distribution
$stmt = $conn->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$stmt->execute();
$role_distribution = $stmt->get_result();
$stmt->close();

// Get recent complaints
$stmt = $conn->prepare("SELECT c.*, u.name as citizen_name FROM complaints c 
    LEFT JOIN users u ON c.user_id = u.id 
    ORDER BY c.created_at DESC LIMIT 5");
$stmt->execute();
$recent_complaints = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
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
                        <?php renderNotificationBell($conn, $user_id); ?>
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
            <h1 style="color: var(--primary-color); margin-bottom: 2rem;">Admin Dashboard</h1>

            <!-- Statistics Cards -->
            <div class="dashboard">
                <div class="card">
                    <div class="card-header">Total Users</div>
                    <div class="card-value"><?php echo $total_users; ?></div>
                    <div class="card-subtitle">Registered users</div>
                </div>
                <div class="card">
                    <div class="card-header">Total Complaints</div>
                    <div class="card-value"><?php echo $total_complaints; ?></div>
                    <div class="card-subtitle">All complaints</div>
                </div>
                <div class="card">
                    <div class="card-header">Pending Review</div>
                    <div class="card-value"><?php echo $pending_complaints; ?></div>
                    <div class="card-subtitle">Under review</div>
                </div>
                <div class="card">
                    <div class="card-header">FIRs Filed</div>
                    <div class="card-value"><?php echo $total_firs; ?></div>
                    <div class="card-subtitle">Total FIRs</div>
                </div>
            </div>

            <!-- Quick Links -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 2rem 0; margin-bottom: 2rem;">
                <a href="manage_users.php" class="btn btn-primary" style="text-align: center; padding: 1rem;">Manage Users</a>
                <a href="manage_complaints.php" class="btn btn-secondary" style="text-align: center; padding: 1rem;">View Complaints</a>
                <a href="reports.php" class="btn btn-secondary" style="text-align: center; padding: 1rem;">System Reports</a>
            </div>

            <!-- Role Distribution -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 2rem;">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">User Distribution</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <?php while ($role = $role_distribution->fetch_assoc()): ?>
                        <div style="text-align: center; padding: 1rem; background-color: #f9f9f9; border-radius: var(--border-radius);">
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--secondary-color);"><?php echo $role['count']; ?></div>
                            <div style="color: #666; margin-top: 0.5rem;"><?php echo getRoleName($role['role']); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Recent Complaints -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Recent Complaints</h2>
                <?php if ($recent_complaints->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Citizen</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($complaint = $recent_complaints->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>CCS-<?php echo str_pad($complaint['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo escapeHtml($complaint['citizen_name']); ?></td>
                                        <td><?php echo escapeHtml(substr($complaint['title'], 0, 25)) . (strlen($complaint['title']) > 25 ? '...' : ''); ?></td>
                                        <td><?php echo formatStatus($complaint['status']); ?></td>
                                        <td><?php echo formatPriority($complaint['priority']); ?></td>
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
