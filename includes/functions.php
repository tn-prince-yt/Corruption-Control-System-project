<?php
// Common Functions

// Validate Email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate Phone Number (simple validation)
function validatePhone($phone) {
    return preg_match('/^[0-9\-\+\s\(\)]{10,}$/', $phone);
}

// Hash Password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify Password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Sanitize Input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Redirect User
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Check if User is Logged In
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check User Role
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function isOfficer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'officer';
}

function isCitizen() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'citizen';
}

// Require Login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . 'auth/login.php');
    }
}

// Require Admin
function requireAdmin() {
    if (!isAdmin()) {
        redirect(SITE_URL . 'index.php');
    }
}

// Require Officer
function requireOfficer() {
    if (!isOfficer()) {
        redirect(SITE_URL . 'index.php');
    }
}

// Require Citizen
function requireCitizen() {
    if (!isCitizen()) {
        redirect(SITE_URL . 'index.php');
    }
}

// Get File Extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Validate File Upload
function validateFileUpload($file) {
    global $conn;
    
    $errors = array();
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = "File size exceeds maximum limit of 5MB.";
    }
    
    // Allowed file types
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx');
    $ext = getFileExtension($file['name']);
    
    if (!in_array($ext, $allowed)) {
        $errors[] = "File type not allowed. Allowed types: " . implode(', ', $allowed);
    }
    
    return $errors;
}

// Upload File
function uploadFile($file, $folder) {
    global $conn;
    
    $errors = validateFileUpload($file);
    
    if (count($errors) > 0) {
        return array('success' => false, 'errors' => $errors);
    }
    
    // Create filename with timestamp
    $ext = getFileExtension($file['name']);
    $filename = time() . '_' . sanitizeInput(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $ext;
    $upload_path = UPLOAD_DIR . $folder . '/' . $filename;
    
    // Create folder if not exists
    if (!is_dir(UPLOAD_DIR . $folder)) {
        mkdir(UPLOAD_DIR . $folder, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return array('success' => true, 'filename' => $filename, 'path' => $upload_path);
    } else {
        return array('success' => false, 'errors' => array('Failed to upload file.'));
    }
}

// Format Date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Format DateTime
function formatDateTime($datetime) {
    return date('M d, Y H:i A', strtotime($datetime));
}

// Get User Info
function getUserInfo($user_id) {
    global $conn;
    $user_id = intval($user_id);
    $query = "SELECT * FROM users WHERE user_id = $user_id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Get Officer Info
function getOfficerInfo($officer_id) {
    global $conn;
    $officer_id = intval($officer_id);
    $query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone 
              FROM officers o 
              JOIN users u ON o.user_id = u.user_id 
              WHERE o.officer_id = $officer_id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Log Action
function auditLog($action, $table_name, $record_id, $description) {
    global $conn;
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    $action = $conn->real_escape_string($action);
    $table_name = $conn->real_escape_string($table_name);
    $record_id = intval($record_id);
    $description = $conn->real_escape_string($description);
    
    $query = "INSERT INTO audit_logs (user_id, action, table_name, record_id, description) 
              VALUES ($user_id, '$action', '$table_name', $record_id, '$description')";
    
    return $conn->query($query);
}
