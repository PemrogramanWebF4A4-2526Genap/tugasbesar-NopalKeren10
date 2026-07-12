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
$_SESSION['user_type'] = 'buyer';

// 1. Ambil Nama Pengguna (Buyer) yang Sedang Login
$query_user = mysqli_query($db, "SELECT full_name FROM users WHERE id = '$buyer_id'");
$data_user = mysqli_fetch_assoc($query_user);
$nama_pengguna = isset($data_user['full_name']) ? $data_user['full_name'] : 'Pelanggan';

// 2. Hitung total Qty barang di keranjang dari database milik pembeli ini
$query_badge = mysqli_query($db, "SELECT SUM(quantity) AS total_item FROM carts WHERE buyer_id = '$buyer_id'");
$data_badge = mysqli_fetch_assoc($query_badge);
$cart_count = $data_badge['total_item'] ? $data_badge['total_item'] : 0;

// Get unread notification count
$unread_notifications = getUnreadNotificationCount($buyer_id, 'buyer');

// --- AMBIL SEMUA DAFTAR KATEGORI UNTUK DISPLAI TOMBOL ---
$categories_data = mysqli_query($db, "SELECT * FROM categories");


// ==========================================
//   LOGIKA FILTER KATEGORI & SEARCH DINAMIS
// ==========================================
$filter_query = "";
$active_category = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$search_keyword  = isset($_GET['search']) ? mysqli_real_escape_string($db, $_GET['search']) : '';

// Jika ada kategori yang dipilih
if (!empty($active_category)) {
    $category_id_clean = mysqli_real_escape_string($db, $active_category);
    $filter_query .= " AND p.category_id = '$category_id_clean'";
}

// Jika ada kata kunci pencarian yang diketik
if (!empty($search_keyword)) {
    $filter_query .= " AND p.name LIKE '%$search_keyword%'";
}


