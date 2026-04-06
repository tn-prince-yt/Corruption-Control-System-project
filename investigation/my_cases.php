<?php
/**
 * My Cases
 * List of cases assigned to investigator
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require investigator login
requireRole('investigator');

$user_id = getUserId();
$user = getCurrentUser();

// Get assigned cases
$stmt = $conn->prepare("SELECT c.*, u.name as citizen_name, f.fir_number FROM complaints c 
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN fir f ON c.id = f.complaint_id
WHERE c.assigned_investigator_id = ? 
ORDER BY c.status DESC, c.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cases = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cases - <?php echo APP_NAME; ?></title>
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
            <h1 style="color: var(--primary-color); margin-bottom: 2rem;">My Assigned Cases</h1>

            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <?php if ($cases->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>FIR Number</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($case = $cases->fetch_assoc()): ?>
                                    <tr>
                                        <td>CCS-<?php echo str_pad($case['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo escapeHtml($case['title']); ?></td>
                                        <td><?php echo formatStatus($case['status']); ?></td>
                                        <td><?php echo $case['fir_number'] ? escapeHtml($case['fir_number']) : '<span style="color:#999;">N/A</span>'; ?></td>
                                        <td><?php echo escapeHtml($case['category']); ?></td>
                                        <td><?php echo formatPriority($case['priority']); ?></td>
                                        <td>
                                            <a href="view_case.php?id=<?php echo $case['id']; ?>" class="btn btn-primary" style="font-size: 0.85rem;">Manage</a>
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
