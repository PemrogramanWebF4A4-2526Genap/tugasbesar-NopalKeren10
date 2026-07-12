<?php
session_start();
include '../config/database.php';
require_once '../config/notification_helper.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

// Ambil data keranjang dari database
$result_cart = mysqli_query($db, "SELECT c.quantity, p.* FROM carts c 
                                  JOIN products p ON c.product_id = p.id 
                                  WHERE c.buyer_id = '$buyer_id'");

// Cek apakah keranjang kosong di database
if (isset($_POST['bayar']) && mysqli_num_rows($result_cart) > 0) {
    $shipping_fee = 15000; // Ongkir per toko
    $address      = mysqli_real_escape_string($db, $_POST['address']);
    
    // Ambil data POST dulu, baru bersihkan
    $phone_input  = isset($_POST['phone']) ? $_POST['phone'] : '';
    $phone        = preg_replace('/[^0-9]/', '', $phone_input);
    $phone        = mysqli_real_escape_string($db, $phone);
    
    // 1. Kelompokkan produk berdasarkan seller_id terlebih dahulu
    $items_by_seller = [];
    while ($item = mysqli_fetch_assoc($result_cart)) {
        $seller_id = $item['seller_id'];
        $items_by_seller[$seller_id][] = $item;
    }

    // 2. Proses Upload Gambar Bukti Transfer
    $filename = $_FILES['proof']['name'];
    $tmp_name = $_FILES['proof']['tmp_name'];
    
    if (!empty($filename)) {
        $new_filename = 'PROOF-' . uniqid() . '-' . $filename;
        $folder_tujuan = '../assets/uploads/' . $new_filename;
        move_uploaded_file($tmp_name, $folder_tujuan);
    } else {
        $new_filename = 'no_image.png';
    }

    // Array untuk menampung order_id yang sukses dibuat
    $created_orders = [];

    // 3. Loop per Penjual untuk SPLIT ORDER
    foreach ($items_by_seller as $seller_id => $items) {
        
        // Hitung total harga buku khusus untuk penjual ini
        $total_price_seller = 0;
        foreach ($items as $item) {
            $total_price_seller += ($item['price'] * $item['quantity']);
        }
        $grand_total_seller = $total_price_seller + $shipping_fee;

        // PERBAIKAN: Menggunakan 'paid' agar sesuai dengan ENUM database (menggantikan 'diproses')
        $query_order = "INSERT INTO orders (buyer_id, seller_id, total_amount, status, address, phone) 
                        VALUES ('$buyer_id', '$seller_id', '$grand_total_seller', 'paid', '$address', '$phone')";
        
        if (mysqli_query($db, $query_order)) {
            $order_id = mysqli_insert_id($db);
            $created_orders[] = $order_id; // catat order id-nya

            // Insert ke tabel order_items & potong stok produk untuk penjual ini
            foreach ($items as $item) {
                $product_id = $item['id'];
                $qty        = $item['quantity'];
                $price      = $item['price'];

                $query_item = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                               VALUES ('$order_id', '$product_id', '$qty', '$price')";
                mysqli_query($db, $query_item);

                // Potong stok produk otomatis
                mysqli_query($db, "UPDATE products SET stock = stock - $qty WHERE id = '$product_id'");
            }

            // Create notification for seller about new order
            // Get buyer name
            $query_buyer_name = mysqli_query($db, "SELECT full_name FROM users WHERE id = '$buyer_id'");
            $buyer_data = mysqli_fetch_assoc($query_buyer_name);
            $buyer_name = $buyer_data['full_name'];

            createNotification(
                $seller_id,
                'seller',
                'Pesanan Baru Masuk',
                $buyer_name . ' telah melakukan pesanan baru #' . $order_id . '. Silakan validasi pembayaran.',
                'payment'
            );
        } else {
            echo "Gagal memproses split order untuk Seller ID $seller_id: " . mysqli_error($db);
            exit;
        }
    }

    // 4. Catat Bukti Transfer ke Semua Order ID yang Terbuat
    foreach ($created_orders as $order_id) {
        $query_payment = "INSERT INTO payments (order_id, proof, status) 
                          VALUES ('$order_id', '$new_filename', 'unverified')";
        mysqli_query($db, $query_payment);
    }

    // 5. Bersihkan isi keranjang pembeli
    mysqli_query($db, "DELETE FROM carts WHERE buyer_id = '$buyer_id'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - BookStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#161F2B',
                        paper: '#F5EFE2',
                        card: '#FFFDF7',
                        brass: '#B4823A',
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
    <script>
        setTimeout(function() {
            window.location.href = 'track_order.php';
        }, 3500);
    </script>
</head>
<body class="bg-paper min-h-screen flex items-center justify-center p-4 font-sans text-ink">

    <div class="bg-card max-w-md w-full rounded-sm shadow-lg border border-ink/10 p-8 text-center animate-fade-in">
        
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-sage/10 text-sage border-2 border-sage/30 mb-6">
            <svg class="h-10 w-10 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-serif font-black text-ink mb-2">Pembayaran Berhasil!</h1>
        <p class="text-sm text-ink/70 leading-relaxed mb-6">
            Pesanan Anda telah otomatis dipisahkan sesuai toko penjual masing-masing. Terima kasih sudah berbelanja!
        </p>

        <div class="w-full bg-paper h-1.5 rounded-full overflow-hidden mb-6">
            <div class="bg-sage h-full rounded-full w-full animate-[pulse_1.5s_infinite]"></div>
        </div>

        <a href="track_order.php" class="inline-block w-full bg-ink hover:bg-sage text-paper text-xs font-bold py-3 px-4 rounded-sm uppercase tracking-wider transition">
            Lihat Riwayat Pesanan Sekarang →
        </a>
        
        <p class="text-[11px] text-ink/40 mt-3 font-mono">
            Mengalihkan halaman secara otomatis...
        </p>
    </div>

</body>
</html>
<?php
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>