-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2026 at 02:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `buyer_id`, `product_id`, `quantity`, `created_at`) VALUES
(82, 11, 7, 1, '2026-07-11 09:46:46'),
(85, 4, 4, 1, '2026-07-12 05:49:24');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Fiksi', 'Buku cerita, novel, dan sastra'),
(2, 'Edukasi & Sains', 'Buku pelajaran, kuliah, dan jurnal ilmiah'),
(3, 'Komik & Manga', 'Buku bergambar dan novel grafis'),
(4, 'Pengembangan Diri', 'Buku motivasi, bisnis, dan finansial');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('buyer','seller') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('order_status','new_review','payment','other') DEFAULT 'other',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `user_type`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 4, 'buyer', 'Pesanan Sedang Dikirim', 'Pesanan #65 Anda telah dikirim oleh penjual. Silakan tunggu paket sampai.', 'order_status', 1, '2026-07-08 19:37:15'),
(2, 4, 'buyer', 'Pesanan Selesai', 'Pesanan #65 Anda telah selesai. Terima kasih telah berbelanja di toko kami!', 'order_status', 1, '2026-07-08 19:38:51'),
(3, 5, 'seller', 'Ulasan Baru Diterima', 'Muhammad Naufal memberikan ulasan 5 bintang untuk produk Anda.', 'new_review', 1, '2026-07-08 19:39:11'),
(4, 5, 'seller', 'Pesanan Baru Masuk', 'Muhammad Naufal telah melakukan pesanan baru #66. Silakan validasi pembayaran.', 'payment', 1, '2026-07-10 08:59:11'),
(5, 4, 'buyer', 'Pesanan Sedang Dikirim', 'Pesanan #66 Anda telah dikirim oleh penjual. Silakan tunggu paket sampai.', 'order_status', 1, '2026-07-10 08:59:22'),
(6, 4, 'buyer', 'Pesanan Selesai', 'Pesanan #66 Anda telah selesai. Terima kasih telah berbelanja di toko kami!', 'order_status', 1, '2026-07-10 08:59:22'),
(7, 6, 'seller', 'Pesanan Baru Masuk', 'Naufal telah melakukan pesanan baru #67. Silakan validasi pembayaran.', 'payment', 1, '2026-07-10 09:11:17'),
(8, 4, 'buyer', 'Pesanan Sedang Dikirim', 'Pesanan #67 Anda telah dikirim oleh penjual. Silakan tunggu paket sampai.', 'order_status', 1, '2026-07-10 09:11:46'),
(9, 4, 'buyer', 'Pesanan Selesai', 'Pesanan #67 Anda telah selesai. Terima kasih telah berbelanja di toko kami!', 'order_status', 1, '2026-07-10 09:11:47'),
(10, 6, 'seller', 'Ulasan Baru Diterima', 'Muhammad Naufal memberikan ulasan 5 bintang untuk produk Anda.', 'new_review', 0, '2026-07-10 09:12:19'),
(11, 5, 'seller', 'Ulasan Baru Diterima', 'Muhammad Naufal memberikan ulasan 5 bintang untuk produk Anda.', 'new_review', 1, '2026-07-10 09:12:28'),
(12, 5, 'seller', 'Pesanan Baru Masuk', 'Muhammad Naufal telah melakukan pesanan baru #68. Silakan validasi pembayaran.', 'payment', 1, '2026-07-11 08:43:44'),
(13, 4, 'buyer', 'Pesanan Sedang Dikirim', 'Pesanan #68 Anda telah dikirim oleh penjual. Silakan tunggu paket sampai.', 'order_status', 0, '2026-07-11 08:44:19'),
(14, 4, 'buyer', 'Pesanan Selesai', 'Pesanan #68 Anda telah selesai. Terima kasih telah berbelanja di toko kami!', 'order_status', 0, '2026-07-11 08:44:53'),
(15, 5, 'seller', 'Ulasan Baru Diterima', 'Muhammad Naufal memberikan ulasan 5 bintang untuk produk Anda.', 'new_review', 1, '2026-07-11 09:25:27'),
(16, 6, 'seller', 'Pesanan Baru Masuk', 'Ahmad Fadhilah telah melakukan pesanan baru #69. Silakan validasi pembayaran.', 'payment', 0, '2026-07-11 09:27:02'),
(17, 11, 'buyer', 'Pesanan Sedang Dikirim', 'Pesanan #69 Anda telah dikirim oleh penjual. Silakan tunggu paket sampai.', 'order_status', 1, '2026-07-11 09:27:24'),
(18, 11, 'buyer', 'Pesanan Selesai', 'Pesanan #69 Anda telah selesai. Terima kasih telah berbelanja di toko kami!', 'order_status', 1, '2026-07-11 09:27:25'),
(19, 6, 'seller', 'Ulasan Baru Diterima', 'Ahmad Fadhilah memberikan ulasan 5 bintang untuk produk Anda.', 'new_review', 0, '2026-07-11 09:28:03'),
(20, 5, 'seller', 'Pesanan Baru Masuk', 'Muhammad Naufal telah melakukan pesanan baru #70. Silakan validasi pembayaran.', 'payment', 0, '2026-07-12 05:39:30'),
(21, 4, 'buyer', 'Pesanan Sedang Dikirim', 'Pesanan #70 Anda telah dikirim oleh penjual. Silakan tunggu paket sampai.', 'order_status', 0, '2026-07-12 05:40:49'),
(22, 4, 'buyer', 'Pesanan Selesai', 'Pesanan #70 Anda telah selesai. Terima kasih telah berbelanja di toko kami!', 'order_status', 0, '2026-07-12 05:40:51'),
(23, 5, 'seller', 'Pesanan Baru Masuk', 'Muhammad Naufal telah melakukan pesanan baru #71. Silakan validasi pembayaran.', 'payment', 0, '2026-07-12 05:43:13'),
(24, 5, 'seller', 'Pesanan Baru Masuk', 'Azka telah melakukan pesanan baru #72. Silakan validasi pembayaran.', 'payment', 0, '2026-07-12 11:40:28'),
(25, 4, 'buyer', 'Pesanan Sedang Dikirim', 'Pesanan #71 Anda telah dikirim oleh penjual. Silakan tunggu paket sampai.', 'order_status', 0, '2026-07-12 11:41:08'),
(26, 4, 'buyer', 'Pesanan Selesai', 'Pesanan #71 Anda telah selesai. Terima kasih telah berbelanja di toko kami!', 'order_status', 0, '2026-07-12 11:41:10'),
(29, 5, 'seller', 'Pesanan Baru Masuk', 'Azka Habibur telah melakukan pesanan baru #73. Silakan validasi pembayaran.', 'payment', 0, '2026-07-12 11:46:14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `seller_id`, `total_amount`, `status`, `address`, `phone`, `created_at`) VALUES
(66, 4, 5, 90000.00, 'completed', 'Jl Sultan Agung, Gg inhutani RT 04/11 No.01, KOTA BEKASI, MEDAN SATRIA, JAWA BARAT, ID, 17132', '087885005985', '2026-07-10 08:59:11'),
(67, 4, 6, 95000.00, 'completed', 'Jl Sultan Agung, Gg inhutani RT 04/11 No.01, KOTA BEKASI, MEDAN SATRIA, JAWA BARAT, ID, 17132', '087885005985', '2026-07-10 09:11:17'),
(68, 4, 5, 100000.00, 'completed', 'Jl Sultan Agung, Gg inhutani RT 04/11 No.01, KOTA BEKASI, MEDAN SATRIA, JAWA BARAT, ID, 17132', '087885005985', '2026-07-11 08:43:44'),
(69, 11, 6, 95000.00, 'completed', 'Jl Sultan Agung, Gg inhutani RT 04/11 No.01, KOTA BEKASI, MEDAN SATRIA, JAWA BARAT, ID, 17132', '087658499381', '2026-07-11 09:27:02'),
(70, 4, 5, 75000.00, 'completed', 'Jl Sultan Agung, Gg inhutani RT 04/11 No.01, KOTA BEKASI, MEDAN SATRIA, JAWA BARAT, ID, 17132', '087885005985', '2026-07-12 05:39:30'),
(71, 4, 5, 90000.00, 'completed', 'Jl Sultan Agung, Gg inhutani RT 04/11 No.01, KOTA BEKASI, MEDAN SATRIA, JAWA BARAT, ID, 17132', '087885005985', '2026-07-12 05:43:13');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(67, 66, 9, 1, 75000.00),
(68, 67, 7, 1, 80000.00),
(69, 68, 4, 1, 85000.00),
(70, 69, 7, 1, 80000.00),
(71, 70, 20, 1, 60000.00),
(72, 71, 23, 1, 75000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `proof` varchar(255) NOT NULL,
  `status` enum('unverified','verified','rejected') DEFAULT 'unverified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `proof`, `status`) VALUES
(60, 66, '', 'PROOF-6a50b45fb95d0-bukti pembayaran.jpg', 'unverified'),
(61, 67, '', 'PROOF-6a50b7350d72b-bukti pembayaran.jpg', 'unverified'),
(62, 68, '', 'PROOF-6a520240b1f59-bukti pembayaran.jpg', 'unverified'),
(63, 69, '', 'PROOF-6a520c66b63b3-bukti pembayaran.jpg', 'unverified'),
(64, 70, '', 'PROOF-6a53289289bf4-bukti pembayaran.jpg', 'unverified'),
(65, 71, '', 'PROOF-6a5329710b454-bukti pembayaran.jpg', 'unverified');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `name`, `description`, `price`, `stock`, `category_id`, `author`, `image`) VALUES
(4, 5, 'Dilan: Dia Adalah Dilanku Tahun 1990', 'Dilan 1990 menceritakan kisah cinta masa SMA di Bandung pada tahun 1990 antara Milea, siswi pindahan dari Jakarta, dan Dilan, seorang ketua geng motor yang nakal namun romantis. Pendekatan Dilan yang unik dan puitis dengan caranya sendiri secara perlahan meluluhkan hati Milea dan membuat mereka saling jatuh cinta.', 85000.00, 8, 1, 'Pidi Baiq', '6a42023f776c1-cover dilan.jpg'),
(5, 6, 'Ensiklopedia Sains untuk Anak Cerdas', 'Ensiklopedia Sains untuk Anak Cerdas adalah buku edukasi yang dirancang khusus untuk memperkenalkan konsep sains secara interaktif. Buku ini menggunakan penjelasan bahasa yang sederhana dan dilengkapi dengan berbagai ilustrasi menarik, yang sangat membantu anak-anak memahami fenomena alam, energi, gaya, hingga rahasia alam semesta.', 110000.00, 11, 2, 'Laura Baker', '6a4202c56ece1-cover ensiklopedia sains.jpg'),
(6, 5, 'Programming Basics with Python', 'Programming Basics with Python memperkenalkan pembaca pada penulisan kode pemrograman tingkat pemula (variabel dan data, pernyataan kondisional, perulangan, dan fungsi) menggunakan bahasa pemrograman Python.', 90000.00, 11, 2, 'Svetlin Nakov', '6a4205d87611d-cover programming basics with python.png'),
(7, 6, 'SINOPAL', 'Komik Si Nopal (karya Naufal Faridurrazak) adalah komik komedi yang menceritakan kehidupan sehari-hari tokoh utama, Si Nopal, yang sering mengomentari berbagai fenomena konyol di sekitarnya. Cerita berkembang dengan menyoroti dinamika keluarganya yang unik dan penuh humor.', 80000.00, 10, 3, 'Naufal Faridurrazak', '6a43e5d4aed4c-cover sinopal.jpg'),
(9, 5, 'Sebuah Seni Untuk Bersikap Bodo Amat', 'Buku \"Sebuah Seni untuk Bersikap Bodo Amat\" karya Mark Manson adalah panduan pengembangan diri yang menentang saran \"berpikir positif\" secara konstan. Buku ini mengajarkan kita untuk lebih realistis, berani menerima kenyataan pahit, dan memilah prioritas.', 75000.00, 9, 4, 'Mark Manson', '6a4c9bafd759d-cover sebuah seni untuk bersikap bodo amat.jpg'),
(13, 5, 'Nebula', 'Sekuel langsung dari novel Selena. Fokus utamanya menceritakan persahabatan tiga mahasiswa Akademi Bayangan Tingkat Tinggi yang memiliki rencana besar untuk bertualang ke tempat jauh. Di tengah perjalanan, mereka harus menghadapi konflik besar, pengkhianatan, dan ambisi yang menguji kesetiaan persahabatan tersebut.', 85000.00, 10, 1, 'Tere Liye', '6a521f3f0b504-cover nebula.jpg'),
(14, 5, 'Nenek Hebat Dari Saga', 'Novel inspiratif karya Yoshichi Shimada (nama asli Akihiro Tokunaga). Buku ini menceritakan kisah nyata Akihiro yang dititipkan ibunya kepada sang nenek di Saga setelah ayahnya meninggal akibat bom atom Hiroshima. Meskipun hidup dalam kemiskinan ekstrem pasca-Perang Dunia II, neneknya selalu ceria, kreatif, dan mengajarkan cucunya cara hidup bahagia dengan cara bersyukur.', 75000.00, 10, 1, 'Yosichi Shimada', '6a52206982926-cover nenek hebat dari saga.jpg'),
(15, 5, 'Damar Kambang', 'Menyingkap tirai kusam tradisi dan budaya patriarki masyarakat Madura. Ceritanya berpusat pada Cebbhing, seorang gadis berusia 14 tahun yang terjebak dalam dilema pernikahan dini demi menjaga harkat dan martabat keluarganya. Buku ini diterbitkan oleh Kepustakaan Populer Gramedia (KPG).', 80000.00, 10, 1, 'Muna Masyari', '6a5220fb3ce03-cover damar kambang.jpg'),
(16, 5, 'Seminggu Sebelum Aku Mati', 'Seminggu Sebelum Aku Mati adalah kisah yang menggugah tentang seseorang yang mengetahui bahwa hidupnya akan berakhir dalam waktu tujuh hari. Dalam waktu yang terbatas ini, ia berusaha menyelesaikan berbagai hal yang belum tuntas, mulai dari hubungan dengan keluarga, sahabat, hingga cinta sejati.', 80000.00, 10, 1, 'Seo Eun-chae', '6a52215a57ad2-cover seminggu sebelum aku mati.jpg'),
(17, 5, 'Kimia Forensik', 'Buku Kimia Forensik mengulas penerapan ilmu kimia dalam penegakan hukum dan investigasi kriminal. Buku ini membahas identifikasi material di Tempat Kejadian Perkara (TKP), analisis toksikologi untuk mendeteksi racun atau obat-obatan, serta prosedur analisis barang bukti yang dapat dipertanggungjawabkan di pengadilan', 80000.00, 10, 2, 'Prof. Riyanto, Ph. D', '6a52220ec2cae-cover kimia forensik.avif'),
(18, 5, 'Why? The Human Body - Tubuh Kita', 'Dari ujung rambut sampai ujung kaki, adalah bagian tubuh kita yang penting. Jantung memompa darah ke seluruh tubuh, paru-paru membantu kita bernapas, tulang melindungi organ tubuh bagian dalam, mata melihat, telinga mendengar…. Rambut hidung diperlukan untuk menyaring debu yang masuk ke hidung. Setiap organ tubuh kita melaksanakan perannya masing-masing dengan sempurna. Tubuh kita sungguh menakjubkan dan misterius', 110000.00, 10, 2, 'YeaRimDang', '6a52227209a42-cover why the humand body.jpg'),
(19, 5, 'Ensiklopedia Saintis Junior: Eksperimen Sains', 'Melalui buku ini anak anak sudah dapat belajar tentang ilmu kimia, biologi dan fisika melalui eksperimen sains. Jadilah saintis junior dan temukan sendiri prinsip-prinsip pokok ilmu kimia, biologi, dan fisika, dengan ensiklopedia berilustrasi ini. Setiap proyek eksperimen berisi langkah-langkah instruksi yang mudah diikuti. Tak hanya itu, setiap eksperimen disertai penjelasan prinsip sains dasar dan aplikasinya di kehidupan sehari-hari melalui teks dan foto-foto menakjubkan dari seluruh dunia.', 120000.00, 10, 2, 'Thomas Canavan', '6a5222f514d92-cover Ensiklopedia Saintis Junior Eksperimen Sains.jpg'),
(20, 5, 'Real Masjid', 'Belajar agama bisa dilakukan dengan cara menyenangkan. Misalnya, dengan membaca komik Real Masjid. Real Masjid merupakan komik Islami yang tidak hanya mengocok perut karena lucu, tetapi juga dapat menambah pengetahuan dan pemahaman tentang dunia Islam.', 60000.00, 9, 3, 'TONY TRAX', '6a5223933d27c-cover real masjid.jpg'),
(21, 5, 'Lagak Jakarta: 100 \'Tokoh\' yang Mewarnai Jakarta', 'Siapakah tokoh yang paling berpengaruh di Jakarta? Benny dan Mice mengajukan 100 tokoh yang paling banyak menyumbang dalam peri kehidupan Jakarta sehari-hari.', 60000.00, 10, 3, 'Benny Rachmadi & Muhammad \"Mice\" Misrad', '6a5223ff725e9-cover Lagak Jakarta 100 Tokoh yang Mewarnai Jakarta.jpg'),
(22, 5, 'Komik Next G: Fans Baru', 'Membaca merupakan kegiatan yang sangat menyenangkan. Terlebih lagi jika membaca cerita seru yang dikemas dalam bentuk komik. Komik untuk anak pun kini hadir dalam beragam pilihan yang menarik.', 45000.00, 9, 3, 'Atiqah Zakkyah Az-Zahra', '6a5224a0c6889-cover Komik Next G Fans Baru.avif'),
(23, 5, 'Si Juki: Lika-Liku Anak Kos', '\"Si Juki: Lika-Liku Anak Kos\" mengisahkan keseharian Juki yang nama aslinya adalah Muhammad Marzuki sebagai representasi mahasiswa yang hidup sebagai anak kos yang kreatif dan kocak. Dibarengi juga dengan berbagai macam kesulitan yang biasa dialami oleh anak kos seperti kehabisan uang bulanan, tugas yang menumpuk dan ibu kos yang menagih uang sewa. Sampai hal-hal receh lainnya khas anak kos yang siap mengocok perut pembaca dengan komedi dan humor yang disajikan, seperti tips-tips memasak hemat ala anak kos dan mungkin juga tata cara merawat kecoa yang baik dan benar.\r\nMeskipun ia sebenarnya merupakan anak yang pada awalnya tidak diharapkan oleh keluarganya, namun Juki tidak terlalu membawa pusing perihal tersebut, Juki yang kemudian menjadi dikenal banyak orang karena karakternya yang berani beda, alias anti mainstream dan nyentrik bagi sebagian orang ini sukses menjadi hot topic di kalangan masyarakat dan membuatnya menjadi selebgram dadakan. Memiliki ratusan ribu followers di media sosial tidak membuat Juki meninggalkan mie instan kesukaannya dan kebiasaan-kebiasaan lainnya yang digandrungi sebagai anak kos, baginya lifestyle anak kos sangat cocok dengannya, apalagi ditemani mie instan kesayangannya. Satu lagi prinsip yang ia pegang teguh sebagai anak kos! Asal sesuatu itu gratis, maka sikat saja!.', 75000.00, 9, 3, 'Faza Meonk', '6a5225840fe07-cover Si Juki Lika-Liku Anak Kos.avif'),
(24, 5, 'The Long Tail', 'buku bisnis klasik yang mengubah paradigma pemasaran. Inti utamanya: masa depan bisnis bukan lagi tentang menjual produk hit atau terlaris saja, melainkan menjual lebih sedikit dari banyak produk khusus (ceruk/niche) yang jumlahnya tak terbatas.', 90000.00, 10, 4, 'Chris Anderson', '6a522665ecbe6-cover the long tail.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `book_id`, `buyer_id`, `order_id`, `rating`, `review_text`, `created_at`) VALUES
(13, 7, 4, 67, 5, 'Bukunya lucu bangettt', '2026-07-10 09:12:19'),
(14, 9, 4, 66, 5, 'Buku yang kerenn', '2026-07-10 09:12:28'),
(15, 4, 4, 68, 5, 'Novel Terbaikkk!!!', '2026-07-11 09:25:27'),
(16, 7, 11, 69, 5, 'bikin aku ketawa bangett', '2026-07-11 09:28:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','seller','buyer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `created_at`, `is_verified`) VALUES
(4, 'Muhammad Naufal', 'buyer@gmail.com', '$2y$10$UuJoUkoyR2/F2hHpB/mBY.ghugzeipplq1gAVrrntuVQgi2Q0zn1G', 'Muhammad Naufal', '087885005985', 'Jl Sultan Agung, Gg inhutani RT 04/11 No.01, KOTA BEKASI, MEDAN SATRIA, JAWA BARAT, ID, 17132', 'buyer', '2026-06-29 05:24:32', 0),
(5, 'DuniaBuku', 'seller@gmail.com', '$2y$10$jZip1ihzXDvYA5n52Jmv9.6LPU9AtYL70itZI.NvqXtFktS6SxXeu', NULL, NULL, NULL, 'seller', '2026-06-29 05:25:13', 1),
(6, 'Buku Bekasi', 'seller1@gmail.com', '$2y$10$Fqtq81VOOuYWPzdA0ZtTWusCwy.SpBvviyHzC3HjVIJpJwd6Qcgq.', NULL, NULL, NULL, 'seller', '2026-06-29 05:27:54', 1),
(9, 'Admin Utama', 'admin@gmail.com', '$2y$10$RVc5QvrtTPQ9BVfB20rH6uD5eYEXHotyQfkIZaBM3qAUl.YPimThK', NULL, NULL, NULL, 'admin', '2026-06-30 18:19:31', 0),
(11, 'Ahmad Fadhillah', 'buyer1@gmail.com', '$2y$10$3Pd8NEzHH2HGifdxu2jkSOO26shTvPPINuSldE5xmT5ORCg4JjRy6', 'Ahmad Fadhilah', '087658499381', 'Jl Sultan Agung, Gg inhutani RT 04/11 No.01, KOTA BEKASI, MEDAN SATRIA, JAWA BARAT, ID, 17132', 'buyer', '2026-06-30 18:55:01', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_notifications` (`user_id`,`user_type`,`is_read`,`created_at`),
  ADD KEY `idx_unread_notifications` (`user_id`,`user_type`,`is_read`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
