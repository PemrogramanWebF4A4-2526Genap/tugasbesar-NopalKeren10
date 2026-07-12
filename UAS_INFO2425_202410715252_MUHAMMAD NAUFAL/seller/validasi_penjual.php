<?php
session_start();
include '../config/database.php';
require_once '../config/notification_helper.php';

// 1. Proteksi Halaman: Hanya boleh diakses oleh Seller
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$_SESSION['user_type'] = 'seller';

// Get unread notification count
$unread_notifications = getUnreadNotificationCount($seller_id, 'seller');

// --- 2. PROSES AKSI: PROSES BARANG KE KIRIM ---
if (isset($_GET['action']) && $_GET['action'] == 'ship' && isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($db, $_GET['order_id']);
    
    // Get buyer_id before updating
    $query_buyer = mysqli_query($db, "SELECT buyer_id FROM orders WHERE id = '$order_id' AND seller_id = '$seller_id'");
    $order_data = mysqli_fetch_assoc($query_buyer);
    $buyer_id = $order_data['buyer_id'];
    
    // Update status menjadi 'shipped' (sesuai ENUM database)
    $update_status = mysqli_query($db, "UPDATE orders SET status = 'shipped' WHERE id = '$order_id' AND seller_id = '$seller_id'");
    
    if ($update_status) {
        // Create notification for buyer
        createNotification(
            $buyer_id, 
            'buyer', 
            'Pesanan Sedang Dikirim', 
            'Pesanan #' . $order_id . ' Anda telah dikirim oleh penjual. Silakan tunggu paket sampai.', 
            'order_status'
        );
        
        $_SESSION['toast'] = [
            'type' => 'success', 
            'title' => 'Pesanan Dikirim',
            'message' => 'Pesanan #' . $order_id . ' telah diubah menjadi Sedang Dikirim.'
        ];
    }
    header("Location: validasi_penjual.php");
    exit;
}

// --- PROSES AKSI: SELESAIKAN PESANAN ---
if (isset($_GET['action']) && $_GET['action'] == 'complete' && isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($db, $_GET['order_id']);
    
    // Get buyer_id before updating
    $query_buyer = mysqli_query($db, "SELECT buyer_id FROM orders WHERE id = '$order_id' AND seller_id = '$seller_id'");
    $order_data = mysqli_fetch_assoc($query_buyer);
    $buyer_id = $order_data['buyer_id'];
    
    // Update status menjadi 'completed' (sesuai ENUM database)
    $update_status = mysqli_query($db, "UPDATE orders SET status = 'completed' WHERE id = '$order_id' AND seller_id = '$seller_id'");
    
    if ($update_status) {
        // Create notification for buyer
        createNotification(
            $buyer_id, 
            'buyer', 
            'Pesanan Selesai', 
            'Pesanan #' . $order_id . ' Anda telah selesai. Terima kasih telah berbelanja di toko kami!', 
            'order_status'
        );
        
        $_SESSION['toast'] = [
            'type' => 'success', 
            'title' => 'Pesanan Selesai',
            'message' => 'Pesanan #' . $order_id . ' berhasil diselesaikan.'
        ];
    }
    header("Location: validasi_penjual.php");
    exit;
}

