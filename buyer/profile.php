<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../config/database.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../auth/login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($db, $_POST['full_name']);
    $phone = mysqli_real_escape_string($db, $_POST['phone']);
    $address = mysqli_real_escape_string($db, $_POST['address']);

    // Check if columns exist first
    $check_columns = mysqli_query($db, "SHOW COLUMNS FROM users LIKE 'full_name'");
    if (mysqli_num_rows($check_columns) == 0) {
        // Add missing columns if they don't exist
        mysqli_query($db, "ALTER TABLE users ADD COLUMN full_name VARCHAR(100) AFTER password");
        mysqli_query($db, "ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER full_name");
        mysqli_query($db, "ALTER TABLE users ADD COLUMN address TEXT AFTER phone");
    }

    $update_query = "UPDATE users SET full_name = '$full_name', phone = '$phone', address = '$address' WHERE id = '$buyer_id'";

    if (mysqli_query($db, $update_query)) {
        $_SESSION['toast'] = [
            'type' => 'success',
            'title' => 'Profil Diperbarui',
            'message' => 'Profil Anda berhasil diperbarui.'
        ];
    } else {
        $_SESSION['toast'] = [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => 'Terjadi kesalahan: ' . mysqli_error($db)
        ];
    }

    header("Location: profile.php");
    exit;
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current password from database
    $query = mysqli_query($db, "SELECT password FROM users WHERE id = '$buyer_id'");
    $user = mysqli_fetch_assoc($query);
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$buyer_id'";
            
            if (mysqli_query($db, $update_query)) {
                $_SESSION['toast'] = [
                    'type' => 'success',
                    'title' => 'Password Diubah',
                    'message' => 'Password Anda berhasil diubah.'
                ];
            } else {
                $_SESSION['toast'] = [
                    'type' => 'error',
                    'title' => 'Gagal',
                    'message' => 'Terjadi kesalahan saat mengubah password.'
                ];
            }
        } else {
            $_SESSION['toast'] = [
                'type' => 'error',
                'title' => 'Password Tidak Cocok',
                'message' => 'Password baru dan konfirmasi password tidak sama.'
            ];
        }
    } else {
        $_SESSION['toast'] = [
            'type' => 'error',
            'title' => 'Password Salah',
            'message' => 'Password saat ini tidak benar.'
        ];
    }
    
    header("Location: profile.php");
    exit;
}

// Get buyer data
$query = mysqli_query($db, "SELECT * FROM users WHERE id = '$buyer_id'");
$buyer = mysqli_fetch_assoc($query);

// Ensure fields exist with fallbacks
$buyer['username'] = $buyer['username'] ?? '';
$buyer['email'] = $buyer['email'] ?? '';
$buyer['full_name'] = $buyer['full_name'] ?? $buyer['username'] ?? 'User';
$buyer['phone'] = $buyer['phone'] ?? '';
$buyer['address'] = $buyer['address'] ?? '';

