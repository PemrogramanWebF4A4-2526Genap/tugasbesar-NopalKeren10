<?php
session_start();

// Hapus semua data session
session_unset();

// Hancurkan session
session_destroy();

// Tendang balik ke halaman login setelah logout berhasil
header("Location: login.php");
exit;
?>