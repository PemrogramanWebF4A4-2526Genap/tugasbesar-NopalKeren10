<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID Pesanan tidak ditemukan!'); window.location='track_order.php';</script>";
    exit;
}

$order_id = mysqli_real_escape_string($db, $_GET['id']);

// 1. Ambil data Order, Nama Pembeli
$query_order = mysqli_query($db, "SELECT o.*, u.name AS nama_pembeli, u.email AS email_pembeli 
                                  FROM orders o 
                                  JOIN users u ON o.buyer_id = u.id 
                                  WHERE o.id = '$order_id' AND o.buyer_id = '$buyer_id'");
$order = mysqli_fetch_assoc($query_order);

if (!$order) {
    echo "<script>alert('Invoice tidak ditemukan atau Anda tidak memiliki akses!'); window.location='track_order.php';</script>";
    exit;
}

// 2. Ambil daftar item yang dibeli
$query_items = mysqli_query($db, "SELECT oi.*, p.name AS nama_buku, u.name AS nama_toko 
                                  FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.id 
                                  JOIN users u ON p.seller_id = u.id 
                                  WHERE oi.order_id = '$order_id'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $order['id']; ?> - BookStore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@400;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#161F2B',
                        paper: '#F5EFE2',
                        card: '#FFFDF7',
                        brass: '#B4823A',
                        'brass-light': '#DDBD82',
                        maroon: '#7B2A32',
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
        body { background-color: #F5EFE2; }
        @media print {
            .no-print { display: none !important; }
            body { background-color: #ffffff; }
            .invoice-box { border: none !important; shadow: none !important; }
        }
    </style>
</head>
<body class="bg-paper font-sans text-ink min-h-screen flex flex-col items-center">

    <nav class="no-print w-full bg-ink text-paper p-4 shadow-lg border-b-4 border-brass">
        <div class="container mx-auto flex justify-between items-center">
            <a href="track_order.php" class="text-2xl font-serif font-bold tracking-wide flex items-center gap-2">
                <span class="text-brass-light">📚</span> BookStore
            </a>
            <div class="flex items-center gap-3">
                <a href="track_order.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition text-sm">
                    ← Kembali ke Pesanan Saya
                </a>
                <button onclick="window.print()" class="bg-brass hover:bg-brass-light text-ink px-4 py-2 rounded-sm font-bold transition text-sm uppercase tracking-wide shadow">
                    🖨️ Cetak / Simpan PDF
                </button>
            </div>
        </div>
    </nav>

    <main class="w-full max-w-3xl p-4 md:p-10">

    <div class="invoice-box bg-card w-full rounded-sm shadow-md border border-ink/10 p-6 md:p-10">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b-2 border-dashed border-ink/10 pb-6 mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-serif font-black tracking-wide text-ink flex items-center gap-2">
                    <span class="text-brass">📚</span> BookStore
                </h1>
                <p class="text-xs text-ink/50 mt-1 font-serif">Koleksi Terkurasi, Rak Digital Semua Cerita</p>
            </div>
            <div class="sm:text-right">
                <h2 class="text-xl font-serif font-bold text-maroon uppercase tracking-wider">Invoice Pembelian</h2>
                <p class="text-sm font-mono mt-0.5 font-bold">#INV-ORD-<?= $order['id']; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm mb-8">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-brass mb-1">Ditagihkan Kepada:</p>
                <p class="font-bold text-base"><?= htmlspecialchars($order['nama_pembeli']); ?></p>
                <p class="text-ink/70 mt-0.5"><?= htmlspecialchars($order['email_pembeli']); ?></p>
            </div>
            <div class="md:text-right">
                <p class="text-xs font-bold uppercase tracking-wider text-brass mb-1">Rincian Waktu & Status:</p>
                <p class="text-ink/80">Tanggal: <span class="font-semibold"><?= date('d M Y, H:i', strtotime($order['created_at'])); ?> WIB</span></p>
                <p class="text-ink/80 mt-0.5">Metode: <span class="font-semibold uppercase">Transfer Bank</span></p>
                <div class="mt-2 md:float-right">
                    <?php if ($order['status'] === 'selesai') : ?>
                        <span class="bg-sage/10 text-sage font-bold px-3 py-1 text-xs uppercase border border-sage/30 rounded-sm">
                            ✓ Selesai / Lunas
                        </span>
                    <?php else : ?>
                        <span class="bg-brass/10 text-brass font-bold px-3 py-1 text-xs uppercase border border-brass/30 rounded-sm">
                            <?= htmlspecialchars($order['status']); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-paper border-b border-ink/10 text-xs font-bold uppercase tracking-wider text-ink/70">
                        <th class="py-3 px-4">Nama Item Buku</th>
                        <th class="py-3 px-4">Toko Penjual</th>
                        <th class="py-3 px-4 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Harga Satuan</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-ink/5">
                    <?php 
                    $total_kalkulasi = 0; // SOLUSI: Menghilangkan error Undefined array key total_price
                    while($item = mysqli_fetch_assoc($query_items)) : 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total_kalkulasi += $subtotal;
                    ?>
                        <tr>
                            <td class="py-4 px-4 font-serif font-bold text-ink"><?= htmlspecialchars($item['nama_buku']); ?></td>
                            <td class="py-4 px-4 text-xs text-ink/60">🏪 <?= htmlspecialchars($item['nama_toko']); ?></td>
                            <td class="py-4 px-4 text-center font-mono font-semibold"><?= $item['quantity']; ?>x</td>
                            <td class="py-4 px-4 text-right font-mono text-ink/70">Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                            <td class="py-4 px-4 text-right font-mono font-bold text-ink">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col items-end border-t border-ink/10 pt-4">
            <div class="w-full sm:w-64 space-y-2 text-sm font-medium">
                <div class="flex justify-between text-ink/60">
                    <span>Total Belanja:</span>
                    <span class="font-mono">Rp <?= number_format($total_kalkulasi, 0, ',', '.'); ?></span>
                </div>
                
                <?php 
                $ongkir = $order['total_amount'] - $total_kalkulasi; 
                ?>
                <div class="flex justify-between text-ink/60">
                    <span>Biaya Ongkos Kirim:</span>
                    <span class="font-mono">Rp <?= number_format($ongkir, 0, ',', '.'); ?></span>
                </div>
                
                <div class="flex justify-between text-lg font-serif font-bold text-maroon border-t border-dashed border-ink/10 pt-2">
                    <span>Total Bayar:</span>
                    <span class="font-mono">Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center text-[10px] text-ink/40 tracking-wider border-t border-ink/5 pt-4">
            <p>Terima kasih telah berbelanja literasi di BookStore! Ini adalah dokumen sah bukti transaksi digital komputer.</p>
            <p class="font-mono mt-0.5">&copy; 2026 BookStore E-commerce. All Rights Reserved.</p>
        </div>

    </div>

    </main>

</body>
</html>