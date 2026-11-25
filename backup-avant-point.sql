-- MySQL dump 10.13  Distrib 8.0.32, for Linux (x86_64)
--
-- Host: localhost    Database: app
-- ------------------------------------------------------
-- Server version	8.0.32

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
-- Table structure for table `commerce`
--

DROP TABLE IF EXISTS `commerce`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commerce` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` longtext,
  `address` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `owner_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BBF5FDF97E3C61F9` (`owner_id`),
  CONSTRAINT `FK_BBF5FDF97E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commerce`
--

LOCK TABLES `commerce` WRITE;
/*!40000 ALTER TABLE `commerce` DISABLE KEYS */;
INSERT INTO `commerce` VALUES (5,'kebab','QQZDQZ','21 rue du kebabier','fast food','52552225','2026-10-21 06:47:27',5),(6,'Tonzinc Zincos','qdzdqd','fsesfe','qzdqdqd','qzdqzdqd','2026-10-21 09:11:52',6),(7,'add','aszdqdz','qzdqzdq','qzdqzd','qzdqzdd','2026-10-21 09:25:42',7),(8,'arigato','qzqzd','arigato@test.fr','qzdq','qzdqdqz','2026-10-21 09:29:46',8),(9,'kebab','sefsefsesfe','kebab87@outlook.fr','fast food','03 25 65 85 87','2026-11-12 12:50:06',10),(10,'Sushi','sushi','12 rue du sushi legendaire','restaurant','06 52 45 36 60','2026-11-18 08:42:34',13);
/*!40000 ALTER TABLE `commerce` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commerce_images`
--

DROP TABLE IF EXISTS `commerce_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commerce_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `position` smallint NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `commerce_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B613A92B09114B7` (`commerce_id`),
  CONSTRAINT `FK_B613A92B09114B7` FOREIGN KEY (`commerce_id`) REFERENCES `commerce` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commerce_images`
--

LOCK TABLES `commerce_images` WRITE;
/*!40000 ALTER TABLE `commerce_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `commerce_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20261010192919','2026-10-10 19:29:35',34),('DoctrineMigrations\\Version20261010193724','2026-10-10 19:37:36',38),('DoctrineMigrations\\Version20261010195659','2026-10-10 19:57:11',63),('DoctrineMigrations\\Version20261021071955',NULL,NULL),('DoctrineMigrations\\Version20261021084610','2026-10-21 08:47:44',157),('DoctrineMigrations\\Version20261118074944','2026-11-18 07:50:01',279),('DoctrineMigrations\\Version20261118091519','2026-11-18 09:15:37',71),('DoctrineMigrations\\Version20261118092035','2026-11-18 09:20:51',127),('DoctrineMigrations\\Version20261118093105','2026-11-18 09:31:09',95),('DoctrineMigrations\\Version20261118093545','2026-11-18 09:35:56',96),('DoctrineMigrations\\Version20261118094603','2026-11-18 09:46:08',111),('DoctrineMigrations\\Version20261118095523','2026-11-18 09:55:27',91),('DoctrineMigrations\\Version20261118100630','2026-11-18 10:06:34',57),('DoctrineMigrations\\Version20261118104141','2026-11-18 10:41:46',74),('DoctrineMigrations\\Version20261118104931','2026-11-18 10:49:45',77);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `commerce_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`commerce_id`),
  KEY `IDX_E46960F5A76ED395` (`user_id`),
  KEY `IDX_E46960F5B09114B7` (`commerce_id`),
  CONSTRAINT `FK_E46960F5A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E46960F5B09114B7` FOREIGN KEY (`commerce_id`) REFERENCES `commerce` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
INSERT INTO `favorites` VALUES ('2026-11-18 10:52:54',13,10);
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message` longtext NOT NULL,
  `type` varchar(30) DEFAULT NULL,
  `is_read` tinyint NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_read` (`user_id`,`is_read`),
  KEY `IDX_6000B0D3A76ED395` (`user_id`),
  CONSTRAINT `FK_6000B0D3A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (3,'Votre réservation « sushi » chez Sushi est confirmée. Code de retrait : A3397E — retrait le 18/11 entre 18:00 et 19:00.','reservation_confirmee',1,'2026-11-18 10:24:52',13),(4,'Sushi, l’un de vos commerces favoris, propose « grec » à 5,00 €.','nouvelle_offre',1,'2026-11-18 10:51:34',13);
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_preferences`
--

DROP TABLE IF EXISTS `offer_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offer_preferences` (
  `offer_id` int NOT NULL,
  `preference_id` int NOT NULL,
  PRIMARY KEY (`offer_id`,`preference_id`),
  KEY `IDX_68131AFB53C674EE` (`offer_id`),
  KEY `IDX_68131AFBD81022C0` (`preference_id`),
  CONSTRAINT `FK_68131AFB53C674EE` FOREIGN KEY (`offer_id`) REFERENCES `offre` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_68131AFBD81022C0` FOREIGN KEY (`preference_id`) REFERENCES `preference` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_preferences`
--

LOCK TABLES `offer_preferences` WRITE;
/*!40000 ALTER TABLE `offer_preferences` DISABLE KEYS */;
INSERT INTO `offer_preferences` VALUES (9,3);
/*!40000 ALTER TABLE `offer_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offre`
--

DROP TABLE IF EXISTS `offre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offre` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` longtext,
  `original_price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) NOT NULL,
  `quantity_total` int NOT NULL,
  `quantity_available` int NOT NULL,
  `pickup_start` datetime NOT NULL,
  `pickup_end` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `status` longtext NOT NULL,
  `commerce_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AF86866FB09114B7` (`commerce_id`),
  CONSTRAINT `FK_AF86866FB09114B7` FOREIGN KEY (`commerce_id`) REFERENCES `commerce` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offre`
--

LOCK TABLES `offre` WRITE;
/*!40000 ALTER TABLE `offre` DISABLE KEYS */;
INSERT INTO `offre` VALUES (3,'Panier anti-gaspillage','Assortiment de produits frais invendus.',15.00,7.50,10,10,'2026-11-12 19:00:00','2026-11-12 19:30:00','2026-11-12 13:13:50','active',5),(6,'Kebab','Halal',10.00,7.00,10,7,'2026-11-18 18:00:00','2026-11-18 19:00:00','2026-11-18 08:26:15','active',9),(7,'sushi','sushi legendaire',15.00,6.00,25,23,'2026-11-18 18:00:00','2026-11-18 19:00:00','2026-11-18 08:44:14','active',10),(9,'grec','grec',14.00,5.00,9,9,'2026-11-18 18:00:00','2026-11-18 19:00:00','2026-11-18 10:51:34','active',10);
/*!40000 ALTER TABLE `offre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offre_images`
--

DROP TABLE IF EXISTS `offre_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offre_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `position` smallint NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `offre_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8795DE414CC8505A` (`offre_id`),
  CONSTRAINT `FK_8795DE414CC8505A` FOREIGN KEY (`offre_id`) REFERENCES `offre` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offre_images`
--

LOCK TABLES `offre_images` WRITE;
/*!40000 ALTER TABLE `offre_images` DISABLE KEYS */;
INSERT INTO `offre_images` VALUES (1,'a8b1682e16e53617749f5dedd3976f4b.jpg',0,'2026-11-18 10:51:34',9);
/*!40000 ALTER TABLE `offre_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preference`
--

DROP TABLE IF EXISTS `preference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preference` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5D69B0535E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preference`
--

LOCK TABLES `preference` WRITE;
/*!40000 ALTER TABLE `preference` DISABLE KEYS */;
INSERT INTO `preference` VALUES (4,'halal'),(3,'sans gluten'),(5,'sans lactose'),(2,'vegan'),(1,'végétarien');
/*!40000 ALTER TABLE `preference` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quantity` int NOT NULL DEFAULT '1',
  `pickup_code` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `reserved_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `offer_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42C84955A76ED395` (`user_id`),
  KEY `IDX_42C8495553C674EE` (`offer_id`),
  CONSTRAINT `FK_42C8495553C674EE` FOREIGN KEY (`offer_id`) REFERENCES `offre` (`id`),
  CONSTRAINT `FK_42C84955A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation`
--

LOCK TABLES `reservation` WRITE;
/*!40000 ALTER TABLE `reservation` DISABLE KEYS */;
INSERT INTO `reservation` VALUES (1,1,'413494','cancelled','2026-11-18 08:02:55',10,3),(4,1,'19476B','collected','2026-11-18 08:32:05',10,6),(5,1,'CF0917','collected','2026-11-18 08:32:37',10,6),(6,1,'3096DF','confirmed','2026-11-18 08:32:50',10,6),(7,1,'AA54E4','collected','2026-11-18 08:49:03',13,7),(9,1,'A3397E','confirmed','2026-11-18 10:24:52',13,7);
/*!40000 ALTER TABLE `reservation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rating` smallint NOT NULL,
  `comment` longtext,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  `commerce_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6970EB0FA76ED395` (`user_id`),
  KEY `IDX_6970EB0FB09114B7` (`commerce_id`),
  CONSTRAINT `FK_6970EB0FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6970EB0FB09114B7` FOREIGN KEY (`commerce_id`) REFERENCES `commerce` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (7,4,'super bon','2026-11-18 10:25:05',13,10);
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mail` varchar(180) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `cgu_validated` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `inactivated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_MAIL` (`mail`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'test@example.com','[\"ROLE_USER\"]','$2y$13$REMPLACE_PAR_UN_HASH_BCRYPT','2026-10-14 06:56:28','2026-10-14 06:56:28','2026-10-14 06:56:28',NULL,NULL),(2,'test1@example.com','[\"ROLE_ADMIN\"]','$2y$13$7txpNCOVVfB6PWyenYuNA..PE8zShZoVspS/Go6lJlrQXuUcV9ze6','2026-10-14 06:58:34','2026-10-14 06:58:34','2026-10-14 06:58:34',NULL,NULL),(3,'bzz@test.fr','[\"ROLE_USER\"]','$2y$13$yfvgg1Y1BTLC9zFsleHTbuzj5NNwmwIJw2GEwLxpF4dh09w4udm3W','2026-10-14 07:06:59',NULL,NULL,NULL,NULL),(4,'titi@test.fr','[\"ROLE_ADMIN\"]','$2y$13$V1aSyYI0R9iSignT8uHrxOEwKDWHVxuEX4fQgmGULcyLztAIVt9A2','2026-10-14 07:56:47',NULL,NULL,NULL,NULL),(5,'non@test.fr','[\"ROLE_ADMIN\"]','$2y$13$W7ROk9p8PVrj62oaIfd5pefi0WJJ4nvhQia/M/qM/YGDDWdaNxQI.','2026-10-21 06:45:37',NULL,NULL,NULL,NULL),(6,'tonzinczincos@gmail.com','[\"ROLE_MERCHANT\"]','$2y$13$NVXZqWz5i4rFoXSyn3qmv.BajUmHbMukFUISXZho30m4i3YSnpnLK','2026-10-21 09:08:29',NULL,NULL,NULL,NULL),(7,'addem87@test.fr','[]','$2y$13$LarB4dpwCHr6fVyhO2H/UeG0J8x7vsnmURPus1KbcIn5eroKG9C66','2026-10-21 09:25:06',NULL,NULL,NULL,NULL),(8,'arigato@test.fr','[\"ROLE_USER\", \"ROLE_MERCHANT\"]','$2y$13$ddyjljb7w/Ephb.oNjb7auThuGJEY8YQnGRJmwjlb54wMRjWiUBfy','2026-10-21 09:28:50',NULL,NULL,NULL,NULL),(9,'tacos@test.fr','[]','$2y$13$eHyqaGeZMOnda7jyB69Bp.GkaDUT65MICWhMx7ZibVNpJieEuvCrK','2026-10-21 09:47:21',NULL,NULL,NULL,NULL),(10,'kebab87@outlook.fr','[\"ROLE_USER\", \"ROLE_MERCHANT\"]','$2y$13$Iz1zk4tN3WZkSNJmtzsrIORS.FERQKujvFxHRorXsr9ki302.UB26','2026-11-12 12:49:09',NULL,NULL,NULL,NULL),(11,'addem@gmail.com','[]','$2y$13$VhgdEYnaKy/IzkROSp.tWOSH0ACOCY99IjqZmgyvXYY243LYNRFvy','2026-11-12 12:51:01',NULL,NULL,NULL,NULL),(12,'addemdu87@gmail.com','[]','$2y$13$DD4CZerKh0YmtFNdSQ4qT.lU1qZAj5ocgwGVD4F8YEUZ4Z1jIA3cq','2026-11-18 08:37:41',NULL,NULL,NULL,NULL),(13,'sushi87@gmail.com','[\"ROLE_MERCHANT\"]','$2y$13$zlgrCloVxqwwnbNpUPi1gu5pRbwMguSodkr7P9DfWW1zytULXrh8K','2026-11-18 08:42:34',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_preferences` (
  `user_id` int NOT NULL,
  `preference_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`preference_id`),
  KEY `IDX_402A6F60A76ED395` (`user_id`),
  KEY `IDX_402A6F60D81022C0` (`preference_id`),
  CONSTRAINT `FK_402A6F60A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_402A6F60D81022C0` FOREIGN KEY (`preference_id`) REFERENCES `preference` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_preferences`
--

LOCK TABLES `user_preferences` WRITE;
/*!40000 ALTER TABLE `user_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_preferences` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-25  7:22:25
