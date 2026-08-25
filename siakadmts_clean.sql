-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: siakadmts
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `year` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` enum('odd','even') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'odd',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_years_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (1,'2025/2026','odd',0,'2026-08-09 04:31:26','2026-08-19 02:06:08'),(2,'2026/2027','odd',1,'2026-08-11 02:46:54','2026-08-19 02:06:08');
/*!40000 ALTER TABLE `academic_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `achievements`
--

DROP TABLE IF EXISTS `achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `achievements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('published','draft') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `achievements`
--

LOCK TABLES `achievements` WRITE;
/*!40000 ALTER TABLE `achievements` DISABLE KEYS */;
INSERT INTO `achievements` VALUES (1,'Juara 1 Emas','Muhammad Sayid Ghani Allauddin Fattah','Tingkat Nasional',2025,'Muhammad Sayid Ghani Allauddin Fattah meraih medali emas tingkat SMP Jakarta Nasional Pencak Silat','achievements/a66JgQ7arWYPo6u8pM3NSwJqDDUCnkDaO0ckqQEx.jpg','published','2026-08-10 09:10:04','2026-08-13 01:59:17'),(2,'Medali Perak','Aulia Al Zahra','Tingkat Nasional',2025,'Siti Aisyah meraih medali Perak pada ajang SMP Jakarta Nasional Pencak Silat','achievements/HUVnpZiNlrkP3zFK7muD3dPYXhbJtV0Zt1dH9rP2.jpg','published','2026-08-10 09:10:05','2026-08-13 01:55:59'),(3,'Juara 1 Emas','Muhammad Daffa Rahadiansyah','Tingkat Nasional',2025,'Muhammad Daffa Rahadiansyah Meraih medali Emas tingkat SMP Jakarta Nasional Pencak Silat','achievements/CEezRM9GeyS8EqKrU9QkntWNqmetBAo4teVpJx2g.jpg','published','2026-08-10 09:10:05','2026-08-13 01:57:53');
/*!40000 ALTER TABLE `achievements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `teacher_id` bigint unsigned DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `status` enum('present','sick','permission','alpha') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_class_id_foreign` (`class_id`),
  KEY `attendances_student_id_class_id_date_index` (`student_id`,`class_id`,`date`),
  KEY `attendances_subject_id_foreign` (`subject_id`),
  KEY `attendances_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `attendances_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendances_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendances`
--

LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
INSERT INTO `attendances` VALUES (2,1,1,15,8,'2026-08-15','present','Hadir','2026-08-14 21:54:20','2026-08-14 22:01:21');
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Kegiatan Khusus',
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'violet-500',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
INSERT INTO `calendar_events` VALUES (9,'2026-07-01','2026-07-11','Libur Akhir Semester Genap Tp 2025/2026','2026-08-11 00:06:02','2026-08-11 00:06:02','Libur Akhir Semester Genap Tp 2025/2026','rose-500'),(10,'2026-07-10','2026-07-10','Tes Calon SPMB TP 2026/2027','2026-08-11 00:06:24','2026-08-11 00:06:24','Tes Calon SPMB TP 2026/2027','amber-500'),(11,'2026-07-14','2026-07-14','Hari Pertama Masuk Madrasah TP 2026/2027','2026-08-11 00:12:18','2026-08-11 00:12:18','Hari Pertama Masuk Madrasah TP 2026/2027','emerald-500'),(12,'2026-07-13','2026-07-15','Hari Pertama Masuk Madrasah TP 2026/2027','2026-08-11 00:12:43','2026-08-11 00:12:43','Hari Pertama Masuk Madrasah TP 2026/2027','teal-500'),(13,'2026-07-16','2026-07-16','KBM Mulai Efektif','2026-08-11 00:15:34','2026-08-11 00:15:34','KBM Mulai Efektif','slate-500'),(14,'2026-08-17','2026-08-17','Perayaan HUT Kemerdekaan RI (Upacara dan Lomba)','2026-08-11 00:16:16','2026-08-11 00:16:16','Perayaan HUT Kemerdekaan RI (Upacara dan Lomba)','teal-500'),(15,'2026-08-25','2026-08-25','Libur Maulid Nabi Muhammad SAW','2026-08-11 00:16:36','2026-08-11 00:16:36','Libur Maulid Nabi Muhammad SAW','rose-500'),(16,'2026-08-29','2026-08-29','Perayaan Maulid Nabi Muhammad SAW','2026-08-11 00:17:01','2026-08-11 00:17:01','Kegiatan Internal','emerald-500'),(17,'2026-09-28','2026-09-30','Pelaksanaan ASTS Ganjil','2026-08-11 00:17:42','2026-08-11 00:17:42','Pelaksanaan ASTS Ganjil','amber-500'),(18,'2026-10-01','2026-10-03','Pelaksanaan ASTS Ganjil','2026-08-11 00:18:03','2026-08-11 00:18:03','Pelaksanaan ASTS Ganjil','amber-500'),(19,'2026-10-11','2026-10-11','Pengambilan Rapot Bayangan ASTS Ganjil','2026-08-11 00:18:29','2026-08-11 00:18:29','Pengambilan Rapot Bayangan ASTS Ganjil','blue-500'),(20,'2026-11-25','2026-11-25','Hari Guru  Nasional (Upacara)','2026-08-11 00:18:57','2026-08-11 00:18:57','Hari Guru  Nasional (Upacara)','teal-500'),(21,'2026-11-30','2026-11-30','Asesmen Sumatif Akhir Semester (ASAS) Ganjil','2026-08-11 00:19:20','2026-08-11 00:19:20','Asesmen Sumatif Akhir Semester (ASAS) Ganjil','amber-500'),(22,'2026-12-01','2026-12-05','Asesmen Sumatif Akhir Semester (ASAS) Ganjil','2026-08-11 00:19:42','2026-08-11 00:19:42','Asesmen Sumatif Akhir Semester (ASAS) Ganjil','amber-500'),(23,'2026-12-07','2026-12-09','Remedial ASAS Ganjil','2026-08-11 00:20:17','2026-08-11 00:20:17','Remedial ASAS Ganjil','teal-500'),(24,'2026-12-10','2026-12-12','Classmeeting','2026-08-11 00:20:39','2026-08-11 00:20:39','Classmeeting','amber-500'),(25,'2026-12-14','2026-12-18','Pengolahan nilai dan Pengeprint-an Rapot Semester Ganjil oleh Wali Kelas','2026-08-11 00:21:11','2026-08-11 00:21:11','Pengolahan nilai dan Pengeprint-an Rapot Semester Ganjil oleh Wali Kelas','emerald-500'),(26,'2026-12-19','2026-12-19','Pembagian Raport Semester Ganjil','2026-08-11 00:21:33','2026-08-11 00:21:33','Kegiatan Internal','blue-500'),(27,'2026-12-21','2026-12-31','Libur Akhir Semester Ganjil','2026-08-11 00:22:03','2026-08-11 00:22:03','Libur Akhir Semester Ganjil','rose-500'),(28,'2026-12-25','2026-12-25','Hari Raya Natal','2026-08-11 00:22:25','2026-08-11 00:22:25','Hari Raya Natal','rose-500'),(31,'2027-01-01','2027-01-01','Tahun Baru Masehi','2026-08-11 00:25:59','2026-08-11 00:25:59','Tahun Baru Masehi','rose-500'),(32,'2027-01-02','2027-01-02','Libur Semester Ganjil','2026-08-11 00:29:07','2026-08-11 00:29:07','Libur Semester Ganjil','rose-500'),(33,'2027-01-03','2027-01-03','HAB Kementerian Agama','2026-08-11 00:29:31','2026-08-11 00:29:31','HAB Kementerian Agama','rose-500'),(34,'2026-01-04','2026-01-04','Awal Masuk Semester Genap','2026-08-11 00:30:14','2026-08-11 00:30:14','Awal Masuk Semester Genap','emerald-500'),(35,'2027-01-04','2027-01-04','Awal Masuk Semester Genap','2026-08-11 00:31:06','2026-08-11 00:31:06','Awal Masuk Semester Genap','emerald-500'),(36,'2027-01-05','2027-01-05','Libur Isra Mi\'raj Nabi Muhamad SAW','2026-08-11 00:31:29','2026-08-11 00:31:29','Libur Isra Mi\'raj Nabi Muhamad SAW','rose-500'),(37,'2027-01-09','2027-01-09','Peringatan  Isra Mi\'raj Nabi Muhamad SAW','2026-08-11 00:32:16','2026-08-11 00:32:16','Peringatan  Isra Mi\'raj Nabi Muhamad SAW','emerald-500'),(38,'2027-02-06','2027-02-06','Tahun Baru Imlek','2026-08-11 00:32:45','2026-08-11 00:32:45','Kegiatan Internal','violet-500'),(39,'2027-02-09','2027-02-11','Libur Awal Ramadhan','2026-08-11 00:33:08','2026-08-11 00:33:08','Libur Awal Ramadhan','rose-500'),(40,'2027-03-01','2027-03-05','Kegiatan Pesantren Kilat (Sanlat) Ramadhan','2026-08-11 00:34:10','2026-08-11 00:34:10','Kegiatan Pesantren Kilat (Sanlat) Ramadhan','teal-500'),(41,'2027-03-06','2027-03-13','Libur Hari Raya Idul Fitri 1448 H','2026-08-11 00:35:12','2026-08-11 00:35:12','Libur Hari Raya Idul Fitri 1448 H','pink-500'),(42,'2027-03-09','2027-03-09','Hari Raya Nyepi','2026-08-11 00:35:40','2026-08-11 00:35:40','Hari Raya Nyepi','rose-500'),(43,'2027-03-10','2027-03-11','Hari Raya Idul Fitri 1448 H','2026-08-11 00:36:07','2026-08-11 00:36:07','Hari Raya Idul Fitri 1448 H','rose-500'),(44,'2027-03-26','2027-03-26','Wafat Isa Almasih','2026-08-11 00:36:40','2026-08-11 00:36:40','Wafat Isa Almasih','rose-500'),(45,'2027-03-29','2027-03-31','Perkiraan Rentang Waktu ASTS Genap','2026-08-11 00:37:10','2026-08-11 00:37:10','Perkiraan Rentang Waktu ASTS Genap','amber-500'),(46,'2027-04-01','2027-04-03','Perkiraan Rentang Waktu ASTS Genap','2026-08-11 00:37:44','2026-08-11 00:37:44','Perkiraan Rentang Waktu ASTS Genap','amber-500'),(47,'2026-04-10','2026-04-10','Pembagian Rapot Bayangan ASTS Genap','2026-08-11 00:38:09','2026-08-11 00:38:09','Pembagian Rapot Bayangan ASTS Genap','blue-500'),(48,'2027-05-01','2027-05-08','Perkiraan Rentang Waktu Ujian Praktik Kelas IX','2026-08-11 00:40:49','2026-08-11 00:40:49','Perkiraan Rentang Waktu Ujian Praktik Kelas IX','blue-500'),(49,'2027-05-06','2027-05-06','Kenaikan Isa Al-Masih','2026-08-11 00:41:18','2026-08-11 00:41:18','Kenaikan Isa Al-Masih','rose-500'),(50,'2027-05-10','2027-05-15','Perkiraan Rentang Waktu Ujian Madrasah Kelas IX','2026-08-11 00:41:43','2026-08-11 00:41:43','Perkiraan Rentang Waktu Ujian Madrasah Kelas IX','cyan-500'),(51,'2027-05-31','2027-05-31','Asesmen Sumatif Akhir Tahun (ASAT) Genap','2026-08-11 00:42:08','2026-08-11 00:42:08','Asesmen Sumatif Akhir Tahun (ASAT) Genap','amber-500'),(52,'2027-06-01','2027-06-01','Hari Lahir Pancasila','2026-08-11 00:42:42','2026-08-11 00:42:42','Hari Lahir Pancasila','rose-500'),(53,'2027-06-02','2027-06-05','Asesmen Sumatif Akhir Tahun (ASAT) Genap','2026-08-11 00:43:07','2026-08-11 00:43:07','Asesmen Sumatif Akhir Tahun (ASAT) Genap','amber-500'),(54,'2027-06-07','2027-06-09','Remedial ASAT Genap','2026-08-11 00:43:33','2026-08-11 00:43:33','Remedial ASAT Genap','slate-500'),(55,'2027-06-10','2027-06-12','Classmeeting','2026-08-11 00:43:58','2026-08-11 00:43:58','Classmeeting','teal-500'),(56,'2027-06-14','2027-06-18','Pengolahan nilai dan Pengeprint-an Rapot Semester Genap oleh Wali Kelas','2026-08-11 00:44:19','2026-08-11 00:44:19','Pengolahan nilai dan Pengeprint-an Rapot Semester Genap oleh Wali Kelas','amber-500'),(57,'2027-06-19','2027-06-19','Pembagian Raport Semester Genap','2026-08-11 00:44:45','2026-08-11 00:44:45','Pembagian Raport Semester Genap','blue-500'),(58,'2027-06-21','2027-06-30','Libur Akhir Tahun Pelajaran','2026-08-11 00:45:24','2026-08-11 00:45:24','Libur Akhir Tahun Pelajaran','rose-500'),(59,'2027-06-25','2027-06-25','Hari Raya Natal','2026-08-11 00:45:46','2026-08-11 00:45:46','Hari Raya Natal','rose-500');
/*!40000 ALTER TABLE `calendar_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_teacher`
--

DROP TABLE IF EXISTS `class_teacher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_teacher` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `teacher_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_teacher_class_id_foreign` (`class_id`),
  KEY `class_teacher_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `class_teacher_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_teacher_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_teacher`
--

LOCK TABLES `class_teacher` WRITE;
/*!40000 ALTER TABLE `class_teacher` DISABLE KEYS */;
INSERT INTO `class_teacher` VALUES (1,3,3,NULL,NULL),(2,4,3,NULL,NULL),(3,4,2,NULL,NULL),(4,3,2,NULL,NULL),(5,1,4,NULL,NULL),(6,2,4,NULL,NULL),(7,3,4,NULL,NULL),(8,4,4,NULL,NULL),(9,1,5,NULL,NULL),(10,2,5,NULL,NULL),(11,3,5,NULL,NULL),(12,4,5,NULL,NULL),(13,1,6,NULL,NULL),(14,2,6,NULL,NULL),(15,3,6,NULL,NULL),(16,4,6,NULL,NULL),(17,1,7,NULL,NULL),(18,2,7,NULL,NULL),(19,3,7,NULL,NULL),(20,4,7,NULL,NULL),(21,1,8,NULL,NULL),(22,2,8,NULL,NULL),(23,3,8,NULL,NULL),(24,4,8,NULL,NULL),(25,1,9,NULL,NULL),(26,2,9,NULL,NULL),(27,3,9,NULL,NULL),(28,4,9,NULL,NULL),(29,1,10,NULL,NULL),(30,2,10,NULL,NULL),(31,3,10,NULL,NULL),(32,4,10,NULL,NULL),(33,1,11,NULL,NULL),(34,2,11,NULL,NULL),(35,3,11,NULL,NULL),(36,4,11,NULL,NULL),(37,1,12,NULL,NULL),(38,2,12,NULL,NULL),(39,1,13,NULL,NULL),(40,2,13,NULL,NULL),(41,3,13,NULL,NULL),(42,4,13,NULL,NULL),(43,3,14,NULL,NULL),(44,4,14,NULL,NULL),(45,1,15,NULL,NULL),(46,2,15,NULL,NULL),(47,3,15,NULL,NULL),(48,4,15,NULL,NULL);
/*!40000 ALTER TABLE `class_teacher` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `homeroom_teacher_id` bigint unsigned DEFAULT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade_level` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classes_academic_year_id_homeroom_teacher_id_index` (`academic_year_id`,`homeroom_teacher_id`),
  KEY `classes_homeroom_teacher_id_foreign` (`homeroom_teacher_id`),
  CONSTRAINT `classes_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `classes_homeroom_teacher_id_foreign` FOREIGN KEY (`homeroom_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,7,2,'7','7','2026-08-09 04:31:26','2026-08-11 07:13:21'),(2,5,2,'8','8','2026-08-11 00:49:10','2026-08-11 07:13:35'),(3,11,2,'9-A','9','2026-08-11 00:49:22','2026-08-11 07:13:45'),(4,2,2,'9-B','9','2026-08-11 00:49:31','2026-08-11 07:13:56');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facilities`
--

DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('published','draft') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facilities`
--

LOCK TABLES `facilities` WRITE;
/*!40000 ALTER TABLE `facilities` DISABLE KEYS */;
INSERT INTO `facilities` VALUES (1,'Gedung MTs Al - Hasanah','Fasilitas Tempat Mengajar di MTs Al - Hasanah','/storage/facilities/mxbv2jXkxxLxJ5PLyWk5yeqmf4hrSwtnSgMOfsgU.png','published','2026-08-10 02:53:35','2026-08-13 02:01:48'),(2,'Lapanga Madrasah','Fasilitas tempat olah raga, dan upacara bendera, dan kegaitan lainnya','/storage/facilities/fQ2dQyPWqWaXACaIZbiDMMlAXpQNmEBm7bVaXTbO.jpg','published','2026-08-10 02:53:35','2026-08-13 02:14:27'),(3,'Mushola','Fasilitas beribadah untuk guru dan siswa, dan pesantren di lingkungan madrasah','/storage/facilities/FIOrMGUUiOCSc6ae3yov5wP8YyXdWvQYzgXvoflg.jpg','published','2026-08-10 02:53:35','2026-08-13 02:16:23'),(4,'Perpustakaan','Fasilitas untuk membaca bagi murid di lingkungan madrasah kami','/storage/facilities/tX1rOVjD6KKbhljqxDM0PCLfaqrmvAOVn1jc5Gm6.jpg','published','2026-08-13 02:17:03','2026-08-13 02:17:03');
/*!40000 ALTER TABLE `facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `galleries`
--

DROP TABLE IF EXISTS `galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `galleries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `galleries`
--

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
INSERT INTO `galleries` VALUES (1,'Sholat Dhuha','/storage/galleries/3adx8vgOlc6voZSXHqNppCBDzBNI8FzKHL1Fs7b0.jpg','Kegiatan sholat dhuha berjalaah setiap pagi dari hari selasa sampai dengan hari sabtu','2026-08-09 19:26:51','2026-08-13 02:26:23'),(2,'Upacara Bendera','/storage/galleries/E7lUbW5Y03EwMYY5pQAJE7oMnRBU8W3IGvtEy772.jpg','Kegiatan Upacara Bedera Setiap Hari senin','2026-08-09 19:26:51','2026-08-13 02:27:20');
/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `score_assignment` decimal(5,2) NOT NULL DEFAULT '0.00',
  `score_uts` decimal(5,2) NOT NULL DEFAULT '0.00',
  `score_uas` decimal(5,2) NOT NULL DEFAULT '0.00',
  `custom_scores` json DEFAULT NULL,
  `final_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grades_student_id_subject_id_academic_year_id_unique` (`student_id`,`subject_id`,`academic_year_id`),
  KEY `grades_subject_id_foreign` (`subject_id`),
  KEY `grades_academic_year_id_foreign` (`academic_year_id`),
  KEY `grades_student_id_academic_year_id_subject_id_index` (`student_id`,`academic_year_id`,`subject_id`),
  CONSTRAINT `grades_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grades`
--

LOCK TABLES `grades` WRITE;
/*!40000 ALTER TABLE `grades` DISABLE KEYS */;
INSERT INTO `grades` VALUES (3,1,8,1,75.00,75.00,75.00,'[{\"id\": \"assignment\", \"name\": \"Tugas\", \"score\": 75, \"weight\": 30}, {\"id\": \"uts\", \"name\": \"UTS\", \"score\": 75, \"weight\": 35}, {\"id\": \"uas\", \"name\": \"UAS\", \"score\": 75, \"weight\": 35}]',75.00,'2026-08-14 21:55:42','2026-08-14 21:55:42');
/*!40000 ALTER TABLE `grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_04_142652_create_personal_access_tokens_table',1),(5,'2026_08_04_200001_create_academic_years_table',1),(6,'2026_08_04_200002_create_teachers_table',1),(7,'2026_08_04_200003_create_classes_table',1),(8,'2026_08_04_200004_create_students_table',1),(9,'2026_08_04_200005_create_subjects_table',1),(10,'2026_08_04_200006_create_grades_table',1),(11,'2026_08_04_200007_create_attendances_table',1),(12,'2026_08_05_010404_add_username_to_users_table',1),(13,'2026_08_05_021856_add_student_parent_fields_to_students_table',1),(14,'2026_08_05_022531_add_photo_url_to_students_table',1),(15,'2026_08_05_034139_add_subject_to_teachers_table',1),(16,'2026_08_05_085107_add_nullable_to_homeroom_teacher_in_classes_table',1),(17,'2026_08_06_010415_add_missing_columns_to_students',1),(18,'2026_08_07_070520_create_subject_teacher_table',1),(19,'2026_08_07_070521_remove_subject_from_teachers_table',1),(20,'2026_08_07_071022_add_photo_to_students_table',1),(21,'2026_08_07_092648_add_photo_to_teachers_table',1),(22,'2026_08_07_124539_create_settings_table',1),(23,'2026_08_07_134440_create_schedules_table',1),(24,'2026_08_08_140000_add_subject_id_to_attendances_table',1),(25,'2026_08_08_170000_add_custom_scores_and_kkm_to_grades_table',1),(26,'2026_08_08_180000_add_position_to_teachers_table',1),(27,'2026_08_09_122422_create_posts_table',2),(28,'2026_08_09_122429_create_galleries_table',2),(29,'2026_08_10_000000_create_facilities_table',3),(30,'2026_08_10_144410_create_achievements_table',4),(31,'2026_08_10_161328_create_calendar_events_table',5),(32,'2026_08_11_065632_alter_calendar_events_type_and_color_columns',6),(33,'2026_08_11_130000_alter_calendar_events_type_and_color',6),(34,'2026_08_11_140000_add_date_range_to_calendar_events_table',7),(35,'2026_08_11_200000_create_class_teacher_table',8),(36,'2026_08_11_210000_create_teacher_subject_class_table',9),(37,'2026_08_12_000000_create_password_reset_requests_table',10),(38,'2026_08_15_110000_add_teacher_id_to_attendances_table',11),(39,'2026_08_15_130000_create_school_settings_and_teacher_attendances_tables',12),(40,'2026_08_15_140000_create_teacher_attendance_requests_table',13),(41,'2026_08_18_000001_add_nik_and_guardian_fields_to_students_table',14);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_requests`
--

DROP TABLE IF EXISTS `password_reset_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `identity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_reset_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `password_reset_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_requests`
--

LOCK TABLES `password_reset_requests` WRITE;
/*!40000 ALTER TABLE `password_reset_requests` DISABLE KEYS */;
INSERT INTO `password_reset_requests` VALUES (1,'121232010188008','teacher','Muhamad Wildan','lupa Password Pa Her','rejected',17,'2026-08-12 02:45:08','2026-08-14 21:51:47'),(2,'Muhamad Wildan','teacher','Muhamad Wildan','lupa Password pa','rejected',17,'2026-08-12 02:50:15','2026-08-14 21:51:43');
/*!40000 ALTER TABLE `password_reset_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (46,'App\\Models\\User',1,'web-token','81166402f45e550b7d86ad973f398427dafcb8bc7c4e1d68cecfe323caa79855','[\"*\"]',NULL,NULL,'2026-08-13 00:41:51','2026-08-13 00:41:51'),(84,'App\\Models\\User',1,'web-token','9c600fcb20018ab6c731dceed7096eec2ac956e551266dae464fbab96266056f','[\"*\"]','2026-08-18 08:35:31',NULL,'2026-08-18 07:03:25','2026-08-18 08:35:31'),(86,'App\\Models\\User',1,'web-token','28293745b9b849e7c09b18bd7bf6264ad910092edbcde160d1bfbbea9be7c03c','[\"*\"]','2026-08-18 20:08:20',NULL,'2026-08-18 20:08:13','2026-08-18 20:08:20'),(88,'App\\Models\\User',15,'web-token','00611182801bdd40d6bcfac41b5be6a344e319b65eaabff963da28a62adb8e1b','[\"*\"]','2026-08-18 21:31:05',NULL,'2026-08-18 21:11:20','2026-08-18 21:31:05'),(97,'App\\Models\\User',9,'web-token','49cf9aa74d25c871afbfb4daf458f8f11f7b869b8c45dd834d256c17ff75b1a1','[\"*\"]','2026-08-22 00:51:39',NULL,'2026-08-22 00:45:06','2026-08-22 00:51:39'),(103,'App\\Models\\User',1,'web-token','88fec5d32b7b937e65e47dd5622e9688a07ccf6eec465884d8aa73dfcca2542d','[\"*\"]',NULL,NULL,'2026-08-24 06:30:51','2026-08-24 06:30:51'),(104,'App\\Models\\User',1,'web-token','05e383367d8fb2e7621247e7a48e5eacd67f6b8d1f2eb76b914d310acfe587fb','[\"*\"]',NULL,NULL,'2026-08-24 06:31:50','2026-08-24 06:31:50'),(105,'App\\Models\\User',20,'web-token','b9e383f0796c7186a5dcaf974e9d87398615676cfab5e77ae8730b67945b42bb','[\"*\"]',NULL,NULL,'2026-08-24 06:31:53','2026-08-24 06:31:53'),(106,'App\\Models\\User',21,'web-token','8fe6c86aec7cfe7bf05f52fd0e4247cba3cc44793c035a81ed03b410d477e8b1','[\"*\"]',NULL,NULL,'2026-08-24 06:31:59','2026-08-24 06:31:59'),(107,'App\\Models\\User',1,'web-token','b2129786a91dfa02f6985e35a17b86bf12a31a3e7d24de1048e3ab0591e32376','[\"*\"]','2026-08-24 07:01:15',NULL,'2026-08-24 06:34:46','2026-08-24 07:01:15');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('published','draft') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug_unique` (`slug`),
  KEY `posts_user_id_foreign` (`user_id`),
  CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'Penerimaan Peserta Didik Baru (PPDB) Tahun Ajaran 2026/2027 Resmi Dibuka!','penerimaan-peserta-didik-baru-ppdb-tahun-ajaran-20262027-resmi-dibuka','Kabar gembira bagi seluruh calon peserta didik! Pendaftaran PPDB untuk tahun ajaran baru kini telah resmi dibuka. Segera daftarkan diri Anda dan jadilah bagian dari keluarga besar sekolah kami. Kuota pendaftaran tahun ini sangat terbatas untuk memastikan kualitas pembelajaran yang optimal. Calon siswa dapat mendaftar langsung melalui portal web ini atau mengunjungi ruang tata usaha di jam kerja.','https://picsum.photos/800/500','published',1,'2026-08-10 01:55:33','2026-08-10 02:16:45'),(2,'Prestasi Gemilang: Tim Robotik Sekolah Sabet Juara 1 Tingkat Nasional','prestasi-gemilang-tim-robotik-sekolah-sabet-juara-1-tingkat-nasional','Prestasi membanggakan kembali diukir oleh siswa-siswi kita. Tim Robotik sekolah berhasil menyisihkan lebih dari 50 tim dari seluruh penjuru negeri dan meraih Juara 1 pada ajang Kompetisi Robotik Nasional 2026! Keberhasilan ini tidak lepas dari kerja keras para siswa dan bimbingan tanpa lelah dari bapak/ibu guru pembina. Mari kita terus dukung bakat-bakat luar biasa ini!','https://picsum.photos/800/500','published',1,'2026-08-10 01:55:34','2026-08-10 02:16:45'),(3,'Peringatan Hari Pendidikan & Gebyar Seni Budaya Siswa','peringatan-hari-pendidikan-gebyar-seni-budaya-siswa','Dalam rangka memperingati Hari Pendidikan Nasional, OSIS dengan bangga menyelenggarakan \"Gebyar Seni Budaya 2026\". Acara ini menampilkan puluhan pentas seni dari berbagai ekstrakurikuler, mulai dari tari tradisional, teater, hingga konser musik akustik. Acara berlangsung meriah dan ditutup dengan pemotongan tumpeng oleh Bapak Kepala Sekolah.','https://picsum.photos/800/500','published',1,'2026-08-10 01:55:34','2026-08-10 02:16:45');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `is_activity` tinyint(1) NOT NULL DEFAULT '0',
  `activity_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_type` enum('upacara','religi','ekstrakurikuler','kokurikuler','istirahat','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_id` bigint unsigned DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `teacher_id` bigint unsigned DEFAULT NULL,
  `day` enum('senin','selasa','rabu','kamis','jumat','sabtu') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `end_time` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `room` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedules_class_id_foreign` (`class_id`),
  KEY `schedules_subject_id_foreign` (`subject_id`),
  KEY `schedules_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `schedules_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedules_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
INSERT INTO `schedules` VALUES (7,1,'Istirahat','istirahat',1,NULL,NULL,'senin','22:31','11:00',NULL,'2026-08-11 07:40:21','2026-08-11 07:40:21'),(10,0,NULL,NULL,1,4,10,'senin','07:50','09:10',NULL,'2026-08-11 23:46:05','2026-08-11 23:46:05'),(11,0,NULL,NULL,1,7,12,'senin','09:11','10:30',NULL,'2026-08-11 23:46:59','2026-08-11 23:46:59'),(12,0,NULL,NULL,1,5,4,'senin','11:01','12:20',NULL,'2026-08-11 23:47:45','2026-08-11 23:47:45'),(13,0,NULL,NULL,2,6,5,'senin','07:50','09:10',NULL,'2026-08-11 23:48:26','2026-08-11 23:48:26'),(14,0,NULL,NULL,2,4,10,'senin','09:11','10:30',NULL,'2026-08-11 23:48:59','2026-08-11 23:48:59'),(15,0,NULL,NULL,2,7,12,'senin','11:01','12:20',NULL,'2026-08-11 23:49:41','2026-08-11 23:49:41'),(17,0,NULL,NULL,3,10,2,'senin','07:50','09:50',NULL,'2026-08-11 23:52:22','2026-08-11 23:52:22'),(18,0,NULL,NULL,3,12,11,'senin','09:51','10:30',NULL,'2026-08-11 23:52:52','2026-08-11 23:52:52'),(19,0,NULL,NULL,3,12,11,'senin','11:01','12:20',NULL,'2026-08-11 23:53:18','2026-08-11 23:53:18'),(20,0,NULL,NULL,4,12,11,'senin','07:50','09:50',NULL,'2026-08-11 23:54:12','2026-08-11 23:54:12'),(21,0,NULL,NULL,4,10,2,'senin','09:51','10:30',NULL,'2026-08-11 23:54:46','2026-08-11 23:54:46'),(22,0,NULL,NULL,4,10,2,'senin','11:01','12:20',NULL,'2026-08-11 23:55:13','2026-08-11 23:55:13'),(23,0,NULL,NULL,1,15,15,'selasa','07:30','08:50',NULL,'2026-08-11 23:56:59','2026-08-11 23:56:59'),(24,0,NULL,NULL,1,17,7,'selasa','08:51','09:30',NULL,'2026-08-11 23:57:46','2026-08-11 23:57:46'),(25,0,NULL,NULL,1,12,11,'selasa','09:31','10:10',NULL,'2026-08-11 23:59:03','2026-08-11 23:59:03'),(26,0,NULL,NULL,1,12,11,'selasa','10:41','12:00',NULL,'2026-08-11 23:59:33','2026-08-11 23:59:33'),(27,0,NULL,NULL,2,17,7,'selasa','07:30','08:50',NULL,'2026-08-12 00:00:59','2026-08-12 00:00:59'),(28,0,NULL,NULL,2,15,15,'selasa','08:51','10:10',NULL,'2026-08-12 00:02:11','2026-08-12 00:02:11'),(29,0,NULL,NULL,2,10,10,'selasa','10:40','12:00',NULL,'2026-08-12 00:02:50','2026-08-12 00:02:50'),(30,0,NULL,NULL,3,4,10,'selasa','07:30','08:50',NULL,'2026-08-12 00:22:20','2026-08-12 00:22:20'),(31,0,NULL,NULL,3,16,13,'selasa','08:51','09:30',NULL,'2026-08-12 00:23:02','2026-08-12 00:23:02'),(32,0,NULL,NULL,3,17,7,'selasa','09:31','10:10',NULL,'2026-08-12 00:23:40','2026-08-12 00:23:40'),(33,0,NULL,NULL,3,7,4,'selasa','10:41','12:00',NULL,'2026-08-12 00:24:41','2026-08-12 00:24:41'),(34,0,NULL,NULL,4,6,5,'selasa','07:30','08:50',NULL,'2026-08-12 00:25:23','2026-08-12 00:25:23'),(35,0,NULL,NULL,4,4,10,'selasa','08:51','10:10',NULL,'2026-08-12 00:26:34','2026-08-12 00:26:34'),(36,0,NULL,NULL,4,16,13,'selasa','10:41','12:00',NULL,'2026-08-12 00:28:59','2026-08-12 00:28:59'),(37,0,NULL,NULL,1,10,10,'rabu','07:30','09:30',NULL,'2026-08-12 00:54:24','2026-08-12 00:54:24'),(38,0,NULL,NULL,1,12,11,'rabu','09:31','10:10',NULL,'2026-08-12 00:54:51','2026-08-12 00:54:51'),(39,0,NULL,NULL,1,13,7,'rabu','10:41','12:00',NULL,'2026-08-12 00:56:58','2026-08-12 00:56:58'),(40,0,NULL,NULL,2,13,7,'rabu','07:30','08:50',NULL,'2026-08-12 00:57:53','2026-08-12 00:57:53'),(41,0,NULL,NULL,2,11,6,'rabu','08:51','09:30',NULL,'2026-08-12 00:58:54','2026-08-12 00:58:54'),(42,0,NULL,NULL,2,10,10,'rabu','09:31','10:10',NULL,'2026-08-12 01:26:50','2026-08-12 01:26:50');
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_settings`
--

DROP TABLE IF EXISTS `school_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'YASPIN',
  `latitude` decimal(10,8) DEFAULT '-6.20880000',
  `longitude` decimal(11,8) DEFAULT '106.84560000',
  `max_radius_meters` int NOT NULL DEFAULT '100',
  `work_start_time` time NOT NULL DEFAULT '07:00:00',
  `work_late_time` time NOT NULL DEFAULT '07:15:00',
  `work_end_time` time NOT NULL DEFAULT '15:00:00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_settings`
--

LOCK TABLES `school_settings` WRITE;
/*!40000 ALTER TABLE `school_settings` DISABLE KEYS */;
INSERT INTO `school_settings` VALUES (1,'SMK YASPIN',-6.60881196,106.75742432,100000,'07:30:00','08:00:00','15:00:00','2026-08-14 23:37:04','2026-08-15 01:24:24');
/*!40000 ALTER TABLE `school_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('AqSSB7Vj3hnlIJe23Vf9NaRL5IVeGhatpux5LNrv',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI2c2pYNkRWZEhJeVRXbktCVHRkVXVmTDhlTDRtakt5cmxhbEJRa0YxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvdGVhY2hlcnMiLCJyb3V0ZSI6bnVsbH0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1787578779);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'app_name','MTs AL - HASANAH','2026-08-09 06:54:38','2026-08-09 06:54:38'),(2,'app_tagline','Portal Madrasah Tsanawiyah Al - Hasanah Ciomas','2026-08-09 06:54:38','2026-08-09 18:47:06'),(3,'school_address','Jl. Ciapus Sukamakmur No.05, Desa Sukamakmur, Kec. Ciomas, Kab. Bogor, Prov. Jawa Barat 16610','2026-08-09 06:54:38','2026-08-13 00:02:33'),(4,'academic_year_id','1','2026-08-09 06:54:38','2026-08-09 06:54:38'),(5,'principal_teacher_id','2','2026-08-09 06:54:38','2026-08-10 02:34:09'),(6,'principal_message','Selamat Datang di Portal Madrasah MTs Al - Hasanah','2026-08-09 06:54:39','2026-08-13 00:27:14'),(7,'school_vision','???Terwujudnya Peserta Didik yang cerdas, terampil, mandiri, berakhlakul karimah berdasarkan iman dan taqwa???','2026-08-09 06:54:39','2026-08-13 00:13:44'),(8,'school_mission','1. Meningkatkan fungsi dan peran sekolah sebagai lingkungan pendidikan sakinah/2. Menciptakan lingkungan yang dapat menghasilkan lulusan yang berkuwalitas/3. Menjadikan peserta didik yang berpengetahuan, berkemampuan, berketerampilan, berbudi pekerti luhur serta beriman dan bertaqwa kepada ALLAH SWT/4. Menjalankan Nilai ??? Nilai agama dan berakhlak mulia dalam segala aspek kehidupan','2026-08-09 06:54:39','2026-08-13 00:54:13'),(9,'app_logo','settings/32VgDBEdQc7j4XbZ307gOVKqZLgDjvanyVWJpNb8.png','2026-08-09 06:54:43','2026-08-09 06:54:43'),(10,'hero_title','SISTEM INFORMASI DAN MENEJEMEN DIGITAL','2026-08-09 07:30:23','2026-08-15 09:52:34'),(11,'hero_background','settings/IzXEKJcgmu3Bpl05H2FoKvIj8FlMx0Bw7LTCials.jpg','2026-08-09 07:32:19','2026-08-09 07:32:19'),(12,'school_accreditation','Akreditasi A','2026-08-09 18:54:55','2026-08-09 18:54:55'),(13,'google_maps_embed','<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3963.3053870967196!2d106.75435587418815!3d-6.608923864603689!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69c512cde35a69%3A0xa854d3aba2cdba1a!2sMTS%20Al%20Hasanah!5e0!3m2!1sid!2sid!4v1786604507545!5m2!1sid!2sid\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"strict-origin-when-cross-origin\"></iframe>','2026-08-13 00:02:33','2026-08-13 00:02:33'),(14,'google_maps_link','https://maps.app.goo.gl/z53izTfnRfGmrYXV8','2026-08-13 00:02:33','2026-08-13 00:02:33'),(15,'school_phone','081617666017','2026-08-13 00:02:34','2026-08-13 00:02:34'),(16,'school_email','mtsalhasanah.ciomas@gmail.com','2026-08-13 00:02:34','2026-08-13 00:02:34'),(17,'principal_description','Kami hadirkan platform digital ini sebagai ruang informasi yang transparan, kreatif, dan inspiratif untuk seluruh siswa, orang tua, serta masyarakat. Mari bersama-sama bersinergi memanfaatkan teknologi demi memajukan mutu pendidikan dan mengukir prestasi terbaik!','2026-08-13 00:27:15','2026-08-13 00:27:15');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `class_id` bigint unsigned DEFAULT NULL,
  `nisn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nis` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_place` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `parent_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_status` enum('hidup','meninggal','pisah','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_job` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_income` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_status` enum('hidup','meninggal','pisah','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_job` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_income` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_relation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_nik` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_job` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_income` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_school` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_nisn_unique` (`nisn`),
  UNIQUE KEY `students_nis_unique` (`nis`),
  KEY `students_user_id_foreign` (`user_id`),
  KEY `students_class_id_nisn_nis_index` (`class_id`,`nisn`,`nis`),
  CONSTRAINT `students_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,3,1,'0000000001',NULL,'00000001','Budi Santoso','L','Jakarta','2008-01-15','Jl. Merdeka No. 1','081987654321','Siti Aminah','hidup','3201010101010001','Ibu Rumah Tangga','3.000.000','Sukardi','hidup','3201010101010002','Supir','4.500.000',NULL,NULL,NULL,NULL,NULL,NULL,'SDN 01 Jakarta',NULL,NULL,'2026-08-09 04:31:25','2026-08-09 04:31:26'),(2,19,2,'0976788758','3020001817889197','1212320101882602','Dummy','L','Bogor','2012-01-18','Kp. dummy','8566576576474','siti','hidup','3201267858765768','Tidak Bekerja','800.001 - 1.200.000','ayah dummy','meninggal',NULL,NULL,NULL,'siti','Ibu Kandung','3201267858765768','Tidak Bekerja','8566576576474','800.001 - 1.200.000','MTS AL - HASANAH',NULL,NULL,'2026-08-18 05:51:48','2026-08-18 05:51:48'),(3,21,NULL,'0012345678',NULL,'12345678','Siswa Demo','L','Bogor','2009-01-01','Ciomas, Bogor','081234567891',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-24 06:31:38','2026-08-24 06:31:38');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subject_teacher`
--

DROP TABLE IF EXISTS `subject_teacher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subject_teacher` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` bigint unsigned NOT NULL,
  `teacher_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_teacher_subject_id_foreign` (`subject_id`),
  KEY `subject_teacher_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `subject_teacher_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subject_teacher_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subject_teacher`
--

LOCK TABLES `subject_teacher` WRITE;
/*!40000 ALTER TABLE `subject_teacher` DISABLE KEYS */;
INSERT INTO `subject_teacher` VALUES (2,13,3,NULL,NULL),(3,10,2,NULL,NULL),(4,5,4,NULL,NULL),(5,7,4,NULL,NULL),(6,6,5,NULL,NULL),(7,7,5,NULL,NULL),(8,11,6,NULL,NULL),(9,13,7,NULL,NULL),(10,17,7,NULL,NULL),(11,9,8,NULL,NULL),(12,14,9,NULL,NULL),(13,4,10,NULL,NULL),(14,10,10,NULL,NULL),(15,12,11,NULL,NULL),(16,7,12,NULL,NULL),(17,16,13,NULL,NULL),(18,15,14,NULL,NULL),(19,8,15,NULL,NULL),(20,15,15,NULL,NULL);
/*!40000 ALTER TABLE `subject_teacher` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `passing_grade` int NOT NULL DEFAULT '75',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_code_unique` (`code`),
  KEY `subjects_code_index` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (4,'AQH001',75,'Al - Qur\'an Hadits','Mata Pelajaran Al - Qur\'an Hadits','2026-08-11 04:29:38','2026-08-11 04:29:38'),(5,'AA001',75,'Akidah Akhlak','Mata Pelajaran Akidah Akhlak','2026-08-11 04:29:57','2026-08-11 04:29:57'),(6,'F001',75,'Fikih','Mata Pelajaran Fikih','2026-08-11 04:30:21','2026-08-11 04:30:21'),(7,'SKI001',75,'Sejarah Kebudayaan Islam','Mata Pelajaran  Sejarah Kebudayaan Islam','2026-08-11 05:33:10','2026-08-11 05:34:03'),(8,'BA001',75,'Bahasa Arab','Mata Pelajaran Bahasa Arab','2026-08-11 05:34:15','2026-08-14 21:55:42'),(9,'PP001',75,'Pendidikan Pancasila','Mata Pelajaran Pendidikan Pancasila','2026-08-11 05:34:34','2026-08-11 05:34:34'),(10,'BI001',75,'Bahasa Indonesia','Mata Pelajaran Bahasa Indonesia','2026-08-11 05:34:48','2026-08-11 05:34:48'),(11,'M001',75,'Matematika','Mata Pelajaran Matematika','2026-08-11 05:34:59','2026-08-11 05:34:59'),(12,'IPA001',75,'Ilmu Pengetahuan Alam','Mata Pelajaran Ilmu Pengetahuan Alam','2026-08-11 05:35:14','2026-08-11 05:35:14'),(13,'IPS001',75,'Ilmu Pengetahuan Sosial','Mata Pelajaran Ilmu Pengetahuan Sosial','2026-08-11 05:35:25','2026-08-11 05:35:25'),(14,'BI002',75,'Bahasa Inggris','Mata Pelajaran Bahasa Inggris','2026-08-11 05:35:35','2026-08-11 05:35:35'),(15,'PJO001',75,'Pendidikan Jasmani, Olahraga, dan Kesehatan','Mata Pelajaran Pendidikan Jasmani, Olahraga, dan Kesehatan','2026-08-11 05:35:48','2026-08-11 05:35:48'),(16,'I001',75,'Informatika','Mata Pelajaran Informatika','2026-08-11 05:35:57','2026-08-11 05:35:57'),(17,'SBD001',75,'Seni Budaya dan Prakarya','Mata Pelajaran Seni Budaya dan Prakarya','2026-08-11 05:36:07','2026-08-11 05:36:07');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_attendance_requests`
--

DROP TABLE IF EXISTS `teacher_attendance_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_attendance_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `target_status` enum('hadir','terlambat','izin','sakit','tugas_luar') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hadir',
  `requested_check_in_time` time DEFAULT NULL,
  `requested_check_out_time` time DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `approval_status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_attendance_requests_teacher_id_date_index` (`teacher_id`,`date`),
  KEY `teacher_attendance_requests_approval_status_index` (`approval_status`),
  CONSTRAINT `teacher_attendance_requests_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_attendance_requests`
--

LOCK TABLES `teacher_attendance_requests` WRITE;
/*!40000 ALTER TABLE `teacher_attendance_requests` DISABLE KEYS */;
INSERT INTO `teacher_attendance_requests` VALUES (1,13,'2026-08-15','hadir','07:00:00','15:00:00','saya lupa absen pa','approved',NULL,'2026-08-15 00:36:28','2026-08-15 00:37:09');
/*!40000 ALTER TABLE `teacher_attendance_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_attendances`
--

DROP TABLE IF EXISTS `teacher_attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_in_latitude` decimal(10,8) DEFAULT NULL,
  `check_in_longitude` decimal(11,8) DEFAULT NULL,
  `check_in_distance_meters` int DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `check_out_latitude` decimal(10,8) DEFAULT NULL,
  `check_out_longitude` decimal(11,8) DEFAULT NULL,
  `check_out_distance_meters` int DEFAULT NULL,
  `status` enum('hadir','terlambat','izin','sakit','tugas_luar') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hadir',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_attendances_teacher_id_date_unique` (`teacher_id`,`date`),
  CONSTRAINT `teacher_attendances_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_attendances`
--

LOCK TABLES `teacher_attendances` WRITE;
/*!40000 ALTER TABLE `teacher_attendances` DISABLE KEYS */;
INSERT INTO `teacher_attendances` VALUES (1,13,'2026-08-15','07:00:00',-6.60881196,106.75742432,0,'15:00:00',-6.60881196,106.75742432,0,'hadir','Koreksi Disetujui: saya lupa absen pa','2026-08-15 00:00:04','2026-08-15 00:37:10'),(2,13,'2026-08-19','04:44:14',-6.60881196,106.75742432,0,NULL,NULL,NULL,NULL,'hadir','Presensi via Upload Gambar QR (Scan QR)','2026-08-18 21:44:14','2026-08-18 21:44:14');
/*!40000 ALTER TABLE `teacher_attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_subject_class`
--

DROP TABLE IF EXISTS `teacher_subject_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teacher_subject_class` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned NOT NULL,
  `class_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_subject_class_teacher_id_foreign` (`teacher_id`),
  KEY `teacher_subject_class_subject_id_foreign` (`subject_id`),
  KEY `teacher_subject_class_class_id_foreign` (`class_id`),
  CONSTRAINT `teacher_subject_class_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_class_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subject_class_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_subject_class`
--

LOCK TABLES `teacher_subject_class` WRITE;
/*!40000 ALTER TABLE `teacher_subject_class` DISABLE KEYS */;
INSERT INTO `teacher_subject_class` VALUES (55,2,10,3,'2026-08-13 01:08:42','2026-08-13 01:08:42'),(56,2,10,4,'2026-08-13 01:08:42','2026-08-13 01:08:42'),(57,3,13,3,'2026-08-13 01:33:42','2026-08-13 01:33:42'),(58,3,13,4,'2026-08-13 01:33:44','2026-08-13 01:33:44'),(59,4,5,1,'2026-08-13 01:34:21','2026-08-13 01:34:21'),(60,4,5,2,'2026-08-13 01:34:21','2026-08-13 01:34:21'),(61,4,5,3,'2026-08-13 01:34:21','2026-08-13 01:34:21'),(62,4,5,4,'2026-08-13 01:34:22','2026-08-13 01:34:22'),(63,4,7,3,'2026-08-13 01:34:22','2026-08-13 01:34:22'),(64,5,6,1,'2026-08-13 01:35:02','2026-08-13 01:35:02'),(65,5,6,2,'2026-08-13 01:35:02','2026-08-13 01:35:02'),(66,5,6,3,'2026-08-13 01:35:02','2026-08-13 01:35:02'),(67,5,6,4,'2026-08-13 01:35:02','2026-08-13 01:35:02'),(68,5,7,4,'2026-08-13 01:35:03','2026-08-13 01:35:03'),(69,6,11,1,'2026-08-13 01:35:40','2026-08-13 01:35:40'),(70,6,11,2,'2026-08-13 01:35:40','2026-08-13 01:35:40'),(71,6,11,3,'2026-08-13 01:35:40','2026-08-13 01:35:40'),(72,6,11,4,'2026-08-13 01:35:40','2026-08-13 01:35:40'),(73,15,8,1,'2026-08-13 01:37:14','2026-08-13 01:37:14'),(74,15,8,2,'2026-08-13 01:37:14','2026-08-13 01:37:14'),(75,15,8,3,'2026-08-13 01:37:15','2026-08-13 01:37:15'),(76,15,8,4,'2026-08-13 01:37:15','2026-08-13 01:37:15'),(77,15,15,1,'2026-08-13 01:37:15','2026-08-13 01:37:15'),(78,15,15,2,'2026-08-13 01:37:15','2026-08-13 01:37:15'),(79,14,15,3,'2026-08-13 01:38:51','2026-08-13 01:38:51'),(80,14,15,4,'2026-08-13 01:38:51','2026-08-13 01:38:51'),(81,13,16,1,'2026-08-13 01:39:20','2026-08-13 01:39:20'),(82,13,16,2,'2026-08-13 01:39:21','2026-08-13 01:39:21'),(83,13,16,3,'2026-08-13 01:39:21','2026-08-13 01:39:21'),(84,13,16,4,'2026-08-13 01:39:21','2026-08-13 01:39:21'),(85,12,7,1,'2026-08-13 01:40:18','2026-08-13 01:40:18'),(86,12,7,2,'2026-08-13 01:40:19','2026-08-13 01:40:19'),(87,11,12,1,'2026-08-13 01:41:04','2026-08-13 01:41:04'),(88,11,12,2,'2026-08-13 01:41:04','2026-08-13 01:41:04'),(89,11,12,3,'2026-08-13 01:41:04','2026-08-13 01:41:04'),(90,11,12,4,'2026-08-13 01:41:04','2026-08-13 01:41:04'),(91,10,4,1,'2026-08-13 01:41:38','2026-08-13 01:41:38'),(92,10,4,2,'2026-08-13 01:41:38','2026-08-13 01:41:38'),(93,10,4,3,'2026-08-13 01:41:38','2026-08-13 01:41:38'),(94,10,4,4,'2026-08-13 01:41:39','2026-08-13 01:41:39'),(95,10,10,1,'2026-08-13 01:41:39','2026-08-13 01:41:39'),(96,10,10,2,'2026-08-13 01:41:39','2026-08-13 01:41:39'),(97,9,14,1,'2026-08-13 01:43:51','2026-08-13 01:43:51'),(98,9,14,2,'2026-08-13 01:43:52','2026-08-13 01:43:52'),(99,9,14,3,'2026-08-13 01:43:52','2026-08-13 01:43:52'),(100,9,14,4,'2026-08-13 01:43:52','2026-08-13 01:43:52'),(101,8,9,1,'2026-08-13 01:45:13','2026-08-13 01:45:13'),(102,8,9,2,'2026-08-13 01:45:14','2026-08-13 01:45:14'),(103,8,9,3,'2026-08-13 01:45:14','2026-08-13 01:45:14'),(104,8,9,4,'2026-08-13 01:45:14','2026-08-13 01:45:14'),(105,7,13,1,'2026-08-13 01:45:45','2026-08-13 01:45:45'),(106,7,13,2,'2026-08-13 01:45:45','2026-08-13 01:45:45'),(107,7,17,1,'2026-08-13 01:45:45','2026-08-13 01:45:45'),(108,7,17,2,'2026-08-13 01:45:45','2026-08-13 01:45:45'),(109,7,17,3,'2026-08-13 01:45:45','2026-08-13 01:45:45'),(110,7,17,4,'2026-08-13 01:45:45','2026-08-13 01:45:45');
/*!40000 ALTER TABLE `teacher_subject_class` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teachers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `nip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teachers_nip_unique` (`nip`),
  KEY `teachers_user_id_nip_index` (`user_id`,`nip`),
  CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teachers`
--

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;
INSERT INTO `teachers` VALUES (2,4,'0042750651200013','H. Umar Usman Ali, S.Pd, S.Pd.I','L','878697687585','Kepala Madrasah','teachers/photos/ZSiF9cnvGqQhw3hlIIVYoTqrU1UK37z30U0g5HDM.png','2026-08-10 02:13:40','2026-08-13 01:08:41'),(3,5,'1537749653300002','Hj. Imu Eka Ariyanah, S.Ag','P','8786768758','Guru','teachers/photos/OjX27ttTDz1m6fuN9MXB5q7THKerXnjdcld6couo.png','2026-08-11 05:57:08','2026-08-13 01:33:40'),(4,6,'1735752654200022','Ujang Saepulloh, S.Ag','L','8768758587','Guru','teachers/photos/SkLtjt2eErQpSdWJZ502F3up2M1Bvo7mQ3oHsbYp.png','2026-08-11 07:00:35','2026-08-13 01:34:21'),(5,7,'4554755658200003','Cep Nanang, S.Pd.I','L','878678577','Guru','teachers/photos/yhjNRd8vlg8tIHf893wNN92b8HhQWOhnh9b07eA5.png','2026-08-11 07:01:56','2026-08-13 01:35:01'),(6,8,'2540762663300052','Siti Linawati, S.Pd','P','8657647433','Guru','teachers/photos/xEchMez5OHqSlmMTTJo3y5zqQiwME7NkVnsZo76n.png','2026-08-11 07:02:35','2026-08-13 01:35:39'),(7,9,'4739751652300042','Elah Jamilah, S.Pd','P','8785658758','Guru','teachers/photos/UDfUPRSgfMiusUzzDf9LeLF7YL8J4QpVCfuIpdsh.png','2026-08-11 07:03:40','2026-08-13 01:45:44'),(8,10,'121232010188001','Jumih, S.Ag','L','8685676476333','Guru','teachers/photos/mWAzgqTBVKTp6NRkIO2nD7YQpKx2afGoxzHdLlOJ.png','2026-08-11 07:04:22','2026-08-13 01:45:13'),(9,11,'121232010188002','Ulfah Fitri Ikayati, S.Pd','P','867565788788','Guru','teachers/photos/30ntgQgZKVPebviyTiJpRCMr9nqxX8Tdcg6BfsAC.png','2026-08-11 07:05:06','2026-08-13 01:43:51'),(10,12,'121232010188003','Hesti Fatimah, S.Pd','P','8675858656756','Guru','teachers/photos/KTZ2sgEKfHel3L7J23rEX8v0TQjYeggjYpcFbcrm.png','2026-08-11 07:06:25','2026-08-13 01:41:37'),(11,13,'121232010188004','Syarifah Zahrah, M.Pd','P','878768768768','Guru, Bendahara Madrasah','teachers/photos/ToASN1mG9w3OoEQRVwcvzmc6pkJpANm4Tsfl2vL7.png','2026-08-11 07:07:24','2026-08-13 01:41:03'),(12,14,'121232010188005','Lina Hasanah, S.Pd.I','P','86887877822','Guru','teachers/photos/OifUWZzjwjRh7jfw2fsVBtJq8fNgyxjaoeTDJJ0Q.jpg','2026-08-11 07:08:09','2026-08-13 01:40:18'),(13,15,'121232010188006','Heru Rachmawan','L','867687876222','Oprator, Guru','teachers/photos/2CXReBb0eK9Mknh4LWEYTDpnPoWk7eE4BHluxTND.png','2026-08-11 07:08:53','2026-08-13 01:39:20'),(14,16,'121232010188007','M. Delid Fahlevi, S.E','L','8687858787','Guru','teachers/photos/Tv3Um5FunerUQeAGP7LHw8WGt3KGr4dRG93geGQn.jpg','2026-08-11 07:09:45','2026-08-13 01:38:50'),(15,17,'121232010188008','Muhamad Wildan','L','86756587576','Guru','teachers/photos/0EQvElSROsq6QMJ6mQsVGbDypuo0j0dUzqzNLF7G.png','2026-08-11 07:10:46','2026-08-13 01:37:14'),(16,18,'121232010188009','Rosih','P','868585787687','Kurikulum, Tata Usaha','teachers/photos/Vuf3Iu0mcrrGqbcfbs1jYB1ipRKVUY7ba6j4bE4f.png','2026-08-11 07:11:37','2026-08-13 01:36:12'),(17,20,'198501012010011001','Guru Demo','L','081234567890',NULL,NULL,'2026-08-24 06:31:38','2026-08-24 06:31:38');
/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','teacher','student') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Sekolah','admin@example.com','admin','admin',NULL,'$2y$12$CJuSQ/Myqy5pUv3Ktj1fE.oV95jHUZZTZUD3Ade1WAtanadbp2uGG',NULL,'2026-08-09 04:31:22','2026-08-09 04:31:22'),(3,'Budi Santoso','student@example.com','0000000001','student',NULL,'$2y$12$VQFwJRrzwQxKu8cykxl6.eCZm8AQhhuJ8DRLZHl0wGCL5fR9KUyDe',NULL,'2026-08-09 04:31:25','2026-08-09 04:31:25'),(4,'H. Umar Usman Ali, S.Pd, S.Pd.I','0042750651200013@teacher.example.com','0042750651200013','teacher',NULL,'$2y$12$evpjtokSUHD8OLSu87cKDOoGQ9WBJHBEDfGCpJkSn0qC2q51BfhBK',NULL,'2026-08-10 02:13:40','2026-08-10 02:13:40'),(5,'Hj. Imu Eka Ariyanah, S.Ag','1537749653300002@teacher.example.com','1537749653300002','teacher',NULL,'$2y$12$T8npKLWyOZfyWIaExFU7tOKswLkIbG3gCTO7OzNlcQh39UA4lj9Qm',NULL,'2026-08-11 05:57:07','2026-08-11 05:57:07'),(6,'Ujang Saepulloh, S.Ag','1735752654200022@teacher.example.com','1735752654200022','teacher',NULL,'$2y$12$S8i1AbZdahJ4FZObnrFwF.MCatmnQ1aGHqNoL0yrmrD28vlrWayd2',NULL,'2026-08-11 07:00:35','2026-08-11 07:00:35'),(7,'Cep Nanang, S.Pd.I','4554755658200003@teacher.example.com','4554755658200003','teacher',NULL,'$2y$12$gdF8SU52v4H/2YfB4JGjBODbcu31.xWM7MRN/JQ7sENahCxIIE9ZC',NULL,'2026-08-11 07:01:56','2026-08-11 07:01:56'),(8,'Siti Linawati, S.Pd','2540762663300052@teacher.example.com','2540762663300052','teacher',NULL,'$2y$12$XeV4reQP/wPRhmI9pNr38uut24i8CHmVSjRQN1fIEIsoL/KtK9dGi',NULL,'2026-08-11 07:02:34','2026-08-11 07:02:34'),(9,'Elah Jamilah, S.Pd','4739751652300042@teacher.example.com','4739751652300042','teacher',NULL,'$2y$12$U4FfRtY7CLR6M8xv5MGlNOPLNUwDeqAt3MrUIPy5B.sr/7YRcCf6W',NULL,'2026-08-11 07:03:40','2026-08-11 07:03:40'),(10,'Jumih, S.Ag','121232010188001@teacher.example.com','121232010188001','teacher',NULL,'$2y$12$Sw5DIX4rDbWnD0M.PPpBI.s8.bghMsj5cqo98RzszUyEJAUmkHj3K',NULL,'2026-08-11 07:04:22','2026-08-11 07:04:22'),(11,'Ulfah Fitri Ikayati, S.Pd','121232010188002@teacher.example.com','121232010188002','teacher',NULL,'$2y$12$OaUbxeIrTfbDdY3o7csLX.cjjy74AL8.wvD5LoRXK9w0KonIDtTJu',NULL,'2026-08-11 07:05:06','2026-08-11 07:05:06'),(12,'Hesti Fatimah, S.Pd','121232010188003@teacher.example.com','121232010188003','teacher',NULL,'$2y$12$8QWbUdgjmoNx2PwI61TXT.Q061CpyTvUU/LxxVzUPKUpjFY1nudN2',NULL,'2026-08-11 07:06:25','2026-08-11 07:06:25'),(13,'Syarifah Zahrah, M.Pd','121232010188004@teacher.example.com','121232010188004','teacher',NULL,'$2y$12$whDpUJpnGAK/3luHIRmyt.pI1kCtulFiIy8VUQczNE3SSeSLkOiMC',NULL,'2026-08-11 07:07:24','2026-08-11 07:07:24'),(14,'Lina Hasanah, S.Pd.I','121232010188005@teacher.example.com','121232010188005','teacher',NULL,'$2y$12$DfgHlvwHLkJYhbaAwqP7ieRw.3NThEkQ35gZxChQRMs53cnDR5vuu',NULL,'2026-08-11 07:08:09','2026-08-11 07:08:09'),(15,'Heru Rachmawan','121232010188006@teacher.example.com','121232010188006','teacher',NULL,'$2y$12$f.ZNTSq5.U1G4ANF9uNi/ud370UmaM8NU/TCsqU7VcWLmAtV6LecC',NULL,'2026-08-11 07:08:53','2026-08-11 07:08:53'),(16,'M. Delid Fahlevi, S.E','121232010188007@teacher.example.com','121232010188007','teacher',NULL,'$2y$12$W2Oefe/YsBUuxdoq4Yd15.j7gNyUGVM6quwdCDsPH3bfEGvIDUqn.',NULL,'2026-08-11 07:09:45','2026-08-11 07:09:45'),(17,'Muhamad Wildan','121232010188008@teacher.example.com','121232010188008','teacher',NULL,'$2y$12$rLDkoHTaK2YhGXe2g1mAW.zwzrl3FWTYR56BwkSNRWw7P0TqIVbW6',NULL,'2026-08-11 07:10:46','2026-08-12 02:42:09'),(18,'Rosih','121232010188009@teacher.example.com','121232010188009','teacher',NULL,'$2y$12$f0Es/erqBJ.L16WnFb.mMOy8N47Pz7GaNg9iRzGhpaBM.a0wHlxTC',NULL,'2026-08-11 07:11:36','2026-08-11 07:11:36'),(19,'Dummy','0976788758@student.example.com','0976788758','student',NULL,'$2y$04$fvoi7JAqs9rqMbZXChoReuNmkzzxyoxvk0U4SGdxP.evUVzxHfXqy',NULL,'2026-08-18 05:51:48','2026-08-18 05:51:48'),(20,'Guru Demo (H. Umar Usman Ali)','guru@example.com','guru','teacher',NULL,'$2y$04$qZEL9b6I3GimoEN3/lew2u6kp1cKbxqiraYsIUSaeRWDk/0eeN5LC',NULL,'2026-08-24 06:31:37','2026-08-24 06:31:38'),(21,'Siswa Demo (Budi Santoso)','siswa@example.com','siswa','student',NULL,'$2y$04$xxKqgg0SpO9kOQiwxPRjqu6M02wyo6TP9fcymf11AFKrL4QRPJUkW',NULL,'2026-08-24 06:31:38','2026-08-24 06:31:38');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-25 15:22:34