$nama_pengguna = $buyer['full_name'];

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
    <title>Profil Saya - BookStore</title>
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
<body class="bg-paper min-h-screen font-sans text-ink">

    <nav class="bg-ink text-paper p-4 shadow-lg border-b-4 border-brass">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-serif font-bold tracking-wide flex items-center gap-2">
                <span class="text-brass-light">📚</span> BookStore
            </a>
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    <a href="profile.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-2 py-2 rounded-sm font-semibold transition text-sm" title="Profil">
                    👤
                    </a>
                </div>
                <a href="cart.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition text-sm">
                    🛒 Keranjang
                </a>
                <a href="track_order.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition text-sm">
                    Pesanan Saya
                </a>
                <a href="index.php" class="border border-brass/60 hover:bg-brass hover:text-ink px-4 py-2 rounded-sm font-semibold transition text-sm">← Kembali Belanja</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto py-8 px-4">
        
        <?php if ($toast) : ?>
        <div id="toast-notif" class="fixed top-5 right-5 z-50 bg-card w-full max-w-sm rounded-sm shadow-xl border border-ink/10 overflow-hidden show">
            <div class="p-5 flex items-start gap-3">
                <div class="w-10 h-10 rounded-full <?= $toast['type'] == 'success' ? 'bg-sage/10 border-sage/30 text-sage' : 'bg-maroon/10 border-maroon/30 text-maroon'; ?> border flex items-center justify-center text-lg shrink-0">
                    <?= $toast['type'] == 'success' ? '✓' : '✕'; ?>
                </div>
                <div class="flex-1">
                    <p class="font-serif font-bold text-ink text-sm mb-0.5"><?= htmlspecialchars($toast['title']); ?></p>
                    <p class="text-ink/60 text-xs leading-relaxed"><?= htmlspecialchars($toast['message']); ?></p>
                </div>
                <button onclick="closeToast()" class="text-ink/30 hover:text-ink text-xs shrink-0">✕</button>
            </div>
        </div>
        <?php endif; ?>

        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1">Akun Saya</p>
            <h1 class="text-3xl font-serif font-bold text-ink">Profil Pembeli</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Card -->
            <div class="lg:col-span-1">
                <div class="bg-card rounded-sm shadow-md border border-ink/5 p-6 text-center">
                    <div class="w-24 h-24 bg-ink/10 rounded-full mx-auto mb-4 flex items-center justify-center text-4xl">
                        👤
                    </div>
                    <h2 class="text-xl font-serif font-bold text-ink mb-1"><?= htmlspecialchars($buyer['full_name']); ?></h2>
                    <p class="text-sm text-ink/50 mb-4"><?= htmlspecialchars($buyer['email']); ?></p>
                    <div class="inline-block bg-sage/10 border border-sage/30 text-sage text-xs font-bold px-3 py-1 rounded-full">
                        Buyer
                    </div>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="lg:col-span-2">
                <div class="bg-card rounded-sm shadow-md border border-ink/5 p-6 mb-6">
                    <h3 class="text-lg font-serif font-bold text-ink mb-4 flex items-center gap-2">
                        <span>✏️</span> Edit Profil
                    </h3>
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-ink mb-1">Email</label>
                            <input type="email" value="<?= htmlspecialchars($buyer['email']); ?>"disabled class="w-full bg-paper/50 border border-ink/20 rounded-sm px-3 py-2 text-ink/50 cursor-not-allowed">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-ink mb-1">Nama Lengkap</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($buyer['full_name']); ?>" required class="w-full bg-paper border border-ink/20 rounded-sm px-3 py-2 text-ink focus:outline-none focus:border-brass">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-ink mb-1">No. Telepon</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($buyer['phone'] ?? ''); ?>" class="w-full bg-paper border border-ink/20 rounded-sm px-3 py-2 text-ink focus:outline-none focus:border-brass">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-ink mb-1">Alamat</label>
                            <textarea name="address" rows="3" class="w-full bg-paper border border-ink/20 rounded-sm px-3 py-2 text-ink focus:outline-none focus:border-brass"><?= htmlspecialchars($buyer['address'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="bg-ink hover:bg-maroon text-paper font-bold py-2 px-4 rounded-sm transition text-sm">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Change Password Form -->
                <div class="bg-card rounded-sm shadow-md border border-ink/5 p-6">
                    <h3 class="text-lg font-serif font-bold text-ink mb-4 flex items-center gap-2">
                        <span>🔒</span> Ubah Password
                    </h3>
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-ink mb-1">Password Saat Ini</label>
                            <input type="password" name="current_password" required class="w-full bg-paper border border-ink/20 rounded-sm px-3 py-2 text-ink focus:outline-none focus:border-brass">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1">Password Baru</label>
                                <input type="password" name="new_password" required class="w-full bg-paper border border-ink/20 rounded-sm px-3 py-2 text-ink focus:outline-none focus:border-brass">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink mb-1">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" required class="w-full bg-paper border border-ink/20 rounded-sm px-3 py-2 text-ink focus:outline-none focus:border-brass">
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="bg-maroon hover:bg-maroon-light text-paper font-bold py-2 px-4 rounded-sm transition text-sm">
                            Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-ink text-paper py-6 mt-12 border-t-4 border-brass">
        <div class="container mx-auto text-center">
            <p class="text-sm text-ink/60">© 2024 BookStore. Toko Buku Online Terpercaya.</p>
        </div>
    </footer>

    <script>
        function closeToast() {
            var toast = document.getElementById('toast-notif');
            if (toast) { toast.style.display = 'none'; }
        }
        setTimeout(function() { closeToast(); }, 4000);
    </script>

</body>
</html>