// 3. Ambil produk buku sekaligus JOIN ke tabel users + FILTER KATEGORI & SEARCH
$result = mysqli_query($db, "SELECT p.*, u.name AS nama_toko
                             FROM products p
                             JOIN users u ON p.seller_id = u.id
                             WHERE p.stock > 0 $filter_query
                             ORDER BY p.id DESC");

// ==========================================
//   REKOMENDASI BUKU BERDASARKAN RATING
// ==========================================
// Hanya ditampilkan di tampilan awal (tanpa search & tanpa filter kategori)
// supaya halaman hasil pencarian/filter tetap fokus.
$tampilkan_rekomendasi = empty($search_keyword) && empty($active_category);

$query_rekomendasi_rating = mysqli_query($db, "SELECT p.*, u.name AS nama_toko,
                                                       AVG(r.rating) AS avg_rating,
                                                       COUNT(r.id) AS total_review
                                               FROM products p
                                               JOIN users u ON p.seller_id = u.id
                                               JOIN reviews r ON r.book_id = p.id
                                               WHERE p.stock > 0
                                               GROUP BY p.id
                                               ORDER BY avg_rating DESC, total_review DESC
                                               LIMIT 8");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - BookStore</title>
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
        .ribbon-tab {
            clip-path: polygon(0% 0%, 100% 0%, 100% 76%, 50% 100%, 0% 76%);
            padding-bottom: 1.05rem;
        }
        .book-cover {
            position: relative;
        }
        .book-cover::before {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 7px;
            background: linear-gradient(to right, rgba(0,0,0,0.35), rgba(0,0,0,0));
            z-index: 10;
        }
        .book-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .book-card:hover {
            transform: translateY(-6px);
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
        .rekomendasi-scroll {
            scrollbar-width: thin;
            scrollbar-color: #B4823A #F5EFE2;
        }
    </style>
</head>
<body class="min-h-screen font-sans text-ink">

   <nav class="bg-ink text-paper shadow-lg border-b-4 border-brass">
        <div class="container mx-auto flex justify-between items-center px-4 py-4">
            <a href="index.php" class="text-2xl font-serif font-bold tracking-wide flex items-center gap-2 text-paper">
                <span class="text-brass-light">📚</span> BookStore
            </a>
            
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    <span class="hidden md:inline font-serif italic text-brass-light text-sm">
                        Hai, <?= htmlspecialchars($nama_pengguna); ?>
                    </span>
                    <a href="profile.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-2 py-2 rounded-sm font-semibold transition text-sm" title="Profil">
                        👤
                    </a>
                </div>

                <!-- Notification Bell -->
                <div class="relative">
                    <button onclick="window.location.href='read_notification.php'" class="border border-brass/60 hover:bg-brass hover:text-ink px-3 py-2 rounded-sm font-semibold transition flex items-center gap-2 text-sm relative">
                        <span>🔔</span>
                        <?php if ($unread_notifications > 0) : ?>
                            <span class="absolute -top-1 -right-1 bg-maroon text-paper text-xs font-bold px-1.5 py-0.5 rounded-full">
                                <?= $unread_notifications > 9 ? '9+' : $unread_notifications; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                </div>

                <a href="cart.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition flex items-center gap-2 text-sm">
                    <span>🛒 Keranjang</span>
                    <?php if ($cart_count > 0) : ?>
                        <span class="bg-maroon text-paper text-xs font-bold px-2 py-0.5 rounded-full">
                            <?= $cart_count; ?>
                        </span>
                    <?php endif; ?>
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

    <section class="bg-gradient-to-b from-ink to-ink-light text-paper text-center py-16 px-4 border-b-2 border-brass/20 shadow-inner">
        <div class="container mx-auto max-w-3xl">
            <p class="text-xs font-bold tracking-widest text-brass-light uppercase mb-3">
                ✨ Rak Digital Untuk Semua Cerita
            </p>
            <h1 class="text-4xl md:text-5xl font-serif font-bold tracking-tight text-white mb-4">
                Selamat Datang di BookStore
            </h1>
            <p class="text-sm md:text-base font-medium text-paper/70 leading-relaxed max-w-xl mx-auto">
                Temukan buku favoritmu dengan harga terbaik dari berbagai penjual terpercaya.
            </p>
        </div>
    </section>

    <main class="container mx-auto py-10 px-4">

        <?php if ($tampilkan_rekomendasi && mysqli_num_rows($query_rekomendasi_rating) > 0) : ?>
        <div class="mb-12">
            <div class="mb-6 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <p class="eyebrow text-xs font-bold uppercase tracking-widest text-maroon mb-1">Pilihan Pembaca Lain</p>
                    <h2 class="text-3xl font-serif font-bold text-ink">⭐ Rekomendasi Buku Terbaik</h2>
                </div>
                <p class="text-xs text-ink/50 max-w-xs sm:text-right">Diurutkan berdasarkan rata-rata rating dari ulasan pembeli.</p>
            </div>

            <div class="flex gap-5 overflow-x-auto rekomendasi-scroll pb-3 -mx-1 px-1">
                <?php while($rek = mysqli_fetch_assoc($query_rekomendasi_rating)) : ?>
                    <div class="book-card bg-card rounded-sm shadow p-4 flex flex-col justify-between hover:shadow-xl border border-ink/5 w-48 sm:w-56 shrink-0">
                        <div>
                            <div class="relative">
                                <a href="book_details.php?id=<?= $rek['id']; ?>" class="book-cover block overflow-hidden bg-ink/10 rounded-sm mb-3 shadow-sm aspect-[3/4]">
                                    <img src="../assets/uploads/<?= $rek['image']; ?>" 
                                         alt="<?= htmlspecialchars($rek['name']); ?>" 
                                         class="w-full h-full object-cover object-center transition duration-300 hover:scale-105">
                                </a>
                                <span class="absolute top-2 right-2 z-20 bg-ink/90 text-brass-light text-xs font-bold px-2 py-1 rounded-sm shadow flex items-center gap-1">
                                    ⭐ <?= number_format($rek['avg_rating'], 1); ?>
                                </span>
                            </div>

                            <a href="book_details.php?id=<?= $rek['id']; ?>" class="block hover:text-maroon transition">
                                <h3 class="font-serif font-bold text-sm text-ink line-clamp-1"><?= htmlspecialchars($rek['name']); ?></h3>
                            </a>

                            <?php if (!empty($rek['author'])) : ?>
                            <p class="text-xs text-ink/60 flex items-center gap-1 mt-0.5 mb-1">
                                <span>✍️</span> <span class="font-semibold text-ink/80 line-clamp-1"><?= htmlspecialchars($rek['author']); ?></span>
                            </p>
                            <?php endif; ?>

                            <p class="text-xs text-ink/60 flex items-center gap-1 mt-0.5 mb-1">
                                <span>🏪</span> <span class="font-semibold text-ink/80 line-clamp-1"><?= htmlspecialchars($rek['nama_toko']); ?></span>
                            </p>

                            <p class="text-[11px] text-ink/40 mb-2"><?= $rek['total_review']; ?> ulasan</p>
                        </div>
                        <div class="mt-2 pt-3 border-t border-dashed border-ink/15">
                            <span class="text-maroon font-serif font-bold text-base block mb-2">Rp <?= number_format($rek['price'], 0, ',', '.'); ?></span>
                            <a href="book_details.php?id=<?= $rek['id']; ?>" class="block text-center bg-ink text-paper py-2 rounded-sm font-semibold hover:bg-maroon transition text-xs tracking-wide uppercase">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mb-6 max-w-xl">
            <form action="index.php" method="GET" class="flex gap-2">
                <?php if (!empty($active_category)) : ?>
                    <input type="hidden" name="category_id" value="<?= htmlspecialchars($active_category); ?>">
                <?php endif; ?>
                
                <div class="relative flex-1">
                    <input type="text" name="search" value="<?= htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>" placeholder="Cari judul buku yang kamu inginkan..." class="w-full pl-4 pr-10 py-3 rounded-sm border-b-2 border-ink/20 bg-card focus:outline-none focus:border-maroon shadow-sm text-sm font-medium placeholder:text-ink/40">
                    <?php if (!empty($search_keyword)) : ?>
                        <a href="index.php<?= !empty($active_category) ? '?category_id='.$active_category : ''; ?>" class="absolute right-3 top-3.5 text-ink/40 hover:text-maroon text-xs font-bold">✕</a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="bg-maroon hover:bg-maroon-light text-paper font-bold px-6 py-3 rounded-sm shadow transition text-sm whitespace-nowrap tracking-wide uppercase">
                    Cari
                </button>
            </form>
            <?php if (!empty($search_keyword)) : ?>
                <p class="text-xs text-ink/60 mt-2 pl-1">Menampilkan hasil pencarian untuk: <span class="font-bold text-maroon">"<?= htmlspecialchars($_GET['search']); ?>"</span></p>
            <?php endif; ?>
        </div>

        <div class="mb-10 bg-card px-4 pt-4 pb-2 rounded-sm shadow-sm border border-ink/10">
            <p class="text-xs font-bold uppercase tracking-widest text-brass mb-3">Pilih Kategori Buku</p>
            <div class="flex flex-wrap gap-3">
                <a href="index.php<?= !empty($search_keyword) ? '?search='.$search_keyword : ''; ?>" class="ribbon-tab px-4 pt-2 text-sm font-semibold transition <?= empty($active_category) ? 'bg-ink text-brass-light' : 'bg-paper text-ink/70 hover:bg-brass-light/40'; ?>">
                    📖 Semua Buku
                </a>

                <?php while($cat = mysqli_fetch_assoc($categories_data)) : ?>
                    <a href="index.php?category_id=<?= $cat['id']; ?><?= !empty($search_keyword) ? '&search='.$search_keyword : ''; ?>" class="ribbon-tab px-4 pt-2 text-sm font-semibold transition <?= ($active_category == $cat['id']) ? 'bg-ink text-brass-light' : 'bg-paper text-ink/70 hover:bg-brass-light/40'; ?>">
                        📁 <?= htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="mb-6">
            <p class="eyebrow text-xs font-bold uppercase tracking-widest text-maroon mb-1">Koleksi Terkurasi</p>
            <h2 class="text-3xl font-serif font-bold text-ink">Jelajahi Buku Terbaik</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <?php if (mysqli_num_rows($result) > 0) : ?>
                <?php while($buku = mysqli_fetch_assoc($result)) : ?>
                    <div class="book-card bg-card rounded-sm shadow p-4 flex flex-col justify-between hover:shadow-xl border border-ink/5">
                        <div>
                            <a href="book_details.php?id=<?= $buku['id']; ?>" class="book-cover block overflow-hidden bg-ink/10 rounded-sm mb-4 shadow-sm aspect-[3/4]">
                                <img src="../assets/uploads/<?= $buku['image']; ?>" 
                                     alt="<?= $buku['name']; ?>" 
                                     class="w-full h-full object-cover object-center transition duration-300 hover:scale-105">
                            </a>        
                            
                            <a href="book_details.php?id=<?= $buku['id']; ?>" class="block hover:text-maroon transition">
                                <h3 class="font-serif font-bold text-lg text-ink line-clamp-1"><?= $buku['name']; ?></h3>
                            </a>

                            <?php if (!empty($buku['author'])) : ?>
                            <p class="text-xs text-ink/60 flex items-center gap-1 mt-0.5 mb-1">
                                <span>✍️</span> <span class="font-semibold text-ink/80"><?= $buku['author']; ?></span>
                            </p>
                            <?php endif; ?>

                            <p class="text-xs text-ink/60 flex items-center gap-1 mt-0.5 mb-2">
                                <span>🏪</span> <span class="font-semibold text-ink/80 hover:underline cursor-pointer"><?= $buku['nama_toko']; ?></span>
                            </p>

                            <p class="text-ink/70 text-sm mb-3 line-clamp-2"><?= $buku['description']; ?></p>
                            <span class="text-xs bg-sage/15 text-sage px-2 py-1 rounded-sm font-bold border border-sage/30">Stok: <?= $buku['stock']; ?></span>
                        </div>
                        <div class="mt-4 pt-4 border-t border-dashed border-ink/15">
                            <span class="text-maroon font-serif font-bold text-xl block mb-3">Rp <?= number_format($buku['price'], 0, ',', '.'); ?></span>
                            <a href="cart.php?action=add&id=<?= $buku['id']; ?>" class="block text-center bg-ink text-paper py-2.5 rounded-sm font-semibold hover:bg-maroon transition text-sm tracking-wide uppercase">
                                + Masukkan Keranjang
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-span-full bg-card p-12 text-center rounded-sm border border-ink/10 shadow-sm">
                    <p class="text-ink/40 italic font-serif text-lg">Maaf, buku yang kamu cari tidak ditemukan. 📚</p>
                    <?php if (!empty($search_keyword) || !empty($active_category)) : ?>
                        <a href="index.php" class="inline-block mt-4 bg-maroon/10 text-maroon text-xs font-bold px-4 py-2 rounded-sm hover:bg-maroon/20 transition uppercase tracking-wide">Reset Filter & Cari Ulang</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../components/buyer_footer.php'; ?>

</body>
</html>