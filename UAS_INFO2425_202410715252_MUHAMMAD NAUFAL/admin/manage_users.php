<?php
session_start();
include '../config/database.php';

// Proteksi: Hanya boleh diakses oleh Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Helper untuk set flash notif lalu redirect
function set_flash_and_redirect($type, $title, $message, $target) {
    $_SESSION['flash_notif'] = ['type' => $type, 'title' => $title, 'message' => $message];
    header("Location: $target");
    exit;
}

// Logika Fitur Hapus Pengguna (Delete User)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_user = intval($_GET['id']);
    
    // Cegah admin menghapus dirinya sendiri
    if ($id_user === $_SESSION['user_id']) {
        set_flash_and_redirect('error', 'Aksi Tidak Diizinkan', 'Anda tidak bisa menghapus akun Anda sendiri.', 'manage_users.php');
    }

    // Eksekusi hapus user
    $delete = mysqli_query($db, "DELETE FROM users WHERE id = '$id_user'");
    if ($delete) {
        set_flash_and_redirect('success', 'Pengguna Dihapus', 'Akun pengguna berhasil dihapus dari sistem.', 'manage_users.php');
    } else {
        set_flash_and_redirect('error', 'Gagal Menghapus', 'Terjadi kesalahan: ' . mysqli_error($db), 'manage_users.php');
    }
}

// Ambil flash notif jika ada, lalu hapus dari session
$notif = null;
if (isset($_SESSION['flash_notif'])) {
    $notif = $_SESSION['flash_notif'];
    unset($_SESSION['flash_notif']);
}

// Tarik semua data pembeli & penjual (Admin tidak diikutkan agar aman)
$query_users = mysqli_query($db, "SELECT id, name, email, role, created_at FROM users WHERE role != 'admin' ORDER BY id DESC");

// Ambil nama file saat ini untuk otomatisasi highlight menu sidebar secara dinamis
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin Panel</title>
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
        .eyebrow::before {
            content: '';
            display: inline-block;
            width: 22px;
            height: 1px;
            background: #7B2A32;
            margin-right: 8px;
            vertical-align: middle;
        }
        #confirm-overlay, #toast-overlay {
            position: fixed;
            inset: 0;
            background: rgba(22, 31, 43, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        #confirm-overlay.show, #toast-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
        #confirm-box, #toast-box {
            transform: translateY(14px) scale(0.97);
            opacity: 0;
            transition: transform 0.25s ease, opacity 0.25s ease;
        }
        #confirm-overlay.show #confirm-box, #toast-overlay.show #toast-box {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .toast-progress-bar {
            animation: toast-progress 2.6s linear forwards;
        }
        @keyframes toast-progress {
            from { width: 100%; }
            to { width: 0%; }
        }
    </style>
