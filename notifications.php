<?php
/**
 * Notifications Page
 * Display all notifications for logged in user
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'config/session.php';
require_once 'config/helpers.php';

requireLogin();
$user_id = getUserId();
$user = getCurrentUser();

// Mark all as read
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");

// Get all notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?php echo APP_NAME; ?></title>
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
                        <li><a href="index.php">Home</a></li>
                        <li class="user-menu">
                            <span class="user-info"><?php echo escapeHtml($user['name']); ?></span>
                            <a href="auth/logout.php" class="logout-btn">Logout</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container" style="padding: 2rem;">
            <h1 style="color: var(--primary-color); margin-bottom: 2rem;">🔔 All Notifications</h1>
            
            <?php if ($notifications->num_rows > 0): ?>
                <?php while ($n = $notifications->fetch_assoc()): ?>
                    <div style="background:white; padding:1rem 1.5rem; margin-bottom:1rem; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); border-left:4px solid #2E86AB;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <strong><?php echo escapeHtml($n['title']); ?></strong>
                                <p style="margin:5px 0; color:#555;"><?php echo escapeHtml($n['message']); ?></p>
                                <small style="color:#999;"><?php echo $n['created_at']; ?></small>
                            </div>
                            <?php if ($n['link']): ?>
                                <a href="<?php echo escapeHtml($n['link']); ?>" style="margin-left: 1rem; color:#2E86AB; text-decoration: none; font-weight: bold; white-space: nowrap;">View →</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="background:white; padding:2rem; text-align:center; border-radius:8px;">
                    <p style="color:#999; font-size: 1.1rem;">No notifications yet.</p>
                </div>
            <?php endif; ?>
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
