-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: aplikasi
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `absensi`
--

DROP TABLE IF EXISTS `absensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cabang_id` int(11) DEFAULT NULL,
  `jam_masuk_shift` time DEFAULT NULL,
  `jam_keluar_shift` time DEFAULT NULL,
  `durasi_kerja_menit` int(11) DEFAULT 0,
  `durasi_overwork_menit` int(11) DEFAULT 0,
  `waktu_masuk` datetime DEFAULT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `status_lokasi` varchar(50) DEFAULT 'Valid',
  `latitude_absen` decimal(10,8) DEFAULT NULL,
  `longitude_absen` decimal(11,8) DEFAULT NULL,
  `foto_absen_masuk` varchar(255) DEFAULT NULL COMMENT 'Foto saat absen masuk',
  `foto_absen_keluar` varchar(255) DEFAULT NULL COMMENT 'Foto saat absen keluar',
  `tanggal_absensi` date DEFAULT NULL,
  `menit_terlambat` int(11) NOT NULL,
  `status_keterlambatan` varchar(50) DEFAULT 'tepat waktu',
  `potongan_tunjangan` varchar(50) DEFAULT 'tidak ada' COMMENT 'Tracking potongan tunjangan berdasarkan keterlambatan',
  `status_lembur` enum('Pending','Approved','Rejected','Not Applicable') NOT NULL DEFAULT 'Not Applicable',
  `is_overwork_approved` tinyint(1) DEFAULT 0,
  `status_kehadiran` varchar(50) DEFAULT 'Belum Absen Keluar',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_date` (`user_id`,`tanggal_absensi`),
  KEY `user_id` (`user_id`),
  KEY `idx_cabang` (`cabang_id`),
  KEY `idx_tanggal` (`tanggal_absensi`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi`
--

