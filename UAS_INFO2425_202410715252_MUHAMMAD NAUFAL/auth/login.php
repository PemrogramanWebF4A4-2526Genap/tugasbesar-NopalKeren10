<?php
// 1. Wajib jalankan session di paling atas sebelum include apapun
session_start();
include '../config/database.php';

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($db, trim($_POST['email']));
    $password = $_POST['password'];

    // 2. Cari user berdasarkan email
    $query  = mysqli_query($db, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);

        // 3. Verifikasi password dengan hash Bcrypt di database
        if (password_verify($password, $row['password'])) {
            
            // --- PROTEKSI BARU: CEK VERIFIKASI KHUSUS SELLER ---
            if ($row['role'] === 'seller' && $row['is_verified'] == 0) {
                echo "<script>alert('Akun Toko Anda sedang dalam proses verifikasi oleh Admin. Harap tunggu!'); window.location='login.php';</script>";
                exit;
            }
            
            // Bersihkan sisa session lama (buyer/seller) agar tidak tabrakan
            session_unset();

            // 4. Set ulang session resmi untuk user
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            // 5. Lempar ke halaman sesuai role masing-masing
            if ($row['role'] === 'admin') {
                header("Location: ../admin/index.php");
            } elseif ($row['role'] === 'seller') {
                header("Location: ../seller/index.php");
            } else {
                header("Location: ../buyer/index.php");
            }
            exit;
        }
    }
    
    // Jika gagal, munculkan alert
    echo "<script>alert('Email atau Password salah!');</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BookStore</title>
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
        }
        .ink-panel {
            background-color: #161F2B;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(180,130,58,0.12) 0%, transparent 45%),
                radial-gradient(circle at 80% 75%, rgba(123,42,50,0.18) 0%, transparent 50%);
        }
        .paper-panel {
            background-color: #F5EFE2;
            background-image: radial-gradient(#00000008 1px, transparent 1px);
            background-size: 22px 22px;
        }
        .quote-mark {
            font-family: 'Fraunces', serif;
            line-height: 0.6;
        }
    </style>
</head>
<body class="min-h-screen font-sans text-ink">
    <div class="min-h-screen grid grid-cols-1 md:grid-cols-2">

        <div class="ink-panel hidden md:flex flex-col justify-between p-12 text-paper">
            <a href="../buyer/index.php" class="text-2xl font-serif font-bold tracking-wide flex items-center gap-2">
                <span class="text-brass-light">📚</span> BookStore
            </a>

            <div class="max-w-sm">
                <span class="quote-mark text-brass text-6xl block mb-2">&ldquo;</span>
                <p class="font-serif text-2xl leading-snug text-paper/90 mb-4">
                    Sebuah ruang baca untuk setiap cerita, ilmu, dan halaman yang belum sempat kamu buka.
                </p>
                <p class="text-brass-light text-xs uppercase tracking-widest font-bold">BookStore</p>
            </div>

            <p class="text-paper/30 text-xs">&copy; 2026 BookStore E-commerce.</p>
        </div>

        <div class="paper-panel flex items-center justify-center p-6">
            <div class="bg-card p-8 rounded-sm shadow-md w-full max-w-sm border border-ink/5">
                <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1 text-center">Selamat Datang Kembali</p>
                <h2 class="text-2xl font-serif font-bold mb-6 text-center text-ink">Masuk Akun</h2>
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-ink/70 text-sm font-semibold mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2.5 border border-ink/20 rounded-sm bg-paper focus:outline-none focus:border-maroon focus:ring-1 focus:ring-maroon transition">
                    </div>
                    <div>
                        <label class="block text-ink/70 text-sm font-semibold mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-2.5 border border-ink/20 rounded-sm bg-paper focus:outline-none focus:border-maroon focus:ring-1 focus:ring-maroon transition">
                    </div>
                    <button type="submit" name="login" class="w-full bg-ink text-paper py-3 rounded-sm font-bold hover:bg-maroon transition uppercase tracking-wide text-sm">Login</button>
                </form>
                <p class="text-sm text-center text-ink/60 mt-6">Belum punya akun? <a href="register.php" class="text-maroon font-semibold hover:underline">Daftar disini</a></p>
            </div>
        </div>

    </div>
</body>
</html>