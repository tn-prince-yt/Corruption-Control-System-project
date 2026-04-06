<?php
/**
 * Take Case
 * Investigator takes assignment of a case
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

// Require investigator login
requireRole('investigator');

$user_id = getUserId();
$user = getCurrentUser();

// Get complaint ID from URL
$complaint_id = intval($_GET['id'] ?? 0);

if ($complaint_id <= 0) {
    header("Location: pending_assignment.php");
    exit();
}

// Get complaint details
$stmt = $conn->prepare("SELECT c.*, u.name as citizen_name FROM complaints c 
LEFT JOIN users u ON c.user_id = u.id 
WHERE c.id = ? AND c.status = 'approved' AND c.assigned_investigator_id IS NULL");
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$complaint) {
    header("Location: pending_assignment.php");
    exit();
}

$error = '';
$success = '';

// Handle case assignment and FIR creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fir_description = sanitizeInput($_POST['fir_description'] ?? '');
    
    if (empty($fir_description)) {
        $error = 'FIR description is required';
    } else {
        // Update complaint status and assign investigator
        $status = 'investigation';
        $stmt = $conn->prepare("UPDATE complaints SET status = ?, assigned_investigator_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $status, $user_id, $complaint_id);
        $stmt->execute();
        $stmt->close();
        
        // Create FIR
        $fir_number = generateFIRNumber();
        $fir_status = 'filed';
        $stmt = $conn->prepare("INSERT INTO fir (complaint_id, officer_id, fir_number, description, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $complaint_id, $user_id, $fir_number, $fir_description, $fir_status);
        $stmt->execute();
        $stmt->close();
        
        logActivity($user_id, $complaint_id, 'CASE_ASSIGNED', 'Investigator took assignment. FIR: ' . $fir_number);
        
        $success = 'Case assigned successfully! FIR Number: ' . $fir_number;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Case - <?php echo APP_NAME; ?></title>
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
            <a href="pending_assignment.php" style="color: var(--secondary-color); margin-bottom: 1rem; display: inline-block;">← Back to Pending Cases</a>

            <h1 style="color: var(--primary-color); margin-bottom: 2rem;">Take Case & Register FIR</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo escapeHtml($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo escapeHtml($success); ?></div>
                <a href="my_cases.php" class="btn btn-primary">Go to My Cases</a>
            <?php else: ?>
                <!-- Complaint Details -->
                <div class="complaint-card" style="border-left: 4px solid var(--primary-color);">
                    <div class="complaint-header">
                        <div>
                            <h2 class="complaint-title"><?php echo escapeHtml($complaint['title']); ?></h2>
                            <p class="complaint-ref">Reference: CCS-<?php echo str_pad($complaint['id'], 5, '0', STR_PAD_LEFT); ?></p>
                        </div>
                    </div>

                    <div class="complaint-meta">
                        <div class="complaint-meta-item">
                            <span class="complaint-meta-label">Status</span>
                            <span><?php echo formatStatus($complaint['status']); ?></span>
                        </div>
                        <div class="complaint-meta-item">
                            <span class="complaint-meta-label">Priority</span>
                            <span><?php echo formatPriority($complaint['priority']); ?></span>
                        </div>
                        <div class="complaint-meta-item">
                            <span class="complaint-meta-label">Category</span>
                            <span><?php echo escapeHtml($complaint['category']); ?></span>
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <h3 style="margin-bottom: 0.5rem; color: var(--primary-color);">Description</h3>
                        <p><?php echo escapeHtml($complaint['description']); ?></p>
                    </div>
                </div>

                <!-- FIR Form -->
                <div style="background-color: white; padding: 2rem; border-radius: var(--border-radius); margin-top: 2rem; box-shadow: var(--box-shadow);">
                    <h2 style="color: var(--primary-color); margin-bottom: 1.5rem;">Register First Information Report (FIR)</h2>

                    <form method="POST">
                        <div class="form-group">
                            <label for="fir_description">FIR Description *</label>
                            <textarea id="fir_description" name="fir_description" rows="6" placeholder="Describe the case details, preliminary findings..." required></textarea>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-success">Register FIR & Take Case</button>
                            <a href="pending_assignment.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
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
