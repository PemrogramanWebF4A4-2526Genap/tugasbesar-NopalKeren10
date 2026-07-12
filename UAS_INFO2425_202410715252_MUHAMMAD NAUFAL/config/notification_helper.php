<?php
require_once __DIR__ . '/database.php';

/**
 * Create a new notification
 */
function createNotification($userId, $userType, $title, $message, $type = 'other') {
    global $db;
    
    $query = "INSERT INTO notifications (user_id, user_type, title, message, type) 
              VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "issss", $userId, $userType, $title, $message, $type);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/**
 * Get unread notification count for a user
 */
function getUnreadNotificationCount($userId, $userType) {
    global $db;
    
    $query = "SELECT COUNT(*) as count FROM notifications 
              WHERE user_id = ? AND user_type = ? AND is_read = FALSE";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "is", $userId, $userType);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $row['count'];
}

/**
 * Get recent notifications for a user
 */
function getRecentNotifications($userId, $userType, $limit = 10) {
    global $db;
    
    $query = "SELECT id, title, message, type, is_read, created_at 
              FROM notifications 
              WHERE user_id = ? AND user_type = ? 
              ORDER BY created_at DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "isi", $userId, $userType, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $notifications = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $notifications;
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notificationId) {
    global $db;
    
    $query = "UPDATE notifications SET is_read = TRUE WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $notificationId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/**
 * Mark all notifications as read for a user
 */
function markAllNotificationsAsRead($userId, $userType) {
    global $db;
    
    $query = "UPDATE notifications SET is_read = TRUE 
              WHERE user_id = ? AND user_type = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "is", $userId, $userType);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/**
 * Format notification time relative to now
 */
function formatNotificationTime($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' menit yang lalu';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' jam yang lalu';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' hari yang lalu';
    } else {
        return date('d M Y', $timestamp);
    }
}
?>
