-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2026 at 07:18 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nh_tailor`
--

-- --------------------------------------------------------

--
-- Table structure for table `artikel`
--

CREATE TABLE `artikel` (
  `id_artikel` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `tanggal_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artikel`
--

INSERT INTO `artikel` (`id_artikel`, `judul`, `slug`, `isi`, `gambar`, `tanggal_created`) VALUES
(1, '5 Alasan Mengapa Gaun Custom Tailor Lebih Anggun Dibanding Baju Jadi', '5-alasan-gaun-custom-tailor-lebih-anggun', 'Setiap wanita menginginkan penampilan yang sempurna di momen-momen spesial seperti pernikahan, pesta, atau acara formal lainnya. Saat memilih busana, pilihan sering kali jatuh antara membeli baju siap pakai (ready-to-wear) atau menjahit kustom (custom tailor).\r\n\r\nMeskipun baju siap pakai terkesan praktis, gaun yang dibuat khusus oleh penjahit profesional menawarkan nilai artistik dan kenyamanan yang tidak bisa ditandingi. Berikut adalah 5 alasannya:\r\n\r\n1. Ukuran Presisi Sesuai Bentuk Tubuh\r\nBaju buatan pabrik dibuat berdasarkan standar ukuran umum (S, M, L, XL). Padahal, proporsi tubuh setiap wanita unik. Dengan menjahit kustom, setiap detail lingkar dada, pinggang, hingga panjang gaun diukur secara presisi untuk menonjolkan kelebihan siluet tubuh Anda.\r\n\r\n2. Bebas Menentukan Desain Eksklusif\r\nAnda tidak perlu khawatir tampil seragam dengan tamu lain di sebuah acara. Anda memiliki kebebasan penuh untuk mendesain gaun impian, mulai dari bentuk leher (neckline), potongan lengan, hingga ornamen gaun.\r\n\r\n3. Kualitas Jahitan Tangan (Handmade) yang Detail\r\nProses pembuatan gaun custom melibatkan ketelitian tinggi. Setiap payet, aplikasi renda, dan lipatan kain dipasang secara cermat menggunakan teknik jahitan tangan untuk memastikan ketahanan dan estetika kelas atas.\r\n\r\n4. Kenyamanan Pemilihan Bahan\r\nAnda dapat memilih sendiri jenis kain yang paling nyaman di kulit—mulai dari sutra yang lembut, brokat eksklusif, hingga kain tile yang ringan—sehingga nyaman dipakai seharian.\r\n\r\n5. Investasi Busana Jangka Panjang\r\nGaun kustom berkualitas tinggi dirancang untuk bertahan lama. Bahan yang kokoh dan jahitan yang rapi membuat gaun dapat disimpan sebagai kenangan berharga atau digunakan kembali di kemudian hari.\r\n\r\nIngin mewujudkan gaun impian dengan potongan yang pas di tubuh Anda? Konsultasikan desain dan bahan pilihan Anda langsung bersama studio kami.', 'artikel-custom-tailor.jpg', '2026-08-21 13:11:50'),
(2, 'Cara Merawat Kebaya Brokat dan Gaun Pesta Agar Tetap Awet & Tidak Kusam', 'cara-merawat-kebaya-brokat-dan-gaun-pesta', 'Kebaya modern dan gaun pesta berbahan halus seperti brokat, tulle, dan sutra memerlukan perhatian khusus dalam perawatannya. Kesalahan kecil saat mencuci atau menyimpan bisa merusak struktur kain dan ornamen payet.\r\n\r\nBerikut adalah tips praktis menjaga kualitas busana pesta Anda agar tetap seperti baru:\r\n\r\n- Jangan Gunakan Mesin Cuci\r\nPutaran mesin cuci dapat merobek serat brokat yang tipis dan merusak jahitan payet. Cukup rendam busana sebentar di dalam air dingin yang dicampur dengan detergen cair lembut atau sampo bayi.\r\n\r\n- Hindari Memeras Kain Terlalu Keras\r\nCukup tekan-tekan busana secara perlahan untuk mengeluarkan sisa air. Memeras dengan cara memelintir kain akan mengubah bentuk asli gaun dan melonggarkan jahitan.\r\n\r\n- Keringkan di Tempat Teduh\r\nJemur busana secara mendatar (flat dry) di atas handuk kering atau gantung menggunakan hanger berbusa. Hindari paparan sinar matahari langsung agar warna kain tidak cepat memudar.\r\n\r\n- Gunakan Pelapis Saat Menyetrika\r\nHindari menempelkan plat setrika langsung pada bahan brokat atau payet. Gunakan kain katun tipis di atasnya atau gunakan setrika uap (steamer) dengan jarak aman.\r\n\r\n- Simpan dalam Garment Bag\r\nGantung gaun menggunakan garment bag ber-cover kain (bukan plastik kedap udara) agar ada sirkulasi udara dan terhindar dari debu serta jamur.', 'artikel-perawatan-kebaya.jpg', '2026-08-21 13:11:50'),
(3, 'Tren Gaun Pernikahan Modern: Sentuhan Minimalis Elegan dengan Detail Payet', 'tren-gaun-pernikahan-modern-minimalis', 'Tren gaun pengantin terus berkembang seiring berjalannya waktu. Jika dahulu gaun dengan layer tebal dan megah menjadi pilihan utama, kini makin banyak calon pengantin wanita yang beralih ke gaya Minimalis Elegan.\r\n\r\nGaya ini memadukan potongan busana yang bersih (clean lines) dengan aksen mewah yang terukur. Apa saja ciri khas tren ini?\r\n\r\n1. Siluet A-Line dan Mermaid yang Sleek: Potongan yang mengikuti lekuk tubuh tanpa kesan berlebihan memberikan ilusi tubuh yang lebih tinggi dan jenjang.\r\n\r\n2. Aksen Payet Terfokus: Dibanding memenuhi seluruh permukaan gaun, aplikasi payet dan mutiara difokuskan pada area tertentu seperti bahu, pergelangan tangan, atau bagian pinggang sebagai penegak aksen.\r\n\r\n3. Perpaduan Brokat & Satin Silk: Penggunaan bahan satin berkualitas tinggi yang memberikan kilau alami dikombinasikan dengan sentuhan brokat di area dada atau punggung.\r\n\r\nBagi calon pengantin yang menyukai konsep pernikahan intimate maupun resepsi di gedung bertema modern, gaya minimalis ini memberikan impresi yang abadi (timeless) dan anggun.', 'artikel-gaun-pengantin.jpg', '2026-08-21 13:11:50');

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id` int(11) NOT NULL,
  `foto` varchar(225) NOT NULL,
  `keterangan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `galeri`
--

INSERT INTO `galeri` (`id`, `foto`, `keterangan`) VALUES
(4, 'photo_6199599126874801800_y.jpg', 'Gaun Bridesmaid'),
(5, 'photo_6149710612129319690_y.jpg', 'Pakaian Pesta'),
(6, 'photo_6149710612129319804_y.jpg', 'Dress Batik Custom'),
(7, 'photo_6138809740315182964_y.jpg', 'Kebaya Wisuda'),
(8, 'photo_6228657904936928654_y.jpg', 'Baju Pesta Custom'),
(9, 'photo_6141086240485789154_y.jpg', 'Costum Rebana'),
(10, 'photo_6221750708470484011_y.jpg', 'Batik Pasangan'),
(11, '20260518170051_6a0b29a32ba90.jpg', 'Pakaian Wanita');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `nomortelepon` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('Y','N') NOT NULL,
  `rule` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`id`, `nama`, `alamat`, `nomortelepon`, `username`, `email`, `password`, `status`, `rule`) VALUES
(1, 'Chintia Sendi Prilia Mangamba', 'Pandu Lingkungan 3', '085395390904', 'chsendy', 'chintiasendy@gmail.com', '$2y$10$XPBeN86YzrU2G.t0owz7Je2FCatbx08oolojpJRK6LnShnCFBSkuW', 'Y', 'superadmin'),
(2, 'Stenly Karungu', 'Pandu', '082189300336', 'stenly', 'stenlykarungu@gmail.com', '$2y$10$YysEjxa/yBwSmkH3nZD9ReZJ5LUfizZekVZgnCGZuZgNaB8Ggxyqe', 'Y', 'customer'),
(3, 'Calvin', 'paal 2', '09867456478', 'calvin', 'yeheskielcalvin@gmail.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Y', 'customer'),
(4, 'sherly', 'paal 2', '081243238766', 'sherly', 'sherly@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Y', 'customer');

-- --------------------------------------------------------

--
-- Table structure for table `nh_schema_migrations`
--

CREATE TABLE `nh_schema_migrations` (
  `version` int(11) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nh_schema_migrations`
