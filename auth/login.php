<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Corruption Control System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <div class="auth-logo">⚖️</div>
                <h1>Corruption Control System</h1>
                <p>Online Complaint Management</p>
            </div>

            <?php
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                unset($_SESSION['login_error']);
            }
            if (isset($_SESSION['register_success'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['register_success']) . '</div>';
                unset($_SESSION['register_success']);
            }
            ?>

            <form method="POST" action="process_login.php" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <label for="user_type">Login As</label>
                    <select id="user_type" name="user_type" required>
                        <option value="">Select User Type</option>
                        <option value="citizen">Citizen</option>
                        <option value="admin">Admin</option>
                        <option value="officer">Investigation Officer</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register as Citizen</a></p>
            </div>
        </div>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
