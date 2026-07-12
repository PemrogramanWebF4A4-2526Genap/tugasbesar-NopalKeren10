<?php
session_start();
include '../config/database.php';

// Proteksi: Hanya boleh diakses oleh Buyer
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit;
}

// Validasi Parameter order_id
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: track_order.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];
$order_id = mysqli_real_escape_string($db, $_GET['order_id']);

// 1. QUERY INFO UTAMA TRANSAKSI
$query_order = mysqli_query($db, "SELECT o.id AS order_id, o.total_amount, o.status AS order_status, 
                                         o.address, o.phone, o.created_at, u.name AS nama_toko, p.proof
                                  FROM orders o
                                  JOIN users u ON o.seller_id = u.id
                                  LEFT JOIN payments p ON o.id = p.order_id
                                  WHERE o.id = '$order_id' AND o.buyer_id = '$buyer_id'");

$data_order = mysqli_fetch_assoc($query_order);

// Jika pesanan tidak ditemukan
if (!$data_order) {
    echo "<script>alert('Pesanan tidak ditemukan atau Anda tidak memiliki akses!'); window.location='track_order.php';</script>";
    exit;
}

// 2. QUERY DAFTAR BUKU
$query_items = mysqli_query($db, "SELECT oi.quantity, oi.price, p.name AS judul_buku, p.image 
                                  FROM order_items oi
                                  JOIN products p ON oi.product_id = p.id
                                  WHERE oi.order_id = '$order_id'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $data_order['order_id']; ?> - BookStore</title>
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
<body class="bg-paper min-h-screen font-sans text-ink">

    <nav class="bg-ink text-paper p-4 shadow-lg border-b-4 border-brass">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-serif font-bold tracking-wide flex items-center gap-2">
                <span class="text-brass-light">📚</span> BookStore
            </a>
            <div class="flex items-center space-x-3">
                <a href="cart.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition flex items-center gap-1 text-sm">
                    🛒 Keranjang
                </a>
                <a href="track_order.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition text-sm block">
                    Pesanan Saya
                </a>
                <a href="../auth/logout.php" class="bg-maroon hover:bg-maroon-light px-4 py-2 rounded-sm font-semibold transition text-sm">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto py-10 px-4 max-w-4xl">

        <div class="bg-card border border-ink/5 p-6 rounded-sm shadow-sm mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Rincian Transaksi</p>
                <h1 class="text-2xl font-serif font-bold text-ink">Detail Pesanan #<?= $data_order['order_id']; ?></h1>
                <p class="text-xs text-ink/40 mt-1">Dibuat pada: <?= date('d M Y, H:i', strtotime($data_order['created_at'])); ?> WIB</p>
                <p class="text-sm font-semibold text-ink/70 mt-2">Toko Penjual: <span class="text-maroon font-bold"><?= $data_order['nama_toko']; ?></span></p>
            </div>
            <div>
                <?php if ($data_order['order_status'] == 'pending') : ?>
                    <span class="bg-brass-light/25 text-brass border border-brass/40 text-xs px-4 py-2 rounded-full font-bold uppercase tracking-wider block text-center">Menunggu Validasi</span>
                <?php else : ?>
                    <span class="bg-sage/15 text-sage border border-sage/40 text-xs px-4 py-2 rounded-full font-bold uppercase tracking-wider block text-center">Selesai / Terverifikasi</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="md:col-span-2 space-y-6">
                
                <div class="bg-card border border-ink/5 p-5 rounded-sm shadow-sm">
                    <h2 class="text-xs font-bold text-brass uppercase tracking-widest border-b border-ink/10 pb-2 mb-3">📍 Alamat Tujuan Pengiriman</h2>
                    <p class="text-sm text-ink font-semibold mb-2">📞 Nomor Telepon / HP:</p>
                    <p class="text-base text-ink font-mono bg-paper p-2 rounded-sm border border-ink/10 mb-4"><?= htmlspecialchars($data_order['phone']); ?></p>
                    
                    <p class="text-sm text-ink font-semibold mb-2">🏠 Alamat Lengkap Rumah:</p>
                    <div class="text-sm text-ink/70 bg-paper p-3 rounded-sm border border-ink/10 leading-relaxed">
                        <?= nl2br(htmlspecialchars($data_order['address'])); ?>
                    </div>
                </div>

                <div class="bg-card border border-ink/5 p-5 rounded-sm shadow-sm">
                    <h2 class="text-xs font-bold text-brass uppercase tracking-widest border-b border-ink/10 pb-2 mb-3">📚 Daftar Buku Yang Dibeli</h2>
                    <div class="divide-y divide-dashed divide-ink/10">
                        <?php while ($item = mysqli_fetch_assoc($query_items)) : ?>
                            <div class="flex items-center py-3 first:pt-0 last:pb-0">
                                <img src="../assets/uploads/<?= !empty($item['image']) ? $item['image'] : 'default_book.png'; ?>" 
                                     alt="Cover" class="w-14 h-20 object-cover rounded-sm border border-ink/10 shadow-sm mr-4">
                                <div class="flex-1">
                                    <h3 class="font-serif font-bold text-ink text-sm leading-snug"><?= $item['judul_buku']; ?></h3>
                                    <p class="text-xs text-ink/50 mt-1"><?= $item['quantity']; ?> x Rp <?= number_format($item['price'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-ink text-sm">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <?php
                    // Calculate subtotal from items
                    $subtotal_items = 0;
                    mysqli_data_seek($query_items, 0); // Reset pointer
                    while ($item = mysqli_fetch_assoc($query_items)) {
                        $subtotal_items += ($item['price'] * $item['quantity']);
                    }
                    $shipping_cost = $data_order['total_amount'] - $subtotal_items;
                    ?>

                    <div class="border-t border-ink/10 mt-4 pt-4 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-ink/70">Subtotal Buku:</span>
                            <span class="font-semibold text-ink">Rp <?= number_format($subtotal_items, 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-ink/70">Ongkos Kirim:</span>
                            <span class="font-semibold text-ink">Rp <?= number_format($shipping_cost, 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-ink/20">
                            <span class="text-sm font-bold text-ink/50">Total Pembayaran:</span>
                            <span class="text-2xl font-serif font-black text-maroon">Rp <?= number_format($data_order['total_amount'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="space-y-6">
                <div class="bg-card border border-ink/5 p-5 rounded-sm shadow-sm">
                    <h2 class="text-xs font-bold text-brass uppercase tracking-widest border-b border-ink/10 pb-2 mb-3">🖼️ Bukti Pembayaran</h2>
                    
                    <?php if (!empty($data_order['proof'])) : ?>
                        <p class="text-xs text-sage font-semibold mb-3">✓ Bukti transfer telah tersimpan di sistem</p>
                        
                        <div class="border border-ink/10 rounded-sm overflow-hidden bg-paper p-2 mb-3 shadow-inner">
                            <img src="../assets/uploads/<?= $data_order['proof']; ?>" alt="Bukti Transfer" class="w-full max-h-64 object-contain rounded-sm">
                        </div>

                        <a href="../assets/uploads/<?= $data_order['proof']; ?>" target="_blank" 
                           class="block text-center bg-paper text-ink border border-ink/15 hover:bg-ink hover:text-brass-light px-4 py-2.5 rounded-sm text-xs font-bold transition uppercase tracking-wide">
                            🔍 Buka Gambar Penuh
                        </a>
                    <?php else : ?>
                        <div class="text-center py-6">
                            <p class="text-sm text-maroon italic">Belum ada bukti transfer yang diunggah untuk pesanan ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </main>

    <?php include '../components/buyer_footer.php'; ?>

</body>
</html>