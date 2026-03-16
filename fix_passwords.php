<?php
/**
 * fix_passwords.php
 * Run this ONCE via browser: http://localhost/corruptioncontrolsystem/fix_passwords.php
 * It sets correct bcrypt-hashed passwords for the default demo accounts
 * and verifies the officers table is properly linked.
 * DELETE this file after running it.
 */
include 'includes/config.php';

$errors = [];
$messages = [];

// --- Hash passwords ---
$admin_hash   = password_hash('admin123',   PASSWORD_DEFAULT);
$officer_hash = password_hash('officer123', PASSWORD_DEFAULT);

$r1 = $conn->query("UPDATE users SET password = '$admin_hash'   WHERE email = 'admin@corruption.com'");
$r2 = $conn->query("UPDATE users SET password = '$officer_hash' WHERE email = 'officer@corruption.com'");

if ($r1 && $conn->affected_rows >= 0) $messages[] = "✅ Admin password set.";
else $errors[] = "❌ Admin update failed: " . $conn->error;

if ($r2 && $conn->affected_rows >= 0) $messages[] = "✅ Officer password set.";
else $errors[] = "❌ Officer update failed: " . $conn->error;

// --- Ensure officers row exists for officer user ---
$q = $conn->query("SELECT user_id FROM users WHERE email = 'officer@corruption.com' LIMIT 1");
if ($q && $q->num_rows > 0) {
    $uid = $q->fetch_assoc()['user_id'];

    // Check if officers row already exists
    $exists = $conn->query("SELECT officer_id FROM officers WHERE user_id = $uid");
    if ($exists && $exists->num_rows === 0) {
        $ins = $conn->query("INSERT INTO officers (user_id, department, badge_number, designation, is_available)
                             VALUES ($uid, 'Investigation', 'OFF001', 'Senior Investigator', TRUE)");
        if ($ins) $messages[] = "✅ Officer profile created (user_id=$uid).";
        else       $errors[]  = "❌ Failed to create officer profile: " . $conn->error;
    } else {
        $messages[] = "✅ Officer profile already exists (user_id=$uid).";
    }
} else {
    $errors[] = "❌ officer@corruption.com not found in users table. Import the schema first.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fix Passwords & Setup</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container" style="max-width:600px; margin-top:60px;">
    <div class="main-content">
        <h2>🔧 Setup / Fix Passwords</h2>

        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-success"><?php echo $msg; ?></div>
        <?php endforeach; ?>
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?php echo $err; ?></div>
        <?php endforeach; ?>

        <?php if (empty($errors)): ?>
        <div class="info-box">
            <h3>Ready to use</h3>
            <ul>
                <li><strong>Admin:</strong> admin@corruption.com / admin123</li>
                <li><strong>Officer:</strong> officer@corruption.com / officer123</li>
            </ul>
        </div>
        <div class="action-buttons" style="margin-top:20px;">
            <a href="auth/login.php" class="btn btn-primary">Go to Login</a>
        </div>
        <div class="alert alert-warning" style="margin-top:24px;">
            ⚠️ <strong>Delete this file</strong> from your server after setup is complete.
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
