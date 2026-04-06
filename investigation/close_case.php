<?php
/**
 * Close Case
 * Close investigation case
 */

require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';
require_once '../config/helpers.php';

requireRole('investigator');
$user_id = getUserId();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $complaint_id = intval($_POST['complaint_id'] ?? 0);
    $close_reason = sanitizeInput($_POST['close_reason'] ?? 'Case closed by investigator');

    $stmt = $conn->prepare("SELECT id, title, user_id FROM complaints WHERE id = ? AND assigned_investigator_id = ?");
    $stmt->bind_param("ii", $complaint_id, $user_id);
    $stmt->execute();
    $comp = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($comp) {
        $status = 'closed';
        $stmt = $conn->prepare("UPDATE complaints SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $status, $complaint_id);
        $stmt->execute();
        $stmt->close();

        $comment_text = "Complaint closed by Investigation Officer. Reason: " . $close_reason;
        $stmt = $conn->prepare("INSERT INTO comments (complaint_id, user_id, comment_text, is_internal) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("iis", $complaint_id, $user_id, $comment_text);
        $stmt->execute();
        $stmt->close();

        logActivity($user_id, $complaint_id, 'CASE_CLOSED', 'Case closed by investigator');

        createNotification($conn, $comp['user_id'],
            'Your Complaint Has Been Closed',
            'Your complaint "' . $comp['title'] . '" has been closed. Reason: ' . $close_reason,
            'info',
            APP_URL . '/citizen/view_complaint.php?id=' . $complaint_id
        );

        $admins = $conn->query("SELECT id FROM users WHERE role='admin' AND status='active'");
        while ($admin = $admins->fetch_assoc()) {
            createNotification($conn, $admin['id'],
                'Case Closed by Investigator',
                'Complaint CCS-' . str_pad($complaint_id, 5, '0', STR_PAD_LEFT) . ' "' . $comp['title'] . '" has been closed.',
                'success',
                APP_URL . '/admin/view_complaint.php?id=' . $complaint_id
            );
        }
    }
}

header("Location: view_case.php?id=" . $complaint_id);
exit();
?>