<?php
// Process Complaint Approval/Rejection
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaint_id = intval($_POST['complaint_id']);
    $action = sanitizeInput($_POST['action']);
    $officer_id = isset($_POST['officer_id']) && !empty($_POST['officer_id']) ? intval($_POST['officer_id']) : null;
    $rejection_reason = isset($_POST['rejection_reason']) ? sanitizeInput($_POST['rejection_reason']) : '';
    
    // Validate action
    if (empty($action) || !in_array($action, array('approve', 'reject'))) {
        $_SESSION['form_error'] = "Invalid action selected.";
        redirect(SITE_URL . 'admin/approve_complaint.php?id=' . $complaint_id);
    }
    
    // Get and verify complaint is pending
    $query = "SELECT complaint_id, status FROM complaints WHERE complaint_id = $complaint_id";
    $result = $conn->query($query);
    
    if ($result->num_rows === 0) {
        $_SESSION['form_error'] = "Complaint not found.";
        redirect(SITE_URL . 'admin/view_complaints.php');
    }
    
    $complaint = $result->fetch_assoc();
    if ($complaint['status'] !== 'pending') {
        $_SESSION['form_error'] = "This complaint has already been processed.";
        redirect(SITE_URL . 'admin/view_complaints.php');
    }
    
    if ($action === 'approve') {
        if ($officer_id === null) {
            $_SESSION['form_error'] = "Please select an officer to assign.";
            redirect(SITE_URL . 'admin/approve_complaint.php?id=' . $complaint_id);
        }
        
        // Approve and set status to under_investigation immediately (officer is assigned)
        $update_query = "UPDATE complaints SET status = 'under_investigation', assigned_officer_id = $officer_id, approval_date = NOW() WHERE complaint_id = $complaint_id";
        
        if ($conn->query($update_query) === TRUE) {
            auditLog('APPROVE_COMPLAINT', 'complaints', $complaint_id, 'Complaint approved and assigned to officer #' . $officer_id);
            $_SESSION['form_success'] = "Complaint approved and assigned to officer. Investigation started.";
        } else {
            $_SESSION['form_error'] = "Database error: " . $conn->error;
            redirect(SITE_URL . 'admin/approve_complaint.php?id=' . $complaint_id);
        }

    } else if ($action === 'reject') {
        if (empty($rejection_reason)) {
            $_SESSION['form_error'] = "Please provide a reason for rejection.";
            redirect(SITE_URL . 'admin/approve_complaint.php?id=' . $complaint_id);
        }
        
        $rejection_reason = $conn->real_escape_string($rejection_reason);
        $update_query = "UPDATE complaints SET status = 'rejected', rejection_reason = '$rejection_reason', approval_date = NOW() WHERE complaint_id = $complaint_id";
        
        if ($conn->query($update_query) === TRUE) {
            auditLog('REJECT_COMPLAINT', 'complaints', $complaint_id, 'Complaint rejected');
            $_SESSION['form_success'] = "Complaint rejected successfully.";
        } else {
            $_SESSION['form_error'] = "Database error: " . $conn->error;
            redirect(SITE_URL . 'admin/approve_complaint.php?id=' . $complaint_id);
        }
    }
    
    redirect(SITE_URL . 'admin/view_complaints.php?filter=pending');
} else {
    redirect(SITE_URL . 'admin/view_complaints.php');
}
