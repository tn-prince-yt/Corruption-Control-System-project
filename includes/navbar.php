<?php
// Navigation Bar
if (!isset($_SESSION)) {
    session_start();
}
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-content">
            <h1>Corruption Control System</h1>
            <div class="navbar-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></span>
                    <a href="<?php echo SITE_URL; ?>auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>auth/login.php">Login</a>
                    <a href="<?php echo SITE_URL; ?>auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
