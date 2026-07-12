<?php
session_start();
include '../config/database.php';
require_once '../config/notification_helper.php';

// Proteksi: Hanya boleh diakses oleh Seller yang sudah login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$_SESSION['user_type'] = 'seller';

// Get unread notification count
$unread_notifications = getUnreadNotificationCount($seller_id, 'seller');

// 1. HITUNG TOTAL PENDAPATAN (OMZET) DARI PESANAN YANG STATUSNYA 'completed'
$query_income = mysqli_query($db, "SELECT SUM(total_amount) AS total_pendapatan FROM orders WHERE seller_id = '$seller_id' AND status = 'completed'");
$data_income = mysqli_fetch_assoc($query_income);
$total_pendapatan = $data_income['total_pendapatan'] ? $data_income['total_pendapatan'] : 0;

// 2. AMBIL RIWAYAT PESANAN YANG SUDAH SELESAI / LUNAS (Sudah ditambah o.phone)
$query_history = mysqli_query($db, "SELECT o.id AS order_id, o.total_amount, o.created_at, o.address, o.phone, u.name AS nama_pembeli
                                    FROM orders o
                                    JOIN users u ON o.buyer_id = u.id
                                    WHERE o.seller_id = '$seller_id' AND o.status = 'completed'
                                    ORDER BY o.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Penjualan - BookStore</title>
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
    </style>
</head>
<body class="bg-paper flex min-h-screen font-sans text-ink">

    <div class="bg-ink text-paper w-64 min-h-screen p-5 space-y-6 shrink-0 border-r-4 border-brass">
        <h2 class="text-xl font-serif font-bold flex items-center gap-2"><span class="text-brass-light">📚</span> Seller Panel</h2>
        <nav class="space-y-2 flex flex-col text-sm">
            <a href="index.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Dashboard</a>
            <a href="manage_products.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Produk Buku</a>
            <a href="validasi_penjual.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Validasi Pesanan</a>
            <a href="sales_history.php" class="bg-maroon px-4 py-2.5 rounded-sm font-semibold">Riwayat Penjualan</a>
            <a href="read_notification.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition flex items-center justify-between">
                <span>Notifikasi</span>
                <?php if ($unread_notifications > 0): ?>
                    <span class="bg-maroon text-paper text-xs font-bold px-2 py-0.5 rounded-full">
                        <?= $unread_notifications > 9 ? '9+' : $unread_notifications; ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="../auth/logout.php" class="text-brass-light/80 hover:bg-ink-light px-4 py-2.5 rounded-sm mt-10 transition">Logout</a>
        </nav>
    </div>

    <div class="flex-1 p-10">
        <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Laporan Toko</p>
                <h1 class="text-3xl font-serif font-bold text-ink">📊 Laporan & Riwayat Penjualan</h1>
            </div>
            <span class="bg-brass-light/25 text-brass border border-brass/40 text-xs px-3 py-1.5 rounded-full font-bold uppercase tracking-wide">Toko: Buku Bekasi</span>
        </div>

        <div class="bg-ink p-6 rounded-sm shadow-lg text-paper mb-8 max-w-md border-l-4 border-brass-light">
            <p class="text-sm font-semibold uppercase tracking-widest text-brass-light/80">Total Pendapatan Bersih</p>
            <p class="text-4xl font-serif font-black mt-2">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></p>
            <p class="text-xs mt-3 text-paper/50">*Dihitung dari semua pesanan yang status pembayarannya telah Anda validasi lunas.</p>
        </div>

        <h2 class="text-xl font-serif font-bold text-ink mb-4">📜 Daftar Transaksi Selesai</h2>
        <div class="bg-card rounded-sm shadow-md border border-ink/5 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-paper border-b border-ink/10 text-ink/50 text-xs font-bold uppercase tracking-widest">
                        <th class="p-4">ID Transaksi</th>
                        <th class="p-4">Tanggal Lunas</th>
                        <th class="p-4">Nama Pembeli</th>
                        <th class="p-4">No. Telepon</th>
                        <th class="p-4">Alamat Pengiriman</th>
                        <th class="p-4">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dashed divide-ink/10 text-sm text-ink/80">
                    <?php if (mysqli_num_rows($query_history) == 0) : ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-ink/40 italic font-serif">Belum ada transaksi yang selesai/lunas.</td>
                        </tr>
                    <?php endif; ?>

                    <?php while ($row = mysqli_fetch_assoc($query_history)) : ?>
                        <tr class="hover:bg-paper/60 transition">
                            <td class="p-4 font-bold text-ink">#<?= $row['order_id']; ?></td>
                            <td class="p-4 text-xs text-ink/50"><?= date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                            <td class="p-4 font-medium text-ink"><?= htmlspecialchars($row['nama_pembeli']); ?></td>
                            <td class="p-4 font-mono text-ink/60"><?= htmlspecialchars($row['phone']); ?></td>
                            
                            <td class="p-4 max-w-xs whitespace-normal break-words text-xs text-ink/60 leading-relaxed">
                                📍 <?= htmlspecialchars($row['address']); ?>
                            </td>
                            
                            <td class="p-4 font-serif font-bold text-sage">Rp <?= number_format($row['total_amount'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>


</body>
</html>