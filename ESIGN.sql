-- MySQL dump 10.13  Distrib 8.0.39, for Linux (x86_64)
--
-- Host: localhost    Database: ESIGN
-- ------------------------------------------------------
-- Server version	8.0.39-0ubuntu0.22.04.1

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
-- Table structure for table `candidate`
--

DROP TABLE IF EXISTS `candidate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `depertement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cni` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `field` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `examination_center` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` datetime DEFAULT NULL,
  `place_of_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate_year_bac` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `candidate_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `cni_issue_date` date DEFAULT NULL,
  `secondary_education_start_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_education_end_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_subsystem` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `second_cycle_study_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `baccalaureate_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `baccalaureate_series` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `baccalaureate_mention` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gce_alevel_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gce_alevel_papers_count` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gce_alevel_grade_acount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gce_olevel_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gce_olevel_papers_count` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_operator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `probatoire_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_certificate` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificate_year_gce` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_date` datetime DEFAULT NULL,
  `medical_certificate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criminal_record_extract` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transcript` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C8B28E44A76ED395` (`user_id`),
  CONSTRAINT `FK_C8B28E44A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate`
--

LOCK TABLES `candidate` WRITE;
/*!40000 ALTER TABLE `candidate` DISABLE KEYS */;
INSERT INTO `candidate` VALUES (29,'EMANE BILE FÉLICIEN DAVY','Male','South','Dja-et-Lobo','10023654789','Création et Design Numériques','Bafoussam','EMANE-BILE-FELICIEN-DAVY-CUE-0380317-Diplome.jpg','2002-01-08 00:00:00','ndjom-yekombo','-','Anglais','EUabd124578955','#CUE-0380317','+237676469014','davyemane@esign.cm','Cameroon','EMANE-BILE-FELICIEN-DAVY-CUE-0380317-Photo.jpg',32,'2024-08-08','2012','2017','ANGLOPHONE','Science','-','-','-','Cameroon','5','5','2019','5','EXPRESS UNION','-','EMANE-BILE-FELICIEN-DAVY-CUE-0380317-ActeNaissance.jpg','2019','2024-09-09 14:36:34',NULL,NULL,NULL),(30,'BEKONO BILE','Female','South','Dja-et-Lobo','10023654789','Création et Design Numériques','Yaoundé','BEKONO-BILE-CUE-2714456-Diplome.jpg','2004-09-09 00:00:00','ndjom-yekombo','2022','Français','EUabd124578955','#CUE-2714456','+237676469014','davyemane2@gmail.com','Cameroon','BEKONO-BILE-CUE-2714456-Photo.jpg',33,'2020-09-10','2014','2022','FRANCOPHONE','Science','Cameroon','C','Tres Bien','-','-','-','-','-','EXPRESS UNION','2021','BEKONO-BILE-CUE-2714456-ActeNaissance.jpg','-','2024-09-07 19:58:52','BEKONO-BILE-CUE-2714456.jpg','BEKONO-BILE-CUE-2714456.jpg','BEKONO-BILE-CUE-2714456.jpg');
/*!40000 ALTER TABLE `candidate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20240907194250','2024-09-07 19:43:16',2328);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `has_registered` tinyint(1) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (32,'[\"ROLE_USER\"]','$2y$13$4WEbUEa.WPnm3hNXyCIS.Ohsoee5/pDZbOcPPp290KPLtj0kDThim',1,'davyemane@esign.cm',NULL,NULL),(33,'[\"ROLE_USER\"]','$2y$13$o1.MyVo8DBDR0nmK0bK/iOT9cNPeyO5ZmKpR92v/8zKf9D4iUFQW.',1,'davyemane2@gmail.com',NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-09-07 21:09:07