// --- 3. QUERY AMBIL DAFTAR PESANAN YANG MASUK ---
$query_orders = mysqli_query($db, "SELECT o.id AS order_id, o.total_amount, o.status, o.created_at, o.address, o.phone, 
                                          u.name AS nama_pembeli, p.proof
                                   FROM orders o
                                   JOIN users u ON o.buyer_id = u.id
                                   LEFT JOIN payments p ON o.id = p.order_id
                                   WHERE o.seller_id = '$seller_id'
                                   ORDER BY o.id DESC");

$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Pesanan - BookStore</title>
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
        #toast-notif {
            transform: translateX(24px) translateY(-8px);
            opacity: 0;
            transition: transform 0.35s ease, opacity 0.35s ease;
        }
        #toast-notif.show {
            transform: translateX(0) translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body class="bg-paper flex min-h-screen font-sans text-ink">

    <div class="bg-ink text-paper w-64 min-h-screen p-5 space-y-6 shrink-0 border-r-4 border-brass">
        <h2 class="text-xl font-serif font-bold flex items-center gap-2"><span class="text-brass-light">📚</span> Seller Panel</h2>
        <nav class="space-y-2 flex flex-col text-sm">
            <a href="index.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Dashboard</a>
            <a href="manage_products.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Produk Buku</a>
            <a href="validasi_penjual.php" class="bg-maroon px-4 py-2.5 rounded-sm font-semibold">Validasi Pesanan</a>
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

    <div class="flex-1 p-10 overflow-x-auto">
        
        <?php if ($toast) : ?>
        <div id="toast-notif" class="fixed top-5 right-5 z-50 bg-card w-full max-w-sm rounded-sm shadow-xl border border-ink/10 overflow-hidden show">
            <div class="p-5 flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-sage/10 border border-sage/30 flex items-center justify-center text-sage text-lg shrink-0">✓</div>
                <div class="flex-1">
                    <p class="font-serif font-bold text-ink text-sm mb-0.5"><?= htmlspecialchars($toast['title']); ?></p>
                    <p class="text-ink/60 text-xs leading-relaxed"><?= htmlspecialchars($toast['message']); ?></p>
                </div>
                <button onclick="closeToast()" class="text-ink/30 hover:text-ink text-xs shrink-0">✕</button>
            </div>
        </div>
        <?php endif; ?>

        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Operasional Toko</p>
            <h1 class="text-3xl font-serif font-bold text-ink">Manajemen Status & Pesanan Masuk</h1>
        </div>

        <div class="bg-card p-6 rounded-sm shadow-md border border-ink/5 overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-ink/10 text-left text-xs uppercase font-bold tracking-wider bg-paper">
                        <th class="p-3">ID Order</th>
                        <th class="p-3">Pelanggan</th>
                        <th class="p-3">Total Transaksi</th>
                        <th class="p-3">Bukti Pembayaran</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-center">Aksi Operasional</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10">
                    <?php if (mysqli_num_rows($query_orders) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($query_orders)) : 
                            $status_db = isset($row['status']) ? strtolower(trim($row['status'])) : '';
                        ?>
                        <tr class="hover:bg-paper/40 transition">
                            <td class="p-3 font-mono font-bold">#<?= $row['order_id']; ?></td>
                            <td class="p-3 text-sm">
                                <p class="font-bold"><?= htmlspecialchars($row['nama_pembeli']); ?></p>
                                <p class="text-xs text-ink/50"><?= htmlspecialchars($row['address']); ?> (<?= htmlspecialchars($row['phone']); ?>)</p>
                            </td>
                            <td class="p-3 text-sm font-semibold">Rp <?= number_format($row['total_amount'], 0, ',', '.'); ?></td>
                            <td class="p-3">
                                <?php if($row['proof'] && $row['proof'] != 'no_image.png') : ?>
                                    <a href="../assets/uploads/<?= $row['proof']; ?>" target="_blank" class="text-xs text-brass font-bold underline hover:text-maroon">👁️ Lihat Bukti</a>
                                <?php else : ?>
                                    <span class="text-xs italic text-ink/40">Belum ada bukti</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="p-3 text-center">
                                <?php if ($status_db == 'pending' || $status_db == 'menunggu pembayaran') : ?>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full uppercase bg-yellow-100 text-yellow-800 border border-yellow-300">
                                        Menunggu Pembayaran
                                    </span>
                                <?php elseif ($status_db == 'paid' || $status_db == 'diproses') : ?>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full uppercase bg-blue-100 text-blue-800 border border-blue-300">
                                        Diproses
                                    </span>
                                <?php elseif ($status_db == 'shipped' || $status_db == 'sedang dikirim') : ?>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full uppercase bg-orange-100 text-orange-800 border border-orange-300">
                                        Sedang Dikirim
                                    </span>
                                <?php elseif ($status_db == 'completed' || $status_db == 'selesai') : ?>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full uppercase bg-green-100 text-green-800 border border-green-300">
                                        Selesai
                                    </span>
                                <?php else : ?>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full uppercase bg-gray-100 text-gray-800 border border-gray-300">
                                        Kosong
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="p-3 text-center">
                                <?php if ($status_db == 'paid' || $status_db == 'diproses') : ?>
                                    <a href="validasi_penjual.php?action=ship&order_id=<?= $row['order_id']; ?>" class="bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs px-3 py-1.5 rounded transition shadow-sm">
                                        🚚 Kirim Barang
                                    </a>
                                <?php elseif ($status_db == 'shipped' || $status_db == 'sedang dikirim') : ?>
                                    <a href="validasi_penjual.php?action=complete&order_id=<?= $row['order_id']; ?>" class="bg-sage hover:bg-ink text-white font-bold text-xs px-3 py-1.5 rounded transition shadow-sm">
                                        ✓ Selesaikan Pesanan
                                    </a>
                                <?php else : ?>
                                    <span class="text-xs italic text-ink/40">Tidak ada aksi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center py-6 italic text-ink/40">Belum ada pesanan masuk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function closeToast() {
            var toast = document.getElementById('toast-notif');
            if (toast) { toast.style.display = 'none'; }
        }
        setTimeout(function() { closeToast(); }, 4000);
    </script>


</body>
</html>