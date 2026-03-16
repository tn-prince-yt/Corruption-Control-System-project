<?php
// Process Complaint Submission
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireCitizen();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citizen_id = $_SESSION['user_id'];
    $title = sanitizeInput($_POST['title']);
    $category = sanitizeInput($_POST['category']);
    $location = sanitizeInput($_POST['location']);
    $complaint_date = sanitizeInput($_POST['complaint_date']);
    $description = sanitizeInput($_POST['description']);
    
    // Validation
    $errors = array();
    
    if (empty($title) || empty($category) || empty($location) || empty($complaint_date) || empty($description)) {
        $errors[] = "All fields are required.";
    }
    
    if (count($errors) === 0) {
        // Escape for database
        $title = $conn->real_escape_string($title);
        $category = $conn->real_escape_string($category);
        $location = $conn->real_escape_string($location);
        $description = $conn->real_escape_string($description);
        $complaint_date = $conn->real_escape_string($complaint_date);
        
        // Insert complaint
        $query = "INSERT INTO complaints (citizen_id, title, description, category, location, complaint_date, status) 
                  VALUES ($citizen_id, '$title', '$description', '$category', '$location', '$complaint_date', 'pending')";
        
        if ($conn->query($query) === TRUE) {
            $complaint_id = $conn->insert_id;
            
            // Handle file uploads
            $evidence_count = 0;
            if (isset($_FILES['evidence']) && count($_FILES['evidence']['name']) > 0) {
                for ($i = 0; $i < count($_FILES['evidence']['name']); $i++) {
                    if (!empty($_FILES['evidence']['name'][$i])) {
                        $file = array(
                            'name' => $_FILES['evidence']['name'][$i],
                            'tmp_name' => $_FILES['evidence']['tmp_name'][$i],
                            'size' => $_FILES['evidence']['size'][$i],
                            'error' => $_FILES['evidence']['error'][$i]
                        );
                        
                        $upload_result = uploadFile($file, 'evidence');
                        
                        if ($upload_result['success']) {
                            $filename = $conn->real_escape_string($upload_result['filename']);
                            $filepath = $conn->real_escape_string($upload_result['path']);
                            $file_type = $conn->real_escape_string(getFileExtension($_FILES['evidence']['name'][$i]));
                            $file_size = $_FILES['evidence']['size'][$i];
                            
                            $evidence_query = "INSERT INTO evidence (complaint_id, file_name, file_type, file_size, file_path, uploaded_by) 
                                            VALUES ($complaint_id, '$filename', '$file_type', $file_size, '$filepath', $citizen_id)";
                            
                            if ($conn->query($evidence_query) === TRUE) {
                                $evidence_count++;
                            }
                        }
                    }
                }
            }
            
            // Update evidence count
            $update_query = "UPDATE complaints SET evidence_count = $evidence_count WHERE complaint_id = $complaint_id";
            $conn->query($update_query);
            
            // Audit log
            auditLog('SUBMIT_COMPLAINT', 'complaints', $complaint_id, 'Complaint submitted with ' . $evidence_count . ' evidence files');
            
            $_SESSION['form_success'] = "Complaint submitted successfully! Complaint ID: #" . $complaint_id;
            redirect(SITE_URL . 'citizen/submit_complaint.php');
        } else {
            $errors[] = "Failed to submit complaint. Please try again.";
        }
    }
    
    if (count($errors) > 0) {
        $_SESSION['form_error'] = implode('<br>', $errors);
    }
}

redirect(SITE_URL . 'citizen/submit_complaint.php');
