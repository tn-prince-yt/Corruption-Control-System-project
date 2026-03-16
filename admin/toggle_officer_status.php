<?php
// Toggle Officer Availability
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

$officer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get current status
$query = "SELECT is_available FROM officers WHERE officer_id = $officer_id";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $officer = $result->fetch_assoc();
    $new_status = $officer['is_available'] ? 0 : 1;
    
    $update_query = "UPDATE officers SET is_available = $new_status WHERE officer_id = $officer_id";
    
    if ($conn->query($update_query) === TRUE) {
        auditLog('UPDATE_OFFICER', 'officers', $officer_id, 'Officer availability toggled');
        $_SESSION['form_success'] = "Officer status updated successfully.";
    } else {
        $_SESSION['form_error'] = "Failed to update officer status.";
    }
}

redirect(SITE_URL . 'admin/manage_officers.php');