</head>
<body class="bg-paper flex min-h-screen font-sans text-ink">

    <div class="bg-ink text-paper w-64 min-h-screen p-5 space-y-6 shrink-0 border-r-4 border-brass">
        <h2 class="text-xl font-serif font-bold flex items-center gap-2"><span class="text-brass-light">📚</span> Admin Panel</h2>
        <nav class="space-y-2 flex flex-col text-sm">
            
            <a href="index.php" class="<?= ($current_page == 'index.php') ? 'bg-maroon font-semibold' : 'hover:bg-ink-light' ?> px-4 py-2.5 rounded-sm transition">
                Dashboard
            </a>
            
            <a href="manage_users.php" class="<?= ($current_page == 'manage_users.php') ? 'bg-maroon font-semibold' : 'hover:bg-ink-light' ?> px-4 py-2.5 rounded-sm transition">
                Kelola Pengguna
            </a>
            
            <a href="verify_sellers.php" class="<?= ($current_page == 'verify_sellers.php') ? 'bg-maroon font-semibold' : 'hover:bg-ink-light' ?> px-4 py-2.5 rounded-sm transition">
                Verifikasi Penjual
            </a>
            
            <a href="manage_categories.php" class="<?= ($current_page == 'manage_categories.php') ? 'bg-maroon font-semibold' : 'hover:bg-ink-light' ?> px-4 py-2.5 rounded-sm transition">
                Kelola Kategori
            </a>
            
            <a href="report.php" class="<?= ($current_page == 'report.php') ? 'bg-maroon font-semibold' : 'hover:bg-ink-light' ?> px-4 py-2.5 rounded-sm transition">
                Laporan Penjualan
            </a>
            
            <a href="../auth/logout.php" class="text-brass-light/80 hover:bg-ink-light px-4 py-2.5 rounded-sm mt-10 transition">
                Logout
            </a>
        </nav>
    </div>

    <div class="flex-1 p-10">
        <div class="mb-8">
            <p class="eyebrow text-xs font-bold uppercase tracking-widest text-maroon mb-1">Manajemen Pengguna</p>
            <h1 class="text-3xl font-serif font-bold text-ink">Kelola Pengguna</h1>
            <p class="text-sm text-ink/60 mt-1">Pantau dan kelola hak akses akun Pembeli (Buyer) serta Penjual (Seller).</p>
        </div>

        <div class="bg-card rounded-sm shadow-md overflow-hidden border border-ink/5">
            <div class="p-5 bg-paper border-b border-ink/10">
                <h3 class="font-serif font-bold text-ink">Daftar Akun Terdaftar</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-paper text-ink/50 font-bold uppercase text-xs border-b border-ink/10">
                            <th class="p-4">Nama</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Role / Hak Akses</th>
                            <th class="p-4">Tanggal Gabung</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dashed divide-ink/10">
                        <?php if (mysqli_num_rows($query_users) > 0) : ?>
                            <?php while ($user = mysqli_fetch_assoc($query_users)) : ?>
                                <tr class="hover:bg-paper/60 transition">
                                    <td class="p-4 font-serif font-semibold text-ink"><?= htmlspecialchars($user['name']); ?></td>
                                    <td class="p-4 text-ink/70"><?= htmlspecialchars($user['email']); ?></td>
                                    <td class="p-4">
                                        <?php if ($user['role'] === 'seller') : ?>
                                            <span class="bg-purple-100 text-purple-800 font-bold text-xs px-2.5 py-1 rounded-full uppercase tracking-wider">🏪 Seller</span>
                                        <?php else : ?>
                                            <span class="bg-blue-100 text-blue-800 font-bold text-xs px-2.5 py-1 rounded-full uppercase tracking-wider">🛍️ Buyer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-sm text-ink/40"><?= date('d M Y, H:i', strtotime($user['created_at'])); ?> WIB</td>
                                    <td class="p-4 text-center">
                                        <button type="button"
                                            data-url="manage_users.php?action=delete&id=<?= $user['id']; ?>"
                                            data-title="Hapus Akun Ini?"
                                            data-message="Akun <strong><?= htmlspecialchars($user['name']); ?></strong> akan dihapus permanen dan tidak bisa dikembalikan."
                                            data-confirm-label="Ya, Hapus"
                                            data-confirm-color="red"
                                            onclick="openConfirm(this)"
                                            class="inline-block bg-maroon/10 hover:bg-maroon text-maroon hover:text-paper border border-maroon/30 px-3 py-1.5 rounded-sm text-xs font-bold transition shadow-sm">
                                            🗑️ Hapus Akun
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center py-10 text-ink/40 font-serif italic">Belum ada akun buyer maupun seller yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="confirm-overlay">
        <div id="confirm-box" class="bg-card w-full max-w-sm mx-4 rounded-sm shadow-xl border border-ink/10 overflow-hidden">
            <div class="p-6">
                <p id="confirm-title" class="font-serif font-bold text-ink text-lg mb-2"></p>
                <p id="confirm-message" class="text-ink/70 text-sm leading-relaxed"></p>
            </div>
            <div class="p-4 bg-paper border-t border-ink/10 flex gap-3 justify-end">
                <button onclick="closeConfirm()" class="px-4 py-2 rounded-sm font-semibold text-sm text-ink/70 hover:bg-ink/5 transition">
                    Batal
                </button>
                <a id="confirm-action-btn" href="#" class="px-4 py-2 rounded-sm font-semibold text-sm text-paper shadow transition"></a>
            </div>
        </div>
    </div>

    <?php if ($notif) : ?>
    <div id="toast-overlay">
        <div id="toast-box" class="bg-card w-full max-w-sm mx-4 rounded-sm shadow-xl border border-ink/10 overflow-hidden">
            <div class="p-6 flex items-start gap-4">
                <?php if ($notif['type'] === 'success') : ?>
                    <div class="w-10 h-10 rounded-full bg-sage/10 border border-sage/30 flex items-center justify-center text-sage text-xl shrink-0">✓</div>
                <?php else : ?>
                    <div class="w-10 h-10 rounded-full bg-maroon/10 border border-maroon/30 flex items-center justify-center text-maroon text-xl shrink-0">✕</div>
                <?php endif; ?>
                <div class="flex-1">
                    <p class="font-serif font-bold text-ink text-base mb-1"><?= htmlspecialchars($notif['title']); ?></p>
                    <p class="text-ink/70 text-sm leading-relaxed"><?= htmlspecialchars($notif['message']); ?></p>
                </div>
                <button onclick="closeToast()" class="text-ink/30 hover:text-ink text-sm shrink-0">✕</button>
            </div>
            <div class="h-1 bg-ink/5 w-full">
                <div class="toast-progress-bar h-full <?= $notif['type'] === 'success' ? 'bg-sage' : 'bg-maroon'; ?>"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // ===== Modal Konfirmasi =====
        const confirmOverlay = document.getElementById('confirm-overlay');
        const confirmTitle = document.getElementById('confirm-title');
        const confirmMessage = document.getElementById('confirm-message');
        const confirmActionBtn = document.getElementById('confirm-action-btn');

        const colorClasses = {
            sage: 'bg-sage hover:bg-sage/80',
            red: 'bg-maroon hover:bg-maroon-light'
        };

        function openConfirm(btn) {
            confirmTitle.textContent = btn.dataset.title;
            confirmMessage.innerHTML = btn.dataset.message;
            confirmActionBtn.href = btn.dataset.url;
            confirmActionBtn.textContent = btn.dataset.confirmLabel;
            confirmActionBtn.className = 'px-4 py-2 rounded-sm font-semibold text-sm text-paper shadow transition ' + colorClasses[btn.dataset.confirmColor];
            confirmOverlay.classList.add('show');
        }

        function closeConfirm() {
            confirmOverlay.classList.remove('show');
        }

        confirmOverlay.addEventListener('click', (e) => {
            if (e.target === confirmOverlay) closeConfirm();
        });

        // ===== Toast Notifikasi =====
        const toastOverlay = document.getElementById('toast-overlay');
        if (toastOverlay) {
            window.addEventListener('DOMContentLoaded', () => {
                requestAnimationFrame(() => toastOverlay.classList.add('show'));
            });

            function closeToast() {
                toastOverlay.classList.remove('show');
            }

            toastOverlay.addEventListener('click', (e) => {
                if (e.target === toastOverlay) closeToast();
            });

            setTimeout(closeToast, 2600);
        }
    </script>
</body>
</html>