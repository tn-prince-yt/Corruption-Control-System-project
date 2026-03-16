<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireOfficer();

// Get officer info safely
$user_id = intval($_SESSION['user_id']);
$result = $conn->query("SELECT officer_id FROM officers WHERE user_id = $user_id");

if (!$result || $result->num_rows === 0) {
    $_SESSION['form_error'] = "Officer profile not found. Contact administrator.";
    redirect(SITE_URL . 'officer/view_complaints.php');
}
$officer_id = $result->fetch_assoc()['officer_id'];

$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verify complaint is assigned to this officer and is under investigation
$check = $conn->query("SELECT complaint_id FROM complaints 
                        WHERE complaint_id = $complaint_id 
                        AND assigned_officer_id = $officer_id 
                        AND status = 'under_investigation'");

if ($check && $check->num_rows > 0) {
    // Require a submitted report before closing
    $report_check = $conn->query("SELECT report_id FROM reports WHERE complaint_id = $complaint_id AND status = 'submitted'");

    if ($report_check && $report_check->num_rows > 0) {
        $update = $conn->query("UPDATE complaints SET status = 'completed' WHERE complaint_id = $complaint_id");
        if ($update) {
            auditLog('MARK_COMPLETED', 'complaints', $complaint_id, 'Case marked as completed by officer');
            $_SESSION['form_success'] = "Case #$complaint_id has been marked as completed.";
        } else {
            $_SESSION['form_error'] = "Database error: " . $conn->error;
        }
    } else {
        $_SESSION['form_error'] = "Cannot close the case: please submit (not just save) your investigation report first.";
    }
} else {
    $_SESSION['form_error'] = "Invalid request — complaint not found, not assigned to you, or not under investigation.";
}

redirect(SITE_URL . 'officer/view_complaint_detail.php?id=' . $complaint_id);
