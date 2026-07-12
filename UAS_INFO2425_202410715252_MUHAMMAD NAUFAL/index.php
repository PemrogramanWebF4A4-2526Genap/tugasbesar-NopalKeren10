<?php
include 'config/database.php';

// Ambil data produk/buku dari database
$query = "SELECT * FROM products ORDER BY id DESC LIMIT 8";
$result = mysqli_query($db, $query);

// ==========================================
//   REKOMENDASI BUKU BERDASARKAN RATING
// ==========================================
$query_rekomendasi_rating = mysqli_query($db, "SELECT p.*, 
                                                       AVG(r.rating) AS avg_rating,
                                                       COUNT(r.id) AS total_review
                                               FROM products p
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
    <title>BookStore - Toko Buku Online</title>
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
        .hero-panel {
            background-color: #161F2B;
            background-image:
                radial-gradient(circle at 15% 30%, rgba(180,130,58,0.14) 0%, transparent 45%),
                radial-gradient(circle at 85% 70%, rgba(123,42,50,0.2) 0%, transparent 50%);
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
                <a href="auth/login.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition text-sm">Login</a>
                <a href="auth/register.php" class="bg-maroon hover:bg-maroon-light px-4 py-2 rounded-sm font-semibold transition text-sm">Register</a>
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

        <?php if (mysqli_num_rows($query_rekomendasi_rating) > 0) : ?>
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
                                <a href="auth/login.php" class="book-cover block overflow-hidden bg-ink/10 rounded-sm mb-3 shadow-sm aspect-[3/4]">
                                    <img src="assets/uploads/<?= $rek['image'] ? $rek['image'] : 'default-book.png'; ?>" 
                                         alt="<?= htmlspecialchars($rek['name']); ?>" 
                                         class="w-full h-full object-cover object-top transition duration-300 hover:scale-105">
                                </a>
                                <span class="absolute top-2 right-2 z-20 bg-ink/90 text-brass-light text-xs font-bold px-2 py-1 rounded-sm shadow flex items-center gap-1">
                                    ⭐ <?= number_format($rek['avg_rating'], 1); ?>
                                </span>
                            </div>

                            <a href="auth/login.php" class="block hover:text-maroon transition">
                                <h3 class="font-serif font-bold text-sm text-ink line-clamp-1"><?= htmlspecialchars($rek['name']); ?></h3>
                            </a>

                            <p class="text-[11px] text-ink/40 mt-1 mb-2"><?= $rek['total_review']; ?> ulasan</p>
                        </div>
                        <div class="mt-2 pt-3 border-t border-dashed border-ink/15">
                            <span class="text-maroon font-serif font-bold text-base block mb-2">Rp <?= number_format($rek['price'], 0, ',', '.'); ?></span>
                            <a href="auth/login.php" class="block text-center bg-ink text-paper py-2 rounded-sm font-semibold hover:bg-maroon transition text-xs tracking-wide uppercase">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mb-6">
            <p class="eyebrow text-xs font-bold uppercase tracking-widest text-maroon mb-1">Baru Ditambahkan</p>
            <h2 class="text-3xl font-serif font-bold text-ink">Katalog Buku Terbaru</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while($buku = mysqli_fetch_assoc($result)) : 
            ?>
                <div class="book-card bg-card rounded-sm shadow p-4 flex flex-col justify-between hover:shadow-xl border border-ink/5">
                    <div>
                        <a href="buyer/cart.php?add_id=<?= $buku['id']; ?>" class="book-cover block overflow-hidden bg-ink/10 rounded-sm mb-4 shadow-sm aspect-[3/4]">
                            <img src="assets/uploads/<?= $buku['image'] ? $buku['image'] : 'default-book.png'; ?>" 
                                 alt="<?= $buku['name']; ?>" 
                                 class="w-full h-full object-cover object-top transition duration-300 hover:scale-105">
                        </a>
                        <h3 class="font-serif font-bold text-lg text-ink line-clamp-1"><?= $buku['name']; ?></h3>
                        <p class="text-ink/70 text-sm my-2 line-clamp-2"><?= $buku['description']; ?></p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-dashed border-ink/15">
                        <span class="text-maroon font-serif font-bold text-xl block mb-3">Rp <?= number_format($buku['price'], 0, ',', '.'); ?></span>
                        <a href="buyer/cart.php?add_id=<?= $buku['id']; ?>" class="block text-center bg-ink text-paper py-2.5 rounded-sm font-semibold hover:bg-maroon transition text-sm tracking-wide uppercase">
                            + Keranjang
                        </a>
                    </div>
                </div>
            <?php 
                endwhile; 
            } else { 
            ?>
                <div class="col-span-full bg-card p-12 text-center rounded-sm border border-ink/10 shadow-sm">
                    <p class="text-ink/40 italic font-serif text-lg">Belum ada buku yang ditambahkan ke sistem.</p>
                </div>
            <?php } ?>
        </div>
    </main>

    <?php include 'components/buyer_footer.php'; ?>

</body>
</html>