--

INSERT INTO `nh_schema_migrations` (`version`, `applied_at`) VALUES
(1, '2026-11-10 04:58:10');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `nama_pelanggan` varchar(50) NOT NULL,
  `no_hp` varchar(50) NOT NULL,
  `username_customer` varchar(50) DEFAULT NULL,
  `jenis_pakaian` varchar(50) NOT NULL,
  `total_biaya` varchar(50) NOT NULL,
  `dp` varchar(50) NOT NULL,
  `kain_foto` varchar(255) DEFAULT NULL,
  `detail_model` text NOT NULL,
  `tgl_selesai` date NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `nama_pelanggan`, `no_hp`, `username_customer`, `jenis_pakaian`, `total_biaya`, `dp`, `kain_foto`, `detail_model`, `tgl_selesai`, `status`) VALUES
(8, 'stenly', '082189300336', 'stenly', 'Kebaya Modern', '430000', '140000', NULL, 'secepatnya', '2026-05-30', 'Fitting / Pas'),
(9, 'sherly', '081243238766', 'sherly', 'Wedding Dress', '1700000', '1500000', NULL, 'njgeuy', '2026-08-29', 'Fitting / Pas'),
(10, 'sendy', '08135118450', '', 'Kebaya Modern', '450000', '200000', NULL, 'fftdjihu', '2026-10-30', 'Menunggu Antrian');

