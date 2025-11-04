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
  `waktu_masuk` datetime DEFAULT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `status_lokasi` enum('Valid','Tidak Valid') DEFAULT NULL,
  `latitude_absen` decimal(10,8) DEFAULT NULL,
  `longitude_absen` decimal(11,8) DEFAULT NULL,
  `foto_absen` varchar(255) DEFAULT NULL,
  `foto_absen_keluar` varchar(255) DEFAULT NULL COMMENT 'Foto saat absen keluar',
  `tanggal_absensi` date DEFAULT NULL,
  `menit_terlambat` int(11) NOT NULL,
  `status_keterlambatan` varchar(50) DEFAULT 'tepat waktu',
  `potongan_tunjangan` varchar(50) DEFAULT 'tidak ada' COMMENT 'Tracking potongan tunjangan berdasarkan keterlambatan',
  `status_lembur` enum('Pending','Approved','Rejected','Not Applicable') NOT NULL DEFAULT 'Not Applicable',
  `status_kehadiran` varchar(50) DEFAULT 'Belum Absen Keluar',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_date` (`user_id`,`tanggal_absensi`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi`
--

LOCK TABLES `absensi` WRITE;
/*!40000 ALTER TABLE `absensi` DISABLE KEYS */;
INSERT INTO `absensi` VALUES (1,1,'2025-11-01 02:32:24','2025-11-01 02:32:27','Valid',-5.19803190,119.44795108,'masuk_absen_1_1761935544.jpg',NULL,'2025-10-31',0,'tidak ada shift','tidak ada','Not Applicable','Tidak Hadir'),(2,4,'2025-11-01 05:07:15','2025-11-01 05:07:20','Valid',-5.19802692,119.44796855,'masuk_absen_4_1761944835.jpg',NULL,'2025-10-31',428,'di luar shift','tidak ada','Not Applicable','Belum Absen Keluar'),(3,1,'2025-11-01 13:27:30','2025-11-01 13:27:32','Valid',-5.19801695,119.44796535,'masuk_absen_1_1761974850.jpg',NULL,'2025-11-01',0,'tidak ada shift','tidak ada','Not Applicable','Tidak Hadir'),(4,7,'2025-11-01 16:00:31','2025-11-01 16:03:06','Valid',-5.19802262,119.44798307,'masuk_absen_7_1761984031.jpg',NULL,'2025-11-01',121,'terlambat lebih dari 40 menit','tunjangan makan dan transport','Not Applicable','Belum Absen Keluar'),(25,8,'2025-11-03 00:46:21','2025-11-03 00:46:22','Valid',-5.19790573,119.44801409,NULL,'keluar_absen_keluar_8_1762101982.jpg','2025-11-02',167,'di luar shift','tidak ada','Pending','Belum Absen Keluar'),(27,1,'2025-11-03 01:36:58','2025-11-03 01:57:22','Valid',-5.19800051,119.44799465,'masuk_1_2025-11-03_1762105018.jpg','keluar_1_2025-11-03_1762106242.jpg','2025-11-03',0,'tidak ada shift','tidak ada','Pending','Tidak Hadir');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log for absensi errors and abuse attempts';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi_error_log`
--

LOCK TABLES `absensi_error_log` WRITE;
/*!40000 ALTER TABLE `absensi_error_log` DISABLE KEYS */;
INSERT INTO `absensi_error_log` VALUES (1,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:30:05'),(2,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:30:18'),(3,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:32:10'),(4,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:36:13'),(5,7,'DB_ERROR','Database error during absensi','SQLSTATE[01000]: Warning: 1265 Data truncated for column \'status_lokasi\' at row 1','::1',NULL,'2025-11-03 11:38:57');
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
-- Table structure for table `pegawai_whitelist`
--

DROP TABLE IF EXISTS `pegawai_whitelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pegawai_whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) NOT NULL,
  `posisi` varchar(100) DEFAULT NULL,
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
  UNIQUE KEY `unique_pegawai` (`nama_lengkap`,`posisi`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Whitelist table with salary components - allows import before registration';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pegawai_whitelist`
--

