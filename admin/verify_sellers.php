<?php
session_start();
include '../config/database.php';

// Proteksi Session Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses Persetujuan / Penolakan Seller
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        mysqli_query($db, "UPDATE users SET is_verified = 1 WHERE id = $id AND role = 'seller'");
        header("Location: verify_sellers.php?notif=approved");
        exit;
    } elseif ($action === 'reject') {
        // Jika ditolak, bisa dihapus atau diubah rolenya jadi buyer kembali
        mysqli_query($db, "DELETE FROM users WHERE id = $id AND role = 'seller' AND is_verified = 0");
        header("Location: verify_sellers.php?notif=rejected");
        exit;
    }
}

// Ambil data seller yang belum diverifikasi
$pending_sellers = mysqli_query($db, "SELECT * FROM users WHERE role = 'seller' AND is_verified = 0 ORDER BY id DESC");

// Tentukan notifikasi yang perlu ditampilkan (hasil redirect dari proses approve/reject)
$notif = isset($_GET['notif']) ? $_GET['notif'] : '';
$notif_title = '';
$notif_message = '';
$notif_type = ''; // success | error

if ($notif === 'approved') {
    $notif_title = 'Seller Disetujui';
    $notif_message = 'Akun penjual berhasil diverifikasi dan sekarang bisa mulai berjualan.';
    $notif_type = 'success';
} elseif ($notif === 'rejected') {
    $notif_title = 'Pendaftaran Ditolak';
    $notif_message = 'Pengajuan akun seller ini telah ditolak dan dihapus dari sistem.';
    $notif_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Penjual - BookStore</title>
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
            <a href="index.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Dashboard</a>
            <a href="manage_users.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Pengguna</a>
            <a href="verify_sellers.php" class="bg-maroon px-4 py-2.5 rounded-sm font-semibold transition">Verifikasi Penjual</a>
            <a href="manage_categories.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Kelola Kategori</a>
            <a href="report.php" class="hover:bg-ink-light px-4 py-2.5 rounded-sm transition">Laporan Penjualan</a>
            <a href="../auth/logout.php" class="text-brass-light/80 hover:bg-ink-light px-4 py-2.5 rounded-sm mt-10 transition">Logout</a>
        </nav>
    </div>

    <div class="flex-1 p-10">
        <div class="mb-8">
            <p class="eyebrow text-xs font-bold uppercase tracking-widest text-maroon mb-1">Verifikasi Penjual</p>
            <h1 class="text-3xl font-serif font-bold text-ink">Persetujuan Akun Penjual</h1>
            <p class="text-sm text-ink/60 mt-1">Sesuai SOP, verifikasi berkas pendaftaran toko baru di bawah ini sebelum memberikan akses jualan.</p>
        </div>

        <div class="bg-card rounded-sm shadow-md border border-ink/5 overflow-hidden">
            <div class="p-5 border-b bg-paper">
                <h3 class="font-serif font-bold text-ink text-lg">Daftar Pengajuan Toko (Pending)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-paper text-ink/50 font-bold uppercase text-xs tracking-wider border-b border-ink/10">
                            <th class="p-4">No</th>
                            <th class="p-4">Nama Lengkap</th>
                            <th class="p-4">Email</th>
                            <th class="p-4 text-center">Aksi Konfirmasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dashed divide-ink/10 text-sm text-ink/70">
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($pending_sellers) > 0) :
                            while ($seller = mysqli_fetch_assoc($pending_sellers)) : 
                        ?>
                            <tr class="hover:bg-paper/60 transition">
                                <td class="p-4 font-medium"><?= $no++; ?></td>
                                <td class="p-4 font-serif font-bold text-ink"><?= htmlspecialchars($seller['name']); ?></td>
                                <td class="p-4"><?= htmlspecialchars($seller['email']); ?></td>
                                <td class="p-4 text-center space-x-2">
                                    <button type="button"
                                        data-url="verify_sellers.php?action=approve&id=<?= $seller['id']; ?>"
                                        data-title="Setujui Penjual Ini?"
                                        data-message="Toko <strong><?= htmlspecialchars($seller['name']); ?></strong> akan mulai bisa berjualan di BookStore setelah disetujui."
                                        data-confirm-label="Ya, Setujui"
                                        data-confirm-color="sage"
                                        onclick="openConfirm(this)"
                                        class="bg-sage hover:bg-sage/80 text-paper px-3 py-1.5 rounded-sm font-semibold text-xs shadow transition">
                                        ✓ Setujui
                                    </button>
                                    <button type="button"
                                        data-url="verify_sellers.php?action=reject&id=<?= $seller['id']; ?>"
                                        data-title="Tolak Pendaftaran Ini?"
                                        data-message="Pengajuan akun <strong><?= htmlspecialchars($seller['name']); ?></strong> akan dihapus permanen dari sistem."
                                        data-confirm-label="Ya, Tolak"
                                        data-confirm-color="red"
                                        onclick="openConfirm(this)"
                                        class="bg-maroon hover:bg-maroon-light text-paper px-3 py-1.5 rounded-sm font-semibold text-xs shadow transition">
                                        ✕ Tolak
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else : 
                        ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-ink/40 font-serif">Tidak ada pengajuan pendaftaran seller baru saat ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== Modal Konfirmasi ===== -->
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
                <a id="confirm-action-btn" href="#" class="px-4 py-2 rounded-sm font-semibold text-sm text-paper shadow transition">
                </a>
            </div>
        </div>
    </div>

    <!-- ===== Toast Notifikasi ===== -->
    <?php if (!empty($notif_message)) : ?>
    <div id="toast-overlay">
        <div id="toast-box" class="bg-card w-full max-w-sm mx-4 rounded-sm shadow-xl border border-ink/10 overflow-hidden">
            <div class="p-6 flex items-start gap-4">
                <?php if ($notif_type === 'success') : ?>
                    <div class="w-10 h-10 rounded-full bg-sage/10 border border-sage/30 flex items-center justify-center text-sage text-xl shrink-0">
                        ✓
                    </div>
                <?php else : ?>
                    <div class="w-10 h-10 rounded-full bg-maroon/10 border border-maroon/30 flex items-center justify-center text-maroon text-xl shrink-0">
                        ✕
                    </div>
                <?php endif; ?>
                <div class="flex-1">
                    <p class="font-serif font-bold text-ink text-base mb-1"><?= htmlspecialchars($notif_title); ?></p>
                    <p class="text-ink/70 text-sm leading-relaxed"><?= htmlspecialchars($notif_message); ?></p>
                </div>
                <button onclick="closeToast()" class="text-ink/30 hover:text-ink text-sm shrink-0">✕</button>
            </div>
            <div class="h-1 bg-ink/5 w-full">
                <div class="toast-progress-bar h-full <?= $notif_type === 'success' ? 'bg-sage' : 'bg-maroon'; ?>"></div>
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
                // Bersihkan query string ?notif=... dari URL setelah ditutup
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, '', cleanUrl);
            }

            toastOverlay.addEventListener('click', (e) => {
                if (e.target === toastOverlay) closeToast();
            });

            setTimeout(closeToast, 2600);
        }
    </script>

</body>
</html>