-- --------------------------------------------------------

--
-- Table structure for table `ukuran_customer`
--

CREATE TABLE `ukuran_customer` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nama_ukuran` varchar(100) NOT NULL,
  `lingkar_dada` varchar(20) NOT NULL,
  `lingkar_pinggang` varchar(20) NOT NULL,
  `lingkar_pinggul` varchar(20) NOT NULL,
  `lebar_bahu` varchar(20) NOT NULL,
  `panjang_lengan` varchar(20) NOT NULL,
  `panjang_baju` varchar(20) NOT NULL,
  `panjang_rok` varchar(20) NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `lebar_punggung` varchar(20) DEFAULT NULL,
  `panjang_punggung` varchar(20) DEFAULT NULL,
  `panjang_pinggang` varchar(20) DEFAULT NULL,
  `panjang_panggul` varchar(20) DEFAULT NULL,
  `panjang_muka` varchar(20) DEFAULT NULL,
  `lebar_muka` varchar(20) DEFAULT NULL,
  `lingkar_lengan` varchar(20) DEFAULT NULL,
  `kerung_lengan` varchar(20) DEFAULT NULL,
  `lingkar_pinggang_celana` varchar(20) DEFAULT NULL,
  `lingkar_pesak` varchar(20) DEFAULT NULL,
  `lingkar_paha` varchar(20) DEFAULT NULL,
  `lingkar_betis_bawah` varchar(20) DEFAULT NULL,
  `panjang_celana` varchar(20) DEFAULT NULL,
  `lingkar_sabrina` varchar(20) DEFAULT NULL,
  `tinggi_leher` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ukuran_customer`
--

INSERT INTO `ukuran_customer` (`id`, `username`, `nama_ukuran`, `lingkar_dada`, `lingkar_pinggang`, `lingkar_pinggul`, `lebar_bahu`, `panjang_lengan`, `panjang_baju`, `panjang_rok`, `catatan`, `created_at`, `updated_at`, `lebar_punggung`, `panjang_punggung`, `panjang_pinggang`, `panjang_panggul`, `panjang_muka`, `lebar_muka`, `lingkar_lengan`, `kerung_lengan`, `lingkar_pinggang_celana`, `lingkar_pesak`, `lingkar_paha`, `lingkar_betis_bawah`, `panjang_celana`, `lingkar_sabrina`, `tinggi_leher`) VALUES
(2, 'stenly', 'Kebaya', '345', '9', '66', '66', '55', '54.9', '55', 'jnjhbjf', '2026-05-25 04:58:27', '2026-05-26 02:51:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`id_artikel`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nh_schema_migrations`
--
ALTER TABLE `nh_schema_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ukuran_customer`
--
ALTER TABLE `ukuran_customer`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id_artikel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ukuran_customer`
--
ALTER TABLE `ukuran_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