LOCK TABLES `pegawai_whitelist` WRITE;
/*!40000 ALTER TABLE `pegawai_whitelist` DISABLE KEYS */;
INSERT INTO `pegawai_whitelist` VALUES (1,'Mochammad Rifqi Athaullah Herfian','Barista','pending','2025-11-01 07:05:32','user',1750000.00,200000.00,200000.00,50000.00,250000.00,0.00,0.00,0.00),(2,'Farhan Zul Iqram','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(3,'Fathur Dwi Bintang','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(4,'Muh. Rezky Widodo','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(5,'Akbar Andipa','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(6,'Muhammad Isnan Al Gaffar','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(7,'Muhammad Arhamul Ihza M','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(8,'Zulfikar','Server','pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(9,'Muh. Rayhan Aprisal Fachrun','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,250000.00,0.00,0.00,0.00),(10,'Fitrah Ramadan Saputra','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(11,'Heru Hermawan','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(12,'Muh. Farel Rumante','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(13,'Andi Utha Ananta Winata','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(14,'Ahmad Fairuzi','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(15,'Muhammad Arya','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(16,'M Audi Alfan M S','Server','pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(17,'M. Maher','Server','pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(18,'Eko Prasetio','Barista','pending','2025-11-01 07:05:32','user',1750000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(19,'Muhammad Rafiul','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(20,'Arman Maulana','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(21,'Ahmad Mahendra','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(22,'Miftahul Ichwan','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(23,'Muh. Chasan Abdillah','Barista','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(24,'Muh. Rasul Alamsyah H','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(25,'Virgiawan','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(26,'M. Afif Yunus','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(27,'M Taufiq Ramadhan','Kitchen','pending','2025-11-01 07:05:32','user',1500000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(28,'Angga Eka Saputra Dewa','Server','pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(29,'Al Mukmin Dwi Yanto','Server','pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(30,'Muh. Adnil Nizar','Server','pending','2025-11-01 07:05:32','user',1300000.00,200000.00,200000.00,50000.00,0.00,0.00,0.00,0.00),(31,'Andi Abdul Chaerullah A','Marketing','pending','2025-11-01 07:05:32','admin',1750000.00,650000.00,600000.00,0.00,0.00,0.00,0.00,0.00),(32,'Rahmat Maulana','SCM','pending','2025-11-01 07:05:32','admin',1750000.00,650000.00,600000.00,0.00,0.00,0.00,0.00,0.00),(33,'M Yogi Alfadillah','Akuntan','pending','2025-11-01 07:05:32','admin',1750000.00,650000.00,600000.00,0.00,0.00,0.00,0.00,0.00),(34,'Muhammad Abizar Nafara','HR','terdaftar','2025-11-01 07:05:32','admin',1750000.00,350000.00,300000.00,0.00,0.00,0.00,0.00,0.00),(35,'Agung Dharmawan','Finance','pending','2025-11-01 07:05:32','admin',10000000.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00),(36,'Mohammad Rizky Putra','Owner','pending','2025-11-01 07:05:32','admin',10000000.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00),(39,'superadmin','superadmin','pending','2025-11-02 20:05:02','admin',0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00);
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
  `tanggal_pengajuan` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengajuan_izin`
--

LOCK TABLES `pengajuan_izin` WRITE;
/*!40000 ALTER TABLE `pengajuan_izin` DISABLE KEYS */;
INSERT INTO `pengajuan_izin` VALUES (1,2,'Cuti Melahirkan','2025-10-28','2025-10-30',2,'karena mau melahirkan','surat_izin_user_2_1761034041.docx','ttd_user_2_1761034041.png','Pending','2025-10-21'),(2,2,'Cuti Melahirkan','2025-10-28','2025-10-30',2,'karena mau melahirkan','surat_izin_user_2_1761034112.docx','ttd_user_2_1761034111.png','Pending','2025-10-21'),(3,2,'izin sakit banget','2025-10-22','2025-10-31',10,'karena sakit banget','surat_izin_user_2_1761069614.docx','ttd_user_2_1761069614.png','Pending','2025-10-22'),(4,1,'Tes Aplikasi','2025-11-01','2025-11-04',4,'ingin melakukan tes aplikasi','surat_izin_user_1_1761944033.docx','ttd_user_1.png','Diterima','2025-11-01');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posisi_jabatan`
--

LOCK TABLES `posisi_jabatan` WRITE;
/*!40000 ALTER TABLE `posisi_jabatan` DISABLE KEYS */;
INSERT INTO `posisi_jabatan` VALUES (1,'Kitchen','user'),(2,'Barista','user'),(3,'Senior Barista','user'),(4,'Server','user'),(5,'Marketing','admin'),(6,'Akuntan','admin'),(7,'Finance','admin'),(8,'Owner','admin'),(10,'SCM','admin'),(11,'HR','admin'),(12,'Tidak Ada Posisi','user'),(13,'superadmin','admin');
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
  `no_whatsapp` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `time_created` date NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `tanda_tangan_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `EMAIL` (`email`),
  UNIQUE KEY `no_whatsapp` (`no_whatsapp`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `unique_nama` (`nama_lengkap`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `register`
--

LOCK TABLES `register` WRITE;
/*!40000 ALTER TABLE `register` DISABLE KEYS */;
INSERT INTO `register` VALUES (1,'superadmin','superadmin','superadmin','superadmin','superadmin@gmail.com','$2y$10$lXNDAqQOdVC0uzYk64O68.pHH4RV.U1XkGw9i4YDtjjzgxEflrf2y','superadmin','2025-10-21','admin','1_1762016999.png','ttd_user_1_1762019092.png'),(4,'tesrole','Tidak Ada Posisi','Citraland Gowa','081928390128','tesrole@gmail.com','$2y$10$Q4OfmDwU1go70TILcznLkOWIW0Gn62dvbQu5wU8N6etOqtPxeRDsW','tesrole','2025-10-31','user',NULL,NULL),(7,'Muhammad Abizar Nafara','HR','Citraland Gowa','+62 8125800437','abizarnafara26@gmail.com','$2y$10$gf3UkVsrfTGhEFOLG4REreURGdYX1fRD0shUgHITf3JEKBCrbod2e','abizarnafara','2025-11-01','admin',NULL,NULL);
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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-03 19:49:28
