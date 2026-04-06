<?php
/**
 * Initialize Users with Correct Passwords
 * Run this once to properly set up admin and test users
 * Access via: http://localhost/corruptioncontrolsystem/database/init_users.php
 */

require_once '../config/database.php';
require_once '../config/helpers.php';

echo "<h2>Initializing Users with Correct Passwords</h2>";

// Define users to initialize
$users = [
    [
        'name' => 'System Admin',
        'email' => 'admin@corruptioncontrol.com',
        'password' => 'admin123@Ab',
        'role' => 'admin',
        'department' => 'Admin',
        'phone' => '9876543200'
    ],
    [
        'name' => 'Rajesh Kumar',
        'email' => 'rajesh.officer@corruptioncontrol.com',
        'password' => 'admin123@Ab',
        'role' => 'officer',
        'department' => 'Anti-Corruption',
        'phone' => '9876543210'
    ],
    [
        'name' => 'Priya Singh',
        'email' => 'priya.investigator@corruptioncontrol.com',
        'password' => 'admin123@Ab',
        'role' => 'investigator',
        'department' => 'Investigation',
        'phone' => '9876543211'
    ]
];

try {
    foreach ($users as $user) {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $user['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing user
            $hashed_password = hashPassword($user['password']);
            $update_stmt = $conn->prepare(
                "UPDATE users SET password = ? WHERE email = ?"
            );
            $update_stmt->bind_param("ss", $hashed_password, $user['email']);
            
            if ($update_stmt->execute()) {
                echo "<p style='color: green;'>✓ Updated: " . $user['email'] . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to update: " . $user['email'] . "</p>";
            }
            $update_stmt->close();
        } else {
            // Insert new user
            $hashed_password = hashPassword($user['password']);
            $insert_stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, role, department, phone, status) 
                 VALUES (?, ?, ?, ?, ?, ?, 'active')"
            );
            $insert_stmt->bind_param(
                "ssssss",
                $user['name'],
                $user['email'],
                $hashed_password,
                $user['role'],
                $user['department'],
                $user['phone']
            );
            
            if ($insert_stmt->execute()) {
                echo "<p style='color: green;'>✓ Created: " . $user['email'] . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to create: " . $user['email'] . "</p>";
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✓ User Initialization Complete!</h3>";
    echo "<p><strong>Test Credentials:</strong></p>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Email</th><th>Password</th><th>Role</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['password'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><a href='" . APP_URL . "/auth/login.php'>← Back to Login</a></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
