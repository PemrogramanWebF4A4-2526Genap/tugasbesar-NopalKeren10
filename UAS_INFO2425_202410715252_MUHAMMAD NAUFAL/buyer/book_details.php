<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

// Ambil ID Buku dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_buku = $_GET['id'];

// Hitung total Qty barang di keranjang untuk Badge Navbar
$query_badge = mysqli_query($db, "SELECT SUM(quantity) AS total_item FROM carts WHERE buyer_id = '$buyer_id'");
$data_badge = mysqli_fetch_assoc($query_badge);
$cart_count = $data_badge['total_item'] ? $data_badge['total_item'] : 0;

// Query join untuk mengambil data buku, nama kategori, dan nama penjual (seller)
$query = "SELECT p.*, c.name AS nama_kategori, u.name AS nama_penjual
          FROM products p
          JOIN categories c ON p.category_id = c.id
          JOIN users u ON p.seller_id = u.id
          WHERE p.id = '$id_buku'";

$result = mysqli_query($db, $query);
$buku = mysqli_fetch_assoc($result);

// Jika buku tidak ditemukan
if (!$buku) {
    echo "<script>alert('Buku tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// AMBIL REKOMENDASI: Mengambil 4 buku lain secara acak, selain buku yang sedang dibuka
$query_rekomendasi = mysqli_query($db, "SELECT * FROM products WHERE id != '$id_buku' AND stock > 0 ORDER BY RAND() LIMIT 4");


// ==========================================
// INTEGRASI FITUR RATING & REVIEW (BARU)
// ==========================================

// 1. Validasi Pembelian: Cek apakah pembeli ini sudah pernah membeli buku ini dengan status 'completed'
$query_cek_pembelian = mysqli_query($db, "SELECT o.id 
                                          FROM orders o 
                                          JOIN order_items oi ON o.id = oi.order_id 
                                          WHERE o.buyer_id = '$buyer_id' 
                                          AND oi.product_id = '$id_buku' 
                                          AND o.status = 'completed'");
$sudah_beli = mysqli_num_rows($query_cek_pembelian) > 0;

// 2. Hitung Rata-rata Rating dan Total Ulasan Buku ini
$query_avg = mysqli_query($db, "SELECT AVG(rating) AS rata_rating, COUNT(*) AS total_review FROM reviews WHERE book_id = '$id_buku'");
$data_avg = mysqli_fetch_assoc($query_avg);
$rating_rata = $data_avg['rata_rating'] ? round($data_avg['rata_rating'], 1) : 0;
$total_review = $data_avg['total_review'];

// 3. Tarik Semua Daftar Ulasan dari tabel reviews beserta nama pembelinya
$query_reviews = mysqli_query($db, "SELECT r.*, u.name FROM reviews r JOIN users u ON r.buyer_id = u.id WHERE r.book_id = '$id_buku' ORDER BY r.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $buku['name']; ?> - Detail Buku</title>
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
        .book-cover { position: relative; }
        .book-cover::before {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 7px;
            background: linear-gradient(to right, rgba(0,0,0,0.35), rgba(0,0,0,0));
            z-index: 10;
        }
        .book-card { transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .book-card:hover { transform: translateY(-6px); }
    </style>
</head>
<body class="bg-paper min-h-screen font-sans text-ink">

    <nav class="bg-ink text-paper shadow-lg border-b-4 border-brass sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center px-4 py-4">
            <a href="index.php" class="text-2xl font-serif font-bold tracking-wide flex items-center gap-2 text-paper">
                <span class="text-brass-light">📚</span> BookStore
            </a>
            
            <div class="flex items-center space-x-3">
                <a href="cart.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition flex items-center gap-2 text-sm">
                    <span>🛒 Keranjang</span>
                    <span id="cart-badge" class="bg-maroon text-paper text-xs font-bold px-2 py-0.5 rounded-full <?= ($cart_count > 0) ? '' : 'hidden'; ?>">
                        <?= $cart_count; ?>
                    </span>
                </a>

                <a href="index.php" class="bg-maroon hover:bg-maroon-light px-4 py-2 rounded-sm font-semibold text-sm transition">
                    ← Kembali ke Katalog
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto py-10 px-4 max-w-4xl">
        <div class="bg-card rounded-sm shadow-md overflow-hidden p-6 md:p-8 grid grid-cols-1 md:grid-cols-3 gap-8 border border-ink/5">
            
            <div class="flex justify-center">
                <div class="book-cover rounded-sm overflow-hidden shadow-md border border-ink/10 w-64 h-80">
                    <img src="../assets/uploads/<?= $buku['image']; ?>" class="w-full h-full object-cover object-top scale-110">
                </div>
            </div>

            <div class="md:col-span-2 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center flex-wrap gap-2">
                        <span class="text-xs bg-ink text-brass-light font-bold px-3 py-1 rounded-sm uppercase tracking-widest">
                            <?= $buku['nama_kategori']; ?>
                        </span>
                        
                        <div class="flex items-center space-x-1 bg-brass-light/20 px-2.5 py-1 rounded-sm border border-brass/40">
                            <span class="text-brass font-bold text-sm">⭐ <?= $rating_rata; ?> / 5</span>
                            <span class="text-ink/40 text-xs">(<?= $total_review; ?> Ulasan)</span>
                        </div>
                    </div>

                    <h1 class="text-3xl font-serif font-bold text-ink mt-3 mb-2"><?= $buku['name']; ?></h1>
                    <?php if (!empty($buku['author'])) : ?>
                    <p class="text-sm text-ink/60 mb-1">Penulis: <span class="font-semibold text-ink/80"><?= $buku['author']; ?></span></p>
                    <?php endif; ?>
                    <p class="text-sm text-ink/60 mb-4">Penjual: <span class="font-semibold text-ink/80"><?= $buku['nama_penjual']; ?></span></p>
                    
                    <div class="border-t border-b border-dashed border-ink/15 py-4 my-4">
                        <h3 class="font-serif font-bold text-ink mb-2">Sinopsis / Deskripsi Buku:</h3>
                        <p class="text-ink/70 leading-relaxed text-justify whitespace-pre-line"><?= $buku['description']; ?></p>
                    </div>
                </div>

                <div>
                    <div class="bg-paper p-4 rounded-sm flex flex-col sm:flex-row sm:items-center sm:justify-between mt-2 border border-ink/10">
                        <div class="mb-3 sm:mb-0">
                            <span class="text-sm text-ink/50 block">(Sisa <?= $buku['stock']; ?>)</span>
                            <span class="text-3xl font-serif font-black text-maroon">Rp <?= number_format($buku['price'], 0, ',', '.'); ?></span>
                        </div>
                        <div>
                            <button id="btn-add-cart" data-id="<?= $buku['id']; ?>" class="inline-block text-center bg-ink text-paper font-bold py-3 px-8 rounded-sm shadow hover:bg-maroon transition w-full sm:w-auto cursor-pointer uppercase tracking-wide text-sm">
                                🛒 Masukkan Keranjang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 bg-card rounded-sm shadow-md p-6 md:p-8 border border-ink/5">
            <h3 class="text-xl font-serif font-bold text-ink mb-6 flex items-center gap-2">
                <span>💬</span> Ulasan Pembeli (<?= $total_review; ?>)
            </h3>
            
            <?php if (mysqli_num_rows($query_reviews) == 0) : ?>
                <p class="text-ink/40 italic text-sm p-4 bg-paper rounded-sm border border-dashed border-ink/15 text-center">
                    Belum ada ulasan untuk buku ini. Jadilah yang pertama memberikan review!
                </p>
            <?php else : ?>
                <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                    <?php while ($rev = mysqli_fetch_assoc($query_reviews)) : ?>
                        <div class="bg-paper p-4 rounded-sm border border-ink/10 transition hover:bg-brass-light/10">
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="font-bold text-ink text-sm"><?= htmlspecialchars($rev['name']); ?></span>
                                <span class="text-xs text-ink/40"><?= date('d M Y', strtotime($rev['created_at'])); ?></span>
                            </div>
                            
                            <div class="text-brass text-xs mb-2 tracking-wide">
                                <?= str_repeat('⭐', $rev['rating']); ?>
                            </div>
                            
                            <p class="text-ink/70 text-sm leading-relaxed">
                                <?= nl2br(htmlspecialchars($rev['review_text'] ? $rev['review_text'] : 'Pembeli tidak meninggalkan pesan ulasan teks.')); ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-12">
            <h3 class="text-xl font-serif font-bold text-ink mb-6 flex items-center gap-2">
                <span>📚</span> Buku Lain yang Mungkin Kamu Suka
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <?php if (mysqli_num_rows($query_rekomendasi) > 0) : ?>
                    <?php while($rek = mysqli_fetch_assoc($query_rekomendasi)) : ?>
                        <div class="book-card bg-card rounded-sm shadow p-4 flex flex-col justify-between hover:shadow-xl border border-ink/5">
                            <div>
                                <a href="book_details.php?id=<?= $rek['id']; ?>" class="book-cover block overflow-hidden bg-ink/10 rounded-sm mb-3 aspect-[3/4]">
                                    <img src="../assets/uploads/<?= $rek['image']; ?>" 
                                         alt="<?= $rek['name']; ?>" 
                                         class="w-full h-full object-cover object-top scale-105 transition duration-300 hover:scale-110">
                                </a>        
                                
                                <a href="book_details.php?id=<?= $rek['id']; ?>" class="block hover:text-maroon transition">
                                    <h3 class="font-serif font-bold text-sm text-ink line-clamp-1"><?= $rek['name']; ?></h3>
                                </a>
                                <p class="text-maroon font-serif font-semibold text-sm mt-1">Rp <?= number_format($rek['price'], 0, ',', '.'); ?></p>
                            </div>
                            <div class="mt-3">
                                <a href="book_details.php?id=<?= $rek['id']; ?>" class="block text-center bg-paper hover:bg-ink text-ink hover:text-brass-light py-1.5 rounded-sm text-xs font-bold transition uppercase tracking-wide">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <p class="text-ink/50 italic text-sm col-span-full">Belum ada rekomendasi buku lain saat ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    document.getElementById('btn-add-cart').addEventListener('click', function() {
        const bookId = this.getAttribute('data-id');
        const badge = document.getElementById('cart-badge');
        
        fetch(`cart.php?action=add&id=${bookId}`)
            .then(() => {
                let currentCount = parseInt(badge.innerText) || 0;
                currentCount += 1;
                
                badge.innerText = currentCount;
                badge.classList.remove('hidden');
                
            })
            .catch(err => {
                console.error('Gagal menambah keranjang:', err);
                alert('Gagal memasukkan buku ke keranjang.');
            });
    });
    </script>

    <?php include '../components/buyer_footer.php'; ?>

</body>
</html>