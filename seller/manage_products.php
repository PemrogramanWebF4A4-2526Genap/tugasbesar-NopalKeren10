<?php
session_start();
include '../config/database.php';
require_once '../config/notification_helper.php';

// Proteksi: Hanya boleh diakses oleh Seller yang sudah login
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../auth/login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$_SESSION['user_type'] = 'seller';

// Get unread notification count
$unread_notifications = getUnreadNotificationCount($seller_id, 'seller');

// --- PROSES HAPUS PRODUK BUKU ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $del_id = mysqli_real_escape_string($db, $_GET['id']);
    
    // Ambil info nama file gambar dulu sebelum datanya dihapus
    $check_img = mysqli_query($db, "SELECT image FROM products WHERE id = '$del_id' AND seller_id = '$seller_id'");
    $data_img = mysqli_fetch_assoc($check_img);
    
    if ($data_img) {
        $nama_file_gambar = $data_img['image'];
        
        // Hapus data dari database
        $query_delete = mysqli_query($db, "DELETE FROM products WHERE id = '$del_id' AND seller_id = '$seller_id'");
        
        if ($query_delete) {
            // Hapus file gambarnya dari folder assets/uploads
            if (file_exists('../assets/uploads/' . $nama_file_gambar)) {
                unlink('../assets/uploads/' . $nama_file_gambar);
            }
            $_SESSION['toast'] = [
                'type' => 'success', 
                'title' => 'Buku Dihapus',
                'message' => 'Buku berhasil dihapus dari katalog!'
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'error', 
                'title' => 'Gagal Menghapus',
                'message' => 'Terjadi kesalahan, gagal menghapus produk.'
            ];
        }
    }
    header("Location: manage_products.php");
    exit;
}

// PROSES UPLOAD BUKU ASLI DARI SELLER
if (isset($_POST['tambah_buku'])) {
    $name        = mysqli_real_escape_string($db, $_POST['name']);
    $author      = mysqli_real_escape_string($db, $_POST['author']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $price       = $_POST['price'];
    $stock       = $_POST['stock'];
    $category_id = $_POST['category_id']; // Mengambil kategori pilihan penjual

    // Fitur Upload Gambar Cover Buku ke folder assets/uploads/
    $filename = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    $new_filename = uniqid() . '-' . $filename;
    
    $folder_tujuan = '../assets/uploads/' . $new_filename;

    if (move_uploaded_file($tmp_name, $folder_tujuan)) {
        $query = "INSERT INTO products (seller_id, name, author, description, price, stock, category_id, image)
                  VALUES ('$seller_id', '$name', '$author', '$description', '$price', '$stock', '$category_id', '$new_filename')";
        
        if (mysqli_query($db, $query)) {
            $_SESSION['toast'] = [
                'type' => 'success', 
                'title' => 'Upload Berhasil',
                'message' => 'Buku asli berhasil diupload ke katalog jualanmu!'
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'error', 
                'title' => 'Gagal Menyimpan',
                'message' => 'Gagal memasukkan data produk ke database.'
            ];
        }
    } else {
        $_SESSION['toast'] = [
            'type' => 'error', 
            'title' => 'Gagal Upload',
            'message' => 'Gagal mengupload gambar cover ke server.'
        ];
    }
    header("Location: manage_products.php");
    exit;
}

// Ambil data kategori untuk pilihan dropdown di form
$categories_data = mysqli_query($db, "SELECT * FROM categories");

// Ambil semua daftar buku asli milik penjual ini saja
$my_products = mysqli_query($db, "SELECT * FROM products WHERE seller_id = '$seller_id' ORDER BY id DESC");

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
    <title>Kelola Produk Buku - Seller</title>
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
        /* Style Animasi Keren untuk Toast & Modal */
        #toast-notif {
            transform: translateX(24px) translateY(-8px);
            opacity: 0;
            transition: transform 0.35s ease, opacity 0.35s ease;
        }
        #toast-notif.show {
            transform: translateX(0) translateY(0);
            opacity: 1;
        }
        #delete-modal {
            transition: opacity 0.2s ease;
        }
        #delete-modal.show {
            opacity: 1;
            pointer-events: auto;
        }
    </style>
