<?php
session_start();
include '../config/database.php';
require_once '../config/notification_helper.php';

// Proteksi: Hanya boleh diakses oleh Buyer
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
// TANGKAP ID ORDER DARI URL (Ini yang bikin bisa review berkali-kali)
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// 1. VALIDASI UTAMA: Cek pembelian berdasarkan buyer_id, book_id, DAN order_id
$sql_cek = "SELECT o.id 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            WHERE o.buyer_id = '$buyer_id' 
            AND oi.product_id = '$book_id' 
            AND o.id = '$order_id'
            AND o.status = 'completed'";

$query_cek_pembelian = mysqli_query($db, $sql_cek);

if (!$query_cek_pembelian) {
    die("<b>Error Database:</b> " . mysqli_error($db));
}

// JIKA DATA TRANSAKSI TIDAK COCOK
if (mysqli_num_rows($query_cek_pembelian) == 0) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'title' => 'Akses Ditolak',
        'message' => 'Anda hanya dapat memberikan ulasan berdasarkan data pesanan selesai yang valid!'
    ];
    header("Location: track_order.php");
    exit;
}

// Ambil info nama, penulis, dan gambar buku yang mau di-review
$query_book = mysqli_query($db, "SELECT name, author, image FROM products WHERE id = '$book_id'");
$book = mysqli_fetch_assoc($query_book);
$nama_buku = $book ? $book['name'] : "Buku Tidak Diketahui";
$penulis_buku = $book && !empty($book['author']) ? $book['author'] : "";
$gambar_buku = ($book && !empty($book['image'])) ? $book['image'] : "no_image.png";

// Proses ketika form disubmit
if (isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating']);
    $review_text = mysqli_real_escape_string($db, $_POST['review_text']);

    if ($rating < 1 || $rating > 5) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'title' => 'Rating Salah',
            'message' => 'Penilaian rating harus berkisar antara angka 1 sampai 5.'
        ];
        header("Location: review.php?book_id=" . $book_id . "&order_id=" . $order_id);
        exit;
    } else {
        // PERBAIKAN: Cek ulasan berdasarkan ORDER ID agar tidak bentrok dengan pesanan lama
        $cek_review = mysqli_query($db, "SELECT id FROM reviews WHERE book_id = '$book_id' AND order_id = '$order_id'");
        
        if (mysqli_num_rows($cek_review) > 0) {
            $_SESSION['toast'] = [
                'type' => 'error',
                'title' => 'Ulasan Duplikat',
                'message' => 'Anda sudah pernah mengirimkan ulasan untuk buku ini pada pesanan ini.'
            ];
            header("Location: review.php?book_id=" . $book_id . "&order_id=" . $order_id);
            exit;
        } else {
            // PERBAIKAN: Masukkan data order_id ke database
            $insert = mysqli_query($db, "INSERT INTO reviews (book_id, buyer_id, order_id, rating, review_text) VALUES ('$book_id', '$buyer_id', '$order_id', '$rating', '$review_text')");
            if ($insert) {
                // Get seller_id for the book to send notification
                $query_seller = mysqli_query($db, "SELECT seller_id FROM products WHERE id = '$book_id'");
                $product_data = mysqli_fetch_assoc($query_seller);
                $seller_id = $product_data['seller_id'];
                
                // Get buyer name for notification
                $query_buyer_name = mysqli_query($db, "SELECT full_name FROM users WHERE id = '$buyer_id'");
                $buyer_data = mysqli_fetch_assoc($query_buyer_name);
                $buyer_name = $buyer_data['full_name'];
                
                // Create notification for seller
                createNotification(
                    $seller_id, 
                    'seller', 
                    'Ulasan Baru Diterima', 
                    $buyer_name . ' memberikan ulasan ' . $rating . ' bintang untuk produk Anda.', 
                    'new_review'
                );
                
                $_SESSION['toast'] = [
                    'type' => 'success',
                    'title' => 'Ulasan Dikirim',
                    'message' => 'Terima kasih banyak! Ulasan Anda berhasil disimpan.'
                ];
                header("Location: track_order.php");
                exit;
            } else {
                $_SESSION['toast'] = [
                    'type' => 'error',
                    'title' => 'Gagal Menyimpan',
                    'message' => 'Sistem gagal menyimpan ulasan Anda: ' . mysqli_real_escape_string($db, mysqli_error($db))
                ];
                header("Location: review.php?book_id=" . $book_id . "&order_id=" . $order_id);
                exit;
            }
        }
    }
}

// Ambil data toast dari session jika ada
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
    <title>Beri Ulasan - BookStore</title>
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
<body class="bg-paper min-h-screen font-sans text-ink flex items-center justify-center p-6">

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

    <div class="bg-card p-8 rounded-sm shadow-xl max-w-md w-full border-t-4 border-brass border-x border-b border-ink/5 relative">
        <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Ulasan Pelanggan</p>
        <h2 class="text-2xl font-serif font-bold text-ink mb-4">Tulis Ulasan</h2>
        
        <div class="flex items-start gap-4 mb-6 bg-paper/40 p-3 rounded-sm border border-ink/5">
            <img src="../assets/uploads/<?= htmlspecialchars($gambar_buku); ?>" class="w-16 h-22 object-cover object-top rounded-sm shadow border border-ink/10 shrink-0 bg-white">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-ink/60 leading-relaxed mb-1">Berikan penilaian Anda untuk Buku kami:</p>
                <p class="text-sm font-bold text-ink break-words">"<?= htmlspecialchars($nama_buku); ?>"</p>
                <?php if (!empty($penulis_buku)) : ?>
                <p class="text-xs text-ink/60 mt-0.5">✍️ <?= htmlspecialchars($penulis_buku); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Berikan Rating</label>
                <select name="rating" required class="w-full border border-ink/20 rounded-sm p-3 text-sm font-medium text-ink bg-paper focus:outline-none focus:border-maroon mt-1.5 cursor-pointer">
                    <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                    <option value="4">⭐⭐⭐⭐ (4 - Puas)</option>
                    <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                    <option value="2">⭐⭐ (2 - Kurang)</option>
                    <option value="1">⭐ (1 - Kecewa)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-ink/70">Ulasan Anda (Opsional)</label>
                <textarea name="review_text" rows="4" placeholder="Ceritakan pengalaman berharga Anda membaca buku asli ini..." class="w-full border border-ink/20 rounded-sm p-3 text-sm bg-paper focus:outline-none focus:border-maroon mt-1.5 h-32 resize-none leading-relaxed"></textarea>
            </div>

            <div class="flex space-x-3 pt-2">
                <a href="track_order.php" class="w-1/2 text-center bg-paper border border-ink/10 text-ink/70 px-4 py-3 rounded-sm font-bold text-xs uppercase tracking-wider hover:bg-ink/5 transition flex items-center justify-center">
                    Batal
                </a>
                <button type="submit" name="submit_review" class="w-1/2 bg-ink text-paper px-4 py-3 rounded-sm font-bold text-xs uppercase tracking-wider hover:bg-maroon transition shadow-md">
                    Kirim Ulasan
                </button>
            </div>
        </form>
    </div>

    <script>
        function closeToast() {
            var toast = document.getElementById('toast-notif');
            if (toast) { 
                toast.classList.remove('show');
                setTimeout(() => toast.style.display = 'none', 350); 
            }
        }
        setTimeout(function() { closeToast(); }, 4000);
    </script>
</body>
</html>