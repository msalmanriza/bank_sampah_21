-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Waktu pembuatan: 29 Apr 2026 pada 20.28
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bank_sampah_21`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumentasi`
--

CREATE TABLE `dokumentasi` (
  `id_dokumentasi` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `gambar2` varchar(255) DEFAULT NULL,
  `gambar3` varchar(255) DEFAULT NULL,
  `gambar4` varchar(255) DEFAULT NULL,
  `gambar5` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokumentasi`
--

INSERT INTO `dokumentasi` (`id_dokumentasi`, `judul`, `deskripsi`, `gambar`, `tanggal_upload`, `gambar2`, `gambar3`, `gambar4`, `gambar5`) VALUES
(6, 'Rapat Perdana BSM 21', 'kegiatan rapat tahunan BSM 21', '1777183710_Rapat BSM.jpg', '2026-04-26 06:08:30', NULL, NULL, NULL, NULL),
(7, 'Penimbangan ke 59', 'kegiatan penimbangan rutin BSM 21 ke 59', '1777183780_dokumen BSM 1.jpg', '2026-04-26 06:09:40', NULL, NULL, NULL, NULL),
(8, 'Kenalan dengan petugas BSM 21 😄', 'para punggawa bank sampah manyar 21', '1777183973_PP BSM 21.jpg', '2026-04-26 06:12:53', NULL, NULL, NULL, NULL),
(9, 'Jalan2 bersama anggota BSM', 'test', '1777192527_kegiatan BSM 2.jpg', '2026-04-26 08:35:27', NULL, NULL, NULL, NULL),
(10, 'test', 'asdasda', '1777193111_kegiatan BSM 1.jpg', '2026-04-26 08:45:11', NULL, NULL, NULL, NULL),
(11, 'penimbangan 3', 'wadawaw', '1777193127_kegiatan BSM 3.jpg', '2026-04-26 08:45:27', NULL, NULL, NULL, NULL),
(12, 'profile nasabah tamvan', 'wkwkwkwk', '1777193184_Foto Profile Nsbh.jpg', '2026-04-26 08:46:24', NULL, NULL, NULL, NULL),
(14, 'test 1', 'wadaw', '1777196293_dokumen BSM 2.jpg', '2026-04-26 09:38:13', NULL, NULL, NULL, NULL),
(21, 'testing', 'test 1', '1777198269_0_Day 2 Magang BRIN.jpeg', '2026-04-26 10:11:09', '1777198269_1_Day 3 Magang BRIN.jpeg', '1777198269_2_Foto Presentasi (BI).jpeg', '1777198269_3_Ijasah salman.jpeg', '1777198269_4_PP Salman Batik.jpeg'),
(22, 'bank sampah digital', 'testing', '1777211435_0_BSM digital.jpg', '2026-04-26 13:50:35', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_sampah`
--

CREATE TABLE `jenis_sampah` (
  `id_sampah` int(11) NOT NULL,
  `nama_sampah` varchar(50) NOT NULL,
  `kategori` varchar(30) DEFAULT NULL,
  `harga_perkg` decimal(15,2) NOT NULL,
  `satuan` varchar(10) DEFAULT 'kg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jenis_sampah`
--

INSERT INTO `jenis_sampah` (`id_sampah`, `nama_sampah`, `kategori`, `harga_perkg`, `satuan`) VALUES
(1, 'Plastik', 'Plastik', 2000.00, 'kg'),
(2, 'Kertas', 'Kertas', 1500.00, 'kg'),
(3, 'Logam', 'Logam', 5000.00, 'kg'),
(4, 'Kaca', 'Kaca', 1000.00, 'kg'),
(5, 'Kardus', 'Kertas & Karton', 2500.00, 'kg'),
(6, 'Botol Plastik', 'Plastik', 3000.00, 'kg'),
(7, 'Kertas Koran', 'Kertas', 2000.00, 'kg'),
(8, 'Kaleng Minuman', 'Logam', 4000.00, 'kg'),
(9, 'Besi', 'Logam', 6000.00, 'kg'),
(10, 'Aluminium', 'Anorganik', 10000.00, 'kg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notif` int(11) NOT NULL,
  `id_users` int(11) DEFAULT NULL,
  `pesan` text NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('read','unread') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penarikan`
--

CREATE TABLE `penarikan` (
  `id_tarik` int(11) NOT NULL,
  `id_users` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `jumlah_tarik` decimal(15,2) NOT NULL,
  `tahun_periode` year(4) NOT NULL,
  `tanggal_pembagian` timestamp NOT NULL DEFAULT current_timestamp(),
  `keterangan` varchar(255) DEFAULT 'Pembagian kas tahunan (Cash)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesan_chat`
--

CREATE TABLE `pesan_chat` (
  `id_chat` int(11) NOT NULL,
  `id_pengirim` int(11) NOT NULL,
  `id_penerima` int(11) NOT NULL,
  `isi_pesan` text NOT NULL,
  `waktu_kirim` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_baca` enum('belum','sudah') DEFAULT 'belum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesan_chat`
--

INSERT INTO `pesan_chat` (`id_chat`, `id_pengirim`, `id_penerima`, `isi_pesan`, `waktu_kirim`, `status_baca`) VALUES
(1, 4, 1, 'test', '2026-04-24 21:12:22', 'belum'),
(2, 4, 1, 'halo min', '2026-04-26 08:17:20', 'belum');

-- --------------------------------------------------------

--
-- Struktur dari tabel `setor_sampah`
--

CREATE TABLE `setor_sampah` (
  `id_setor` int(11) NOT NULL,
  `id_users` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `id_sampah` int(11) DEFAULT NULL,
  `berat` float NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `tanggal_setor` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `setor_sampah`
--

INSERT INTO `setor_sampah` (`id_setor`, `id_users`, `id_admin`, `id_sampah`, `berat`, `total_harga`, `tanggal_setor`) VALUES
(1, 5, 1, 10, 1, 10000.00, '2026-04-28 17:04:14'),
(2, 5, 1, 3, 5, 25000.00, '2026-04-28 17:04:14'),
(3, 4, 1, 10, 2, 20000.00, '2026-04-28 17:06:56'),
(4, 4, 1, 1, 10, 20000.00, '2026-04-28 17:06:56'),
(5, 5, 1, 1, 0.5, 1000.00, '2026-04-28 17:09:13'),
(6, 5, 1, 4, 1.5, 1500.00, '2026-04-28 17:09:13'),
(7, 4, 1, 1, 5, 10000.00, '2026-04-28 17:11:13'),
(8, 4, 1, 10, 2, 20000.00, '2026-04-28 17:11:13'),
(9, 5, 1, 9, 12, 72000.00, '2026-04-28 17:12:04'),
(10, 5, 1, 6, 6, 18000.00, '2026-04-28 17:12:04'),
(11, 5, 1, 10, 5, 50000.00, '2026-04-28 17:23:07'),
(12, 5, 1, 10, 5, 50000.00, '2026-04-28 17:23:27'),
(13, 4, 1, 1, 5, 10000.00, '2026-04-28 17:23:52'),
(14, 5, 1, 6, 10, 30000.00, '2026-04-28 17:24:32'),
(15, 4, 1, 10, 2, 20000.00, '2026-04-28 17:26:37'),
(16, 5, 1, 10, 1, 10000.00, '2026-04-28 17:33:02'),
(17, 5, 1, 8, 5, 20000.00, '2026-04-28 17:33:02'),
(18, 4, 1, 10, 2, 20000.00, '2026-04-28 17:33:40'),
(19, 4, 1, 1, 5, 10000.00, '2026-04-28 17:33:40'),
(20, 4, 1, 1, 5, 10000.00, '2026-04-28 17:35:55'),
(21, 4, 1, 10, 2, 20000.00, '2026-04-28 17:35:55'),
(22, 4, 1, 9, 8, 48000.00, '2026-04-28 17:36:30'),
(23, 5, 1, 4, 6, 6000.00, '2026-04-28 17:36:54'),
(24, 5, 1, 9, 6, 36000.00, '2026-04-28 17:36:54'),
(25, 4, 1, 6, 12, 36000.00, '2026-04-28 17:37:59'),
(26, 4, 1, 8, 8, 32000.00, '2026-04-28 17:37:59'),
(27, 4, 1, 10, 2, 20000.00, '2026-04-28 17:37:59'),
(28, 4, 1, 3, 5, 25000.00, '2026-04-28 17:37:59'),
(29, 5, 1, 4, 8, 8000.00, '2026-04-28 17:38:47'),
(30, 5, 1, 1, 9, 18000.00, '2026-04-28 17:38:47'),
(31, 4, 1, 10, 12, 120000.00, '2026-04-28 17:43:55'),
(32, 4, 1, 1, 2, 4000.00, '2026-04-28 17:43:55'),
(33, 4, 1, 10, 2, 20000.00, '2026-04-28 18:15:47'),
(34, 4, 1, 1, 5, 10000.00, '2026-04-28 18:15:47'),
(35, 5, 1, 1, 5, 10000.00, '2026-04-29 07:11:48'),
(36, 5, 1, 9, 2, 12000.00, '2026-04-29 07:11:48'),
(37, 4, 1, 6, 5, 15000.00, '2026-04-29 07:12:11'),
(38, 4, 1, 10, 2, 20000.00, '2026-04-29 07:12:11'),
(39, 5, 1, 10, 5, 50000.00, '2026-04-29 07:35:06'),
(40, 5, 1, 9, 2, 12000.00, '2026-04-29 07:35:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_users` int(11) DEFAULT NULL,
  `id_sampah` int(11) DEFAULT NULL,
  `berat` decimal(10,2) DEFAULT NULL,
  `saldo` int(11) DEFAULT NULL,
  `status` enum('pending','selesai','ditolak') DEFAULT 'pending',
  `jenis_transaksi` enum('setor','tarik') NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_users`, `id_sampah`, `berat`, `saldo`, `status`, `jenis_transaksi`, `jumlah`, `tanggal`) VALUES
(1, 5, 1, 5.00, 10000, 'selesai', 'setor', 0.00, '2026-04-29 07:11:48'),
(2, 5, 9, 2.00, 12000, 'selesai', 'setor', 0.00, '2026-04-29 07:11:48'),
(3, 4, 6, 5.00, 15000, 'selesai', 'setor', 0.00, '2026-04-29 07:12:11'),
(4, 4, 10, 2.00, 20000, 'selesai', 'setor', 0.00, '2026-04-29 07:12:11'),
(5, 5, 10, 5.00, 50000, 'selesai', 'setor', 0.00, '2026-04-29 07:35:06'),
(6, 5, 9, 2.00, 12000, 'selesai', 'setor', 0.00, '2026-04-29 07:35:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_users` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(250) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `role` enum('admin','nasabah') DEFAULT 'nasabah',
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_users`, `nama`, `username`, `password`, `email`, `no_hp`, `alamat`, `saldo`, `role`, `tanggal_daftar`) VALUES
(1, 'Administrator', 'admin', 'adminbanksampah21', 'adminbsm21@gmail.com', NULL, NULL, 0.00, 'admin', '2026-04-06 18:11:46'),
(4, 'herawati', 'herawati', '$2y$10$vNd1JgQE1hnaNKIgHN/pUusrrQFxHKq7UfilEyF9HGW.Aa8wWWkbu', 'herawati21@gmail.com', '081297547930', 'Villa dago tol blok i3 no 29 RT 02/21 ', 0.00, 'nasabah', '2026-04-24 13:44:59'),
(5, 'mrfrengky', 'mrfrengky', '$2y$10$LveJYMGs4WWBkaBfGPBcLOk0zmZH1Y6YoHAa9/rZ2O4RDsN4gt0d2', 'boskuenjoy@gmail.com', '081290191221', 'pamoelang', 0.00, 'nasabah', '2026-04-26 14:18:59'),
(8, 'faisal amri septiyanto', 'faisal amri septiyanto', '$2y$10$Nlpi/Zu9xntlOIBwHw8Wx.ybS1V7XKhegziOSxiM8dstoTd3dizqq', 'faisal21@gmail.com', '082113123892', 'gunung sindur', 0.00, 'nasabah', '2026-04-29 17:24:55'),
(9, 'dopusgani', 'dopusgani', '$2y$10$Oskx5GHSw/Fp9BVp7Rm9t.iYxgKvEvvKUIU443hj/d.8jKyJsqke6', 'dopus21@gmail.com', '0819289089090', 'blok i3', 0.00, 'nasabah', '2026-04-29 17:45:07');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD PRIMARY KEY (`id_dokumentasi`);

--
-- Indeks untuk tabel `jenis_sampah`
--
ALTER TABLE `jenis_sampah`
  ADD PRIMARY KEY (`id_sampah`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notif`),
  ADD KEY `id_users` (`id_users`);

--
-- Indeks untuk tabel `penarikan`
--
ALTER TABLE `penarikan`
  ADD PRIMARY KEY (`id_tarik`),
  ADD KEY `id_users` (`id_users`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `pesan_chat`
--
ALTER TABLE `pesan_chat`
  ADD PRIMARY KEY (`id_chat`),
  ADD KEY `id_pengirim` (`id_pengirim`),
  ADD KEY `id_penerima` (`id_penerima`);

--
-- Indeks untuk tabel `setor_sampah`
--
ALTER TABLE `setor_sampah`
  ADD PRIMARY KEY (`id_setor`),
  ADD KEY `id_users` (`id_users`),
  ADD KEY `id_admin` (`id_admin`),
  ADD KEY `id_sampah` (`id_sampah`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_users` (`id_users`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `dokumentasi`
--
ALTER TABLE `dokumentasi`
  MODIFY `id_dokumentasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `jenis_sampah`
--
ALTER TABLE `jenis_sampah`
  MODIFY `id_sampah` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notif` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penarikan`
--
ALTER TABLE `penarikan`
  MODIFY `id_tarik` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pesan_chat`
--
ALTER TABLE `pesan_chat`
  MODIFY `id_chat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `setor_sampah`
--
ALTER TABLE `setor_sampah`
  MODIFY `id_setor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penarikan`
--
ALTER TABLE `penarikan`
  ADD CONSTRAINT `penarikan_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE,
  ADD CONSTRAINT `penarikan_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id_users`);

--
-- Ketidakleluasaan untuk tabel `pesan_chat`
--
ALTER TABLE `pesan_chat`
  ADD CONSTRAINT `pesan_chat_ibfk_1` FOREIGN KEY (`id_pengirim`) REFERENCES `users` (`id_users`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesan_chat_ibfk_2` FOREIGN KEY (`id_penerima`) REFERENCES `users` (`id_users`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `setor_sampah`
--
ALTER TABLE `setor_sampah`
  ADD CONSTRAINT `setor_sampah_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE,
  ADD CONSTRAINT `setor_sampah_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id_users`),
  ADD CONSTRAINT `setor_sampah_ibfk_3` FOREIGN KEY (`id_sampah`) REFERENCES `jenis_sampah` (`id_sampah`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
