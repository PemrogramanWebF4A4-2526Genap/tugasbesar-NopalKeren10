<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

// Query ambil data history pesanan
$query_history = "SELECT o.id AS order_id, o.total_amount, o.status, o.created_at,
                         GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, 'x)') SEPARATOR ', ') AS rincian_buku
                  FROM orders o
                  JOIN order_items oi ON o.id = oi.order_id
                  JOIN products p ON oi.product_id = p.id
                  WHERE o.buyer_id = '$buyer_id'
                  GROUP BY o.id
                  ORDER BY o.id DESC";

$result_history = mysqli_query($db, $query_history);

// Tangkap notifikasi toast jika ada (limpahan dari review.php)
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
    <title>Pesanan Saya - BookStore</title>
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
        /* Style Animasi Toast */
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
<body class="bg-paper min-h-screen font-sans text-ink">

    <?php if ($toast) : ?>
    <?php 
        $is_success = ($toast['type'] === 'success');
        $icon_symbol = $is_success ? '✓' : '✕';
        $icon_style = $is_success ? 'bg-sage/10 border-sage/30 text-sage' : 'bg-maroon/10 border-maroon/30 text-maroon';
    ?>
    <div id="toast-notif" class="fixed top-5 right-5 z-50 bg-card w-full max-w-sm rounded-sm shadow-xl border border-ink/10 overflow-hidden show">
        <div class="p-5 flex items-start gap-3">
            <div class="w-10 h-10 rounded-full <?= $icon_style ?> border flex items-center justify-center text-lg shrink-0"><?= $icon_symbol ?></div>
            <div class="flex-1">
                <p class="font-serif font-bold text-ink text-sm mb-0.5"><?= htmlspecialchars($toast['title']); ?></p>
                <p class="text-ink/60 text-xs leading-relaxed"><?= htmlspecialchars($toast['message']); ?></p>
            </div>
            <button onclick="closeToast()" class="text-ink/30 hover:text-ink text-xs shrink-0">✕</button>
        </div>
    </div>
    <?php endif; ?>

    <nav class="bg-ink text-paper p-4 shadow-lg border-b-4 border-brass">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-serif font-bold tracking-wide flex items-center gap-2">
                <span class="text-brass-light">📚</span> BookStore
            </a>
            <a href="index.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition text-sm">← Kembali Belanja</a>
        </div>
    </nav>

    <main class="container mx-auto py-10 px-4 max-w-5xl">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Arsip Transaksi</p>
            <h1 class="text-3xl font-serif font-bold text-ink">Riwayat Pesanan Anda</h1>
        </div>

        <div class="bg-card p-6 rounded-sm shadow-md overflow-x-auto border border-ink/5">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b-2 border-ink/10 text-ink/60 text-left text-xs uppercase font-bold tracking-widest bg-paper">
                        <th class="p-3">ID Order</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Daftar Buku</th>
                        <th class="p-3">Total Bayar</th>
                        <th class="p-3 text-center">Status Pesanan</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dashed divide-ink/10 text-ink/80">
                    <?php if (mysqli_num_rows($result_history) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($result_history)) : ?>
                        <tr class="hover:bg-paper/60 transition">
                            <td class="p-3 font-mono font-bold text-maroon">#<?= $row['order_id']; ?></td>
                            <td class="p-3 text-sm text-ink/50"><?= date('d M Y, H:i', strtotime($row['created_at'])); ?> WIB</td>
                            
                            <td class="p-3 text-sm font-semibold text-ink max-w-xs">
                                <div class="space-y-2">
                                    <?php 
                                    $order_id_check = $row['order_id'];
                                    $query_items = mysqli_query($db, "SELECT oi.product_id, p.name, oi.quantity 
                                                                      FROM order_items oi 
                                                                      JOIN products p ON oi.product_id = p.id 
                                                                      WHERE oi.order_id = '$order_id_check'");
                                    
                                    // Normalisasi status untuk pengecekan ulasan buku
                                    $status_review_check = isset($row['status']) ? strtolower(trim($row['status'])) : '';

                                    while ($item = mysqli_fetch_assoc($query_items)) : 
                                        $prod_id = $item['product_id'];
                                        
                                        // PERUBAHAN: Cek ulasan sekarang spesifik ke order_id ini, bukan sekadar buyer_id
                                        $cek_review = mysqli_query($db, "SELECT id FROM reviews WHERE book_id = '$prod_id' AND order_id = '$order_id_check'");
                                        $sudah_review = mysqli_num_rows($cek_review) > 0;
                                    ?>
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-paper p-2 rounded-sm border border-ink/10 gap-2">
                                            <span class="text-xs text-ink/70 font-medium"><?= htmlspecialchars($item['name']); ?> (<?= $item['quantity']; ?>x)</span>
                                            
                                            <?php if ($status_review_check == 'completed' || $status_review_check == 'selesai') : ?>
                                                <?php if (!$sudah_review) : ?>
                                                    <a href="review.php?book_id=<?= $prod_id; ?>&order_id=<?= $order_id_check; ?>" 
                                                       class="inline-block bg-brass hover:bg-brass-light text-ink font-bold text-[10px] px-2 py-1 rounded-sm transition text-center shrink-0 uppercase tracking-wide">
                                                         ⭐ Beri Ulasan
                                                    </a>
                                                <?php else : ?>
                                                    <span class="text-[10px] text-sage font-bold bg-sage/10 px-2 py-1 rounded-sm border border-sage/30 text-center shrink-0 uppercase tracking-wide">
                                                         ✓ Sudah Diulas
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </td>

                            <td class="p-4 font-bold text-gray-900 whitespace-nowrap">Rp <?= number_format($row['total_amount'], 0, ',', '.'); ?></td>
                            
                            <td class="p-3 text-center">
                                <?php 
                                $status_db = isset($row['status']) ? strtolower(trim($row['status'])) : '';
                                
                                if ($status_db == 'pending' || $status_db == 'menunggu pembayaran') : ?>
                                    <span class="inline-block bg-yellow-100 text-yellow-800 border border-yellow-300 text-xs px-3 py-1 rounded-full font-extrabold uppercase tracking-wide">
                                        💳 Menunggu Pembayaran
                                    </span>
                                <?php elseif ($status_db == 'paid' || $status_db == 'diproses') : ?>
                                    <span class="inline-block bg-blue-100 text-blue-800 border border-blue-300 text-xs px-3 py-1 rounded-full font-extrabold uppercase tracking-wide">
                                        ⚙️ Diproses
                                    </span>
                                <?php elseif ($status_db == 'shipped' || $status_db == 'sedang dikirim') : ?>
                                    <span class="inline-block bg-orange-100 text-orange-800 border border-orange-300 text-xs px-3 py-1 rounded-full font-extrabold uppercase tracking-wide">
                                        🚚 Sedang Dikirim
                                    </span>
                                <?php elseif ($status_db == 'completed' || $status_db == 'selesai') : ?>
                                    <span class="inline-block bg-sage/15 text-sage border border-sage/40 text-xs px-3 py-1 rounded-full font-extrabold uppercase tracking-wide">
                                        ✓ Selesai
                                    </span>
                                <?php else : ?>
                                    <span class="inline-block bg-gray-100 text-gray-800 border border-gray-300 text-xs px-3 py-1 rounded-full font-extrabold uppercase tracking-wide">
                                        ⚠️ Unknown (<?= htmlspecialchars($row['status'] ?: 'Kosong'); ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="p-3 text-center">
                                <div class="flex flex-col sm:flex-row justify-center items-center gap-2">
                                    <a href="detail_pesanan.php?order_id=<?= $row['order_id']; ?>" 
                                       class="w-full sm:w-auto inline-block bg-paper text-ink border border-ink/15 hover:bg-ink hover:text-brass-light px-3 py-1.5 rounded-sm text-xs font-bold transition shadow-sm uppercase tracking-wide">
                                        🔍 Detail
                                    </a>
                                    
                                    <a href="invoice.php?id=<?= $row['order_id']; ?>" 
                                       class="w-full sm:w-auto inline-block bg-ink text-paper border border-transparent hover:bg-maroon hover:text-paper px-3 py-1.5 rounded-sm text-xs font-bold transition shadow-sm uppercase tracking-wide">
                                        📄 Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-ink/40 italic font-serif">Kamu belum pernah melakukan transaksi apa pun.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../components/buyer_footer.php'; ?>

    <script>
        function closeToast() {
            var toast = document.getElementById('toast-notif');
            if (toast) { 
                toast.classList.remove('show');
                setTimeout(() => toast.style.display = 'none', 350); 
            }
        }
        // Otomatis menutup toast setelah 4 detik (4000ms)
        setTimeout(function() { closeToast(); }, 4000);
    </script>
</body>
</html>