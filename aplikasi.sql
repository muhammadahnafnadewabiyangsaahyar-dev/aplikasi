-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 31 Okt 2025 pada 17.29
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aplikasi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `waktu_masuk` datetime DEFAULT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `status_lokasi` enum('Valid','Tidak Valid') DEFAULT NULL,
  `latitude_absen` decimal(10,8) DEFAULT NULL,
  `longitude_absen` decimal(11,8) DEFAULT NULL,
  `foto_absen` varchar(255) DEFAULT NULL,
  `tanggal_absensi` date DEFAULT NULL,
  `menit_terlambat` int(11) NOT NULL,
  `status_keterlambatan` enum('tepat waktu','terlambat kurang dari 20 menit','terlambat lebih dari 20 menit') NOT NULL,
  `status_lembur` enum('Pending','Approved','Rejected','Not Applicable') NOT NULL DEFAULT 'Not Applicable'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cabang`
--

CREATE TABLE `cabang` (
  `id` int(11) NOT NULL,
  `nama_cabang` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius_meter` int(11) NOT NULL,
  `nama_shift` varchar(255) NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_keluar` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cabang`
--

INSERT INTO `cabang` (`id`, `nama_cabang`, `latitude`, `longitude`, `radius_meter`, `nama_shift`, `jam_masuk`, `jam_keluar`) VALUES
(1, 'Citraland Gowa', -5.17994582, 119.46337357, 50, 'pagi', '07:00:00', '15:00:00'),
(2, 'Adhyaksa', -5.16039705, 119.44607614, 50, 'pagi', '07:00:00', '15:00:00'),
(3, 'BTP', -5.12957150, 119.50036078, 50, 'pagi', '08:00:00', '15:00:00'),
(4, 'Citraland Gowa', -5.17994582, 119.46337357, 50, 'middle', '13:00:00', '21:00:00'),
(5, 'Citraland Gowa', -5.17994582, 119.46337357, 50, 'sore', '15:00:00', '23:00:00'),
(6, 'Adhyaksa', -5.16039705, 119.44607614, 50, 'middle', '12:00:00', '20:00:00'),
(7, 'Adhyaksa', -5.16039705, 119.44607614, 50, 'sore', '15:00:00', '23:00:00'),
(8, 'BTP', -5.12957150, 119.50036078, 50, 'middle', '13:00:00', '21:00:00'),
(9, 'BTP', -5.12957150, 119.50036078, 50, 'sore', '15:00:00', '23:00:00'),
(10, 'tes', -5.19800341, 119.44793994, 50, 'pagi', '07:00:00', '15:00:00'),
(11, 'tes', -5.19800341, 119.44793994, 50, 'middle', '12:00:00', '21:00:00'),
(12, 'tes', -5.19800341, 119.44793994, 50, 'sore', '15:00:00', '23:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cabang_outlet`
--

CREATE TABLE `cabang_outlet` (
  `id` int(11) NOT NULL,
  `nama_cabang` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cabang_outlet`
--

INSERT INTO `cabang_outlet` (`id`, `nama_cabang`) VALUES
(2, 'Adhyaksa'),
(3, 'BTP'),
(1, 'Citraland Gowa'),
(4, 'Tes');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komponen_gaji`
--

CREATE TABLE `komponen_gaji` (
  `id` int(11) NOT NULL,
  `register_id` int(11) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `gaji_pokok` decimal(15,2) NOT NULL,
  `tunjangan_transport` decimal(15,2) NOT NULL,
  `tunjangan_makan` decimal(15,2) NOT NULL,
  `bonus` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pegawai_whitelist`
--

CREATE TABLE `pegawai_whitelist` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `posisi` varchar(100) DEFAULT NULL,
  `status_registrasi` enum('pending','terdaftar') NOT NULL DEFAULT 'pending',
  `tanggal_ditambahkan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan_izin`
--

CREATE TABLE `pengajuan_izin` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `lama_izin` int(11) NOT NULL,
  `alasan` text NOT NULL,
  `file_surat` varchar(255) NOT NULL,
  `tanda_tangan_file` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Diterima','Ditolak','') NOT NULL DEFAULT 'Pending',
  `tanggal_pengajuan` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengajuan_izin`
--

INSERT INTO `pengajuan_izin` (`id`, `user_id`, `perihal`, `tanggal_mulai`, `tanggal_selesai`, `lama_izin`, `alasan`, `file_surat`, `tanda_tangan_file`, `status`, `tanggal_pengajuan`) VALUES
(1, 2, 'Cuti Melahirkan', '2025-10-28', '2025-10-30', 2, 'karena mau melahirkan', 'surat_izin_user_2_1761034041.docx', 'ttd_user_2_1761034041.png', 'Pending', '2025-10-21'),
(2, 2, 'Cuti Melahirkan', '2025-10-28', '2025-10-30', 2, 'karena mau melahirkan', 'surat_izin_user_2_1761034112.docx', 'ttd_user_2_1761034111.png', 'Pending', '2025-10-21'),
(3, 2, 'izin sakit banget', '2025-10-22', '2025-10-31', 10, 'karena sakit banget', 'surat_izin_user_2_1761069614.docx', 'ttd_user_2_1761069614.png', 'Pending', '2025-10-22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `posisi_jabatan`
--

CREATE TABLE `posisi_jabatan` (
  `id` int(11) NOT NULL,
  `nama_posisi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `posisi_jabatan`
--

INSERT INTO `posisi_jabatan` (`id`, `nama_posisi`) VALUES
(6, 'Akuntan'),
(2, 'Barista'),
(7, 'Finance'),
(9, 'HR'),
(1, 'Kitchen'),
(5, 'Marketing'),
(8, 'Owner'),
(10, 'SCM'),
(3, 'Senior Barista'),
(4, 'Server');

-- --------------------------------------------------------

--
-- Struktur dari tabel `register`
--

CREATE TABLE `register` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `posisi` text NOT NULL,
  `outlet` text NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `time_created` date NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `tanda_tangan_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `register`
--

INSERT INTO `register` (`id`, `nama_lengkap`, `posisi`, `outlet`, `no_whatsapp`, `email`, `password`, `username`, `time_created`, `role`, `foto_profil`, `tanda_tangan_file`) VALUES
(1, 'superadmin', 'superadmin', 'superadmin', 'superadmin', 'superadmin@gmail.com', '$2y$10$lXNDAqQOdVC0uzYk64O68.pHH4RV.U1XkGw9i4YDtjjzgxEflrf2y', 'superadmin', '2025-10-21', 'admin', '1_1761061705.png', NULL),
(4, 'tesrole', 'tesrole', 'tesrole', '081928390128', 'tesrole@gmail.com', '$2y$10$Q4OfmDwU1go70TILcznLkOWIW0Gn62dvbQu5wU8N6etOqtPxeRDsW', 'tesrole', '2025-10-31', 'user', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_gaji`
--

CREATE TABLE `riwayat_gaji` (
  `id` int(11) NOT NULL,
  `register_id` int(11) NOT NULL,
  `periode_bulan` tinyint(3) UNSIGNED NOT NULL CHECK (`periode_bulan` >= 1 and `periode_bulan` <= 12),
  `periode_tahun` year(4) NOT NULL,
  `gaji_pokok_aktual` decimal(15,2) NOT NULL,
  `tunjangan_makan` decimal(15,2) NOT NULL,
  `tunjangan_transportasi` decimal(15,2) NOT NULL,
  `tunjangan_jabatan` decimal(15,2) NOT NULL,
  `overwork` decimal(15,2) NOT NULL,
  `piutang_toko` decimal(15,2) NOT NULL,
  `kasbon` decimal(15,2) NOT NULL,
  `potongan_absen` decimal(15,2) NOT NULL,
  `potongan_telat_atas_20` decimal(15,2) NOT NULL,
  `potongan_telat_bawah_20` decimal(15,2) NOT NULL,
  `gaji_bersih` decimal(15,2) NOT NULL,
  `jumlah_hadir` int(11) NOT NULL,
  `jumlah_terlambat` int(11) NOT NULL,
  `jumlah_absen` int(11) NOT NULL,
  `file_slip_gaji` varchar(255) NOT NULL,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `cabang`
--
ALTER TABLE `cabang`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `cabang_outlet`
--
ALTER TABLE `cabang_outlet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_cabang` (`nama_cabang`);

--
-- Indeks untuk tabel `komponen_gaji`
--
ALTER TABLE `komponen_gaji`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pegawai_whitelist`
--
ALTER TABLE `pegawai_whitelist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_lengkap` (`nama_lengkap`);

--
-- Indeks untuk tabel `pengajuan_izin`
--
ALTER TABLE `pengajuan_izin`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `posisi_jabatan`
--
ALTER TABLE `posisi_jabatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_posisi` (`nama_posisi`);

--
-- Indeks untuk tabel `register`
--
ALTER TABLE `register`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `EMAIL` (`email`),
  ADD UNIQUE KEY `no_whatsapp` (`no_whatsapp`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `riwayat_gaji`
--
ALTER TABLE `riwayat_gaji`
  ADD PRIMARY KEY (`id`),
  ADD KEY `register_id` (`register_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cabang`
--
ALTER TABLE `cabang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `cabang_outlet`
--
ALTER TABLE `cabang_outlet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `komponen_gaji`
--
ALTER TABLE `komponen_gaji`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pegawai_whitelist`
--
ALTER TABLE `pegawai_whitelist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengajuan_izin`
--
ALTER TABLE `pengajuan_izin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `posisi_jabatan`
--
ALTER TABLE `posisi_jabatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `register`
--
ALTER TABLE `register`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `riwayat_gaji`
--
ALTER TABLE `riwayat_gaji`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
