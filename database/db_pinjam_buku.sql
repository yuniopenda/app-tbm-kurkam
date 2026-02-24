-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: db_pinjam_buku
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `m_anggota`
--

DROP TABLE IF EXISTS `m_anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_anggota` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_anggota` varchar(20) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_by` varchar(26) NOT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_daftar` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim_nis` (`kode_anggota`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `m_anggota`
--
LOCK TABLES `m_anggota` WRITE;
/*!40000 ALTER TABLE `m_anggota` DISABLE KEYS */;
INSERT INTO `m_anggota` VALUES (1,'2026001','Ahmad Subarjo','Laki-laki','081234567890','Jl. Merdeka No. 10, Jakarta','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(2,'2026002','Siti Aminah','Perempuan','081234567891','Jl. mawar No. 5, Bandung','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(3,'2026003','Budi Hartanto','Laki-laki','081234567892','Jl. Melati No. 12, Surabaya','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(4,'2026004','Dewi Lestari','Perempuan','081234567893','Jl. Anggrek No. 8, Semarang','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(5,'2026005','Eko Prasetyo','Laki-laki','081234567894','Jl. Kamboja No. 3, Yogyakarta','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(6,'2026006','Fitri Handayani','Perempuan','081234567895','Jl. Kenanga No. 7, Malang','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(7,'2026007','Gilang Ramadhan','Laki-laki','081234567896','Jl. Dahlia No. 15, Bogor','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(8,'2026008','Hani Fatimah','Perempuan','081234567897','Jl. Tulip No. 2, Tangerang','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(9,'2026009','Indra Wijaya','Laki-laki','081234567898','Jl. Sakura No. 9, Bekasi','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(10,'2026010','Joko Susilo','Laki-laki','081234567899','Jl. Matahari No. 1, Depok','2026-02-21',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21',NULL),(11,'2026011','yunio','Perempuan','07376476322','tes','2026-02-22',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21','0000-00-00'),(12,'2026012','geri','Laki-laki','07376476322566','tes','2026-02-22',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21','2026-02-22'),(13,'2026013','bubu','Laki-laki','253435637334','tes','',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21','2026-02-22'),(14,'2026014','jojo','Perempuan','08127495748484','testing','',NULL,'2026-02-22 15:27:24','2026-02-22 15:46:21','2026-02-22'),(15,'2026015','dara','Perempuan','08352744444','pamulang','',NULL,'2026-02-22 15:29:17','2026-02-22 15:46:21','2026-02-22'),(16,'2026016','akio','Laki-laki','37236535434345','pamulang','',NULL,'2026-02-22 15:29:51','2026-02-22 15:46:21','2026-02-22'),(17,'2026017','dfdffds','Laki-laki','07376476322','tes','Admin',NULL,'2026-02-22 15:34:54','2026-02-22 15:46:21','2026-02-22'),(18,'2026018','dara','Laki-laki','07376476322','pamulang','Admin',NULL,'2026-02-22 15:35:28','2026-02-22 15:46:21','2026-02-22'),(19,'2026019','Sherly','Perempuan','08127495748484','testing','',NULL,'2026-02-22 15:36:13','2026-02-22 15:46:21','2026-02-22'),(20,'2026020','terasi','Laki-laki','08352744444','pamulang','Sherly Yunio','Sherly Yunio','2026-02-22 15:44:13','2026-02-22 15:48:06','2026-02-22');
/*!40000 ALTER TABLE `m_anggota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m_buku`
--
DROP TABLE IF EXISTS `m_buku`;
CREATE TABLE `m_buku` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_buku` varchar(20) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `penulis` varchar(255) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` time DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `penerbit` varchar(100) DEFAULT NULL,
  `tahun_terbit` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_buku` (`kode_buku`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `m_buku` WRITE;
/*!40000 ALTER TABLE `m_buku` DISABLE KEYS */;
INSERT INTO `m_buku` VALUES (1,'BK001','Algoritma Pemrograman','Teknologi','Rinaldi Munir',10,NULL,NULL,NULL,'2026-02-22 18:25:51',NULL,NULL),(2,'BK002','Dasar-Dasar Biologi','Sains','Campbell',5,NULL,NULL,NULL,'2026-02-22 18:25:51',NULL,NULL),(3,'BK003','Laskar Pelangi','Sastra','Andrea Hirata',8,NULL,NULL,NULL,'2026-02-22 18:25:51',NULL,NULL),(4,'BK004','Filosofi Teras','Self Improvement','Henry Manampiring',11,NULL,NULL,NULL,'2026-02-24 07:03:58',NULL,NULL),(5,'BK005','Sejarah Dunia','Sains','H.G. Wells',4,NULL,NULL,NULL,'2026-02-22 18:25:51',NULL,NULL),(6,'BK006','Pemrograman PHP Modern','Teknologi','Budi Raharjo',7,NULL,NULL,NULL,'2026-02-22 18:25:51',NULL,NULL),(7,'BK007','Bumi Manusia','Sastra','Pramoedya Ananta Toer',6,NULL,NULL,NULL,'2026-02-22 18:25:51',NULL,NULL),(8,'BK008','Atomic Habits','Self Improvement','James Clear',15,NULL,NULL,NULL,'2026-02-22 18:25:51',NULL,NULL),(9,'BK009','Fisika Kuantum','Sains','Stephen Hawking',3,NULL,NULL,NULL,'2026-02-22 18:25:51',NULL,NULL),(15,'BK010','sistem informasi','Umum','tes',5,NULL,NULL,NULL,'2026-02-22 18:24:14','tes',NULL);
/*!40000 ALTER TABLE `m_buku` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m_users`
--
DROP TABLE IF EXISTS `m_users`;
CREATE TABLE `m_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','petugas') DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `m_users` WRITE;
/*!40000 ALTER TABLE `m_users` DISABLE KEYS */;
INSERT INTO `m_users` VALUES (1,'admin','$argon2id$v=19$m=19456,t=2,p=1$MDAwM2RlYzg1MTlhMzcxM2M0MmE4ZmMwN2EzZjFiMjg$2kh9On5TUZ7WO+K0qrjMEIC2gmlM8C02Mi4rdwBqEWc','Sherly Yunio','admin');
/*!40000 ALTER TABLE `m_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_peminjaman`
--
DROP TABLE IF EXISTS `t_peminjaman`;
CREATE TABLE `t_peminjaman` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_anggota` bigint(20) unsigned NOT NULL,
  `id_buku` bigint(20) unsigned NOT NULL DEFAULT 0,
  `tgl_pinjam` date DEFAULT NULL,
  `tgl_kembali` date DEFAULT NULL,
  `status` enum('Dipinjam','Kembali') DEFAULT 'Dipinjam',
  `created_by` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tgl_kembali_seharusnya` date DEFAULT NULL,
  `tgl_dikembalikan` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_t_peminjaman_m_anggota` (`id_anggota`),
  KEY `FK_t_peminjaman_m_buku` (`id_buku`),
  CONSTRAINT `FK_t_peminjaman_m_anggota` FOREIGN KEY (`id_anggota`) REFERENCES `m_anggota` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_t_peminjaman_m_buku` FOREIGN KEY (`id_buku`) REFERENCES `m_buku` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `t_peminjaman` WRITE;
/*!40000 ALTER TABLE `t_peminjaman` DISABLE KEYS */;
INSERT INTO `t_peminjaman` VALUES (11,1,1,'2026-02-18','2026-02-20','Kembali',NULL,NULL,NULL,NULL,'2026-02-19',NULL),(12,7,9,'2026-02-22','2026-02-22','',NULL,NULL,NULL,NULL,NULL,NULL),(13,10,1,'2026-02-23','2026-03-02','Dipinjam',NULL,NULL,NULL,NULL,NULL,NULL),(14,8,4,'2026-02-24','2026-03-03','Dipinjam',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `t_peminjaman` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-24 20:48:49
