# 📚 BookStore - Aplikasi Toko Buku Online

Aplikasi e-commerce untuk toko buku dengan fitur lengkap untuk buyer, seller, dan admin.

## 🌟 Fitur Utama

### Untuk Buyer
- 📖 **Katalog Buku** - Jelajahi berbagai kategori buku dengan pencarian dan filter
- 🛒 **Keranjang Belanja** - Kelola item belanjaan sebelum checkout
- 💳 **Pembayaran Aman** - Sistem pembayaran dengan berbagai metode
- 📦 **Lacak Pesanan** - Pantau status pengiriman pesanan
- ⭐ **Ulasan & Rating** - Berikan ulasan untuk buku yang dibeli
- 🔔 **Notifikasi** - Dapatkan update status pesanan

### Untuk Seller
- 📝 **Kelola Produk** - Tambah, edit, dan hapus buku
- 📊 **Validasi Pesanan** - Proses dan konfirmasi pembayaran
- 📈 **Riwayat Penjualan** - Laporan transaksi dan pendapatan
- 🔔 **Notifikasi** - Alert untuk pesanan baru dan ulasan

### Untuk Admin
- 👥 **Manajemen User** - Atur akun buyer dan seller
- 📂 **Kategori Buku** - Kelola kategori produk
- 🎛️ **Kontrol Sistem** - Monitoring dan maintenance

## 📁 Struktur Project

```
UAS_INFO2425_202410715252_MUHAMMAD NAUFAL/
├── admin/              # Halaman admin
├── assets/             # File statis (gambar, CSS, JS)
├── auth/               # Login, register, logout
├── buyer/              # Halaman buyer (katalog, cart, dll)
├── seller/             # Halaman seller (kelola produk, validasi)
├── components/         # Komponen reusable
├── config/             # Konfigurasi database dan helper
├── docs/               # Dokumentasi
│   ├── README.md       # Dokumentasi project
│   └── schema.sql      # Skema database
└── index.php           # Halaman landing page
```

## 🗄️ Struktur Database

Database `bookstore` terdiri dari tabel-tabel berikut:

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Data pengguna (admin, seller, buyer) |
| `categories` | Kategori buku |
| `products` | Data produk/buku dengan penulis |
| `orders` | Data pesanan |
| `order_items` | Detail item dalam pesanan |
| `cart` | Keranjang belanja |
| `reviews` | Ulasan dan rating produk |
| `notifications` | Notifikasi sistem |

## 🚀 Cara Instalasi

### Prasyarat
- XAMPP/WAMP (Apache + MySQL + PHP)
- Browser modern (Chrome, Firefox, Edge)
- PHP 7.4 atau lebih tinggi

### Langkah-langkah

1. **Clone atau download project**
   ```bash
   cd C:\xampp\htdocs\
   ```

2. **Setup Database**
   - Buka phpMyAdmin di `http://localhost/phpmyadmin`
   - Buat database baru bernama `bookstore`
   - Pilih database `bookstore`
   - Klik tab **Import**
   - Pilih file `docs/schema.sql`
   - Klik **Go**

3. **Konfigurasi Database**
   - Pastikan file `config/database.php` sudah sesuai:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "bookstore";
   ```

4. **Jalankan Aplikasi**
   - Buka browser dan akses: `http://localhost/UAS_INFO2425_202410715252_MUHAMMAD NAUFAL/`

5. **Daftar Akun**
   - **Buyer**: Daftar melalui halaman pendaftaran buyer
   - **Seller**: Daftar melalui halaman pendaftaran seller
   - **Admin**: Buat akun admin langsung di database atau melalui form pendaftaran

## 👤 Cara Penggunaan

### Sebagai Buyer
1. Login sebagai buyer
2. Jelajahi katalog buku di halaman utama
3. Cari buku dengan fitur pencarian atau filter kategori
4. Tambah buku ke keranjang
5. Checkout dan lakukan pembayaran
6. Lacak status pesanan di halaman "Pesanan Saya"
7. Berikan ulasan setelah pesanan selesai

### Sebagai Seller
1. Login sebagai seller
2. Tambah produk buku baru di "Kelola Produk Buku"
3. Masukkan detail buku (judul, penulis, harga, stok, dll)
4. Upload gambar cover buku
5. Validasi pesanan masuk di "Validasi Pesanan"
6. Lihat riwayat penjualan di "Riwayat Penjualan"

### Sebagai Admin
1. Login sebagai admin
2. Kelola kategori buku
3. Verifikasi akun seller baru
4. Monitoring aktivitas sistem

## � Keamanan

- ✅ Password di-hash menggunakan bcrypt
- ✅ Validasi input untuk mencegah SQL Injection
- ✅ Session management yang aman
- ✅ Role-based access control (RBAC)
- ✅ Proteksi halaman berdasarkan role

## 🎨 Teknologi yang Digunakan

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: TailwindCSS
- **Icons**: Unicode emojis
- **Font**: Google Fonts (Fraunces, Inter)

## 🛠️ Reset Database

Jika ingin mereset database sepenuhnya:

```sql
DROP DATABASE IF EXISTS bookstore;
```

Kemudian buat database baru dan import ulang `schema.sql`.

## � Catatan Penting

- Pastikan XAMPP sudah berjalan (Apache & MySQL)
- File upload disimpan di folder `assets/uploads/`
- Pastikan folder `assets/uploads/` memiliki permission write
- Ganti password default setelah login pertama

## 🐛 Troubleshooting

### Database tidak terkoneksi
- Pastikan MySQL server berjalan
- Cek konfigurasi di `config/database.php`
- Pastikan database `bookstore` sudah dibuat

### Gambar tidak terupload
- Pastikan folder `assets/uploads/` ada
- Cek permission folder (chmod 755 atau 777)
- Pastikan ukuran file tidak melebihi limit PHP

### Session expired
- Cek konfigurasi `session.save_path` di php.ini
- Pastikan folder temp session disimpan memiliki permission write

📄 License
Project ini dibuat untuk keperluan tugas kuliah UAS Pemrograman Web.

👨‍💻 Developer
Nama: Muhammad Naufal
NIM: 202410715252
Mata Kuliah: Pemrograman Web
Tahun: 2026

## 📞 Support

Jika mengalami masalah:
1. Pastikan semua prasyarat terpenuhi
2. Cek error log di XAMPP
3. Pastikan konfigurasi database benar
4. Clear cache browser

---

