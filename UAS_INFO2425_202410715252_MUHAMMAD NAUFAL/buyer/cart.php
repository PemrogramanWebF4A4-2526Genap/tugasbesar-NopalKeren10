<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

// 1. LOGIKA UTAMA MODIFIKASI KERANJANG DI DATABASE
if (isset($_GET['action'])) {
    $product_id = mysqli_real_escape_string($db, $_GET['id']);

    if ($_GET['action'] == 'add') {
        // Cek apakah produk sudah ada di keranjang user ini
        $cek_cart = mysqli_query($db, "SELECT * FROM carts WHERE buyer_id = '$buyer_id' AND product_id = '$product_id'");
        
        if (mysqli_num_rows($cek_cart) > 0) {
            // Jika sudah ada, tambahkan quantity-nya
            mysqli_query($db, "UPDATE carts SET quantity = quantity + 1 WHERE buyer_id = '$buyer_id' AND product_id = '$product_id'");
        } else {
            // Jika belum ada, masukkan data baru
            mysqli_query($db, "INSERT INTO carts (buyer_id, product_id, quantity) VALUES ('$buyer_id', '$product_id', 1)");
        }
        
        // UPDATE: Kembalikan ke halaman asal (biar fetch JavaScript di book_details tidak bingung)
        if (isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: index.php");
        }
        exit;
    }

    if ($_GET['action'] == 'delete') {
        // Hapus item dari database
        mysqli_query($db, "DELETE FROM carts WHERE buyer_id = '$buyer_id' AND product_id = '$product_id'");
        header("Location: cart.php");
        exit;
    }
}

// 2. AJAX / BACKGROUND UPDATE QUANTITY DI DATABASE
if (isset($_POST['ajax_update'])) {
    $p_id = mysqli_real_escape_string($db, $_POST['product_id']);
    $qty  = intval($_POST['qty']);
    
    if ($qty <= 0) {
        mysqli_query($db, "DELETE FROM carts WHERE buyer_id = '$buyer_id' AND product_id = '$p_id'");
    } else {
        mysqli_query($db, "UPDATE carts SET quantity = '$qty' WHERE buyer_id = '$buyer_id' AND product_id = '$p_id'");
    }
    exit; // Menghentikan rendering HTML karena ini request latar belakang
}

