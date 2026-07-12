<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
$_SESSION['user_type'] = 'seller';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($page_title) ? $page_title : 'Seller Panel - BookStore'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex min-h-screen">

    <div class="bg-gray-800 text-white w-64 min-h-screen p-5 space-y-6 shrink-0 flex flex-col justify-between">
        <div>
            <h2 class="text-2xl font-bold mb-6 tracking-wide">Seller Panel</h2>
            <nav class="space-y-3 flex flex-col">
                <a href="index.php" class="hover:bg-gray-700 px-4 py-2 rounded transition">Dashboard</a>
                <a href="manage_products.php" class="hover:bg-gray-700 px-4 py-2 rounded transition">Kelola Produk Buku</a>
                <a href="validasi_penjual.php" class="hover:bg-gray-700 px-4 py-2 rounded transition">Validasi Pembayaran</a>
                <a href="sales_history.php" class="hover:bg-gray-700 px-4 py-2 rounded transition">Riwayat Penjualan</a>
            </nav>
        </div>
        
        <div>
            <?php require_once __DIR__ . '/notification_dropdown.php'; ?>
            <a href="../auth/logout.php" class="text-red-400 hover:bg-gray-700 px-4 py-2 rounded block text-sm font-medium transition">
                🚪 Logout
            </a>
        </div>
    </div>

    <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
        <main class="flex-1 p-10 w-full max-w-7xl mx-auto"></main>