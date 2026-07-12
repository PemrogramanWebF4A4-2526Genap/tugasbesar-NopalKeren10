<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
$_SESSION['user_type'] = 'buyer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($page_title) ? $page_title : 'BookStore'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <div class="bg-blue-600 text-white p-4 shadow-md flex justify-between items-center w-full">
        <span class="text-2xl font-bold">📚 BookStore</span>
        <div class="flex items-center space-x-4">
            <?php require_once __DIR__ . '/notification_dropdown.php'; ?>
            <a href="index.php" class="bg-blue-700 hover:bg-blue-800 text-sm px-4 py-2 rounded-lg font-medium">Kembali Belanja</a>
        </div>
    </div>
    <main class="flex-1 p-10"></main>