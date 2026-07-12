<?php
session_start();
include '../config/database.php';
require_once '../config/notification_helper.php';

// Proteksi: Hanya boleh diakses oleh Seller
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil ID Seller yang sedang login dari session
$seller_id = $_SESSION['user_id'];
$_SESSION['user_type'] = 'seller';

// Get unread notification count
$unread_notifications = getUnreadNotificationCount($seller_id, 'seller');

// 1. Hitung Total Buku yang Dijual KHUSUS milik toko ini saja
$query_produk = mysqli_query($db, "SELECT COUNT(*) AS total_buku FROM products WHERE seller_id = '$seller_id'");
$data_produk = mysqli_fetch_assoc($query_produk);
$total_buku_dijual = $data_produk['total_buku'];

// 2. PERBAIKAN: Hitung Pesanan Perlu Validasi menggunakan status 'paid' (sesuai ENUM database alur baru)
$query_validasi = mysqli_query($db, "SELECT COUNT(*) AS perlu_validasi FROM orders WHERE seller_id = '$seller_id' AND status = 'paid'");
$data_validasi = mysqli_fetch_assoc($query_validasi);
$pesanan_perlu_validasi = $data_validasi['perlu_validasi'];

// 3. FIX SINKRONISASI: Menghitung total buku terjual dari detail item order yang statusnya sudah 'completed'
$query_terjual = mysqli_query($db, "SELECT SUM(oi.quantity) AS total_terjual
                                    FROM order_items oi
                                    JOIN orders o ON oi.order_id = o.id
                                    WHERE o.seller_id = '$seller_id' AND o.status = 'completed'");
$data_terjual = mysqli_fetch_assoc($query_terjual);
$total_buku_terjual = $data_terjual['total_terjual'] ? $data_terjual['total_terjual'] : 0;

// 4. TAMBAHAN: Hitung Total Pendapatan (Omzet) dari pesanan yang statusnya 'completed'
$query_income = mysqli_query($db, "SELECT SUM(total_amount) AS total_pendapatan FROM orders WHERE seller_id = '$seller_id' AND status = 'completed'");
$data_income = mysqli_fetch_assoc($query_income);
$total_pendapatan = $data_income['total_pendapatan'] ? $data_income['total_pendapatan'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - BookStore</title>
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
        .stat-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .stat-card:hover { transform: translateY(-4px); }
    </style>
</head>
<body class="bg-paper flex min-h-screen font-sans text-ink">

    <div class="bg-ink text-paper w-64 min-h-screen p-5 space-y-6 shrink-0 border-r-4 border-brass">
        <h2 class="text-xl font-serif font-bold flex items-center gap-2"><span class="text-brass-light">📚</span> Seller Panel</h2>
        <nav class="space-y-2 flex flex-col text-sm">
            <a href="index.php" class="bg-maroon px-4 py-2.5 rounded-sm font-semibold">Dashboard</a>
            <a href="manage_products.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Produk Buku</a>
            <a href="validasi_penjual.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Validasi Pesanan</a>
            <a href="sales_history.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Riwayat Penjualan</a>
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
        <div class="flex justify-between items-center mb-8 flex-wrap gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Ringkasan Toko</p>
                <h1 class="text-3xl font-serif font-bold text-ink">Selamat Datang, <?= htmlspecialchars($_SESSION['name']); ?>!</h1>
            </div>
            <span class="bg-sage/15 text-sage border border-sage/40 text-xs px-3 py-1.5 rounded-full font-bold uppercase tracking-wide">Role: Penjual</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            <a href="manage_products.php" class="stat-card block bg-card p-6 rounded-sm shadow-md border-l-4 border-brass border border-ink/5 cursor-pointer">
                <p class="text-xs font-bold text-ink/50 uppercase tracking-widest">Total Buku Dijual</p>
                <p class="text-4xl font-serif font-bold text-ink mt-2"><?= $total_buku_dijual; ?></p>
                <p class="text-xs text-brass font-semibold mt-3">Kelola Buku →</p>
            </a>

            <a href="validasi_penjual.php" class="stat-card block bg-card p-6 rounded-sm shadow-md border-l-4 border-maroon border border-ink/5 cursor-pointer">
                <p class="text-xs font-bold text-ink/50 uppercase tracking-widest">Pesanan Perlu Validasi</p>
                <p class="text-4xl font-serif font-bold text-ink mt-2"><?= $pesanan_perlu_validasi; ?></p>
                <p class="text-xs text-maroon font-semibold mt-3">Validasi Pembayaran →</p>
            </a>

            <a href="sales_history.php" class="stat-card block bg-card p-6 rounded-sm shadow-md border-l-4 border-sage border border-ink/5 cursor-pointer">
                <p class="text-xs font-bold text-ink/50 uppercase tracking-widest">Total Buku Terjual</p>
                <p class="text-4xl font-serif font-bold text-ink mt-2"><?= $total_buku_terjual; ?></p>
                <p class="text-xs text-sage font-semibold mt-3">Riwayat Penjualan →</p>
            </a>

            <a href="sales_history.php" class="stat-card block bg-ink p-6 rounded-sm shadow-md border-l-4 border-brass-light cursor-pointer">
                <p class="text-xs font-bold text-brass-light/80 uppercase tracking-widest">Total Pendapatan</p>
                <p class="text-3xl font-serif font-black text-paper mt-3">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></p>
                <p class="text-xs text-brass-light font-semibold mt-3">Riwayat Penjualan →</p>
            </a>

        </div>
    </div>

</body>

</html>
