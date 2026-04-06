<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Escape output for HTML
 */
function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Hash password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate unique reference number
 */
function generateReferenceNumber($prefix = 'CCS') {
    return $prefix . '-' . date('YmdHis') . '-' . rand(1000, 9999);
}

/**
 * Generate FIR number
 */
function generateFIRNumber() {
    return 'FIR-' . date('Y') . '-' . rand(100000, 999999);
}

/**
 * Format complaint status for display
 */
function formatStatus($status) {
    $statuses = [
        'submitted' => '<span class="badge badge-info">Submitted</span>',
        'under_review' => '<span class="badge badge-warning">Under Review</span>',
        'approved' => '<span class="badge badge-success">Approved</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>',
        'investigation' => '<span class="badge badge-primary">Investigation</span>',
        'closed' => '<span class="badge badge-secondary">Closed</span>'
    ];
    return $statuses[$status] ?? $status;
}

/**
 * Format priority for display
 */
function formatPriority($priority) {
    $colors = [
        'low' => '<span class="badge badge-secondary">Low</span>',
        'medium' => '<span class="badge badge-info">Medium</span>',
        'high' => '<span class="badge badge-warning">High</span>',
        'critical' => '<span class="badge badge-danger">Critical</span>'
    ];
    return $colors[$priority] ?? $priority;
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('d M Y, h:i A', strtotime($date));
}

/**
 * Format date for input
 */
function formatDateForInput($date) {
    return date('Y-m-d', strtotime($date));
}

/**
 * Calculate days ago
 */
function daysAgo($date) {
    $days = floor((time() - strtotime($date)) / 86400);
    if ($days == 0) {
        return 'Today';
    } elseif ($days == 1) {
        return 'Yesterday';
    } else {
        return $days . ' days ago';
    }
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone
 */
function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

/**
 * Log activity
 */
function logActivity($user_id, $complaint_id, $action, $description = '') {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, complaint_id, action, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $complaint_id, $action, $description);
    return $stmt->execute();
    $stmt->close();
}

/**
 * Upload file
 */
function uploadFile($file, $upload_dir) {
    if (!isset($file) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds maximum limit (5MB)'];
    }
    
    // Get file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate extension
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $upload_dir . $filename;
    
    // Create directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'original_name' => $file['name'],
            'size' => $file['size']
        ];
    } else {
        return ['success' => false, 'error' => 'Failed to upload file'];
    }
}

/**
 * Delete file
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Check if file exists
 */
function fileExists($filepath) {
    return file_exists($filepath);
}

/**
 * Send JSON response
 */
function sendJsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Get user role name
 */
function getRoleName($role) {
    $roles = [
        'citizen' => 'Citizen',
        'admin' => 'Administrator',
        'officer' => 'Anti-Corruption Officer',
        'investigator' => 'Investigation Officer'
    ];
    return $roles[$role] ?? $role;
}

/**
 * Create notification for a user
 */
function createNotification($conn, $user_id, $title, $message, $type = 'info', $link = null) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $title, $message, $type, $link);
    $stmt->execute();
    $stmt->close();
}

/**
 * Get unread notification count for user
 */
function getUnreadCount($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['count'];
}

/**
 * Render notification bell in header
 */
function renderNotificationBell($conn, $user_id) {
    $count = getUnreadCount($conn, $user_id);
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    if (!$stmt) return;
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $notifications = $stmt->get_result();
    $stmt->close();

    echo '<li style="list-style:none; display:flex; align-items:center;">
        <div class="notif-bell" style="position:relative; display:flex; align-items:center; cursor:pointer;">
            <a href="' . APP_URL . '/notifications.php" style="text-decoration:none; font-size:1.3rem; line-height:1; display:flex; align-items:center; padding:0 10px;">
                🔔
                ' . ($count > 0 ? '<span style="position:absolute; top:-4px; right:2px; background:#e74c3c; color:white; border-radius:50%; min-width:18px; height:18px; display:flex; align-items:center; justify-content:center; font-size:0.65rem; font-weight:bold; padding:0 3px;">' . ($count > 99 ? '99+' : $count) . '</span>' : '') . '
            </a>
            <div class="notif-dropdown" style="display:none; position:absolute; top:110%; right:0; background:white; width:300px; box-shadow:0 4px 20px rgba(0,0,0,0.15); border-radius:8px; z-index:9999; overflow:hidden;">
                <div style="padding:10px 15px; background:#1B3A6B; color:white; font-weight:bold; font-size:0.9rem;">
                    🔔 Notifications ' . ($count > 0 ? '<span style="background:#e74c3c; border-radius:10px; padding:1px 8px; font-size:0.75rem; margin-left:5px;">' . $count . ' new</span>' : '') . '
                </div>';

    if ($notifications->num_rows > 0) {
        while ($n = $notifications->fetch_assoc()) {
            $bg   = $n['is_read'] ? '#fff' : '#f0f7ff';
            $dot  = $n['is_read'] ? '' : '<span style="display:inline-block;width:8px;height:8px;background:#e74c3c;border-radius:50%;margin-right:6px;"></span>';
            $colors = ['info'=>'#2E86AB','success'=>'#27AE60','warning'=>'#E67E22','danger'=>'#e74c3c'];
            $border = $colors[$n['type']] ?? '#2E86AB';
            echo '<a href="' . ($n['link'] ?? '#') . '" style="display:block; padding:10px 15px; border-bottom:1px solid #f0f0f0; background:' . $bg . '; text-decoration:none; color:#333; border-left:3px solid ' . $border . ';">
                <div style="font-size:0.82rem; font-weight:600;">' . $dot . htmlspecialchars($n['title']) . '</div>
                <div style="font-size:0.78rem; color:#666; margin-top:3px;">' . htmlspecialchars($n['message']) . '</div>
                <div style="font-size:0.72rem; color:#aaa; margin-top:4px;">🕐 ' . $n['created_at'] . '</div>
            </a>';
        }
    } else {
        echo '<p style="padding:20px; color:#999; text-align:center; font-size:0.85rem;">No notifications yet</p>';
    }

    echo '<a href="' . APP_URL . '/notifications.php" style="display:block; text-align:center; padding:10px; background:#f8f9fa; color:#2E86AB; font-weight:bold; font-size:0.85rem; text-decoration:none;">
                View All Notifications →
            </a>
        </div>
    </div>
</li>';
}

?>
