-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: buocchannho
-- ------------------------------------------------------
-- Server version	8.0.30

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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab','i:1;',1773655580),('laravel-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer','i:1773655580;',1773655580);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
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
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_type` enum('full-time','part-time','intern','contract') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hired_at` date DEFAULT NULL,
  `probation_end_at` date DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_email_unique` (`email`),
  UNIQUE KEY `employees_employee_code_unique` (`employee_code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (4,'GV002','Đào Thị Quỳnh Như  ','dtquynhnhu2610@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'employees/01KKV106WATA36Q983EWPR78WY.jpg',NULL,'female','active','2026-03-16 01:54:10','2026-03-16 03:05:43'),(7,'GV001','hao le','hao.le45@opsgreat.vn',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'employees/01KKV1Q2RT2JVXKG76FXZN1EAN.jpg',NULL,'male','active','2026-03-16 02:58:58','2026-03-16 03:05:26');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluation_histories`
--

DROP TABLE IF EXISTS `evaluation_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluation_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `evaluation_id` bigint unsigned NOT NULL,
  `snapshot` json NOT NULL,
  `saved_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluation_histories_evaluation_id_created_at_index` (`evaluation_id`,`created_at`),
  CONSTRAINT `evaluation_histories_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluation_histories`
--

LOCK TABLES `evaluation_histories` WRITE;
/*!40000 ALTER TABLE `evaluation_histories` DISABLE KEYS */;
INSERT INTO `evaluation_histories` VALUES (1,1,'{\"id\": 1, \"name\": \"KẾT QUẢ ĐÁNH GIÁ GIÁO DỤC CÁ NHÂN THÁNG 09 - 10/2025\", \"status\": \"pending\", \"created_at\": \"2026-03-17T14:57:08.000000Z\", \"updated_at\": \"2026-03-18T03:02:47.000000Z\", \"description\": null, \"planning_id\": 2, \"evaluation_details\": [{\"linh_vuc\": \"**Vận động**\", \"muc_tieu\": [{\"content\": \"Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Chạy nhanh – chậm theo hiệu lệnh.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Cắt giấy theo đường cong có đường kẻ sẵn.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\", \"danh_gia\": \"-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Nhận thức**\", \"muc_tieu\": [{\"content\": \"*Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\", \"danh_gia\": \"-\", \"nhan_xet\": null}, {\"content\": \"Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Đếm và nhớ kết quả của ít nhất 3 đối tượng. \", \"danh_gia\": \"+/-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Ngôn ngữ - giao tiếp**\", \"muc_tieu\": [{\"content\": \"Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\", \"danh_gia\": \"+/-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Kỹ năng tình cảm – xã hội**\", \"muc_tieu\": [{\"content\": \"Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \", \"danh_gia\": \"+\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"khác\", \"muc_tieu\": [{\"content\": \"Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \", \"danh_gia\": \"+\", \"nhan_xet\": null}]}]}',1,'2026-03-18 03:02:47','2026-03-18 03:02:47'),(2,1,'{\"id\": 1, \"name\": \"KẾT QUẢ ĐÁNH GIÁ GIÁO DỤC CÁ NHÂN THÁNG 09 - 10/2025\", \"status\": \"published\", \"created_at\": \"2026-03-17T14:57:08.000000Z\", \"updated_at\": \"2026-03-18T03:02:52.000000Z\", \"description\": null, \"planning_id\": 2, \"evaluation_details\": [{\"linh_vuc\": \"**Vận động**\", \"muc_tieu\": [{\"content\": \"Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Chạy nhanh – chậm theo hiệu lệnh.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Cắt giấy theo đường cong có đường kẻ sẵn.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\", \"danh_gia\": \"-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Nhận thức**\", \"muc_tieu\": [{\"content\": \"*Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\", \"danh_gia\": \"-\", \"nhan_xet\": null}, {\"content\": \"Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Đếm và nhớ kết quả của ít nhất 3 đối tượng. \", \"danh_gia\": \"+/-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Ngôn ngữ - giao tiếp**\", \"muc_tieu\": [{\"content\": \"Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\", \"danh_gia\": \"+/-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Kỹ năng tình cảm – xã hội**\", \"muc_tieu\": [{\"content\": \"Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \", \"danh_gia\": \"+\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"khác\", \"muc_tieu\": [{\"content\": \"Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \", \"danh_gia\": \"+\", \"nhan_xet\": null}]}]}',1,'2026-03-18 03:02:52','2026-03-18 03:02:52'),(3,1,'{\"id\": 1, \"name\": \"KẾT QUẢ ĐÁNH GIÁ GIÁO DỤC CÁ NHÂN THÁNG 09 - 10/2025\", \"status\": \"published\", \"created_at\": \"2026-03-17T14:57:08.000000Z\", \"updated_at\": \"2026-03-18T03:08:01.000000Z\", \"description\": \"sdfsd\", \"planning_id\": 2, \"evaluation_details\": [{\"linh_vuc\": \"**Vận động**\", \"muc_tieu\": [{\"content\": \"Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Chạy nhanh – chậm theo hiệu lệnh.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Cắt giấy theo đường cong có đường kẻ sẵn.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\", \"danh_gia\": \"-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Nhận thức**\", \"muc_tieu\": [{\"content\": \"*Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\", \"danh_gia\": \"-\", \"nhan_xet\": null}, {\"content\": \"Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Đếm và nhớ kết quả của ít nhất 3 đối tượng. \", \"danh_gia\": \"+/-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Ngôn ngữ - giao tiếp**\", \"muc_tieu\": [{\"content\": \"Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\", \"danh_gia\": \"+/-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Kỹ năng tình cảm – xã hội**\", \"muc_tieu\": [{\"content\": \"Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \", \"danh_gia\": \"+\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"khác\", \"muc_tieu\": [{\"content\": \"Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \", \"danh_gia\": \"+\", \"nhan_xet\": null}]}]}',1,'2026-03-18 03:08:01','2026-03-18 03:08:01');
/*!40000 ALTER TABLE `evaluation_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluations`
--

DROP TABLE IF EXISTS `evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `planning_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `evaluation_details` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  PRIMARY KEY (`id`),
  KEY `evaluations_planning_id_foreign` (`planning_id`),
  CONSTRAINT `evaluations_planning_id_foreign` FOREIGN KEY (`planning_id`) REFERENCES `plannings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluations`
--

LOCK TABLES `evaluations` WRITE;
/*!40000 ALTER TABLE `evaluations` DISABLE KEYS */;
INSERT INTO `evaluations` VALUES (1,'KẾT QUẢ ĐÁNH GIÁ GIÁO DỤC CÁ NHÂN THÁNG 09 - 10/2025','sdfsd',2,'2026-03-17 14:57:08','2026-03-18 03:08:01','[{\"linh_vuc\": \"**Vận động**\", \"muc_tieu\": [{\"content\": \"Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Chạy nhanh – chậm theo hiệu lệnh.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Cắt giấy theo đường cong có đường kẻ sẵn.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\", \"danh_gia\": \"-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Nhận thức**\", \"muc_tieu\": [{\"content\": \"*Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\", \"danh_gia\": \"-\", \"nhan_xet\": null}, {\"content\": \"Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Đếm và nhớ kết quả của ít nhất 3 đối tượng. \", \"danh_gia\": \"+/-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Ngôn ngữ - giao tiếp**\", \"muc_tieu\": [{\"content\": \"Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\", \"danh_gia\": \"+\", \"nhan_xet\": null}, {\"content\": \"Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\", \"danh_gia\": \"+/-\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"**Kỹ năng tình cảm – xã hội**\", \"muc_tieu\": [{\"content\": \"Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \", \"danh_gia\": \"+\", \"nhan_xet\": null}]}, {\"linh_vuc\": \"khác\", \"muc_tieu\": [{\"content\": \"Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \", \"danh_gia\": \"+\", \"nhan_xet\": null}]}]','published'),(4,'KẾ HOẠCH GIÁO DỤC CÁ NHÂN THÁNG 09 – 10/2025',NULL,1,'2026-03-18 02:41:20','2026-03-18 02:41:20','[{\"linh_vuc\": \"\", \"muc_tieu\": [{\"content\": \"\", \"danh_gia\": null, \"nhan_xet\": null}]}]','draft');
/*!40000 ALTER TABLE `evaluations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
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
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_available_at_index` (`queue`,`reserved_at`,`available_at`)
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
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(6,'2024_01_01_000000_create_employees_table',2),(7,'2026_03_16_090427_add_avatar_to_employees_table',3),(9,'2026_01_01_000000_create_students_table',4),(10,'2026_03_16_093323_add_sex_column_to_employees_table',4),(11,'2026_03_16_101500_add_basic_fields_to_employees_table',4),(13,'2026_03_16_104610_create_planning_and_evaluation_table',5),(14,'2026_03_18_090000_create_planning_histories_table',6),(15,'2026_03_18_090100_create_evaluation_histories_table',6);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `planning_histories`
--

DROP TABLE IF EXISTS `planning_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planning_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `planning_id` bigint unsigned NOT NULL,
  `snapshot` json NOT NULL,
  `saved_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `planning_histories_planning_id_created_at_index` (`planning_id`,`created_at`),
  CONSTRAINT `planning_histories_planning_id_foreign` FOREIGN KEY (`planning_id`) REFERENCES `plannings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planning_histories`
--

LOCK TABLES `planning_histories` WRITE;
/*!40000 ALTER TABLE `planning_histories` DISABLE KEYS */;
INSERT INTO `planning_histories` VALUES (1,2,'{\"id\": 2, \"name\": \"KẾ HOẠCH GIÁO DỤC CÁ NHÂN THÁNG 09 – 10/2025\", \"status\": \"published\", \"end_date\": \"2026-04-29T17:00:00.000000Z\", \"created_at\": \"2026-03-17T06:44:51.000000Z\", \"start_date\": \"2026-02-28T17:00:00.000000Z\", \"student_id\": 1, \"updated_at\": \"2026-03-17T14:37:21.000000Z\", \"description\": null, \"employee_id\": 4, \"planning_details\": [{\"linh_vuc\": [{\"content\": \"**Vận động**\"}], \"muc_tieu\": [{\"content\": \"- Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\"}, {\"content\": \"- Chạy nhanh – chậm theo hiệu lệnh.\"}, {\"content\": \"- Cắt giấy theo đường cong có đường kẻ sẵn.\"}, {\"content\": \"- Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\"}], \"hoat_dong\": [{\"content\": \"- Tạo hình\"}], \"phuong_tien\": [{\"content\": \"- Bóng, sọt\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Nhận thức**\"}], \"muc_tieu\": [{\"content\": \"- *Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\"}, {\"content\": \"- Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\"}, {\"content\": \"- Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \"}, {\"content\": \"- Đếm và nhớ kết quả của ít nhất 3 đối tượng. \"}], \"hoat_dong\": [{\"content\": \"- Trò chơi vận động “Chim bay – cá lặn\"}], \"phuong_tien\": [{\"content\": \"- 1 bộ lắp ráp nghề nghiệp \"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Ngôn ngữ - giao tiếp**\"}], \"muc_tieu\": [{\"content\": \"- Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\"}, {\"content\": \"- Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\"}], \"hoat_dong\": [{\"content\": \"- Tình huống diễn ra ở trung tâm\"}, {\"content\": \"- Xem video\"}], \"phuong_tien\": [{\"content\": \"- Thẻ tranh về nghề nghiệp\"}, {\"content\": \"- Tranh tô màu nghề nghiệp\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Kỹ năng tình cảm – xã hội**\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"Giới thiệu bản thân\"}], \"phuong_tien\": [], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"khác\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"phuong_tien\": [{\"content\": null}], \"muc_tieu_du_phong\": [{\"content\": null}]}]}',1,'2026-03-18 02:51:29','2026-03-18 02:51:29'),(2,2,'{\"id\": 2, \"name\": \"KẾ HOẠCH GIÁO DỤC CÁ NHÂN THÁNG 09 – 10/2025\", \"status\": \"published\", \"end_date\": \"2026-04-29T17:00:00.000000Z\", \"created_at\": \"2026-03-17T06:44:51.000000Z\", \"start_date\": \"2026-02-28T17:00:00.000000Z\", \"student_id\": 1, \"updated_at\": \"2026-03-17T14:37:21.000000Z\", \"description\": null, \"employee_id\": 4, \"planning_details\": [{\"linh_vuc\": [{\"content\": \"**Vận động**\"}], \"muc_tieu\": [{\"content\": \"- Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\"}, {\"content\": \"- Chạy nhanh – chậm theo hiệu lệnh.\"}, {\"content\": \"- Cắt giấy theo đường cong có đường kẻ sẵn.\"}, {\"content\": \"- Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\"}], \"hoat_dong\": [{\"content\": \"- Tạo hình\"}], \"phuong_tien\": [{\"content\": \"- Bóng, sọt\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Nhận thức**\"}], \"muc_tieu\": [{\"content\": \"- *Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\"}, {\"content\": \"- Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\"}, {\"content\": \"- Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \"}, {\"content\": \"- Đếm và nhớ kết quả của ít nhất 3 đối tượng. \"}], \"hoat_dong\": [{\"content\": \"- Trò chơi vận động “Chim bay – cá lặn\"}], \"phuong_tien\": [{\"content\": \"- 1 bộ lắp ráp nghề nghiệp \"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Ngôn ngữ - giao tiếp**\"}], \"muc_tieu\": [{\"content\": \"- Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\"}, {\"content\": \"- Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\"}], \"hoat_dong\": [{\"content\": \"- Tình huống diễn ra ở trung tâm\"}, {\"content\": \"- Xem video\"}], \"phuong_tien\": [{\"content\": \"- Thẻ tranh về nghề nghiệp\"}, {\"content\": \"- Tranh tô màu nghề nghiệp\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Kỹ năng tình cảm – xã hội**\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"Giới thiệu bản thân\"}], \"phuong_tien\": [], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"khác\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"phuong_tien\": [{\"content\": null}], \"muc_tieu_du_phong\": [{\"content\": null}]}]}',1,'2026-03-18 02:51:44','2026-03-18 02:51:44'),(3,2,'{\"id\": 2, \"name\": \"KẾ HOẠCH GIÁO DỤC CÁ NHÂN THÁNG 09 – 10/2025\", \"status\": \"pending\", \"end_date\": \"2026-04-29T17:00:00.000000Z\", \"created_at\": \"2026-03-17T06:44:51.000000Z\", \"start_date\": \"2026-02-28T17:00:00.000000Z\", \"student_id\": 1, \"updated_at\": \"2026-03-18T03:03:02.000000Z\", \"description\": null, \"employee_id\": 4, \"planning_details\": [{\"linh_vuc\": [{\"content\": \"**Vận động**\"}], \"muc_tieu\": [{\"content\": \"- Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\"}, {\"content\": \"- Chạy nhanh – chậm theo hiệu lệnh.\"}, {\"content\": \"- Cắt giấy theo đường cong có đường kẻ sẵn.\"}, {\"content\": \"- Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\"}], \"hoat_dong\": [{\"content\": \"- Tạo hình\"}], \"phuong_tien\": [{\"content\": \"- Bóng, sọt\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Nhận thức**\"}], \"muc_tieu\": [{\"content\": \"- *Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\"}, {\"content\": \"- Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\"}, {\"content\": \"- Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \"}, {\"content\": \"- Đếm và nhớ kết quả của ít nhất 3 đối tượng. \"}], \"hoat_dong\": [{\"content\": \"- Trò chơi vận động “Chim bay – cá lặn\"}], \"phuong_tien\": [{\"content\": \"- 1 bộ lắp ráp nghề nghiệp \"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Ngôn ngữ - giao tiếp**\"}], \"muc_tieu\": [{\"content\": \"- Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\"}, {\"content\": \"- Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\"}], \"hoat_dong\": [{\"content\": \"- Tình huống diễn ra ở trung tâm\"}, {\"content\": \"- Xem video\"}], \"phuong_tien\": [{\"content\": \"- Thẻ tranh về nghề nghiệp\"}, {\"content\": \"- Tranh tô màu nghề nghiệp\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Kỹ năng tình cảm – xã hội**\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"Giới thiệu bản thân\"}], \"phuong_tien\": [], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"khác\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"phuong_tien\": [{\"content\": null}], \"muc_tieu_du_phong\": [{\"content\": null}]}]}',1,'2026-03-18 03:03:02','2026-03-18 03:03:02'),(4,2,'{\"id\": 2, \"name\": \"KẾ HOẠCH GIÁO DỤC CÁ NHÂN THÁNG 09 – 10/2025\", \"status\": \"published\", \"end_date\": \"2026-04-29T17:00:00.000000Z\", \"created_at\": \"2026-03-17T06:44:51.000000Z\", \"start_date\": \"2026-02-28T17:00:00.000000Z\", \"student_id\": 1, \"updated_at\": \"2026-03-18T03:03:09.000000Z\", \"description\": null, \"employee_id\": 4, \"planning_details\": [{\"linh_vuc\": [{\"content\": \"**Vận động**\"}], \"muc_tieu\": [{\"content\": \"- Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\"}, {\"content\": \"- Chạy nhanh – chậm theo hiệu lệnh.\"}, {\"content\": \"- Cắt giấy theo đường cong có đường kẻ sẵn.\"}, {\"content\": \"- Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\"}], \"hoat_dong\": [{\"content\": \"- Tạo hình\"}], \"phuong_tien\": [{\"content\": \"- Bóng, sọt\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Nhận thức**\"}], \"muc_tieu\": [{\"content\": \"- *Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\"}, {\"content\": \"- Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\"}, {\"content\": \"- Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \"}, {\"content\": \"- Đếm và nhớ kết quả của ít nhất 3 đối tượng. \"}], \"hoat_dong\": [{\"content\": \"- Trò chơi vận động “Chim bay – cá lặn\"}], \"phuong_tien\": [{\"content\": \"- 1 bộ lắp ráp nghề nghiệp \"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Ngôn ngữ - giao tiếp**\"}], \"muc_tieu\": [{\"content\": \"- Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\"}, {\"content\": \"- Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\"}], \"hoat_dong\": [{\"content\": \"- Tình huống diễn ra ở trung tâm\"}, {\"content\": \"- Xem video\"}], \"phuong_tien\": [{\"content\": \"- Thẻ tranh về nghề nghiệp\"}, {\"content\": \"- Tranh tô màu nghề nghiệp\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Kỹ năng tình cảm – xã hội**\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"Giới thiệu bản thân\"}], \"phuong_tien\": [], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"khác\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"phuong_tien\": [{\"content\": null}], \"muc_tieu_du_phong\": [{\"content\": null}]}]}',1,'2026-03-18 03:03:09','2026-03-18 03:03:09'),(5,2,'{\"id\": 2, \"name\": \"KẾ HOẠCH GIÁO DỤC CÁ NHÂN THÁNG 09 – 10/2025\", \"status\": \"published\", \"end_date\": \"2026-04-29T17:00:00.000000Z\", \"created_at\": \"2026-03-17T06:44:51.000000Z\", \"start_date\": \"2026-02-28T17:00:00.000000Z\", \"student_id\": 1, \"updated_at\": \"2026-03-18T03:07:17.000000Z\", \"description\": \"ád\", \"employee_id\": 4, \"planning_details\": [{\"linh_vuc\": [{\"content\": \"**Vận động**\"}], \"muc_tieu\": [{\"content\": \"- Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\"}, {\"content\": \"- Chạy nhanh – chậm theo hiệu lệnh.\"}, {\"content\": \"- Cắt giấy theo đường cong có đường kẻ sẵn.\"}, {\"content\": \"- Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\"}], \"hoat_dong\": [{\"content\": \"- Tạo hình\"}], \"phuong_tien\": [{\"content\": \"- Bóng, sọt\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Nhận thức**\"}], \"muc_tieu\": [{\"content\": \"- *Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\"}, {\"content\": \"- Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\"}, {\"content\": \"- Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \"}, {\"content\": \"- Đếm và nhớ kết quả của ít nhất 3 đối tượng. \"}], \"hoat_dong\": [{\"content\": \"- Trò chơi vận động “Chim bay – cá lặn\"}], \"phuong_tien\": [{\"content\": \"- 1 bộ lắp ráp nghề nghiệp \"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Ngôn ngữ - giao tiếp**\"}], \"muc_tieu\": [{\"content\": \"- Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\"}, {\"content\": \"- Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\"}], \"hoat_dong\": [{\"content\": \"- Tình huống diễn ra ở trung tâm\"}, {\"content\": \"- Xem video\"}], \"phuong_tien\": [{\"content\": \"- Thẻ tranh về nghề nghiệp\"}, {\"content\": \"- Tranh tô màu nghề nghiệp\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Kỹ năng tình cảm – xã hội**\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"Giới thiệu bản thân\"}], \"phuong_tien\": [], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"khác\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"phuong_tien\": [{\"content\": null}], \"muc_tieu_du_phong\": [{\"content\": null}]}]}',1,'2026-03-18 03:07:17','2026-03-18 03:07:17');
/*!40000 ALTER TABLE `planning_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plannings`
--

DROP TABLE IF EXISTS `plannings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plannings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `employee_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `planning_details` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plannings_employee_id_foreign` (`employee_id`),
  KEY `plannings_student_id_foreign` (`student_id`),
  CONSTRAINT `plannings_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `plannings_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plannings`
--

LOCK TABLES `plannings` WRITE;
/*!40000 ALTER TABLE `plannings` DISABLE KEYS */;
INSERT INTO `plannings` VALUES (1,'KẾ HOẠCH GIÁO DỤC CÁ NHÂN THÁNG 09 – 10/2025',NULL,NULL,NULL,4,1,'[{\"linh_vuc\": [{\"content\": null}], \"muc_tieu\": [{\"content\": null}], \"hoat_dong\": [{\"content\": null}], \"phuong_tien\": [{\"content\": null}], \"muc_tieu_du_phong\": [{\"content\": null}]}]','published','2026-03-17 05:10:34','2026-03-17 06:27:46'),(2,'KẾ HOẠCH GIÁO DỤC CÁ NHÂN THÁNG 09 – 10/2025','ád','2026-03-01','2026-04-30',4,1,'[{\"linh_vuc\": [{\"content\": \"**Vận động**\"}], \"muc_tieu\": [{\"content\": \"- Chuyền và bắt bóng theo 2 bên hàng ngang, hàng dọc.\"}, {\"content\": \"- Chạy nhanh – chậm theo hiệu lệnh.\"}, {\"content\": \"- Cắt giấy theo đường cong có đường kẻ sẵn.\"}, {\"content\": \"- Sử dụng các nét thẳng, ngang, xiên, hình tròn để tạo hình ngôi nhà, cây xanh, bông hoa,...\"}], \"hoat_dong\": [{\"content\": \"- Tạo hình\"}], \"phuong_tien\": [{\"content\": \"- Bóng, sọt\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Nhận thức**\"}], \"muc_tieu\": [{\"content\": \"- *Củng cố vị mặn, ngọt* bằng cách nếm vị của một số món ăn\"}, {\"content\": \"- Nhận biết phương hướng trong không gian (trên – dưới) so với bản thân trẻ\"}, {\"content\": \"- Nhận biết tên gọi 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết chức năng công việc của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, , cảnh sát, ngư dân, thợ mộc.\"}, {\"content\": \"- Nhận biết lợi ích của 5 con vật quen thuộc: chó, gà, heo, bò, vịt. \"}, {\"content\": \"- Đếm và nhớ kết quả của ít nhất 3 đối tượng. \"}], \"hoat_dong\": [{\"content\": \"- Trò chơi vận động “Chim bay – cá lặn\"}], \"phuong_tien\": [{\"content\": \"- 1 bộ lắp ráp nghề nghiệp \"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Ngôn ngữ - giao tiếp**\"}], \"muc_tieu\": [{\"content\": \"- Nghe và thực hiện các yêu cầu bằng lời nói \\n**Ví dụ:** Con lấy cái kéo cẩt vào tủ rồi rửa tay.\"}, {\"content\": \"- Trả lời được tên gọi của 10 nghề nghiệp: cảnh sát giao thông, lái xe, phi công, họa sĩ, thợ cắt tóc, thợ may, vận động viên, cảnh sát, ngư dân, thợ mộc\"}], \"hoat_dong\": [{\"content\": \"- Tình huống diễn ra ở trung tâm\"}, {\"content\": \"- Xem video\"}], \"phuong_tien\": [{\"content\": \"- Thẻ tranh về nghề nghiệp\"}, {\"content\": \"- Tranh tô màu nghề nghiệp\"}], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"**Kỹ năng tình cảm – xã hội**\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"Giới thiệu bản thân\"}], \"phuong_tien\": [], \"muc_tieu_du_phong\": []}, {\"linh_vuc\": [{\"content\": \"khác\"}], \"muc_tieu\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"hoat_dong\": [{\"content\": \"- Giới thiệu tên, tuổi, giới tính bản thân, những điều trẻ thích và không thích.  \"}], \"phuong_tien\": [{\"content\": null}], \"muc_tieu_du_phong\": [{\"content\": null}]}]','published','2026-03-17 06:44:51','2026-03-18 03:07:17');
/*!40000 ALTER TABLE `plannings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `sessions` VALUES ('ze4VwuDyXBFTvaML5SslEHY95oZTgjtgGfrWJhwb',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0','YTo3OntzOjY6Il90b2tlbiI7czo0MDoidWcySDlkSnM3am1Kbk9jY2ZKcmI1WUxObk5QNmE0OHg2T2tucnprWiI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiNDQwN2IyMmYwYzFkOTZkODZlMjY4ZjU2NzczZTNiYmU0ODQzNzNiNTlhMTAxM2UyZTg2MDljZDcxNWIxMTIzOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly9idW9jY2hhbm5oby50ZXN0L2FkbWluL3BsYW5uaW5ncyI7czo1OiJyb3V0ZSI7czo0MDoiZmlsYW1lbnQuYWRtaW4ucmVzb3VyY2VzLnBsYW5uaW5ncy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6ODoiZmlsYW1lbnQiO2E6MDp7fXM6NjoidGFibGVzIjthOjM6e3M6NDA6IjQyMWQyMDEyMzA2MDRhYWZiN2U0N2I1MjEwNGI3YTVmX2NvbHVtbnMiO2E6NDp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjQ6Im5hbWUiO3M6NToibGFiZWwiO3M6MTY6IlTDqm4gxJHDoW5oIGdpw6EiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJwbGFubmluZy5uYW1lIjtzOjU6ImxhYmVsIjtzOjEyOiJL4bq/IGhv4bqhY2giO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6InN0YXR1cyI7czo1OiJsYWJlbCI7czoxMzoiVHLhuqFuZyB0aMOhaSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InVwZGF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTg6Ik5nw6B5IGPhuq1wIG5o4bqtdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiNWM5ZTRmNjI0MzFlY2NjZTc4YjIzYjk2ZTkwMzY2OTFfY29sdW1ucyI7YTo4OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NDoibmFtZSI7czo1OiJsYWJlbCI7czoxNzoiVMOqbiBr4bq/IGhv4bqhY2giO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJlbXBsb3llZS5uYW1lIjtzOjU6ImxhYmVsIjtzOjExOiJHacOhbyB2acOqbiI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6InN0dWRlbnQubmFtZSI7czo1OiJsYWJlbCI7czoxMDoiSOG7jWMgc2luaCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjM7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InN0YXJ0X2RhdGUiO3M6NToibGFiZWwiO3M6MTg6Ik5nw6B5IGLhuq90IMSR4bqndSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6ODoiZW5kX2RhdGUiO3M6NToibGFiZWwiO3M6MTc6Ik5nw6B5IGvhur90IHRow7pjIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJzdGF0dXMiO3M6NToibGFiZWwiO3M6MTM6IlRy4bqhbmcgdGjDoWkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo2O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjU6ImxhYmVsIjtzOjExOiJOZ8OgeSB04bqhbyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjc7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTA6InVwZGF0ZWRfYXQiO3M6NToibGFiZWwiO3M6MTg6Ik5nw6B5IGPhuq1wIG5o4bqtdCI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO319czo0MDoiODU5OWY1OWFmYjUyMThkZDBmNTQ4NWJkNTM5YzRlY2NfY29sdW1ucyI7YTozOntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NDoibmFtZSI7czo1OiJsYWJlbCI7czoxNjoiVMOqbiDEkcOhbmggZ2nDoSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjU6ImxhYmVsIjtzOjg6Ik3DtCB04bqjIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJzdGF0dXMiO3M6NToibGFiZWwiO3M6MTM6IlRy4bqhbmcgdGjDoWkiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fX19',1773803330);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_student_code_unique` (`student_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'HS001','Mai Ngọc Bích','female',NULL,NULL,NULL,NULL,NULL,'2021-07-21 00:00:00','students/01KKV1JEJP0KA6YRZNF2XWA6DG.jpg','active','2026-03-16 03:01:57','2026-03-17 10:22:01');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'superadmin','thomaszen63@gmail.com',NULL,'$2y$12$Yz9DPXAvRY1xh0Qbe6BCUegHR67N8IXI3IfagduupX9dtZ6VYBIS6','kmGeKqOuLOpWQP4p4W6lvEqrpOJxeD6J73ULF07Es4JgB8nMuEhYjKc4UWB0','2026-03-15 23:46:49','2026-03-15 23:46:49');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'buocchannho'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-18 10:15:50
