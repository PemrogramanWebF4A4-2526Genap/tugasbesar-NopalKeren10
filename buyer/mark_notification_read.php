<?php
session_start();
require_once '../config/database.php';
require_once '../config/notification_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$notificationId = $_GET['id'] ?? 0;

if ($notificationId > 0) {
    markNotificationAsRead($notificationId);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
}
?>