// Ambil data keranjang dari database untuk dirender ke tabel HTML bawah
$result_cart = mysqli_query($db, "SELECT c.quantity, p.* FROM carts c 
                                  JOIN products p ON c.product_id = p.id 
                                  WHERE c.buyer_id = '$buyer_id'");
$cart_count = mysqli_num_rows($result_cart);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - BookStore</title>
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
            <a href="index.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition text-sm block">← Kembali Belanja</a>
        </div>
    </nav>

    <main class="container mx-auto py-10 px-4 max-w-4xl">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Rincian Belanja</p>
            <h1 class="text-3xl font-serif font-bold text-ink">Keranjang Belanja Anda</h1>
        </div>

        <?php if ($cart_count > 0) : ?>
            <div class="bg-card p-6 rounded-sm shadow-md border border-ink/5">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b-2 border-ink/10 text-ink/60 text-left text-xs uppercase tracking-widest font-bold">
                            <th class="pb-3">Buku</th>
                            <th class="pb-3">Harga</th>
                            <th class="pb-3 text-center">Jumlah (Qty)</th>
                            <th class="pb-3 text-right">Total</th>
                            <th class="pb-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grand_total = 0;
                        while ($item = mysqli_fetch_assoc($result_cart)) :
                            $product_id = $item['id'];
                            $qty = $item['quantity'];
                            $subtotal = $item['price'] * $qty;
                            $grand_total += $subtotal;
                        ?>
                        <tr class="border-b border-dashed border-ink/10 hover:bg-paper/60 cart-row" data-id="<?= $product_id; ?>" data-price="<?= $item['price']; ?>">
                            <td class="py-4 flex items-center space-x-4">
                                <a href="book_details.php?id=<?= $product_id; ?>" class="block hover:opacity-80 transition shrink-0">
                                    <img src="../assets/uploads/<?= $item['image']; ?>" class="w-12 h-16 object-cover rounded-sm shadow-sm border border-ink/10">
                                </a>
                                <a href="book_details.php?id=<?= $product_id; ?>" class="font-semibold text-ink hover:text-maroon transition line-clamp-2">
                                    <?= htmlspecialchars($item['name']); ?>
                                </a>
                            </td>
                            <td class="py-4 text-ink/70">Rp <?= number_format($item['price'], 0, ',', '.'); ?></td>
                            <td class="py-4 text-center">
                                <input type="number" value="<?= $qty; ?>" min="1" max="<?= $item['stock']; ?>"
                                       class="w-16 border border-ink/20 p-1 rounded-sm text-center font-semibold qty-input bg-paper focus:outline-none focus:border-maroon">
                            </td>
                            <td class="py-4 text-right font-serif font-bold text-ink item-subtotal">
                                Rp <?= number_format($subtotal, 0, ',', '.'); ?>
                            </td>
                            <td class="py-4 text-center">
                                <button onclick="showDeleteModal(<?= $product_id; ?>, '<?= htmlspecialchars($item['name']); ?>')" class="text-maroon hover:text-maroon-light font-bold text-sm">Hapus</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <?php
                // Calculate shipping cost (Rp 15.000 per order)
                $shipping_cost = ($grand_total > 0) ? 15000 : 0;
                $final_total = $grand_total + $shipping_cost;
                ?>

                <div class="mt-6 flex flex-col items-end space-y-4">
                    <div class="space-y-2 text-right">
                        <div class="text-sm text-ink/70">
                            Subtotal Buku: <span class="font-semibold text-ink">Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
                        </div>
                        <div class="text-sm text-ink/70">
                            Ongkos Kirim: <span class="font-semibold text-ink">Rp <?= number_format($shipping_cost, 0, ',', '.'); ?></span>
                        </div>
                        <div class="text-xl font-serif font-bold text-ink pt-2 border-t border-ink/20">
                            Total Pembayaran: <span id="grand-total" class="text-maroon">Rp <?= number_format($final_total, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div>
                        <a href="checkout.php" class="inline-block bg-ink text-paper font-bold py-2.5 px-6 rounded-sm hover:bg-maroon transition uppercase tracking-wide text-sm">Lanjut ke Checkout →</a>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="bg-card p-10 rounded-sm shadow-md text-center border border-ink/5">
                <p class="text-ink/50 italic font-serif mb-4">Keranjang belanja kamu masih kosong nih.</p>
                <a href="index.php" class="bg-ink text-paper font-bold py-2.5 px-6 rounded-sm hover:bg-maroon transition uppercase tracking-wide text-sm">Mulai Cari Buku</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-ink/50 z-50 hidden items-center justify-center">
        <div class="bg-card rounded-sm shadow-xl border border-ink/10 p-6 max-w-sm w-full mx-4">
            <h3 class="text-lg font-serif font-bold text-ink mb-2">Hapus Buku dari Keranjang?</h3>
            <p class="text-sm text-ink/70 mb-4">Apakah Anda yakin ingin menghapus "<span id="deleteBookName" class="font-semibold text-ink"></span>" dari keranjang belanja?</p>
            <div class="flex gap-3 justify-end">
                <button onclick="closeDeleteModal()" class="px-4 py-2 border border-ink/20 rounded-sm text-ink/70 hover:bg-ink/5 transition text-sm">Batal</button>
                <a id="confirmDeleteBtn" href="" class="px-4 py-2 bg-maroon text-paper rounded-sm hover:bg-maroon-light transition text-sm font-semibold">Ya, Hapus</a>
            </div>
        </div>
    </div>

   <script>
    function showDeleteModal(productId, bookName) {
        document.getElementById('deleteBookName').textContent = bookName;
        document.getElementById('confirmDeleteBtn').href = 'cart.php?action=delete&id=' + productId;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteModal').classList.remove('flex');
    }

    document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', function() { // Menggunakan 'change' agar validasi maksimal berjalan pasca ketik
        const row = this.closest('.cart-row');
        const productId = row.getAttribute('data-id');
        const price = parseFloat(row.getAttribute('data-price'));
        const maxStock = parseInt(this.getAttribute('max')); // Mengambil batas stok maks dari atribut HTML
        let qty = parseInt(this.value);

        // Validasi batas bawah
        if (isNaN(qty) || qty < 1) {
            qty = 1;
            this.value = 1;
        }

        // --- VALIDASI STOK (NOMOR 1) ---
        if (qty > maxStock) {
            alert(`Maaf, stok tidak mencukupi. Sisa stok buku ini hanya ${maxStock} pcs.`);
            qty = maxStock;
            this.value = maxStock; // Paksa input di layar kembali ke angka stok maksimal
        }

        // 1. Hitung Subtotal di Layar
        const subtotal = price * qty;
        row.querySelector('.item-subtotal').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');

        // 2. Hitung Grand Total di Layar
        let currentGrandTotal = 0;
        document.querySelectorAll('.cart-row').forEach(r => {
            const rPrice = parseFloat(r.getAttribute('data-price'));
            const rQty = parseInt(r.querySelector('.qty-input').value) || 1;
            currentGrandTotal += (rPrice * rQty);
        });
        document.getElementById('grand-total').innerText = 'Rp ' + currentGrandTotal.toLocaleString('id-ID');

        // 3. Update Database via AJAX Latar Belakang
        const formData = new FormData();
        formData.append('ajax_update', true);
        formData.append('product_id', productId);
        formData.append('qty', qty);

        fetch('cart.php', {
            method: 'POST',
            body: formData
        });
    });
});
    </script>
    
    <?php include '../components/buyer_footer.php'; ?>

</body>
</html>