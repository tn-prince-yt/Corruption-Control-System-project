<?php
include 'includes/config.php';
include 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Corruption Control System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/navbar.php'; ?>

        <div class="main-content" style="margin: 40px 0; max-width: 900px; margin-left: auto; margin-right: auto;">
            <div style="text-align:center; padding: 20px 0 40px;">
                <div style="font-size:56px; margin-bottom:16px;">⚖️</div>
                <h1 style="color: var(--primary); font-size: 32px; font-weight: 800; margin-bottom: 12px;">Corruption Control System</h1>
                <p style="font-size: 16px; color: var(--text-muted); max-width: 560px; margin: 0 auto 32px;">
                    A transparent platform for reporting corruption, tracking investigations and holding officials accountable.
                </p>
                <?php if (!isLoggedIn()): ?>
                <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                    <a href="auth/register.php" class="btn btn-primary" style="padding:12px 28px; font-size:15px;">Register as Citizen</a>
                    <a href="auth/login.php" class="btn btn-secondary" style="padding:12px 28px; font-size:15px;">Login</a>
                </div>
                <?php endif; ?>
            </div>

            <div class="stats-grid" style="margin-bottom:40px;">
                <div class="stat-card" style="text-align:left; padding:28px;">
                    <div style="font-size:28px; margin-bottom:12px;">👥</div>
                    <h3 style="font-size:18px; margin-bottom:8px; color:var(--primary);">Citizens</h3>
                    <p style="color:var(--text-muted); margin-bottom:16px; text-transform:none; letter-spacing:0;">Report corruption complaints and track their investigation status in real time.</p>
                    <a href="auth/register.php" class="btn btn-primary btn-sm">Register Now</a>
                    <a href="auth/login.php" class="btn btn-secondary btn-sm" style="margin-left:8px;">Login</a>
                </div>
                <div class="stat-card" style="text-align:left; padding:28px;">
                    <div style="font-size:28px; margin-bottom:12px;">🛡️</div>
                    <h3 style="font-size:18px; margin-bottom:8px; color:var(--primary);">Administrators</h3>
                    <p style="color:var(--text-muted); margin-bottom:16px; text-transform:none; letter-spacing:0;">Review submitted complaints, approve or reject, and assign investigation officers.</p>
                    <a href="auth/login.php" class="btn btn-primary btn-sm">Admin Login</a>
                </div>
                <div class="stat-card" style="text-align:left; padding:28px;">
                    <div style="font-size:28px; margin-bottom:12px;">🔍</div>
                    <h3 style="font-size:18px; margin-bottom:8px; color:var(--primary);">Officers</h3>
                    <p style="color:var(--text-muted); margin-bottom:16px; text-transform:none; letter-spacing:0;">Investigate assigned cases, submit detailed findings and close completed cases.</p>
                    <a href="auth/login.php" class="btn btn-primary btn-sm">Officer Login</a>
                </div>
            </div>

            <div class="info-box">
                <h3>System Features</h3>
                <ul>
                    <li>Online complaint submission with evidence file uploads</li>
                    <li>Real-time complaint status tracking for citizens</li>
                    <li>Admin review, approval and officer assignment workflow</li>
                    <li>Investigation report submission by assigned officers</li>
                    <li>Comprehensive audit logging for accountability</li>
                    <li>Secure password hashing and session authentication</li>
                </ul>
            </div>

            <div style="margin-top: 28px; padding: 24px; background: #f0f4f8; border-radius: var(--radius); border: 1px solid var(--border);">
                <h3 style="color: var(--primary); margin-bottom: 12px; font-size:14px; text-transform:uppercase; letter-spacing:0.5px;">Demo Credentials</h3>
                <p style="font-size: 13px; margin-bottom: 6px; color: var(--text);">
                    <strong>Admin:</strong> admin@corruption.com &nbsp;|&nbsp; <strong>Password:</strong> admin123
                </p>
                <p style="font-size: 13px; color: var(--text);">
                    <strong>Officer:</strong> officer@corruption.com &nbsp;|&nbsp; <strong>Password:</strong> officer123
                </p>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">⚠️ Run <code>fix_passwords.php</code> once after importing the database schema.</p>
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
