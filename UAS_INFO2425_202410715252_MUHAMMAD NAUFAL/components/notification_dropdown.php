<?php
require_once __DIR__ . '/../config/notification_helper.php';

$userId = $_SESSION['user_id'] ?? 0;
$userType = $_SESSION['user_type'] ?? 'buyer';

$unreadCount = getUnreadNotificationCount($userId, $userType);
$notifications = getRecentNotifications($userId, $userType, 10);
?>

<div class="relative" id="notification-container">
    <!-- Bell Icon -->
    <button onclick="toggleNotificationDropdown()" class="relative p-2 rounded-full hover:bg-gray-100 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <?php if ($unreadCount > 0): ?>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold">
                <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
            </span>
        <?php endif; ?>
    </button>

    <!-- Dropdown -->
    <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-semibold text-gray-800">Notifikasi</h3>
            <?php if ($unreadCount > 0): ?>
                <a href="read_notification.php" class="text-sm text-blue-600 hover:text-blue-800">Tandai semua dibaca</a>
            <?php endif; ?>
        </div>
        
        <div class="max-h-96 overflow-y-auto">
            <?php if (empty($notifications)): ?>
                <div class="p-8 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p>Tidak ada notifikasi</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer <?= !$notif['is_read'] ? 'bg-blue-50' : '' ?>" 
                         onclick="markAsRead(<?= $notif['id'] ?>)">
                        <div class="flex items-start space-x-3">
                            <?php
                            $iconColor = 'text-gray-500';
                            $iconPath = '';
                            
                            switch($notif['type']) {
                                case 'order_status':
                                    $iconColor = 'text-blue-500';
                                    $iconPath = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                                    break;
                                case 'new_review':
                                    $iconColor = 'text-yellow-500';
                                    $iconPath = 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z';
                                    break;
                                case 'payment':
                                    $iconColor = 'text-green-500';
                                    $iconPath = 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z';
                                    break;
                                default:
                                    $iconColor = 'text-gray-500';
                                    $iconPath = 'M13 16	h-2v-4h2v4zm0-6h-2v-2h2v2zm-1-8C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z';
                            }
                            ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 <?= $iconColor ?> mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $iconPath ?>" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800 <?= !$notif['is_read'] ? 'font-semibold' : '' ?>">
                                    <?= htmlspecialchars($notif['title']) ?>
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?= htmlspecialchars($notif['message']) ?>
                                </p>
                                <p class="text-xs text-gray-400 mt-2">
                                    <?= formatNotificationTime($notif['created_at']) ?>
                                </p>
                            </div>
                            <?php if (!$notif['is_read']): ?>
                                <span class="h-2 w-2 bg-blue-500 rounded-full"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($notifications)): ?>
            <div class="p-3 border-t border-gray-200 text-center">
                <a href="read_notification.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lihat Semua Notifikasi</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notification-dropdown');
    dropdown.classList.toggle('hidden');
}

function markAsRead(notificationId) {
    fetch('mark_notification_read.php?id=' + notificationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const container = document.getElementById('notification-container');
    const dropdown = document.getElementById('notification-dropdown');
    
    if (!container.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