LOCK TABLES `absensi` WRITE;
/*!40000 ALTER TABLE `absensi` DISABLE KEYS */;
INSERT INTO `absensi` VALUES (2,4,NULL,NULL,NULL,0,0,'2025-11-01 05:07:15','2025-11-01 05:07:20','Valid',-5.19802692,119.44796855,'masuk_absen_4_1761944835.jpg',NULL,'2025-10-31',428,'di luar shift','tidak ada','Not Applicable',0,'Belum Absen Keluar'),(25,8,NULL,NULL,NULL,0,0,'2025-11-03 00:46:21','2025-11-03 00:46:22','Valid',-5.19790573,119.44801409,NULL,'keluar_absen_keluar_8_1762101982.jpg','2025-11-02',167,'di luar shift','tidak ada','Pending',0,'Belum Absen Keluar'),(105,1,NULL,NULL,NULL,0,0,'2025-11-02 08:00:00',NULL,'Admin - Office',NULL,NULL,'test_absen.jpg',NULL,'2025-11-02',0,'tepat waktu','tidak ada','Not Applicable',0,'Belum Absen Keluar'),(106,1,NULL,NULL,NULL,0,0,'2025-11-05 19:45:30','2025-11-05 19:45:41','Admin - Remote',-5.19803514,119.44794933,'masuk_1_2025-11-05_1762343130.jpg','keluar_1_2025-11-05_1762343141.jpg','2025-11-05',0,'tidak ada shift','tidak ada','Pending',0,'Belum Absen Keluar');
/*!40000 ALTER TABLE `absensi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `absensi_duplicates_backup`
--

DROP TABLE IF EXISTS `absensi_duplicates_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absensi_duplicates_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `waktu_masuk` datetime DEFAULT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `status_lokasi` enum('Valid','Tidak Valid') DEFAULT NULL,
  `latitude_absen` decimal(10,8) DEFAULT NULL,
  `longitude_absen` decimal(11,8) DEFAULT NULL,
  `foto_absen` varchar(255) DEFAULT NULL,
  `foto_absen_keluar` varchar(255) DEFAULT NULL COMMENT 'Foto saat absen keluar',
  `tanggal_absensi` date DEFAULT NULL,
  `menit_terlambat` int(11) NOT NULL,
  `status_keterlambatan` enum('tepat waktu','terlambat kurang dari 20 menit','terlambat lebih dari 20 menit') NOT NULL,
  `status_lembur` enum('Pending','Approved','Rejected','Not Applicable') NOT NULL DEFAULT 'Not Applicable'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi_duplicates_backup`
--

LOCK TABLES `absensi_duplicates_backup` WRITE;
/*!40000 ALTER TABLE `absensi_duplicates_backup` DISABLE KEYS */;
INSERT INTO `absensi_duplicates_backup` VALUES (5,7,'2025-11-01 16:08:01','2025-11-01 16:08:13','Valid',-5.19799780,119.44803184,NULL,'absen_keluar_7_1761984493.jpg','2025-11-01',129,'terlambat lebih dari 20 menit','Not Applicable'),(6,7,'2025-11-01 16:08:10','2025-11-01 16:08:15','Valid',-5.19802107,119.44802844,NULL,'absen_keluar_7_1761984495.jpg','2025-11-01',129,'terlambat lebih dari 20 menit','Not Applicable'),(7,7,'2025-11-01 19:25:48','2025-11-01 19:25:49','Valid',-5.19802107,119.44802844,NULL,'absen_keluar_7_1761996349.jpg','2025-11-01',26,'terlambat lebih dari 20 menit','Not Applicable'),(8,7,'2025-11-01 19:26:08','2025-11-01 19:28:50','Valid',-5.19794886,119.44798036,NULL,'absen_keluar_7_1761996530.jpg','2025-11-01',27,'terlambat lebih dari 20 menit','Not Applicable'),(9,7,'2025-11-01 19:27:01','2025-11-01 19:28:52','Valid',-5.19794044,119.44796440,NULL,'absen_keluar_7_1761996532.jpg','2025-11-01',28,'terlambat lebih dari 20 menit','Not Applicable'),(10,7,'2025-11-01 19:28:18','2025-11-01 19:28:53','Valid',-5.19794046,119.44796442,NULL,'absen_keluar_7_1761996533.jpg','2025-11-01',29,'terlambat lebih dari 20 menit','Not Applicable'),(11,7,'2025-11-01 19:28:59','2025-11-01 19:29:42','Valid',-5.19800166,119.44793948,NULL,'absen_keluar_7_1761996582.jpg','2025-11-01',29,'terlambat lebih dari 20 menit','Not Applicable'),(12,7,'2025-11-01 19:29:38','2025-11-01 19:29:45','Valid',-5.19800166,119.44793948,NULL,'absen_keluar_7_1761996585.jpg','2025-11-01',30,'terlambat lebih dari 20 menit','Not Applicable'),(13,7,'2025-11-01 19:29:53','2025-11-01 19:32:52','Valid',-5.19801813,119.44797242,NULL,'absen_keluar_7_1761996772.jpg','2025-11-01',30,'terlambat lebih dari 20 menit','Not Applicable'),(14,7,'2025-11-01 19:32:51','2025-11-01 19:32:54','Valid',-5.19801814,119.44797243,NULL,'absen_keluar_7_1761996774.jpg','2025-11-01',33,'terlambat lebih dari 20 menit','Not Applicable'),(15,7,'2025-11-01 19:33:00','2025-11-01 19:33:06','Valid',-5.19801407,119.44801565,NULL,'absen_keluar_7_1761996786.jpg','2025-11-01',33,'terlambat lebih dari 20 menit','Not Applicable'),(16,7,'2025-11-01 19:39:05','2025-11-01 19:39:07','Valid',-5.19802694,119.44800720,NULL,'absen_keluar_7_1761997147.jpg','2025-11-01',40,'terlambat lebih dari 20 menit','Not Applicable'),(17,7,'2025-11-01 19:39:26','2025-11-01 19:39:31','Valid',-5.19802694,119.44800720,NULL,'absen_keluar_7_1761997171.jpg','2025-11-01',40,'terlambat lebih dari 20 menit','Not Applicable'),(18,7,'2025-11-01 19:40:57','2025-11-01 19:40:58','Valid',-5.19801331,119.44794206,NULL,'absen_keluar_7_1761997258.jpg','2025-11-01',41,'terlambat lebih dari 20 menit','Not Applicable'),(19,7,'2025-11-01 19:45:22','2025-11-01 19:45:24','Valid',-5.19801331,119.44794205,NULL,'absen_keluar_7_1761997524.jpg','2025-11-01',46,'terlambat lebih dari 20 menit','Not Applicable'),(20,7,'2025-11-01 19:46:53','2025-11-01 19:46:55','Valid',-5.19799438,119.44790599,NULL,'absen_keluar_7_1761997615.jpg','2025-11-01',47,'terlambat lebih dari 20 menit','Not Applicable'),(21,7,'2025-11-01 19:49:17','2025-11-01 19:49:18','Valid',-5.19799438,119.44790598,NULL,'absen_keluar_7_1761997758.jpg','2025-11-01',50,'terlambat lebih dari 20 menit','Not Applicable'),(22,7,'2025-11-01 19:51:52','2025-11-01 19:51:53','Valid',-5.19799438,119.44790598,NULL,'absen_keluar_7_1761997913.jpg','2025-11-01',52,'terlambat lebih dari 20 menit','Not Applicable'),(23,1,'2025-11-01 19:55:25','2025-11-01 19:55:26','Valid',-5.19791133,119.44801604,NULL,'absen_keluar_1_1761998126.jpg','2025-11-01',56,'terlambat lebih dari 20 menit','Approved'),(24,1,'2025-11-01 20:57:37','2025-11-01 20:57:39','Valid',-5.19802107,119.44802844,NULL,'absen_keluar_1_1762001859.jpg','2025-11-01',0,'tepat waktu','Not Applicable'),(26,8,'2025-11-03 00:46:32','2025-11-03 00:46:33','Valid',-5.19790573,119.44801409,NULL,'absen_keluar_8_1762101993.jpg','2025-11-02',167,'terlambat lebih dari 20 menit','Pending');
/*!40000 ALTER TABLE `absensi_duplicates_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `absensi_error_log`
--

DROP TABLE IF EXISTS `absensi_error_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absensi_error_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `error_type` varchar(50) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `error_details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_error_type` (`error_type`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log for absensi errors and abuse attempts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi_error_log`
--

LOCK TABLES `absensi_error_log` WRITE;
/*!40000 ALTER TABLE `absensi_error_log` DISABLE KEYS */;
INSERT INTO `absensi_error_log` VALUES (1,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:30:05'),(2,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:30:18'),(3,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:32:10'),(4,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:36:13'),(5,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:38:57'),(6,7,'DB_ERROR','Database error during absensi','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'latitude_absen_masuk\' in \'field list\'','::1',NULL,'2025-11-05 12:04:44'),(7,7,'DB_ERROR','Database error during absensi','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'latitude_absen_masuk\' in \'field list\'','::1',NULL,'2025-11-05 12:04:57'),(8,7,'DB_ERROR','Database error during absensi','SQLSTATE[42S22]: Column not found: 1054 Unknown column \'latitude_absen_masuk\' in \'field list\'','::1',NULL,'2025-11-05 12:06:18');
/*!40000 ALTER TABLE `absensi_error_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `absensi_paths_backup`
--

DROP TABLE IF EXISTS `absensi_paths_backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absensi_paths_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `foto_absen` varchar(255) DEFAULT NULL,
  `foto_absen_keluar` varchar(255) DEFAULT NULL COMMENT 'Foto saat absen keluar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi_paths_backup`
--

LOCK TABLES `absensi_paths_backup` WRITE;
/*!40000 ALTER TABLE `absensi_paths_backup` DISABLE KEYS */;
INSERT INTO `absensi_paths_backup` VALUES (1,'absen_1_1761935544.jpg',NULL),(2,'absen_4_1761944835.jpg',NULL),(3,'absen_1_1761974850.jpg',NULL),(4,'absen_7_1761984031.jpg',NULL),(25,NULL,'absen_keluar_8_1762101982.jpg');
/*!40000 ALTER TABLE `absensi_paths_backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `absensi_rate_limit_log`
--

DROP TABLE IF EXISTS `absensi_rate_limit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absensi_rate_limit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `attempt_type` varchar(20) DEFAULT NULL COMMENT 'masuk or keluar',
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `blocked` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_user_time` (`user_id`,`attempt_time`),
  KEY `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log for rate limiting tracking';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi_rate_limit_log`
--

LOCK TABLES `absensi_rate_limit_log` WRITE;
/*!40000 ALTER TABLE `absensi_rate_limit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `absensi_rate_limit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cabang`
--

DROP TABLE IF EXISTS `cabang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cabang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_cabang` varchar(255) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius_meter` int(11) NOT NULL,
  `nama_shift` varchar(255) NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_keluar` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cabang`
--

LOCK TABLES `cabang` WRITE;
/*!40000 ALTER TABLE `cabang` DISABLE KEYS */;
INSERT INTO `cabang` VALUES (1,'Citraland Gowa',-5.17994582,119.46337357,50,'pagi','07:00:00','15:00:00'),(2,'Adhyaksa',-5.16039705,119.44607614,50,'pagi','07:00:00','15:00:00'),(3,'BTP',-5.12957150,119.50036078,50,'pagi','08:00:00','15:00:00'),(4,'Citraland Gowa',-5.17994582,119.46337357,50,'middle','13:00:00','21:00:00'),(5,'Citraland Gowa',-5.17994582,119.46337357,50,'sore','15:00:00','23:00:00'),(6,'Adhyaksa',-5.16039705,119.44607614,50,'middle','12:00:00','20:00:00'),(7,'Adhyaksa',-5.16039705,119.44607614,50,'sore','15:00:00','23:00:00'),(8,'BTP',-5.12957150,119.50036078,50,'middle','13:00:00','21:00:00'),(9,'BTP',-5.12957150,119.50036078,50,'sore','15:00:00','23:00:00');
/*!40000 ALTER TABLE `cabang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cabang_outlet`
--

DROP TABLE IF EXISTS `cabang_outlet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cabang_outlet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_cabang` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_cabang` (`nama_cabang`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cabang_outlet`
--

LOCK TABLES `cabang_outlet` WRITE;
/*!40000 ALTER TABLE `cabang_outlet` DISABLE KEYS */;
INSERT INTO `cabang_outlet` VALUES (2,'Adhyaksa'),(3,'BTP'),(1,'Citraland Gowa');
/*!40000 ALTER TABLE `cabang_outlet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `komponen_gaji`
--

DROP TABLE IF EXISTS `komponen_gaji`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `komponen_gaji` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_id` int(11) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `gaji_pokok` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tunjangan_transport` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tunjangan_makan` decimal(10,2) NOT NULL DEFAULT 0.00,
  `overwork` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tunjangan_jabatan` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bonus_kehadiran` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bonus_marketing` decimal(10,2) NOT NULL DEFAULT 0.00,
  `insentif_omset` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_register` (`register_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `komponen_gaji`
--

LOCK TABLES `komponen_gaji` WRITE;
/*!40000 ALTER TABLE `komponen_gaji` DISABLE KEYS */;
INSERT INTO `komponen_gaji` VALUES (1,7,'HR',1750000.00,350000.00,300000.00,0.00,0.00,0.00,0.00,0.00);
/*!40000 ALTER TABLE `komponen_gaji` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `komponen_gaji_detail`
--

DROP TABLE IF EXISTS `komponen_gaji_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `komponen_gaji_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `periode_bulan` tinyint(2) unsigned NOT NULL CHECK (`periode_bulan` >= 1 and `periode_bulan` <= 12),
  `periode_tahun` year(4) NOT NULL,
  `gaji_pokok` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_transport` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_makan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_jabatan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bonus_kehadiran` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bonus_marketing` decimal(15,2) NOT NULL DEFAULT 0.00,
  `insentif_omset` decimal(15,2) NOT NULL DEFAULT 0.00,
  `overwork_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `overwork_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `potongan_telat_berat` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Terlambat > 20 menit',
  `potongan_telat_ringan` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Terlambat < 20 menit',
  `potongan_alfa` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tidak hadir tanpa izin',
  `kasbon` decimal(15,2) NOT NULL DEFAULT 0.00,
  `piutang_toko` decimal(15,2) NOT NULL DEFAULT 0.00,
  `potongan_lainnya` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan_potongan` text DEFAULT NULL,
  `total_pendapatan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_potongan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gaji_bersih` decimal(15,2) NOT NULL DEFAULT 0.00,
  `jumlah_hadir` int(11) NOT NULL DEFAULT 0,
  `jumlah_telat_ringan` int(11) NOT NULL DEFAULT 0,
  `jumlah_telat_berat` int(11) NOT NULL DEFAULT 0,
  `jumlah_alfa` int(11) NOT NULL DEFAULT 0,
  `jumlah_izin` int(11) NOT NULL DEFAULT 0,
  `status_slip` enum('draft','generated','sent','revised') DEFAULT 'draft',
  `file_slip_gaji` varchar(255) DEFAULT NULL,
  `generated_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_period` (`user_id`,`periode_bulan`,`periode_tahun`),
  KEY `idx_periode` (`periode_bulan`,`periode_tahun`),
  KEY `idx_status` (`status_slip`),
  CONSTRAINT `komponen_gaji_detail_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `register` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detailed payroll components per employee per month';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `komponen_gaji_detail`
--

LOCK TABLES `komponen_gaji_detail` WRITE;
/*!40000 ALTER TABLE `komponen_gaji_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `komponen_gaji_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `libur_nasional`
--

DROP TABLE IF EXISTS `libur_nasional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `libur_nasional` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `nama_libur` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tanggal` (`tanggal`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='National holidays and special non-working days';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `libur_nasional`
--

LOCK TABLES `libur_nasional` WRITE;
/*!40000 ALTER TABLE `libur_nasional` DISABLE KEYS */;
INSERT INTO `libur_nasional` VALUES (1,'2025-01-01','Tahun Baru 2025','Tahun Baru Masehi','2025-11-03 16:45:23'),(2,'2025-01-29','Tahun Baru Imlek 2576','Tahun Baru Imlek','2025-11-03 16:45:23'),(3,'2025-03-29','Hari Raya Nyepi 1947','Tahun Baru Saka','2025-11-03 16:45:23'),(4,'2025-03-30','Wafat Isa Al-Masih','Hari Suci Kristen','2025-11-03 16:45:23'),(5,'2025-03-31','Isra Mikraj Nabi Muhammad SAW','Hari Besar Islam','2025-11-03 16:45:23'),(6,'2025-04-01','Hari Raya Idul Fitri 1446 H','Hari Raya Islam (Est)','2025-11-03 16:45:23'),(7,'2025-04-02','Hari Raya Idul Fitri 1446 H','Hari Raya Islam (Est)','2025-11-03 16:45:23'),(8,'2025-04-03','Cuti Bersama Idul Fitri','Cuti Bersama','2025-11-03 16:45:23'),(9,'2025-04-04','Cuti Bersama Idul Fitri','Cuti Bersama','2025-11-03 16:45:23'),(10,'2025-05-01','Hari Buruh Internasional','Hari Buruh','2025-11-03 16:45:23'),(11,'2025-05-29','Kenaikan Isa Al-Masih','Hari Suci Kristen','2025-11-03 16:45:23'),(12,'2025-06-07','Hari Raya Idul Adha 1446 H','Hari Raya Islam (Est)','2025-11-03 16:45:23'),(13,'2025-06-28','Tahun Baru Islam 1447 H','Tahun Baru Hijriah (Est)','2025-11-03 16:45:23'),(14,'2025-08-17','Hari Kemerdekaan RI','HUT Kemerdekaan RI ke-80','2025-11-03 16:45:23'),(15,'2025-09-06','Maulid Nabi Muhammad SAW','Hari Besar Islam (Est)','2025-11-03 16:45:23'),(16,'2025-12-25','Hari Raya Natal','Hari Raya Natal','2025-11-03 16:45:23');
/*!40000 ALTER TABLE `libur_nasional` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pegawai_whitelist`
--

DROP TABLE IF EXISTS `pegawai_whitelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pegawai_whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `posisi` varchar(100) DEFAULT NULL,
  `id_cabang` int(11) DEFAULT NULL,
  `status_registrasi` enum('pending','terdaftar') NOT NULL DEFAULT 'pending',
  `tanggal_ditambahkan` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'user',
  `gaji_pokok` decimal(15,2) DEFAULT 0.00,
  `tunjangan_transport` decimal(15,2) DEFAULT 0.00,
  `tunjangan_makan` decimal(15,2) DEFAULT 0.00,
  `overwork` decimal(15,2) DEFAULT 0.00,
  `tunjangan_jabatan` decimal(15,2) DEFAULT 0.00,
  `bonus_kehadiran` decimal(15,2) DEFAULT 0.00,
  `bonus_marketing` decimal(15,2) DEFAULT 0.00,
  `insentif_omset` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_lengkap` (`nama_lengkap`),
  UNIQUE KEY `unique_pegawai` (`nama_lengkap`,`posisi`),
  KEY `idx_cabang` (`id_cabang`),
  CONSTRAINT `fk_whitelist_cabang` FOREIGN KEY (`id_cabang`) REFERENCES `cabang` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Whitelist table with salary components - allows import before registration';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pegawai_whitelist`
--

LOCK TABLES `pegawai_whitelist` WRITE;
/*!40000 ALTER TABLE `pegawai_whitelist` DISABLE KEYS */;
INSERT INTO `pegawai_whitelist` VALUES (1,'Mochammad Rifqi Athaullah Herfian','Kepala Toko',NULL,'pending','2025-11-01 07:05:32','admin',1750000.00,200000.00,200000.00,50000.00,250000.00,0.00,0.00,0.00),(2,'Farhan Zul Iqram','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(3,'Fathur Dwi Bintang','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(4,'Muh. Rezky Widodo','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(5,'Akbar Andipa','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(6,'Muhammad Isnan Al Gaffar','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(7,'Muhammad Arhamul Ihza M','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(8,'Zulfikar','Server',NULL,'pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(9,'Muh. Rayhan Aprisal Fachrun','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,250000.00,0.00,0.00,0.00),(10,'Fitrah Ramadan Saputra','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(11,'Heru Hermawan','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(12,'Muh. Farel Rumante','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(13,'Andi Utha Ananta Winata','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(14,'Ahmad Fairuzi','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(15,'Muhammad Arya','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(16,'M Audi Alfan M S','Server',NULL,'pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(17,'M. Maher','Server',NULL,'pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(18,'Eko Prasetio','Kepala Toko',NULL,'pending','2025-11-01 07:05:32','admin',1750000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(19,'Muhammad Rafiul','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(20,'Arman Maulana','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(21,'Ahmad Mahendra','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(22,'Miftahul Ichwan','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(23,'Muh. Chasan Abdillah','Barista',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(24,'Muh. Rasul Alamsyah H','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(25,'Virgiawan','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(26,'M. Afif Yunus','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(27,'M Taufiq Ramadhan','Kitchen',NULL,'pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(28,'Angga Eka Saputra Dewa','Server',NULL,'pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(29,'Al Mukmin Dwi Yanto','Server',NULL,'pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(30,'Muh. Adnil Nizar','Server',NULL,'pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(31,'Andi Abdul Chaerullah A','Marketing',NULL,'pending','2025-11-01 07:05:32','admin',1750000.00,650000.00,600000.00,0.00,0.00,0.00,0.00,0.00),(32,'Rahmat Maulana','SCM',NULL,'pending','2025-11-01 07:05:32','admin',1750000.00,650000.00,600000.00,0.00,0.00,0.00,0.00,0.00),(33,'M Yogi Alfadillah','Akuntan',NULL,'pending','2025-11-01 07:05:32','admin',1750000.00,650000.00,600000.00,0.00,0.00,0.00,0.00,0.00),(34,'Muhammad Abizar Nafara','HR',NULL,'terdaftar','2025-11-01 07:05:32','admin',1750000.00,350000.00,300000.00,0.00,0.00,0.00,0.00,0.00),(35,'Agung Dharmawan','Finance',NULL,'pending','2025-11-01 07:05:32','admin',10000000.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00),(36,'Mohammad Rizky Putra','Owner',NULL,'pending','2025-11-01 07:05:32','admin',10000000.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00),(39,'superadmin','superadmin',NULL,'pending','2025-11-02 20:05:02','admin',0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00);
/*!40000 ALTER TABLE `pegawai_whitelist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengajuan_izin`
--

DROP TABLE IF EXISTS `pengajuan_izin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengajuan_izin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `lama_izin` int(11) NOT NULL,
  `alasan` text NOT NULL,
  `file_surat` varchar(255) NOT NULL,
  `tanda_tangan_file` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Diterima','Ditolak','') NOT NULL DEFAULT 'Pending',
  `mempengaruhi_shift` tinyint(1) DEFAULT 1,
  `shift_diganti` tinyint(1) DEFAULT 0,
  `tanggal_pengajuan` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengajuan_izin`
--

LOCK TABLES `pengajuan_izin` WRITE;
/*!40000 ALTER TABLE `pengajuan_izin` DISABLE KEYS */;
INSERT INTO `pengajuan_izin` VALUES (1,2,'Cuti Melahirkan','2025-10-28','2025-10-30',2,'karena mau melahirkan','surat_izin_user_2_1761034041.docx','ttd_user_2_1761034041.png','Pending',1,0,'2025-10-21'),(2,2,'Cuti Melahirkan','2025-10-28','2025-10-30',2,'karena mau melahirkan','surat_izin_user_2_1761034112.docx','ttd_user_2_1761034111.png','Pending',1,0,'2025-10-21'),(3,2,'izin sakit banget','2025-10-22','2025-10-31',10,'karena sakit banget','surat_izin_user_2_1761069614.docx','ttd_user_2_1761069614.png','Pending',1,0,'2025-10-22'),(4,1,'Tes Aplikasi','2025-11-01','2025-11-04',4,'ingin melakukan tes aplikasi','surat_izin_user_1_1761944033.docx','ttd_user_1.png','Diterima',1,0,'2025-11-01'),(5,1,'tes','2025-11-03','2025-11-03',1,'tes','surat_izin_user_1_1762181641.docx','ttd_user_1_1762019092.png','Diterima',1,0,'2025-11-03'),(6,1,'tes','2025-11-29','2025-11-29',1,'tes','surat_izin_user_1_1762182569.docx','ttd_user_1_1762019092.png','Diterima',1,0,'2025-11-03'),(7,1,'tes','2025-11-03','2025-11-03',1,'tes','surat_izin_user_1_1762182653.docx','ttd_user_1_1762019092.png','Ditolak',1,0,'2025-11-03');
/*!40000 ALTER TABLE `pengajuan_izin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posisi_jabatan`
--

DROP TABLE IF EXISTS `posisi_jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posisi_jabatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_posisi` varchar(100) NOT NULL,
  `role_posisi` varchar(20) DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_posisi` (`nama_posisi`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posisi_jabatan`
--

LOCK TABLES `posisi_jabatan` WRITE;
/*!40000 ALTER TABLE `posisi_jabatan` DISABLE KEYS */;
INSERT INTO `posisi_jabatan` VALUES (1,'Kitchen','user'),(2,'Barista','user'),(3,'Senior Barista','user'),(4,'Server','user'),(5,'Marketing','admin'),(6,'Akuntan','admin'),(7,'Finance','admin'),(8,'Owner','admin'),(10,'SCM','admin'),(11,'HR','admin'),(12,'Tidak Ada Posisi','user'),(13,'superadmin','admin'),(14,'Kepala Toko','admin');
/*!40000 ALTER TABLE `posisi_jabatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `register`
--

DROP TABLE IF EXISTS `register`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `register` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(50) NOT NULL,
  `posisi` text NOT NULL,
  `outlet` text NOT NULL,
  `id_cabang` int(11) DEFAULT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `time_created` date NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `tanda_tangan_file` varchar(255) DEFAULT NULL,
  `gaji_pokok` decimal(15,2) DEFAULT 0.00,
  `tunjangan_transport` decimal(15,2) DEFAULT 0.00,
  `tunjangan_makan` decimal(15,2) DEFAULT 0.00,
  `tunjangan_jabatan` decimal(15,2) DEFAULT 0.00,
  `upah_overwork_per_8jam` decimal(10,2) DEFAULT 50000.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `EMAIL` (`email`),
  UNIQUE KEY `no_whatsapp` (`no_whatsapp`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `unique_nama` (`nama_lengkap`),
  KEY `idx_cabang` (`id_cabang`),
  CONSTRAINT `fk_register_cabang` FOREIGN KEY (`id_cabang`) REFERENCES `cabang` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `register`
--

LOCK TABLES `register` WRITE;
/*!40000 ALTER TABLE `register` DISABLE KEYS */;
INSERT INTO `register` VALUES (1,'superadmin','superadmin','superadmin',NULL,'superadmin','baristasastra@gmail.com','$2y$10$lXNDAqQOdVC0uzYk64O68.pHH4RV.U1XkGw9i4YDtjjzgxEflrf2y','superadmin','2025-10-21','admin','1_1762016999.png','ttd_user_1_1762019092.png',0.00,0.00,0.00,0.00,50000.00),(4,'tesrole','Tidak Ada Posisi','Citraland Gowa',1,'081928390128','tesrole@gmail.com','$2y$10$Q4OfmDwU1go70TILcznLkOWIW0Gn62dvbQu5wU8N6etOqtPxeRDsW','tesrole','2025-10-31','user',NULL,NULL,4000000.00,390000.00,260000.00,0.00,45000.00),(7,'Muhammad Abizar Nafara','HR','Citraland Gowa',1,'+62 8125800437','abizarnafara26@gmail.com','$2y$10$gf3UkVsrfTGhEFOLG4REreURGdYX1fRD0shUgHITf3JEKBCrbod2e','abizarnafara','2025-11-01','admin',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(9,'Ahmad Pratama','Staff Toko','Citraland Gowa',1,'081234567801','citraland01@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland01','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(10,'Siti Nurhaliza','Kasir','Citraland Gowa',1,'081234567802','citraland02@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland02','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(11,'Budi Santoso','Stock Keeper','Citraland Gowa',1,'081234567803','citraland03@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland03','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(12,'Dewi Lestari','Sales','Citraland Gowa',1,'081234567804','citraland04@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland04','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(13,'Eko Prasetyo','Staff Toko','Citraland Gowa',1,'081234567805','citraland05@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland05','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(14,'Fitri Handayani','Kasir','Citraland Gowa',1,'081234567806','citraland06@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland06','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(15,'Gunawan Setiawan','Staff Toko','Citraland Gowa',1,'081234567807','citraland07@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland07','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(16,'Hani Permata','Sales','Citraland Gowa',1,'081234567808','citraland08@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland08','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(17,'Indra Wijaya','Stock Keeper','Citraland Gowa',1,'081234567809','citraland09@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland09','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(18,'Julia Rahmawati','Kasir','Citraland Gowa',1,'081234567810','citraland10@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','citraland10','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(19,'Kartika Sari','Staff Toko','Adhyaksa',2,'081234568801','adhyaksa01@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa01','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(20,'Lukman Hakim','Kasir','Adhyaksa',2,'081234568802','adhyaksa02@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa02','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(21,'Maya Angelina','Sales','Adhyaksa',2,'081234568803','adhyaksa03@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa03','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(22,'Nanda Pratama','Stock Keeper','Adhyaksa',2,'081234568804','adhyaksa04@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa04','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(23,'Olivia Margareta','Staff Toko','Adhyaksa',2,'081234568805','adhyaksa05@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa05','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(24,'Pandu Kusuma','Kasir','Adhyaksa',2,'081234568806','adhyaksa06@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa06','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(25,'Qory Sandrina','Sales','Adhyaksa',2,'081234568807','adhyaksa07@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa07','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(26,'Rudi Hermawan','Staff Toko','Adhyaksa',2,'081234568808','adhyaksa08@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa08','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(27,'Sarah Amelia','Kasir','Adhyaksa',2,'081234568809','adhyaksa09@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa09','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(28,'Tono Sugiarto','Stock Keeper','Adhyaksa',2,'081234568810','adhyaksa10@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','adhyaksa10','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(29,'Umi Kalsum','Staff Toko','BTP',3,'081234569801','btp01@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp01','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(30,'Victor Ramadhan','Kasir','BTP',3,'081234569802','btp02@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp02','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(31,'Wulan Dari','Sales','BTP',3,'081234569803','btp03@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp03','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(32,'Xavier Putra','Stock Keeper','BTP',3,'081234569804','btp04@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp04','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(33,'Yuni Shara','Staff Toko','BTP',3,'081234569805','btp05@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp05','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(34,'Zaki Ismail','Kasir','BTP',3,'081234569806','btp06@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp06','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(35,'Ayu Ting Ting','Sales','BTP',3,'081234569807','btp07@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp07','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(36,'Bayu Skak','Staff Toko','BTP',3,'081234569808','btp08@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp08','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(37,'Cinta Laura','Kasir','BTP',3,'081234569809','btp09@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp09','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00),(38,'Denny Caknan','Stock Keeper','BTP',3,'081234569810','btp10@test.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','btp10','2025-11-04','user',NULL,NULL,0.00,0.00,0.00,0.00,50000.00);
/*!40000 ALTER TABLE `register` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reset_password`
--

DROP TABLE IF EXISTS `reset_password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reset_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reset_password`
--

LOCK TABLES `reset_password` WRITE;
/*!40000 ALTER TABLE `reset_password` DISABLE KEYS */;
INSERT INTO `reset_password` VALUES (6,8,'de206dfdf4fa1a4b639a68e2fcfaa2158b8596cde867ae0db4ab32dc4f54b86f','2025-11-03 01:26:53',1,'2025-11-03 00:26:53'),(7,7,'f827a1da299b8c7e7a0377d59a1aa13a2e410d739475b1ebd3c005e68d7e0541','2025-11-03 20:41:33',0,'2025-11-03 19:41:33');
/*!40000 ALTER TABLE `reset_password` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riwayat_gaji`
--

DROP TABLE IF EXISTS `riwayat_gaji`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riwayat_gaji` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `register_id` int(11) NOT NULL,
  `periode_bulan` tinyint(3) unsigned NOT NULL CHECK (`periode_bulan` >= 1 and `periode_bulan` <= 12),
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
  `potongan_telat_40` decimal(15,2) NOT NULL,
  `gaji_bersih` decimal(15,2) NOT NULL,
  `jumlah_hadir` int(11) NOT NULL,
  `jumlah_terlambat` int(11) NOT NULL,
  `jumlah_absen` int(11) NOT NULL,
  `file_slip_gaji` varchar(255) NOT NULL,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `register_id` (`register_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat_gaji`
--

LOCK TABLES `riwayat_gaji` WRITE;
/*!40000 ALTER TABLE `riwayat_gaji` DISABLE KEYS */;
/*!40000 ALTER TABLE `riwayat_gaji` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_assignments`
--

DROP TABLE IF EXISTS `shift_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shift_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cabang_id` int(11) NOT NULL,
  `tanggal_shift` date NOT NULL,
  `status_konfirmasi` enum('pending','confirmed','declined') DEFAULT 'pending',
  `waktu_konfirmasi` datetime DEFAULT NULL,
  `catatan_pegawai` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_date` (`user_id`,`tanggal_shift`),
  KEY `idx_tanggal` (`tanggal_shift`),
  KEY `idx_cabang` (`cabang_id`),
  KEY `idx_status` (`status_konfirmasi`),
  KEY `idx_user_date` (`user_id`,`tanggal_shift`),
  KEY `idx_cabang_date` (`cabang_id`,`tanggal_shift`),
  CONSTRAINT `shift_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `register` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shift_assignments_ibfk_2` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Assigns users to specific shifts on specific dates';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_assignments`
--

LOCK TABLES `shift_assignments` WRITE;
/*!40000 ALTER TABLE `shift_assignments` DISABLE KEYS */;
INSERT INTO `shift_assignments` VALUES (2,9,1,'2025-11-02','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(3,10,1,'2025-11-02','confirmed',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(4,11,1,'2025-11-02','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(5,19,2,'2025-11-02','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(6,20,2,'2025-11-02','confirmed',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(7,21,2,'2025-11-02','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(8,29,3,'2025-11-02','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(9,30,3,'2025-11-02','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(10,31,3,'2025-11-02','confirmed',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(11,12,1,'2025-11-03','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(12,13,1,'2025-11-03','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(13,22,2,'2025-11-03','confirmed',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(14,23,2,'2025-11-03','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(15,32,3,'2025-11-03','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(16,33,3,'2025-11-03','confirmed',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(17,14,1,'2025-11-04','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(18,15,1,'2025-11-04','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(19,24,2,'2025-11-04','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(20,25,2,'2025-11-04','confirmed',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(21,34,3,'2025-11-04','confirmed',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(22,35,3,'2025-11-04','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(23,16,1,'2025-11-05','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(24,17,1,'2025-11-05','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(25,26,2,'2025-11-05','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(26,27,2,'2025-11-05','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(27,36,3,'2025-11-05','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(28,37,3,'2025-11-05','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(29,18,1,'2025-11-06','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(30,9,1,'2025-11-06','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(31,28,2,'2025-11-06','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(32,19,2,'2025-11-06','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(33,38,3,'2025-11-06','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(34,29,3,'2025-11-06','pending',NULL,NULL,1,'2025-11-03 18:51:13','2025-11-03 18:51:13'),(35,19,2,'2025-11-05','pending',NULL,NULL,1,'2025-11-05 12:51:31','2025-11-05 12:51:31'),(36,20,2,'2025-11-05','pending',NULL,NULL,1,'2025-11-05 12:51:32','2025-11-05 12:51:32'),(37,21,2,'2025-11-05','pending',NULL,NULL,1,'2025-11-05 12:51:32','2025-11-05 12:51:32'),(38,25,2,'2025-11-05','pending',NULL,NULL,1,'2025-11-05 12:51:32','2025-11-05 12:51:32');
/*!40000 ALTER TABLE `shift_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slip_gaji_history`
--

DROP TABLE IF EXISTS `slip_gaji_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `slip_gaji_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` varchar(50) NOT NULL COMMENT 'Unique ID for each generation batch',
  `periode_bulan` tinyint(2) unsigned NOT NULL,
  `periode_tahun` year(4) NOT NULL,
  `jumlah_pegawai` int(11) NOT NULL DEFAULT 0,
  `total_gaji_dibayarkan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status_batch` enum('processing','completed','failed','cancelled') DEFAULT 'processing',
  `email_sent_count` int(11) NOT NULL DEFAULT 0,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `error_log` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_id` (`batch_id`),
  KEY `idx_periode` (`periode_bulan`,`periode_tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='History of payroll generation batches';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slip_gaji_history`
--

LOCK TABLES `slip_gaji_history` WRITE;
/*!40000 ALTER TABLE `slip_gaji_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `slip_gaji_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_absensi_dengan_shift`
--

DROP TABLE IF EXISTS `v_absensi_dengan_shift`;
/*!50001 DROP VIEW IF EXISTS `v_absensi_dengan_shift`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_absensi_dengan_shift` AS SELECT
 1 AS `id`,
  1 AS `user_id`,
  1 AS `nama_lengkap`,
  1 AS `posisi`,
  1 AS `tanggal_absensi`,
  1 AS `waktu_masuk`,
  1 AS `waktu_keluar`,
  1 AS `jam_masuk_shift`,
  1 AS `jam_keluar_shift`,
  1 AS `durasi_kerja_menit`,
  1 AS `durasi_overwork_menit`,
  1 AS `jam_overwork`,
  1 AS `menit_terlambat`,
  1 AS `status_keterlambatan`,
  1 AS `status_lembur`,
  1 AS `is_overwork_approved`,
  1 AS `status_lokasi`,
  1 AS `outlet`,
  1 AS `nama_shift`,
  1 AS `status_absensi` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_jadwal_shift_harian`
--

DROP TABLE IF EXISTS `v_jadwal_shift_harian`;
/*!50001 DROP VIEW IF EXISTS `v_jadwal_shift_harian`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_jadwal_shift_harian` AS SELECT
 1 AS `id`,
  1 AS `tanggal_shift`,
  1 AS `user_id`,
  1 AS `nama_lengkap`,
  1 AS `posisi`,
  1 AS `no_whatsapp`,
  1 AS `email`,
  1 AS `cabang_id`,
  1 AS `outlet`,
  1 AS `nama_shift`,
  1 AS `jam_masuk`,
  1 AS `jam_keluar`,
  1 AS `status_konfirmasi`,
  1 AS `waktu_konfirmasi`,
  1 AS `catatan_pegawai`,
  1 AS `created_at`,
  1 AS `status_hari` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_ringkasan_gaji`
--

DROP TABLE IF EXISTS `v_ringkasan_gaji`;
/*!50001 DROP VIEW IF EXISTS `v_ringkasan_gaji`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_ringkasan_gaji` AS SELECT
 1 AS `id`,
  1 AS `user_id`,
  1 AS `nama_lengkap`,
  1 AS `posisi`,
  1 AS `outlet`,
  1 AS `periode_bulan`,
  1 AS `periode_tahun`,
  1 AS `gaji_pokok`,
  1 AS `tunjangan_transport`,
  1 AS `tunjangan_makan`,
  1 AS `tunjangan_jabatan`,
  1 AS `bonus_kehadiran`,
  1 AS `bonus_marketing`,
  1 AS `insentif_omset`,
  1 AS `overwork_amount`,
  1 AS `overwork_hours`,
  1 AS `total_pendapatan`,
  1 AS `potongan_telat_berat`,
  1 AS `potongan_telat_ringan`,
  1 AS `potongan_alfa`,
  1 AS `kasbon`,
  1 AS `piutang_toko`,
  1 AS `potongan_lainnya`,
  1 AS `total_potongan`,
  1 AS `gaji_bersih`,
  1 AS `jumlah_hadir`,
  1 AS `jumlah_telat_ringan`,
  1 AS `jumlah_telat_berat`,
  1 AS `jumlah_alfa`,
  1 AS `jumlah_izin`,
  1 AS `status_slip`,
  1 AS `file_slip_gaji`,
  1 AS `generated_at`,
  1 AS `sent_at` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `v_absensi_dengan_shift`
--

/*!50001 DROP VIEW IF EXISTS `v_absensi_dengan_shift`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_absensi_dengan_shift` AS select `a`.`id` AS `id`,`a`.`user_id` AS `user_id`,`r`.`nama_lengkap` AS `nama_lengkap`,`r`.`posisi` AS `posisi`,`a`.`tanggal_absensi` AS `tanggal_absensi`,`a`.`waktu_masuk` AS `waktu_masuk`,`a`.`waktu_keluar` AS `waktu_keluar`,`a`.`jam_masuk_shift` AS `jam_masuk_shift`,`a`.`jam_keluar_shift` AS `jam_keluar_shift`,`a`.`durasi_kerja_menit` AS `durasi_kerja_menit`,`a`.`durasi_overwork_menit` AS `durasi_overwork_menit`,round(`a`.`durasi_overwork_menit` / 60.0,2) AS `jam_overwork`,`a`.`menit_terlambat` AS `menit_terlambat`,`a`.`status_keterlambatan` AS `status_keterlambatan`,`a`.`status_lembur` AS `status_lembur`,`a`.`is_overwork_approved` AS `is_overwork_approved`,`a`.`status_lokasi` AS `status_lokasi`,`c`.`nama_cabang` AS `outlet`,`c`.`nama_shift` AS `nama_shift`,case when `a`.`waktu_masuk` is null and `a`.`waktu_keluar` is null then 'Alfa' when `a`.`waktu_masuk` is not null and `a`.`waktu_keluar` is null then 'Belum Absen Keluar' when `a`.`waktu_masuk` is not null and `a`.`waktu_keluar` is not null then 'Lengkap' else 'Unknown' end AS `status_absensi` from ((`absensi` `a` join `register` `r` on(`a`.`user_id` = `r`.`id`)) left join `cabang` `c` on(`a`.`cabang_id` = `c`.`id`)) order by `a`.`tanggal_absensi` desc,`a`.`waktu_masuk` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_jadwal_shift_harian`
--

/*!50001 DROP VIEW IF EXISTS `v_jadwal_shift_harian`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_jadwal_shift_harian` AS select `sa`.`id` AS `id`,`sa`.`tanggal_shift` AS `tanggal_shift`,`sa`.`user_id` AS `user_id`,`r`.`nama_lengkap` AS `nama_lengkap`,`r`.`posisi` AS `posisi`,`r`.`no_whatsapp` AS `no_whatsapp`,`r`.`email` AS `email`,`sa`.`cabang_id` AS `cabang_id`,`co`.`nama_cabang` AS `outlet`,`c`.`nama_shift` AS `nama_shift`,`c`.`jam_masuk` AS `jam_masuk`,`c`.`jam_keluar` AS `jam_keluar`,`sa`.`status_konfirmasi` AS `status_konfirmasi`,`sa`.`waktu_konfirmasi` AS `waktu_konfirmasi`,`sa`.`catatan_pegawai` AS `catatan_pegawai`,`sa`.`created_at` AS `created_at`,case when `sa`.`tanggal_shift` in (select `libur_nasional`.`tanggal` from `libur_nasional`) then 'Libur Nasional' when dayofweek(`sa`.`tanggal_shift`) = 1 then 'Minggu' else 'Hari Kerja' end AS `status_hari` from (((`shift_assignments` `sa` join `register` `r` on(`sa`.`user_id` = `r`.`id`)) join `cabang` `c` on(`sa`.`cabang_id` = `c`.`id`)) join `cabang_outlet` `co` on(`c`.`nama_cabang` = `co`.`nama_cabang`)) order by `sa`.`tanggal_shift` desc,`c`.`jam_masuk` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_ringkasan_gaji`
--

/*!50001 DROP VIEW IF EXISTS `v_ringkasan_gaji`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_ringkasan_gaji` AS select `kg`.`id` AS `id`,`kg`.`user_id` AS `user_id`,`r`.`nama_lengkap` AS `nama_lengkap`,`r`.`posisi` AS `posisi`,`r`.`outlet` AS `outlet`,`kg`.`periode_bulan` AS `periode_bulan`,`kg`.`periode_tahun` AS `periode_tahun`,`kg`.`gaji_pokok` AS `gaji_pokok`,`kg`.`tunjangan_transport` AS `tunjangan_transport`,`kg`.`tunjangan_makan` AS `tunjangan_makan`,`kg`.`tunjangan_jabatan` AS `tunjangan_jabatan`,`kg`.`bonus_kehadiran` AS `bonus_kehadiran`,`kg`.`bonus_marketing` AS `bonus_marketing`,`kg`.`insentif_omset` AS `insentif_omset`,`kg`.`overwork_amount` AS `overwork_amount`,`kg`.`overwork_hours` AS `overwork_hours`,`kg`.`total_pendapatan` AS `total_pendapatan`,`kg`.`potongan_telat_berat` AS `potongan_telat_berat`,`kg`.`potongan_telat_ringan` AS `potongan_telat_ringan`,`kg`.`potongan_alfa` AS `potongan_alfa`,`kg`.`kasbon` AS `kasbon`,`kg`.`piutang_toko` AS `piutang_toko`,`kg`.`potongan_lainnya` AS `potongan_lainnya`,`kg`.`total_potongan` AS `total_potongan`,`kg`.`gaji_bersih` AS `gaji_bersih`,`kg`.`jumlah_hadir` AS `jumlah_hadir`,`kg`.`jumlah_telat_ringan` AS `jumlah_telat_ringan`,`kg`.`jumlah_telat_berat` AS `jumlah_telat_berat`,`kg`.`jumlah_alfa` AS `jumlah_alfa`,`kg`.`jumlah_izin` AS `jumlah_izin`,`kg`.`status_slip` AS `status_slip`,`kg`.`file_slip_gaji` AS `file_slip_gaji`,`kg`.`generated_at` AS `generated_at`,`kg`.`sent_at` AS `sent_at` from (`komponen_gaji_detail` `kg` join `register` `r` on(`kg`.`user_id` = `r`.`id`)) order by `kg`.`periode_tahun` desc,`kg`.`periode_bulan` desc,`r`.`nama_lengkap` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-05 21:30:02
