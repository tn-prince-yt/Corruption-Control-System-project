<?php
/**
 * Investigation Dashboard
 * Investigation Officer dashboard
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require investigator login
requireRole('investigator');

$user_id = getUserId();
$user = getCurrentUser();

// Get statistics
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_cases,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as pending_fir,
    SUM(CASE WHEN status = 'investigation' THEN 1 ELSE 0 END) as investigating,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
FROM complaints WHERE assigned_investigator_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get approved complaints awaiting FIR
$stmt = $conn->prepare("SELECT c.*, u.name as citizen_name FROM complaints c 
LEFT JOIN users u ON c.user_id = u.id
WHERE c.status = 'approved' AND c.assigned_investigator_id IS NULL
ORDER BY c.priority DESC, c.created_at ASC LIMIT 5");
$stmt->execute();
$pending_fir = $stmt->get_result();
$stmt->close();

// Get assigned cases
$stmt = $conn->prepare("SELECT c.*, u.name as citizen_name, f.fir_number FROM complaints c 
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN fir f ON c.id = f.complaint_id
WHERE c.assigned_investigator_id = ? 
ORDER BY c.status DESC, c.created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$assigned_cases = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investigation Dashboard - <?php echo APP_NAME; ?></title>
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
                        <li><a href="pending_assignment.php">Pending Assignment</a></li>
                        <li><a href="my_cases.php">My Cases</a></li>
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
            <h1 style="color: var(--primary-color); margin-bottom: 2rem;">Investigation Officer Dashboard</h1>

            <!-- Statistics -->
            <div class="dashboard">
                <div class="card">
                    <div class="card-header">Total Cases</div>
                    <div class="card-value"><?php echo $stats['total_cases'] ?? 0; ?></div>
                </div>
                <div class="card">
                    <div class="card-header">Pending FIR</div>
                    <div class="card-value"><?php echo $stats['pending_fir'] ?? 0; ?></div>
                </div>
                <div class="card">
                    <div class="card-header">Investigating</div>
                    <div class="card-value"><?php echo $stats['investigating'] ?? 0; ?></div>
                </div>
                <div class="card">
                    <div class="card-header">Closed</div>
                    <div class="card-value"><?php echo $stats['closed'] ?? 0; ?></div>
                </div>
            </div>

            <!-- Quick Links -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 2rem 0;">
                <a href="pending_assignment.php" class="btn btn-primary" style="text-align: center; padding: 1rem;">Pending Assignment</a>
                <a href="my_cases.php" class="btn btn-secondary" style="text-align: center; padding: 1rem;">My Cases</a>
            </div>

            <!-- Pending Complaints -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 2rem;">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Pending Case Assignment</h2>
                <?php if ($pending_fir->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Citizen</th>
                                    <th>Assigned</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($complaint = $pending_fir->fetch_assoc()): ?>
                                    <tr>
                                        <td>CCS-<?php echo str_pad($complaint['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo escapeHtml(substr($complaint['title'], 0, 25)) . (strlen($complaint['title']) > 25 ? '...' : ''); ?></td>
                                        <td><?php echo escapeHtml($complaint['category']); ?></td>
                                        <td><?php echo formatPriority($complaint['priority']); ?></td>
                                        <td><?php echo escapeHtml($complaint['citizen_name']); ?></td>
                                        <td><?php echo formatDate($complaint['created_at']); ?></td>
                                        <td>
                                            <a href="take_case.php?id=<?php echo $complaint['id']; ?>" class="btn btn-primary" style="font-size: 0.85rem;">Take Case</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 2rem;">No pending cases to assign.</p>
                <?php endif; ?>
            </div>

            <!-- My Cases -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">My Assigned Cases</h2>
                <?php if ($assigned_cases->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>FIR</th>
                                    <th>Citizen</th>
                                    <th>Assigned</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($case = $assigned_cases->fetch_assoc()): ?>
                                    <tr>
                                        <td>CCS-<?php echo str_pad($case['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo escapeHtml($case['title']); ?></td>
                                        <td><?php echo formatStatus($case['status']); ?></td>
                                        <td><?php echo $case['fir_number'] ? escapeHtml($case['fir_number']) : '<span style="color:#999;">Pending</span>'; ?></td>
                                        <td><?php echo escapeHtml($case['citizen_name']); ?></td>
                                        <td><?php echo formatDate($case['created_at']); ?></td>
                                        <td>
                                            <a href="view_case.php?id=<?php echo $case['id']; ?>" class="btn btn-primary" style="font-size: 0.85rem;">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 2rem;">No assigned cases.</p>
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
