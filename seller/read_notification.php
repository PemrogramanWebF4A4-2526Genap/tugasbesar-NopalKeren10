<?php
session_start();
require_once '../config/database.php';
require_once '../config/notification_helper.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userType = 'seller';

// Mark all as read if requested
if (isset($_GET['mark_all_read'])) {
    markAllNotificationsAsRead($userId, $userType);
    header('Location: read_notification.php');
    exit;
}

$notifications = getRecentNotifications($userId, $userType, 50);
$unreadCount = getUnreadNotificationCount($userId, $userType);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Seller Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#161F2B',
                        'ink-light': '#212F40',
                        paper: '#F5EFE2',
                        card: '#FFFDF7',
                        brass: '#B4823A',
                        'brass-light': '#DDBD82',
                        maroon: '#7B2A32',
                        'maroon-light': '#9A3B44',
                        sage: '#5C7460',
                    },
                    fontFamily: {
                        serif: ['Fraunces', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #F5EFE2;
            background-image: radial-gradient(#00000008 1px, transparent 1px);
            background-size: 22px 22px;
        }
        .eyebrow::before {
            content: '';
            display: inline-block;
            width: 22px;
            height: 1px;
            background: #7B2A32;
            margin-right: 8px;
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-paper flex min-h-screen font-sans text-ink">

    <div class="bg-ink text-paper w-64 min-h-screen p-5 space-y-6 shrink-0 border-r-4 border-brass">
        <h2 class="text-xl font-serif font-bold flex items-center gap-2"><span class="text-brass-light">📚</span> Seller Panel</h2>
        <nav class="space-y-2 flex flex-col text-sm">
            <a href="index.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Dashboard</a>
            <a href="manage_products.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Produk Buku</a>
            <a href="validasi_penjual.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Validasi Pesanan</a>
            <a href="sales_history.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Riwayat Penjualan</a>
            <a href="read_notification.php" class="bg-maroon px-4 py-2.5 rounded-sm font-semibold">Notifikasi</a>
            <a href="../auth/logout.php" class="text-brass-light/80 hover:bg-ink-light px-4 py-2.5 rounded-sm mt-10 transition">Logout</a>
        </nav>
    </div>

    <div class="flex-1 p-10 overflow-x-auto">
        <div class="max-w-4xl">
            <div class="mb-8">
                <p class="eyebrow text-xs font-bold uppercase tracking-widest text-maroon mb-1">Pusat Informasi</p>
                <h1 class="text-3xl font-serif font-bold text-ink">🔔 Notifikasi</h1>
            </div>

            <div class="bg-card p-6 rounded-sm shadow-md border border-ink/5">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-ink/10">
                    <p class="text-sm text-ink/60">
                        <?php if ($unreadCount > 0): ?>
                            <span class="font-bold text-maroon"><?= $unreadCount ?></span> notifikasi belum dibaca
                        <?php else: ?>
                            Semua notifikasi sudah dibaca
                        <?php endif; ?>
                    </p>
                    <?php if ($unreadCount > 0): ?>
                        <a href="?mark_all_read=1" class="bg-maroon hover:bg-maroon-light text-paper px-4 py-2 rounded-sm text-xs font-bold uppercase tracking-wide transition">
                            Tandai Semua Dibaca
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($notifications)): ?>
                    <div class="text-center py-16">
                        <div class="text-6xl mb-4">🔔</div>
                        <p class="text-ink/40 font-serif text-lg mb-2">Tidak ada notifikasi</p>
                        <p class="text-ink/30 text-sm">Anda akan menerima notifikasi saat ada pesanan baru atau ulasan dari pembeli</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($notifications as $notif): ?>
                            <div class="p-4 rounded-sm border <?= !$notif['is_read'] ? 'bg-brass/10 border-brass/30' : 'bg-paper/30 border-ink/10' ?> hover:shadow-md transition">
                                <div class="flex items-start space-x-4">
                                    <?php
                                    $iconColor = 'text-ink/40';
                                    $iconBg = 'bg-ink/5';
                                    $iconEmoji = '📌';
                                    
                                    switch($notif['type']) {
                                        case 'order_status':
                                            $iconColor = 'text-sage';
                                            $iconBg = 'bg-sage/10';
                                            $iconEmoji = '📦';
                                            break;
                                        case 'new_review':
                                            $iconColor = 'text-brass';
                                            $iconBg = 'bg-brass/10';
                                            $iconEmoji = '⭐';
                                            break;
                                        case 'payment':
                                            $iconColor = 'text-maroon';
                                            $iconBg = 'bg-maroon/10';
                                            $iconEmoji = '💳';
                                            break;
                                        default:
                                            $iconColor = 'text-ink/40';
                                            $iconBg = 'bg-ink/5';
                                            $iconEmoji = '📌';
                                    }
                                    ?>
                                    <div class="<?= $iconBg ?> p-3 rounded-sm shrink-0">
                                        <span class="text-2xl"><?= $iconEmoji ?></span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <h3 class="text-base font-serif font-bold text-ink <?= !$notif['is_read'] ? 'text-maroon' : '' ?>">
                                                <?= htmlspecialchars($notif['title']) ?>
                                            </h3>
                                            <?php if (!$notif['is_read']): ?>
                                                <span class="bg-maroon text-paper text-xs font-bold px-2 py-0.5 rounded-sm uppercase tracking-wide">Baru</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-ink/70 mt-1 leading-relaxed">
                                            <?= htmlspecialchars($notif['message']) ?>
                                        </p>
                                        <p class="text-xs text-ink/40 mt-2 font-medium">
                                            <?= formatNotificationTime($notif['created_at']) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
