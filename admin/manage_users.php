<?php
/**
 * Manage Users Page
 * Admin panel for managing users
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require admin login
requireRole('admin');
$user_id = getUserId();

$user = getCurrentUser();
$error = '';
$success = '';

// Handle user status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $action = sanitizeInput($_POST['action'] ?? '');
    
    if ($user_id == $user['id']) {
        $error = 'Cannot modify your own account';
    } else {
        if ($action === 'activate') {
            $status = 'active';
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $user_id);
            $stmt->execute();
            $stmt->close();
            $success = 'User activated successfully';
        } else if ($action === 'suspend') {
            $status = 'suspended';
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $user_id);
            $stmt->execute();
            $stmt->close();
            $success = 'User suspended successfully';
        } else if ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            $success = 'User deleted successfully';
        }
    }
}

// Get filter
$role_filter = sanitizeInput($_GET['role'] ?? '');
$status_filter = sanitizeInput($_GET['status'] ?? '');

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($role_filter)) {
    $query .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo APP_NAME; ?></title>
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
            <h1 style="color: var(--primary-color); margin-bottom: 2rem;">Manage Users</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo escapeHtml($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo escapeHtml($success); ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div style="background-color: white; padding: 1.5rem; border-radius: var(--border-radius); margin-bottom: 2rem; display: flex; gap: 1rem;">
                <div>
                    <label for="roleFilter">Filter by Role:</label>
                    <select id="roleFilter" onchange="filterUsers()">
                        <option value="">All Roles</option>
                        <option value="citizen" <?php echo $role_filter === 'citizen' ? 'selected' : ''; ?>>Citizen</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="officer" <?php echo $role_filter === 'officer' ? 'selected' : ''; ?>>Officer</option>
                        <option value="investigator" <?php echo $role_filter === 'investigator' ? 'selected' : ''; ?>>Investigator</option>
                    </select>
                </div>
                <div>
                    <label for="statusFilter">Filter by Status:</label>
                    <select id="statusFilter" onchange="filterUsers()">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
                <?php if ($users->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user_row = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo escapeHtml($user_row['name']); ?></td>
                                        <td><?php echo escapeHtml($user_row['email']); ?></td>
                                        <td><?php echo escapeHtml($user_row['phone']); ?></td>
                                        <td><span class="badge badge-info"><?php echo getRoleName($user_row['role']); ?></span></td>
                                        <td>
                                            <?php 
                                            $statusClass = $user_row['status'] === 'active' ? 'badge-success' : 'badge-danger';
                                            echo '<span class="badge ' . $statusClass . '">' . ucfirst($user_row['status']) . '</span>';
                                            ?>
                                        </td>
                                        <td><?php echo formatDate($user_row['created_at']); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user_row['id']; ?>">
                                                <?php if ($user_row['status'] === 'active'): ?>
                                                    <button type="submit" name="action" value="suspend" class="btn btn-warning" style="font-size: 0.8rem; padding: 0.5rem; margin-right: 0.25rem;">Suspend</button>
                                                <?php else: ?>
                                                    <button type="submit" name="action" value="activate" class="btn btn-success" style="font-size: 0.8rem; padding: 0.5rem; margin-right: 0.25rem;">Activate</button>
                                                <?php endif; ?>
                                                <?php if ($user_row['id'] !== $user['id']): ?>
                                                    <button type="submit" name="action" value="delete" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.5rem;" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 2rem;">No users found.</p>
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

    <script>
        function filterUsers() {
            const role = document.getElementById('roleFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            let url = '?';
            if (role) url += 'role=' + role + '&';
            if (status) url += 'status=' + status;
            
            window.location.href = url;
        }
    </script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
</body>
</html>
