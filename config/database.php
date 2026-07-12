<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bookstore"; // Sesuaikan dengan nama database di phpMyAdmin kamu

$db = mysqli_connect($host, $user, $pass, $db);

if (!$db) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>