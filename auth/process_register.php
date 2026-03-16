<?php
// Registration Processing
include '../includes/config.php';
include '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = array();
    
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
        $errors[] = "All fields are required.";
    }
    
    if (!validateEmail($email)) {
        $errors[] = "Invalid email format.";
    }
    
    if (!validatePhone($phone)) {
        $errors[] = "Invalid phone number.";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check if email already exists
    $email_check = $conn->real_escape_string($email);
    $query = "SELECT user_id FROM users WHERE email = '$email_check'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $errors[] = "Email already registered.";
    }
    
    // If no errors, register user
    if (count($errors) === 0) {
        $first_name = $conn->real_escape_string($first_name);
        $last_name = $conn->real_escape_string($last_name);
        $phone = $conn->real_escape_string($phone);
        $hashed_password = hashPassword($password);
        
        $query = "INSERT INTO users (email, password, first_name, last_name, phone, user_type, is_active) 
                  VALUES ('$email_check', '$hashed_password', '$first_name', '$last_name', '$phone', 'citizen', TRUE)";
        
        if ($conn->query($query) === TRUE) {
            $_SESSION['register_success'] = "Registration successful! Please login with your credentials.";
            auditLog('REGISTER', 'users', $conn->insert_id, 'New citizen registered');
            redirect(SITE_URL . 'auth/login.php');
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
    
    // If errors, redirect back
    if (count($errors) > 0) {
        $_SESSION['register_error'] = implode('<br>', $errors);
        redirect(SITE_URL . 'auth/register.php');
    }
} else {
    redirect(SITE_URL . 'auth/register.php');
}
