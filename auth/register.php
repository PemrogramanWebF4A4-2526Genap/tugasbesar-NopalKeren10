<?php
include '../config/database.php';

$notif_message = '';
$notif_type = ''; // 'success' atau 'error'
$redirect_to = '';

if (isset($_POST['register'])) {
    $name     = mysqli_real_escape_string($db, $_POST['name']);
    $email    = mysqli_real_escape_string($db, $_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role']; 

    // Hash password demi keamanan
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    // Cek apakah email sudah terdaftar
    $cek_email = mysqli_query($db, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $notif_message = 'Email ini sudah terdaftar. Coba gunakan email lain, atau langsung login.';
        $notif_type = 'error';
    } else {
        // Simpan ke database
        $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password_hashed', '$role')";
        if (mysqli_query($db, $query)) {
            $notif_message = 'Registrasi berhasil! Kamu akan diarahkan ke halaman login...';
            $notif_type = 'success';
            $redirect_to = 'login.php';
        } else {
            $notif_message = 'Gagal melakukan registrasi. Silakan coba lagi.';
            $notif_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BookStore</title>
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

        /* ===== Toast Notification ===== */
        #toast-overlay {
            position: fixed;
            inset: 0;
            background: rgba(22, 31, 43, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }
        #toast-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        #toast-box {
            transform: translateY(14px) scale(0.97);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        #toast-overlay.show #toast-box {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .toast-progress-bar {
            animation: toast-progress 2.4s linear forwards;
        }
        @keyframes toast-progress {
            from { width: 100%; }
            to { width: 0%; }
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
                    Buka satu akun, buka jalan ke ribuan rak buku dan toko dari seluruh penjuru.
                </p>
                <p class="text-brass-light text-xs uppercase tracking-widest font-bold">BookStore</p>
            </div>

            <p class="text-paper/30 text-xs">&copy; 2026 BookStore E-commerce.</p>
        </div>

        <div class="paper-panel flex items-center justify-center p-6 py-10">
            <div class="bg-card p-8 rounded-sm shadow-md w-full max-w-sm border border-ink/5">
                <p class="text-xs font-bold uppercase tracking-widest text-maroon mb-1 text-center">Gabung Sekarang</p>
                <h2 class="text-2xl font-serif font-bold mb-6 text-center text-ink">Daftar Akun</h2>
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label id="label-nama" class="block text-ink/70 text-sm font-semibold mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required class="w-full px-3 py-2.5 border border-ink/20 rounded-sm bg-paper focus:outline-none focus:border-maroon focus:ring-1 focus:ring-maroon transition">
                    </div>
                    <div>
                        <label class="block text-ink/70 text-sm font-semibold mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-3 py-2.5 border border-ink/20 rounded-sm bg-paper focus:outline-none focus:border-maroon focus:ring-1 focus:ring-maroon transition">
                    </div>
                    <div>
                        <label class="block text-ink/70 text-sm font-semibold mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-2.5 border border-ink/20 rounded-sm bg-paper focus:outline-none focus:border-maroon focus:ring-1 focus:ring-maroon transition">
                    </div>
                    <div>
                        <label class="block text-ink/70 text-sm font-semibold mb-1">Mendaftar Sebagai</label>
                        <select name="role" id="role-select" required class="w-full px-3 py-2.5 border border-ink/20 rounded-sm bg-paper focus:outline-none focus:border-maroon focus:ring-1 focus:ring-maroon transition">
                            <option value="buyer">Pembeli (Buyer)</option>
                            <option value="seller">Penjual (Seller)</option>
                        </select>
                    </div>
                    <button type="submit" name="register" class="w-full bg-ink text-paper py-3 rounded-sm font-bold hover:bg-maroon transition uppercase tracking-wide text-sm">Daftar</button>
                </form>
                <p class="text-sm text-center text-ink/60 mt-6">Sudah punya akun? <a href="login.php" class="text-maroon font-semibold hover:underline">Login disini</a></p>
            </div>
        </div>

    </div>

    <?php if (!empty($notif_message)) : ?>
    <!-- ===== Toast Notification ===== -->
    <div id="toast-overlay">
        <div id="toast-box" class="bg-card w-full max-w-sm mx-4 rounded-sm shadow-xl border border-ink/10 overflow-hidden">
            <div class="p-6 flex items-start gap-4">
                <?php if ($notif_type === 'success') : ?>
                    <div class="w-10 h-10 rounded-full bg-sage/15 border border-sage/40 flex items-center justify-center text-sage text-xl shrink-0">
                        ✓
                    </div>
                <?php else : ?>
                    <div class="w-10 h-10 rounded-full bg-maroon/10 border border-maroon/40 flex items-center justify-center text-maroon text-xl shrink-0">
                        ✕
                    </div>
                <?php endif; ?>

                <div class="flex-1">
                    <p class="font-serif font-bold text-ink text-base mb-1">
                        <?= $notif_type === 'success' ? 'Registrasi Berhasil' : 'Registrasi Gagal'; ?>
                    </p>
                    <p class="text-ink/60 text-sm leading-relaxed"><?= htmlspecialchars($notif_message); ?></p>
                </div>

                <button onclick="closeToast()" class="text-ink/30 hover:text-ink text-sm shrink-0">✕</button>
            </div>

            <?php if ($notif_type === 'success') : ?>
                <div class="h-1 bg-ink/5 w-full">
                    <div class="toast-progress-bar h-full bg-sage"></div>
                </div>
            <?php else : ?>
                <div class="px-6 pb-5 -mt-1">
                    <button onclick="closeToast()" class="w-full bg-ink text-paper py-2.5 rounded-sm font-semibold hover:bg-maroon transition text-sm uppercase tracking-wide">
                        Coba Lagi
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const overlay = document.getElementById('toast-overlay');
        const notifType = <?= json_encode($notif_type); ?>;
        const redirectTo = <?= json_encode($redirect_to); ?>;

        window.addEventListener('DOMContentLoaded', () => {
            requestAnimationFrame(() => overlay.classList.add('show'));
        });

        function closeToast() {
            overlay.classList.remove('show');
        }

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeToast();
        });

        if (notifType === 'success' && redirectTo) {
            setTimeout(() => {
                window.location.href = redirectTo;
            }, 2400);
        }
    </script>
    <?php endif; ?>

    <script>
        const roleSelect = document.getElementById('role-select');
        const labelNama = document.getElementById('label-nama');

        roleSelect.addEventListener('change', function() {
            if (this.value === 'seller') {
                labelNama.textContent = 'Nama Toko';
            } else {
                labelNama.textContent = 'Nama Lengkap';
            }
        });
    </script>

</body>
</html>