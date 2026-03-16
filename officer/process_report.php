<?php
include '../includes/config.php';
include '../includes/functions.php';

requireLogin();
requireOfficer();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . 'officer/view_complaints.php');
}

$complaint_id = intval($_POST['complaint_id']);
$officer_id   = intval($_POST['officer_id']);
$findings        = sanitizeInput($_POST['findings']);
$recommendations = isset($_POST['recommendations']) ? sanitizeInput($_POST['recommendations']) : '';
$status          = sanitizeInput($_POST['status']);

// Validate
if (empty($findings)) {
    $_SESSION['form_error'] = "Findings field is required.";
    redirect(SITE_URL . 'officer/submit_report.php?id=' . $complaint_id);
}

if (!in_array($status, array('draft', 'submitted'))) {
    $_SESSION['form_error'] = "Invalid status value.";
    redirect(SITE_URL . 'officer/submit_report.php?id=' . $complaint_id);
}

// Confirm the officer_id in POST matches the session officer
$user_id = intval($_SESSION['user_id']);
$check = $conn->query("SELECT officer_id FROM officers WHERE user_id = $user_id AND officer_id = $officer_id");
if (!$check || $check->num_rows === 0) {
    $_SESSION['form_error'] = "Authorisation error.";
    redirect(SITE_URL . 'officer/view_complaints.php');
}

$findings        = $conn->real_escape_string($findings);
$recommendations = $conn->real_escape_string($recommendations);

// Check if report exists
$existing = $conn->query("SELECT report_id FROM reports WHERE complaint_id = $complaint_id");

if ($existing && $existing->num_rows > 0) {
    // Update — only update submitted_date if transitioning to submitted
    $sql = "UPDATE reports 
            SET findings = '$findings', 
                recommendations = '$recommendations', 
                status = '$status',
                submitted_date = IF('$status' = 'submitted' AND submitted_date IS NULL, NOW(), submitted_date),
                updated_at = NOW()
            WHERE complaint_id = $complaint_id";

    if ($conn->query($sql)) {
        auditLog('UPDATE_REPORT', 'reports', $complaint_id, 'Investigation report updated to status: ' . $status);
        $_SESSION['form_success'] = "Report updated successfully.";
    } else {
        $_SESSION['form_error'] = "Failed to update report: " . $conn->error;
    }
} else {
    // Insert new report
    $submitted_date_sql = ($status === 'submitted') ? "NOW()" : "NULL";
    $sql = "INSERT INTO reports (complaint_id, officer_id, findings, recommendations, status, submitted_date) 
            VALUES ($complaint_id, $officer_id, '$findings', '$recommendations', '$status', $submitted_date_sql)";

    if ($conn->query($sql)) {
        auditLog('CREATE_REPORT', 'reports', $complaint_id, 'Investigation report created with status: ' . $status);
        $_SESSION['form_success'] = ($status === 'submitted') ? "Report submitted for review." : "Report saved as draft.";
    } else {
        $_SESSION['form_error'] = "Failed to create report: " . $conn->error;
    }
}

redirect(SITE_URL . 'officer/view_complaint_detail.php?id=' . $complaint_id);
