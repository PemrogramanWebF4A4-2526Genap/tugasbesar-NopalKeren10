<?php
session_start();
include '../config/database.php';

// Proteksi Session Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 1. Ambil data total pendapatan
$query_income = mysqli_query($db, "SELECT SUM(total_amount) as total FROM orders WHERE status = 'success' OR status = 'selesai' OR status = 'completed'");
$income = mysqli_fetch_assoc($query_income);
$total_income = $income['total'] ?? 0;

// 2. Ambil data total buku terjual dari order_items
$query_books_sold = mysqli_query($db, "SELECT SUM(quantity) as total FROM order_items");
$books_sold = mysqli_fetch_assoc($query_books_sold);
$total_books_sold = $books_sold['total'] ?? 0;

// 3. Ambil data total transaksi keseluruhan
$query_total_orders = mysqli_query($db, "SELECT COUNT(*) as total FROM orders");
$total_orders = mysqli_fetch_assoc($query_total_orders);
$orders_count = $total_orders['total'] ?? 0;

// 4. Ambil list riwayat transaksi (JOIN menggunakan buyer_id)
$orders_list = mysqli_query($db, "SELECT orders.*, users.name as buyer_name 
                                  FROM orders 
                                  JOIN users ON orders.buyer_id = users.id 
                                  ORDER BY orders.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - BookStore</title>
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
        @media print {
            .no-print { display: none !important; }
            .print-area { width: 100% !important; margin: 0 !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-paper flex min-h-screen font-sans text-ink">

    <div class="no-print bg-ink text-paper w-64 min-h-screen p-5 space-y-6 shrink-0 border-r-4 border-brass">
        <h2 class="text-xl font-serif font-bold flex items-center gap-2"><span class="text-brass-light">📚</span> Admin Panel</h2>
        <nav class="space-y-2 flex flex-col text-sm">
            <a href="index.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Dashboard</a>
            <a href="manage_users.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Pengguna</a>
            <a href="verify_sellers.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Verifikasi Penjual</a>
            <a href="manage_categories.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Kategori</a>
            <a href="report.php" class="bg-maroon px-4 py-2.5 rounded-sm font-semibold transition">Laporan Penjualan</a>
            <a href="../auth/logout.php" class="text-brass-light/80 hover:bg-ink-light px-4 py-2.5 rounded-sm mt-10 transition">Logout</a>
        </nav>
    </div>

    <div class="print-area flex-1 p-10">
        <div class="flex justify-between items-center border-b border-ink/10 pb-4 mb-8">
            <div>
                <p class="eyebrow text-xs font-bold uppercase tracking-widest text-maroon mb-1">Laporan Penjualan</p>
                <h1 class="text-3xl font-serif font-bold text-ink">Laporan Penjualan Toko</h1>
                <p class="text-sm text-ink/60 mt-1">Dicetak pada: <?php echo date('d F Y, H:i'); ?> WIB</p>
            </div>
            <button onclick="window.print()" class="no-print bg-sage hover:bg-sage/80 text-paper px-4 py-2 rounded-sm font-semibold shadow transition">
                📂 Cetak Laporan (PDF)
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-card p-6 rounded-sm shadow-md border-l-4 border-emerald-500 border border-ink/5">
                <p class="text-xs font-bold text-ink/50 uppercase tracking-wider">Total Pendapatan</p>
                <p class="text-2xl font-serif font-black text-ink mt-2">Rp <?php echo number_format($total_income, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-card p-6 rounded-sm shadow-md border-l-4 border-blue-500 border border-ink/5">
                <p class="text-xs font-bold text-ink/50 uppercase tracking-wider">Buku Terjual</p>
                <p class="text-2xl font-serif font-black text-ink mt-2"><?php echo $total_books_sold; ?> <span class="text-xs text-ink/40 font-normal">Pcs</span></p>
            </div>
            <div class="bg-card p-6 rounded-sm shadow-md border-l-4 border-purple-500 border border-ink/5">
                <p class="text-xs font-bold text-ink/50 uppercase tracking-wider">Total Transaksi</p>
                <p class="text-2xl font-serif font-black text-ink mt-2"><?php echo $orders_count; ?> <span class="text-xs text-ink/40 font-normal">Pesanan</span></p>
            </div>
        </div>

        <div class="bg-card rounded-sm shadow-md border border-ink/5 overflow-hidden">
            <div class="p-5 border-b bg-paper">
                <h3 class="font-serif font-bold text-ink text-lg">Rincian Riwayat Transaksi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-paper text-ink/50 font-bold uppercase text-xs tracking-wider border-b border-ink/10">
                            <th class="p-4">No</th>
                            <th class="p-4">ID Pesanan</th>
                            <th class="p-4">Nama Pembeli</th>
                            <th class="p-4">Tanggal</th>
                            <th class="p-4">Total Bayar</th>
                            <th class="p-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dashed divide-ink/10 text-sm text-ink/70">
                        <?php 
                        $no = 1;
                        if ($orders_list && mysqli_num_rows($orders_list) > 0) :
                            while ($order = mysqli_fetch_assoc($orders_list)) : 
                        ?>
                            <tr class="hover:bg-paper/60 transition">
                                <td class="p-4 font-medium"><?php echo $no++; ?></td>
                                <td class="p-4 font-mono text-brass">#TRX-<?php echo $order['id']; ?></td>
                                <td class="p-4 font-serif font-semibold text-ink"><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                <td class="p-4"><?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></td>
                                <td class="p-4 font-serif font-bold text-sage">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                <td class="p-4 text-center">
                                    <?php if ($order['status'] === 'success' || $order['status'] === 'selesai' || $order['status'] === 'completed') : ?>
                                        <span class="px-2.5 py-1 text-xs font-semibold bg-sage/10 text-sage rounded-full">Selesai</span>
                                    <?php else : ?>
                                        <span class="px-2.5 py-1 text-xs font-semibold bg-brass/10 text-brass rounded-full"><?php echo htmlspecialchars($order['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else : 
                        ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-ink/40 font-serif">Belum ada riwayat transaksi masuk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>