</head>
<body class="bg-paper flex min-h-screen font-sans text-ink">

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

    <div class="bg-ink text-paper w-64 min-h-screen p-5 space-y-6 shrink-0 border-r-4 border-brass">
        <h2 class="text-xl font-serif font-bold flex items-center gap-2"><span class="text-brass-light">📚</span> Seller Panel</h2>
        <nav class="space-y-2 flex flex-col text-sm">
            <a href="index.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Dashboard</a>
            <a href="manage_products.php" class="bg-maroon px-4 py-2.5 rounded-sm font-semibold">Kelola Produk Buku</a>
            <a href="validasi_penjual.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Validasi Pesanan</a>
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

    <div class="flex-1 p-10">
        <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Manajemen Katalog</p>
        <h1 class="text-3xl font-serif font-bold text-ink mb-6">Kelola Produk Buku Kamu</h1>

        <div class="bg-card p-6 rounded-sm shadow-md mb-10 border border-ink/5">
            <h2 class="text-xl font-serif font-bold mb-4 text-ink">Tambah Buku Baru (Data Asli)</h2>
            <form action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-ink/70 text-sm font-semibold">Judul Buku</label>
                    <input type="text" name="name" placeholder="Masukkan judul buku asli..." required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                </div>
                <div>
                    <label class="block text-ink/70 text-sm font-semibold">Kategori Buku</label>
                    <select name="category_id" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                        <option value="">-- Pilih Kategori --</option>
                        <?php while($cat = mysqli_fetch_assoc($categories_data)) : ?>
                            <option value="<?= $cat['id']; ?>"><?= $cat['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-ink/70 text-sm font-semibold">Penulis</label>
                    <input type="text" name="author" placeholder="Contoh: Andrea Hirata" class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                </div>
                <div>
                    <label class="block text-ink/70 text-sm font-semibold">Harga Jual (Rp)</label>
                    <input type="number" name="price" placeholder="Contoh: 85000" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                </div>
                <div>
                    <label class="block text-ink/70 text-sm font-semibold">Stok Buku</label>
                    <input type="number" name="stock" placeholder="Jumlah ready stock" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-ink/70 text-sm font-semibold">Cover Gambar (.jpg / .png)</label>
                    <input type="file" name="image" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-ink/70 text-sm font-semibold">Deskripsi / Sinopsis Buku</label>
                    <textarea name="description" placeholder="Tulis sinopsis singkat buku di sini..." required class="w-full border border-ink/20 p-2 rounded-sm mt-1 h-24 bg-paper focus:outline-none focus:border-maroon"></textarea>
                </div>
                <button type="submit" name="tambah_buku" class="bg-ink text-paper font-bold py-3 px-4 rounded-sm hover:bg-maroon md:col-span-2 transition uppercase tracking-wide text-sm">Simpan & Upload Buku</button>
            </form>
        </div>

        <div class="bg-card p-6 rounded-sm shadow-md border border-ink/5">
            <h2 class="text-xl font-serif font-bold mb-4 text-ink">Katalog Buku Anda</h2>
            <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-paper text-ink/60 text-xs uppercase tracking-widest font-bold">
                        <th class="border border-ink/10 p-3 text-left">Cover</th>
                        <th class="border border-ink/10 p-3 text-left">Judul</th>
                        <th class="border border-ink/10 p-3 text-left">Harga</th>
                        <th class="border border-ink/10 p-3 text-left">Stok</th>
                        <th class="border border-ink/10 p-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($my_products) > 0) : ?>
                        <?php while($row = mysqli_fetch_assoc($my_products)) : ?>
                        <tr class="hover:bg-paper/60 transition">
                            <td class="border border-ink/10 p-3">
                                <img src="../assets/uploads/<?= $row['image']; ?>" class="w-16 h-20 object-cover object-top rounded-sm shadow border border-ink/10">
                            </td>
                            <td class="border border-ink/10 p-3 font-serif font-semibold text-ink"><?= $row['name']; ?></td>
                            <td class="border border-ink/10 p-3 text-ink/70">Rp <?= number_format($row['price'], 0, ',', '.'); ?></td>
                            <td class="border border-ink/10 p-3 text-ink/70"><?= $row['stock']; ?> pcs</td>
                            <td class="border border-ink/10 p-3 space-y-1.5">
                                <a href="edit_product.php?id=<?= $row['id']; ?>" class="bg-brass text-ink px-3 py-1.5 rounded-sm text-xs font-bold hover:bg-brass-light block text-center transition uppercase tracking-wide">
                                    Edit
                                </a>
                                <button type="button" onclick="openDeleteModal(<?= $row['id']; ?>)" 
                                   class="bg-maroon text-paper w-full px-3 py-1.5 rounded-sm text-xs font-bold hover:bg-maroon-light block text-center transition uppercase tracking-wide cursor-pointer">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" class="text-center p-6 text-ink/40 italic font-serif border border-ink/10">Kamu belum menambahkan buku untuk dijual.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div id="delete-modal" class="fixed inset-0 z-[100] opacity-0 pointer-events-none flex items-center justify-center">
        <div class="absolute inset-0 bg-ink/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
        <div class="bg-card w-full max-w-md p-6 rounded-sm shadow-2xl relative z-10 border border-ink/10 mx-4 transform transition-transform scale-95" id="delete-modal-box">
            <div class="w-12 h-12 rounded-full bg-maroon/10 border border-maroon/30 flex items-center justify-center text-maroon text-2xl font-bold mb-4">
                !
            </div>
            <h3 class="text-xl font-serif font-bold text-ink mb-2">Hapus Buku Ini?</h3>
            <p class="text-ink/70 text-sm mb-6">Apakah Anda yakin ingin menghapus buku ini secara permanen dari katalog? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex gap-3 justify-end">
                <button onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-bold text-ink/70 hover:text-ink transition">
                    Batal
                </button>
                <a id="confirm-delete-btn" href="#" class="bg-maroon hover:bg-maroon-light text-paper px-5 py-2 rounded-sm text-sm font-bold transition">
                    Ya, Hapus Buku
                </a>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk menutup Toast Notifikasi
        function closeToast() {
            var toast = document.getElementById('toast-notif');
            if (toast) { 
                toast.classList.remove('show');
                setTimeout(() => toast.style.display = 'none', 350); 
            }
        }
        // Otomatis menutup toast setelah 4 detik
        setTimeout(function() { closeToast(); }, 4000);

        // Fungsi untuk membuka Custom Modal Hapus
        function openDeleteModal(bookId) {
            var modal = document.getElementById('delete-modal');
            var modalBox = document.getElementById('delete-modal-box');
            var confirmBtn = document.getElementById('confirm-delete-btn');
            
            // Masukkan link href ke tombol "Ya, Hapus Buku"
            confirmBtn.href = 'manage_products.php?action=delete&id=' + bookId;
            
            // Tampilkan animasi masuk modal
            modal.classList.add('show');
            modalBox.classList.remove('scale-95');
            modalBox.classList.add('scale-100');
        }

        // Fungsi untuk menutup Custom Modal Hapus
        function closeDeleteModal() {
            var modal = document.getElementById('delete-modal');
            var modalBox = document.getElementById('delete-modal-box');
            
            // Tampilkan animasi keluar modal
            modalBox.classList.remove('scale-100');
            modalBox.classList.add('scale-95');
            modal.classList.remove('show');
        }
    </script>


</body>
</html>