# 📖 User Manual - BookStore

Panduan lengkap penggunaan aplikasi BookStore untuk Buyer, Seller, dan Admin.

---

## 📑 Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Pendaftaran dan Login](#pendaftaran-dan-login)
3. [Panduan Buyer](#panduan-buyer)
4. [Panduan Seller](#panduan-seller)
5. [Panduan Admin](#panduan-admin)
6. [FAQ](#faq)

---

## 📌 Pendahuluan

BookStore adalah aplikasi toko buku online yang menghubungkan pembeli (buyer), penjual (seller), dan administrator (admin) dalam satu platform. Aplikasi ini memungkinkan:
- Buyer mencari dan membeli buku
- Seller menjual dan mengelola produk buku
- Admin mengelola sistem dan pengguna

---

## 🔐 Pendaftaran dan Login

### Mendaftar Akun Baru

1. Buka halaman utama aplikasi
2. Klik tombol **"Daftar"** di pojok kanan atas
3. Pilih role yang diinginkan:
   - **Buyer** - Untuk membeli buku
   - **Seller** - Untuk menjual buku
4. Isi form pendaftaran:
   - **Nama Lengkap** - Nama lengkap Anda
   - **Email** - Email aktif untuk verifikasi
   - **Password** - Password minimal 6 karakter
   - **Konfirmasi Password** - Ulangi password
5. Klik **"Daftar"**
6. Anda akan diarahkan ke halaman login

### Login

1. Buka halaman utama aplikasi
2. Klik tombol **"Login"** di pojok kanan atas
3. Masukkan email dan password
4. Klik **"Login"**
5. Anda akan diarahkan ke dashboard sesuai role Anda

### Logout

1. Klik tombol profil di pojok kanan atas
2. Pilih **"Logout"**
3. Anda akan diarahkan ke halaman utama

---

## 👤 Panduan Buyer

### 1. Jelajahi Katalog Buku

**Cara Akses:**
- Login sebagai buyer
- Halaman utama otomatis menampilkan katalog buku

**Fitur:**
- **Pencarian** - Ketik judul buku di kolom pencarian
- **Filter Kategori** - Pilih kategori (Fiksi, Non-Fiksi, Sains, dll)
- **Rekomendasi** - Buku dengan rating tertinggi ditampilkan di bagian atas

### 2. Lihat Detail Buku

1. Klik pada cover buku atau judul buku
2. Halaman detail akan menampilkan:
   - Cover buku
   - Judul dan penulis
   - Kategori
   - Harga
   - Stok tersedia
   - Sinopsis/deskripsi
   - Rating dan ulasan pembeli lain
3. Klik **"Tambah ke Keranjang"** untuk membeli

### 3. Kelola Keranjang Belanja

**Cara Akses:**
- Klik ikon keranjang di pojok kanan atas

**Fitur:**
- **Tambah Quantity** - Klik tombol `+` untuk menambah jumlah
- **Kurangi Quantity** - Klik tombol `-` untuk mengurangi jumlah
- **Hapus Item** - Klik tombol hapus untuk menghapus item
- **Total Harga** - Ditampilkan secara otomatis

### 4. Checkout dan Pembayaran

1. Pastikan keranjang tidak kosong
2. Klik tombol **"Checkout"**
3. Isi form pengiriman:
   - **Alamat Lengkap** - Alamat pengiriman
   - **Nomor Telepon** - Untuk kontak pengiriman
   - **Catatan** (Opsional) - Catatan untuk penjual
4. Klik **"Lanjut ke Pembayaran"**
5. Pilih metode pembayaran (Transfer Bank)
6. Upload bukti pembayaran:
   - Klik tombol upload
   - Pilih gambar bukti transfer
7. Klik **"Bayar Sekarang"**
8. Pesanan akan dibuat dengan status "pending"

### 5. Lacak Pesanan

**Cara Akses:**
- Klik menu **"Pesanan Saya"** di navbar

**Status Pesanan:**
- **Pending** - Menunggu konfirmasi pembayaran
- **Paid** - Pembayaran dikonfirmasi, menunggu diproses
- **Processing** - Sedang diproses oleh penjual
- **Shipped** - Barang dikirim
- **Delivered** - Barang diterima
- **Cancelled** - Pesanan dibatalkan

### 6. Lihat Detail Pesanan

1. Di halaman "Pesanan Saya", klik tombol **"Lihat Detail"**
2. Halaman detail menampilkan:
   - Informasi pesanan (nomor, tanggal, total)
   - Daftar item yang dibeli
   - Status pembayaran
   - Status pengiriman
   - Alamat pengiriman
   - Bukti pembayaran

### 7. Berikan Ulasan

1. Pesanan harus berstatus "delivered"
2. Di halaman "Pesanan Saya", klik tombol **"Beri Ulasan"**
3. Isi form ulasan:
   - **Rating** - Pilih 1-5 bintang
   - **Ulasan** (Opsional) - Tulis pengalaman Anda
4. Klik **"Kirim Ulasan"**
5. Ulasan akan ditampilkan di halaman detail buku

### 8. Kelola Profil

**Cara Akses:**
- Klik ikon profil di pojok kanan atas
- Pilih **"Profil"**

**Fitur:**
- **Lihat Profil** - Informasi akun Anda
- **Edit Profil** - Ubah nama, email, telepon, alamat
- **Ganti Password** - Ubah password akun

---

## 🏪 Panduan Seller

### 1. Dashboard Seller

**Cara Akses:**
- Login sebagai seller
- Dashboard menampilkan:
  - Total produk
  - Total penjualan
  - Pesanan yang perlu diproses
  - Notifikasi unread

### 2. Tambah Produk Buku

**Cara Akses:**
- Klik menu **"Kelola Produk Buku"**
- Klik tombol **"+ Tambah Buku"**

**Isi Form:**
- **Judul Buku** - Nama buku
- **Kategori** - Pilih kategori yang sesuai
- **Penulis** - Nama penulis buku
- **Harga** - Harga jual dalam Rupiah
- **Stok** - Jumlah stok tersedia
- **Cover Gambar** - Upload gambar cover buku
- **Deskripsi** - Sinopsis atau deskripsi buku
- **ISBN** (Opsional) - Nomor ISBN
- **Penerbit** (Opsional) - Nama penerbit
- **Tahun Terbit** (Opsional) - Tahun terbit
- **Jumlah Halaman** (Opsional) - Total halaman
- **Bahasa** (Opsional) - Bahasa buku
- **Kondisi** - Baru atau bekas

Klik **"Simpan Buku"** untuk menyimpan.

### 3. Edit Produk

1. Di halaman "Kelola Produk Buku"
2. Klik tombol **"Edit"** pada produk yang ingin diubah
3. Ubah informasi yang diperlukan
4. Klik **"Simpan Perubahan"**

### 4. Hapus Produk

1. Di halaman "Kelola Produk Buku"
2. Klik tombol **"Hapus"** pada produk yang ingin dihapus
3. Konfirmasi penghapusan
4. Produk dan gambar akan dihapus dari sistem

### 5. Validasi Pesanan

**Cara Akses:**
- Klik menu **"Validasi Pesanan"**

**Proses Validasi:**
1. Lihat daftar pesanan dengan status "pending"
2. Klik pesanan untuk melihat detail
3. Cek bukti pembayaran yang diupload buyer
4. Jika pembayaran valid:
   - Klik **"Konfirmasi Pembayaran"**
   - Status berubah menjadi "paid"
5. Jika pembayaran tidak valid:
   - Klik **"Tolak Pembayaran"**
   - Status berubah menjadi "cancelled"

### 6. Proses Pengiriman

1. Di halaman "Validasi Pesanan"
2. Lihat pesanan dengan status "paid"
3. Klik **"Kirim Barang"**
4. Masukkan nomor resi pengiriman (opsional)
5. Status berubah menjadi "shipped"
6. Buyer akan menerima notifikasi

### 7. Selesaikan Pesanan

1. Di halaman "Validasi Pesanan"
2. Lihat pesanan dengan status "shipped"
3. Klik **"Selesaikan Pesanan"**
4. Status berubah menjadi "delivered"
5. Buyer dapat memberikan ulasan

### 8. Lihat Riwayat Penjualan

**Cara Akses:**
- Klik menu **"Riwayat Penjualan"**

**Informasi yang Ditampilkan:**
- Daftar semua pesanan
- Total pendapatan
- Status setiap pesanan
- Detail item yang terjual

### 9. Notifikasi

**Cara Akses:**
- Klik menu **"Notifikasi"**

**Jenis Notifikasi:**
- Pesanan baru
- Pembayaran masuk
- Ulasan baru dari buyer
- Update status pesanan

### 10. Kelola Profil

**Cara Akses:**
- Klik ikon profil di pojok kanan atas
- Pilih **"Profil"**

---

## 👨‍💼 Panduan Admin

### 1. Dashboard Admin

**Cara Akses:**
- Login sebagai admin
- Dashboard menampilkan statistik sistem

### 2. Kelola Kategori

**Cara Akses:**
- Klik menu **"Kelola Kategori"**

**Tambah Kategori:**
1. Masukkan nama kategori
2. Masukkan deskripsi (opsional)
3. Klik **"Tambah Kategori"**

**Edit/Hapus Kategori:**
- Klik tombol edit atau hapus pada kategori yang diinginkan

### 3. Kelola User

**Cara Akses:**
- Klik menu **"Kelola User"**

**Fitur:**
- Lihat daftar semua user (buyer, seller, admin)
- Verifikasi akun seller baru
- Edit informasi user
- Hapus user
- Reset password user

### 4. Verifikasi Seller

**Cara Akses:**
- Klik menu **"Verifikasi Seller"**

**Proses Verifikasi:**
1. Lihat daftar seller yang belum terverifikasi
2. Cek informasi seller
3. Klik **"Verifikasi"** untuk menyetujui
4. Klik **"Tolak"** untuk menolak

### 5. Lihat Laporan

**Cara Akses:**
- Klik menu **"Laporan"**

**Informasi yang Ditampilkan:**
- Statistik penjualan
- Jumlah user terdaftar
- Produk terlaris
- Revenue bulanan

---

## ❓ FAQ

### Buyer

**Q: Bagaimana cara mencari buku spesifik?**
A: Gunakan kolom pencarian di halaman utama. Ketik judul atau penulis buku.

**Q: Apakah bisa membatalkan pesanan?**
A: Pesanan hanya bisa dibatalkan jika status masih "pending". Hubungi admin untuk bantuan.

**Q: Bagaimana cara mengganti alamat pengiriman?**
A: Alamat pengiriman diinput saat checkout. Pastikan alamat benar sebelum checkout.

**Q: Kapan bisa memberikan ulasan?**
A: Ulasan bisa diberikan setelah pesanan berstatus "delivered".

### Seller

**Q: Berapa lama waktu untuk memvalidasi pembayaran?**
A: Secepat mungkin untuk memberikan pelayanan terbaik. Buyer akan menunggu konfirmasi.

**Q: Apakah bisa mengedit harga setelah produk ditambahkan?**
A: Ya, edit produk dan ubah harga yang diinginkan.

**Q: Bagaimana jika stok habis?**
A: Edit produk dan ubah stok menjadi 0. Produk tidak akan ditampilkan di katalog buyer.

**Q: Apakah bisa menghapus pesanan?**
A: Pesanan tidak bisa dihapus, hanya bisa diproses atau ditolak pada tahap pembayaran.

### Admin

**Q: Bagaimana cara mereset password user?**
A: Di menu "Kelola User", klik tombol reset pada user yang diinginkan.

**Q: Apakah bisa menghapus kategori yang masih memiliki produk?**
A: Tidak disarankan. Hapus atau pindahkan produk terlebih dahulu.

**Q: Bagaimana cara melihat log aktivitas?**
A: Gunakan menu "Laporan" untuk melihat statistik dan aktivitas sistem.


