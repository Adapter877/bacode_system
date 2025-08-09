-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: php_blog
-- ------------------------------------------------------
-- Server version	8.0.42

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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_post` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (6,'คณะวิทยาศาสตร์และเทคโนโลยี','2025-04-09 14:22:48',1),(7,'เปิดรับสมัคร คณะกรรมการอนุสโมสรนักศึกษาคณะวิทยาศาสตร์และเทคโนโลยี ประจำปีการศึกษา 2568','2025-07-31 03:58:00',1);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `posts_id` int NOT NULL AUTO_INCREMENT,
  `posts_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `posts_content` text COLLATE utf8mb4_general_ci NOT NULL,
  `posts_image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `category_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tag_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `author` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`posts_id`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (114,'next.js อบรบการพัฒนาเว็บ','next.js อบรบการพัฒนาเว็บ','uploads/Screenshot_2568-07-20_at_15_16_45_20250809_055446_5e9faa27.png','','','admin','2025-08-09 05:54:46');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_activities`
--

DROP TABLE IF EXISTS `student_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `student_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `major` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activity_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activity_hours` int DEFAULT NULL,
  `barcode` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `contact` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_joined` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_activities`
--

LOCK TABLES `student_activities` WRITE;
/*!40000 ALTER TABLE `student_activities` DISABLE KEYS */;
INSERT INTO `student_activities` VALUES (8,'เมธาวี พราหมณ์แก้ว','Adapter87','เทคโนโลยีสารสนเทศ','เปิดรับสมัคร คณะกรรมการอนุสโมสรนักศึกษาคณะวิทยาศาสตร์และเทคโนโลยี  ประจำปีการศึกษา 2568',2,'AC1630-gwuV-dX11','phramkaeo93@gmail.com','2025-08-09','2025-08-09 05:50:33');
/*!40000 ALTER TABLE `student_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (18,'การแข่งขัน คณะวิทยาศาสตร์','2025-04-09 14:18:12'),(20,'งานสัปดาห์วิทยาศาสตร์แห่งชาติส่วนภูมิภาคประจำปี 2568','2025-07-31 03:52:00'),(21,'เปิดรับสมัคร คณะกรรมการอนุสโมสรนักศึกษาคณะวิทยาศาสตร์และเทคโนโลยี ประจำปีการศึกษา 2568','2025-07-31 03:58:18');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `student_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `major` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_info`
--

LOCK TABLES `user_info` WRITE;
/*!40000 ALTER TABLE `user_info` DISABLE KEYS */;
INSERT INTO `user_info` VALUES (1,'admin','admin','admin@example.co',NULL,NULL,'0dc677fe32ca46f6b753e2bd7601920b',0,'2022-08-16 08:12:57'),(24,'demo','demo','demo@example.com',NULL,NULL,'d41d8cd98f00b204e9800998ecf8427e',0,'2022-08-16 13:24:37'),(37,'เมธาวี พราหมณ์แก้ว','Adapter87','phramkaeo93@gmail.com',NULL,NULL,'d33f1a6621f17e8090f8fb9c1b6b6f01',3,'2025-04-11 18:34:04'),(38,'อัมพร สุขเกษร','Runtimess','u64042970103@uru.ac.th',NULL,NULL,'1c0d99b95a6fab4195eaf765112a1c44',3,'2025-06-26 19:24:51'),(39,'ลัดดาวัลย์ เข็มทอง','laddawan','u642380109@uru.ac.th',NULL,NULL,'25d55ad283aa400af464c76d713c07ad',3,'2025-06-27 07:37:23'),(40,'มาริสา พงศ์พินิจ','laddawann','u642380108@uru.ac.th',NULL,NULL,'25f9e794323b453885f5181f1b624d0b',3,'2025-07-03 06:58:38'),(41,'ลัดดาวัลย์ เข็มทอง','u64042380109','caimen2558@gmail.com',NULL,NULL,'25f9e794323b453885f5181f1b624d0b',3,'2025-07-21 03:41:43');
/*!40000 ALTER TABLE `user_info` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-09  5:59:21
