<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

// Get buyer profile data for auto-fill
$query_buyer = mysqli_query($db, "SELECT * FROM users WHERE id = '$buyer_id'");
$buyer_data = mysqli_fetch_assoc($query_buyer);

// Ambil data keranjang belanja pembeli dari database
$result_checkout = mysqli_query($db, "SELECT c.quantity, p.* FROM carts c 
                                     JOIN products p ON c.product_id = p.id 
                                     WHERE c.buyer_id = '$buyer_id'");

// Jika keranjang di database kosong, tendang balik ke beranda pembeli
if (mysqli_num_rows($result_checkout) === 0) {
    header("Location: index.php");
    exit;
}

$grand_total = 0;
$shipping_fee = 15000; // Tarif Ongkir Statis
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - BookStore</title>
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
        <div class="container mx-auto">
            <a href="cart.php" class="text-2xl font-serif font-bold tracking-wide flex items-center gap-2">
                <span class="text-brass-light">📚</span> BookStore Checkout
            </a>
        </div>
    </nav>

    <main class="container mx-auto py-10 px-4 max-w-4xl grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="md:col-span-2 space-y-6">
            <div class="bg-card p-6 rounded-sm shadow-md border border-ink/5">
                <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Langkah Terakhir</p>
                <h2 class="text-xl font-serif font-bold mb-4 text-ink">Form Pengiriman & Pembayaran</h2>
                
                <form action="proses_pembayaran.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-ink/70 font-semibold text-sm">Alamat Lengkap Pengiriman</label>
                        <textarea name="address" required placeholder="Tuliskan alamat lengkap pengiriman rumah kamu..." class="w-full border border-ink/20 p-2 rounded-sm mt-1 h-20 bg-paper focus:outline-none focus:border-maroon"><?= htmlspecialchars($buyer_data['address'] ?? ''); ?></textarea>
                    </div>

                   <div>
                        <label class="block text-ink/70 font-semibold text-sm">Nomor Telepon / WhatsApp</label>
                        <input type="text"
                            name="phone"
                            required
                            inputmode="numeric"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                            placeholder="Contoh: 081234567xxx"
                            value="<?= htmlspecialchars($buyer_data['phone'] ?? ''); ?>"
                            class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper focus:outline-none focus:border-maroon">
                        <p class="text-xs text-ink/40 mt-1">*Hanya boleh diisi angka tanpa spasi atau huruf.</p>
                    </div>
                    
                    <div class="bg-brass-light/15 p-4 rounded-sm border border-brass/40">
                        <h4 class="font-serif font-bold text-ink mb-1">Informasi Rekening Toko:</h4>
                        <p class="text-sm text-ink/70">Bank BCA: <strong class="text-ink">123-456-7890</strong> a.n BookStore Ecommerce</p>
                        <p class="text-sm text-ink/70">Bank Mandiri: <strong class="text-ink">729-026-8720</strong> a.n BookStore Ecommerce</p>
                    </div>

                    <div>
                        <label class="block text-ink/70 font-semibold text-sm">Upload Bukti Pembayaran (.jpg / .png)</label>
                        <input type="file" name="proof" required class="w-full border border-ink/20 p-2 rounded-sm mt-1 bg-paper">
                    </div>

                    <button type="submit" name="bayar" class="w-full bg-ink text-paper font-bold py-3 rounded-sm shadow hover:bg-maroon transition uppercase tracking-wide text-sm">
                        Konfirmasi & Selesaikan Pesanan
                    </button>

                    <a href="cart.php" class="block text-center w-full bg-paper border border-ink/15 text-ink/70 font-semibold py-3 rounded-sm hover:bg-ink/5 transition mt-3">
                        Batal & Kembali ke Keranjang
                    </a>
                </form>
            </div>
        </div>

        <div class="bg-card p-6 rounded-sm shadow-md h-fit border border-ink/5">
            <h2 class="text-xl font-serif font-bold mb-4 text-ink">Rincian Tagihan</h2>
            <div class="divide-y divide-dashed divide-ink/10 space-y-3">
                <?php 
                while ($buku = mysqli_fetch_assoc($result_checkout)) : 
                    $qty = $buku['quantity'];
                    $subtotal = $buku['price'] * $qty;
                    $grand_total += $subtotal;
                ?>
                <div class="flex justify-between pt-3 text-sm text-ink/70">
                    <div>
                        <p class="font-semibold text-ink"><?= $buku['name']; ?></p>
                        <p class="text-xs">Jumlah: <?= $qty; ?>x</p>
                    </div>
                    <span>Rp <?= number_format($subtotal, 0, ',', '.'); ?></span>
                </div>
                <?php endwhile; ?>

                <div class="flex justify-between pt-3 text-sm text-ink/70">
                    <span>Total Harga Buku</span>
                    <span>Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
                </div>
                <div class="flex justify-between pt-3 text-sm text-ink/70">
                    <span>Ongkos Kirim (Flat)</span>
                    <span>Rp <?= number_format($shipping_fee, 0, ',', '.'); ?></span>
                </div>
                <div class="flex justify-between pt-3 text-lg font-serif font-bold text-ink">
                    <span>Total Bayar</span>
                    <span class="text-maroon">Rp <?= number_format($grand_total + $shipping_fee, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/buyer_footer.php'; ?>

</body>
</html>