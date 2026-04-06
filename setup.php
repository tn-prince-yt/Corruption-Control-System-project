<?php
/**
 * System Setup - Initialize Users
 * Access: http://localhost/corruptioncontrolsystem/setup.php
 * 
 * This script initializes admin and test users with correct passwords.
 * IMPORTANT: Delete this file after first run for security!
 */

require_once 'config/database.php';
require_once 'config/helpers.php';
require_once 'config/config.php';

$setup_complete = false;
$message = '';
$status = 'info';

// Process setup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['initialize'])) {
    try {
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

        $results = [];
        
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
                    "UPDATE users SET password = ?, name = ?, phone = ? WHERE email = ?"
                );
                $update_stmt->bind_param("ssss", $hashed_password, $user['name'], $user['phone'], $user['email']);
                
                if ($update_stmt->execute()) {
                    $results[] = [
                        'email' => $user['email'],
                        'action' => 'Updated',
                        'success' => true
                    ];
                } else {
                    $results[] = [
                        'email' => $user['email'],
                        'action' => 'Update Failed',
                        'success' => false,
                        'error' => $update_stmt->error
                    ];
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
                    $results[] = [
                        'email' => $user['email'],
                        'action' => 'Created',
                        'success' => true
                    ];
                } else {
                    $results[] = [
                        'email' => $user['email'],
                        'action' => 'Creation Failed',
                        'success' => false,
                        'error' => $insert_stmt->error
                    ];
                }
                $insert_stmt->close();
            }
            $stmt->close();
        }
        
        // Check if all successful
        $all_success = array_reduce($results, function($carry, $item) {
            return $carry && $item['success'];
        }, true);
        
        if ($all_success) {
            $setup_complete = true;
            $status = 'success';
            $message = 'All users initialized successfully! You can now login.';
        } else {
            $status = 'error';
            $message = 'Some users failed to initialize. See details below.';
        }
        
    } catch (Exception $e) {
        $status = 'error';
        $message = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Setup - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 2rem;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1.5rem;
        }
        
        .setup-header h1 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .setup-header p {
            color: #7f8c8d;
            font-size: 0.95rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            font-size: 0.95rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
        
        .setup-content {
            margin-bottom: 1.5rem;
        }
        
        .setup-step {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #667eea;
        }
        
        .setup-step h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .setup-step p {
            color: #555;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }
        
        .credentials-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        
        .credentials-table th,
        .credentials-table td {
            padding: 0.75rem;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .credentials-table th {
            background-color: #667eea;
            color: white;
            font-weight: 600;
        }
        
        .credentials-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        
        .results-table th,
        .results-table td {
            padding: 0.75rem;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .results-table th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
        }
        
        .results-table .success {
            color: #28a745;
            font-weight: 600;
        }
        
        .results-table .failed {
            color: #dc3545;
            font-weight: 600;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        button {
            flex: 1;
            padding: 0.85rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .icon {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .next-steps {
            margin-top: 1rem;
            padding: 1rem;
            background-color: #e7f3ff;
            border-radius: 6px;
            border-left: 3px solid #2196F3;
        }
        
        .next-steps h4 {
            color: #1565c0;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .next-steps ol {
            margin-left: 1.5rem;
            color: #555;
            font-size: 0.9rem;
        }
        
        .next-steps li {
            margin-bottom: 0.5rem;
        }
        
        .warning {
            background-color: #fff3cd;
            padding: 0.75rem;
            border-left: 3px solid #ffc107;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <div class="icon">⚙️</div>
            <h1><?php echo APP_NAME; ?></h1>
            <p>System Setup & User Initialization</p>
        </div>

        <?php if ($setup_complete): ?>
            <div class="alert alert-success">
                <strong>✓ Setup Complete!</strong><br>
                <?php echo $message; ?>
            </div>
            
            <div class="setup-step">
                <h3>✓ Users Successfully Initialized</h3>
                <table class="credentials-table">
                    <tr>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Role</th>
                    </tr>
                    <tr>
                        <td>admin@corruptioncontrol.com</td>
                        <td><code>admin123@Ab</code></td>
                        <td>Admin</td>
                    </tr>
                    <tr>
                        <td>rajesh.officer@corruptioncontrol.com</td>
                        <td><code>admin123@Ab</code></td>
                        <td>Officer</td>
                    </tr>
                    <tr>
                        <td>priya.investigator@corruptioncontrol.com</td>
                        <td><code>admin123@Ab</code></td>
                        <td>Investigator</td>
                    </tr>
                </table>
            </div>
            
            <div class="next-steps">
                <h4>📋 Next Steps:</h4>
                <ol>
                    <li><strong>Delete this setup.php file</strong> for security</li>
                    <li><strong>Login</strong> with any of the credentials above</li>
                    <li><strong>Change admin password</strong> immediately</li>
                    <li><strong>Create test citizen account</strong> to explore workflow</li>
                </ol>
            </div>
            
            <div class="button-group">
                <a href="auth/login.php" style="flex: 1;">
                    <button class="btn-primary" style="width: 100%; margin: 0;">
                        🔐 Go to Login
                    </button>
                </a>
            </div>
            
        <?php elseif ($message && $status !== 'info'): ?>
            <div class="alert alert-<?php echo $status; ?>">
                <strong><?php echo $status === 'error' ? '✗ Error' : '⚠ Warning'; ?></strong><br>
                <?php echo $message; ?>
            </div>
            
            <?php if (isset($results) && !empty($results)): ?>
                <table class="results-table">
                    <tr>
                        <th>Email</th>
                        <th>Action</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?php echo $result['email']; ?></td>
                        <td><?php echo $result['action']; ?></td>
                        <td class="<?php echo $result['success'] ? 'success' : 'failed'; ?>">
                            <?php echo $result['success'] ? '✓ Success' : '✗ Failed'; ?>
                            <?php if (!$result['success'] && isset($result['error'])): ?>
                                <br><small><?php echo $result['error']; ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            
            <div class="button-group">
                <form method="POST" style="flex: 1;">
                    <button type="submit" name="initialize" class="btn-primary" style="width: 100%; margin: 0;">
                        🔄 Try Again
                    </button>
                </form>
            </div>
            
        <?php else: ?>
            <div class="alert alert-info">
                <strong>ℹ️ Information</strong><br>
                This wizard will initialize the admin user and test accounts with proper password hashing.
            </div>
            
            <div class="warning">
                ⚠️ <strong>Important:</strong> After setup completes, delete this <code>setup.php</code> file for security reasons!
            </div>
            
            <div class="setup-step">
                <h3>📊 What will be initialized:</h3>
                <table class="credentials-table">
                    <tr>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Role</th>
                    </tr>
                    <tr>
                        <td>admin@corruptioncontrol.com</td>
                        <td><code>admin123@Ab</code></td>
                        <td>Admin</td>
                    </tr>
                    <tr>
                        <td>rajesh.officer@corruptioncontrol.com</td>
                        <td><code>admin123@Ab</code></td>
                        <td>Officer</td>
                    </tr>
                    <tr>
                        <td>priya.investigator@corruptioncontrol.com</td>
                        <td><code>admin123@Ab</code></td>
                        <td>Investigator</td>
                    </tr>
                </table>
            </div>
            
            <div class="setup-step">
                <h3>✅ Checklist before setup:</h3>
                <p>
                    ✓ Database <code>corruption_control_system</code> created<br>
                    ✓ Schema imported from <code>database/schema.sql</code><br>
                    ✓ Database credentials configured in <code>config/database.php</code>
                </p>
            </div>
            
            <form method="POST">
                <div class="button-group">
                    <button type="submit" name="initialize" class="btn-primary">
                        🚀 Initialize Users
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
