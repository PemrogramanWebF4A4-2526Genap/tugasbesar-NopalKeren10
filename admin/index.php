<?php
session_start();
include '../config/database.php';

// Proteksi Session Utama
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 1. Hitung Total Pengguna berdasarkan Role
$query_users = mysqli_query($db, "SELECT 
    SUM(CASE WHEN role = 'buyer' THEN 1 ELSE 0 END) AS total_buyer,
    SUM(CASE WHEN role = 'seller' AND is_verified = 1 THEN 1 ELSE 0 END) AS total_seller
    FROM users");
$data_users = mysqli_fetch_assoc($query_users);
$total_buyer = $data_users['total_buyer'] ?? 0;
$total_seller = $data_users['total_seller'] ?? 0;

// 2. Hitung Total Semua Buku yang Terdaftar di Platform
$query_books = mysqli_query($db, "SELECT COUNT(*) AS total_buku FROM products");
$data_books = mysqli_fetch_assoc($query_books);
$total_buku_platform = $data_books['total_buku'] ?? 0;

// 3. Hitung Total Seluruh Transaksi Berhasil (Omzet Global Platform)
$query_sales = mysqli_query($db, "SELECT SUM(total_amount) AS omzet_total, COUNT(*) AS total_transaksi FROM orders WHERE status = 'completed'");
$data_sales = mysqli_fetch_assoc($query_sales);
$omzet_total = $data_sales['omzet_total'] ?? 0;
$total_transaksi = $data_sales['total_transaksi'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - BookStore</title>
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
        <h2 class="text-xl font-serif font-bold flex items-center gap-2"><span class="text-brass-light">📚</span> Admin Panel</h2>
        <nav class="space-y-2 flex flex-col text-sm">
            <a href="index.php" class="bg-maroon px-4 py-2.5 rounded-sm font-semibold">Dashboard</a>
            <a href="manage_users.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Pengguna</a>
            <a href="verify_sellers.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Verifikasi Penjual</a>
            <a href="manage_categories.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Kategori</a>
            <a href="report.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Laporan Penjualan</a>
            <a href="../auth/logout.php" class="text-brass-light/80 hover:bg-ink-light px-4 py-2.5 rounded-sm mt-10 transition">Logout</a>
        </nav>
    </div>

    <div class="flex-1 p-10">
        <div class="flex justify-between items-center mb-8">
            <div>
                <p class="eyebrow text-xs font-bold uppercase tracking-widest text-maroon mb-1">Ringkasan Platform</p>
                <h1 class="text-3xl font-serif font-bold text-ink">Selamat Datang, Admin!</h1>
            </div>
            <span class="bg-sage/15 text-sage border border-sage/40 text-xs px-3 py-1.5 rounded-full font-bold uppercase tracking-wide">Role: Admin</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            
            <a href="manage_users.php" class="stat-card block bg-card p-6 rounded-sm shadow-md border-l-4 border-blue-500 border border-ink/5 cursor-pointer">
                <p class="text-xs font-bold text-ink/50 uppercase tracking-widest">Total Pembeli (Buyer)</p>
                <p class="text-4xl font-serif font-bold text-ink mt-2"><?= $total_buyer; ?> <span class="text-xs text-ink/40 font-normal">User</span></p>
                <p class="text-xs text-blue-500 font-semibold mt-3">Kelola Pengguna →</p>
            </a>

            <a href="verify_sellers.php" class="stat-card block bg-card p-6 rounded-sm shadow-md border-l-4 border-purple-500 border border-ink/5 cursor-pointer">
                <p class="text-xs font-bold text-ink/50 uppercase tracking-widest">Total Penjual (Seller)</p>
                <p class="text-4xl font-serif font-bold text-ink mt-2"><?= $total_seller; ?> <span class="text-xs text-ink/40 font-normal">Toko</span></p>
                <p class="text-xs text-purple-500 font-semibold mt-3">Verifikasi Penjual →</p>
            </a>

            <a href="manage_categories.php" class="stat-card block bg-card p-6 rounded-sm shadow-md border-l-4 border-brass border border-ink/5 cursor-pointer">
                <p class="text-xs font-bold text-ink/50 uppercase tracking-widest">Buku Terdaftar</p>
                <p class="text-4xl font-serif font-bold text-ink mt-2"><?= $total_buku_platform; ?> <span class="text-xs text-ink/40 font-normal">Judul</span></p>
                <p class="text-xs text-brass font-semibold mt-3">Kelola Kategori →</p>
            </a>

            <a href="report.php" class="stat-card block bg-ink p-6 rounded-sm shadow-md border-l-4 border-brass-light cursor-pointer">
                <p class="text-xs font-bold text-brass-light/80 uppercase tracking-widest">Total Perputaran Uang</p>
                <p class="text-3xl font-serif font-black text-paper mt-3">Rp <?= number_format($omzet_total, 0, ',', '.'); ?></p>
                <p class="text-xs text-brass-light font-semibold mt-3">Laporan Penjualan →</p>
            </a>

        </div>

        <div class="mt-10 bg-card border border-ink/5 rounded-sm p-6">
            <h3 class="font-serif font-bold text-ink text-lg mb-2">💡 Kendali Sistem Super Admin:</h3>
            <ul class="list-disc list-inside text-sm text-ink/70 space-y-1.5">
                <li>Gunakan menu <b>Kelola Pengguna</b> jika ingin menertibkan akun pembeli atau penjual bermasalah.</li>
                <li>Gunakan menu <b>Kelola Kategori</b> untuk merancang kategori master rak buku utama.</li>
                <li>Pantau grafik pertumbuhan profit dan data transaksi melalui menu <b>Laporan Penjualan</b>.</li>
            </ul>
        </div>
    </div>

</body>
</html>