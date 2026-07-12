<?php
session_start();
include '../config/database.php';

// Proteksi: Hanya boleh diakses oleh Seller yang sudah login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Pastikan ada ID produk yang mau diedit di URL
if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit;
}

$product_id = mysqli_real_escape_string($db, $_GET['id']);

// Ambil data produk lama yang mau diedit (pastikan memang milik seller ini)
$query_product = mysqli_query($db, "SELECT * FROM products WHERE id = '$product_id' AND seller_id = '$seller_id'");
$product = mysqli_fetch_assoc($query_product);

// Jika produk tidak ditemukan atau bukan milik seller ini, tendang balik
if (!$product) {
    header("Location: manage_products.php");
    exit;
}

// Toast yang tampil di halaman ini sendiri (kalau prosesnya tidak redirect)
$toast = null;

// PROSES UPDATE DATA BUKU
if (isset($_POST['update_buku'])) {
    $name        = mysqli_real_escape_string($db, $_POST['name']);
    $author      = mysqli_real_escape_string($db, $_POST['author']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $price       = $_POST['price'];
    $stock       = $_POST['stock'];
    $category_id = $_POST['category_id'];
    
    $old_image   = $product['image'];
    
    // Cek apakah seller mengupload gambar cover baru
    if ($_FILES['image']['name'] != "") {
        $filename = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $new_filename = uniqid() . '-' . $filename;
        $folder_tujuan = '../assets/uploads/' . $new_filename;
        
        if (move_uploaded_file($tmp_name, $folder_tujuan)) {
            // Hapus gambar cover lama di folder biar file ga numpuk sampah
            if (file_exists('../assets/uploads/' . $old_image)) {
                unlink('../assets/uploads/' . $old_image);
            }
            $image_query = ", image = '$new_filename'";
        } else {
            $toast = [
                'type' => 'error',
                'title' => 'Gagal Upload Gambar',
                'message' => 'Gagal mengupload gambar baru, cover lama tetap digunakan.'
            ];
            $image_query = "";
        }
    } else {
        // Jika tidak upload gambar baru, pakai gambar lama
        $image_query = "";
    }
    
    // Jalankan query update data ke database
    $query_update = "UPDATE products SET
                        name = '$name',
                        author = '$author',
                        description = '$description',
                        price = '$price',
                        stock = '$stock',
                        category_id = '$category_id'
                        $image_query
                     WHERE id = '$product_id' AND seller_id = '$seller_id'";
                     
    if (mysqli_query($db, $query_update)) {
        $_SESSION['toast'] = [
            'type' => 'success',
            'title' => 'Buku Diperbarui',
            'message' => 'Data buku berhasil diperbarui!'
        ];
        header("Location: manage_products.php");
        exit;
    } else {
        $toast = [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => 'Gagal memperbarui data buku. Silakan coba lagi.'
        ];
    }
}

// Ambil data kategori untuk pilihan dropdown
$categories_data = mysqli_query($db, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk Buku - Seller</title>
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

    <?php if ($toast) : ?>
    <div id="toast-notif" class="fixed top-5 right-5 z-50 bg-card w-full max-w-sm rounded-sm shadow-xl border border-ink/10 overflow-hidden show">
        <div class="p-5 flex items-start gap-3">
            <?php if ($toast['type'] === 'success') : ?>
                <div class="w-10 h-10 rounded-full bg-sage/10 border border-sage/30 flex items-center justify-center text-sage text-lg shrink-0">✓</div>
            <?php else : ?>
                <div class="w-10 h-10 rounded-full bg-maroon/10 border border-maroon/30 flex items-center justify-center text-maroon text-lg shrink-0">✕</div>
            <?php endif; ?>
            <div class="flex-1">
                <p class="font-serif font-bold text-ink text-sm mb-0.5"><?= htmlspecialchars($toast['title']); ?></p>
                <p class="text-ink/60 text-xs leading-relaxed"><?= htmlspecialchars($toast['message']); ?></p>
            </div>
            <button onclick="closeToast()" class="text-ink/30 hover:text-ink text-xs shrink-0">✕</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-ink text-paper w-64 min-h-screen p-5 space-y-6 shrink-0 border-r-4 border-brass">
        <h2 class="text-xl font-serif font-bold flex items-center gap-2"><span class="text-brass-light">📚</span> Seller Panel</h2>
        <nav class="space-y-2 flex flex-col text-sm">
            <a href="index.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Dashboard</a>
            <a href="manage_products.php" class="bg-maroon px-4 py-2.5 rounded-sm font-semibold">Kelola Produk Buku</a>
            <a href="validasi_penjual.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Validasi Pesanan</a>
            <a href="sales_history.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Riwayat Penjualan</a>
            <a href="read_notification.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Notifikasi</a>
            <a href="../auth/logout.php" class="text-brass-light/80 hover:bg-ink-light px-4 py-2.5 rounded-sm mt-10 transition">Logout</a>
        </nav>
    </div>

    <div class="flex-1 p-10">
        <div class="flex items-center gap-4 mb-6">
            <a href="manage_products.php" class="bg-card border border-ink/15 text-ink px-3 py-1.5 rounded-sm hover:bg-ink hover:text-paper font-semibold text-sm transition">⬅ Kembali</a>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-maroon">Ubah Data Buku</p>
                <h1 class="text-2xl font-serif font-bold text-ink">Edit Buku: <?= htmlspecialchars($product['name']); ?></h1>
            </div>
        </div>

        <div class="bg-card p-6 rounded-sm shadow-md border border-ink/5">
            <form action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-ink/70 font-semibold text-sm">Judul Buku</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']); ?>" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                </div>
                <div>
                    <label class="block text-ink/70 font-semibold text-sm">Kategori Buku</label>
                    <select name="category_id" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                        <?php while($cat = mysqli_fetch_assoc($categories_data)) : ?>
                            <option value="<?= $cat['id']; ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-ink/70 font-semibold text-sm">Penulis</label>
                    <input type="text" name="author" value="<?= htmlspecialchars($product['author'] ?? ''); ?>" class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                </div>
                <div>
                    <label class="block text-ink/70 font-semibold text-sm">Harga Jual (Rp)</label>
                    <input type="number" name="price" value="<?= $product['price']; ?>" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                </div>
                <div>
                    <label class="block text-ink/70 font-semibold text-sm">Stok Buku</label>
                    <input type="number" name="stock" value="<?= $product['stock']; ?>" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-ink/70 font-semibold text-sm">Ganti Cover Gambar (Biarkan kosong jika tidak ingin diubah)</label>
                    <input type="file" name="image" class="w-full border border-ink/20 p-2 rounded-sm mt-1 mb-2 bg-paper">
                    <p class="text-xs text-ink/40">Cover saat ini:</p>
                    <img src="../assets/uploads/<?= $product['image']; ?>" class="w-20 h-28 object-cover object-top rounded-sm shadow border border-ink/10 mt-1">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-ink/70 font-semibold text-sm">Deskripsi / Sinopsis Buku</label>
                    <textarea name="description" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 h-32 bg-paper focus:outline-none focus:border-maroon"><?= htmlspecialchars($product['description']); ?></textarea>
                </div>
                <button type="submit" name="update_buku" class="bg-ink text-paper font-bold py-3 px-4 rounded-sm hover:bg-maroon md:col-span-2 transition shadow uppercase tracking-wide text-sm">Simpan Perubahan Buku</button>
            </form>
        </div>
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