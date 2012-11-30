-- MySQL dump 10.13  Distrib 5.5.25a, for Linux (x86_64)
--
-- Host: localhost    Database: wms
-- ------------------------------------------------------
-- Server version	5.5.25a-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `firewall_services`
--

DROP TABLE IF EXISTS `firewall_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firewall_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` text,
  `chain` text,
  `comment` text,
  `disabled` enum('yes','no') DEFAULT NULL,
  `connmark` text,
  `newpakmark` text,
  `passthru` enum('yes','no') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firewall_services`
--

LOCK TABLES `firewall_services` WRITE;
/*!40000 ALTER TABLE `firewall_services` DISABLE KEYS */;
INSERT INTO `firewall_services` VALUES (1,'mark-packet','prerouting','bulk','yes','pre-bulk','BULK','no'),(2,'mark-packet','prerouting','service','yes','pre-service','SERVICE','no'),(3,'mark-packet','prerouting','game','yes','pre-game','GAME','no'),(4,'mark-packet','prerouting','real','yes','pre-real','REAL','no'),(5,'mark-connection','pre-bulk','bulk','yes','!pre-bulk','pre-bulk','yes'),(6,'mark-packet','pre-bulk','bulk','yes','pre-bulk','BULK','no'),(8,'mark-connection','pre-service','service','yes','!pre-service','pre-service','yes'),(9,'mark-packet','pre-service','service','yes','pre-service','SERVICE','no'),(10,'mark-connection','pre-game','game','yes','!pre-game','pre-game','yes'),(11,'mark-packet','pre-game','game','yes','pre-game','GAME','no'),(12,'mark-connection','pre-real','real','yes','!pre-real','pre-real','yes'),(13,'mark-packet','pre-real','real','yes','pre-real','REAL','no');
/*!40000 ALTER TABLE `firewall_services` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-11-30 10:51:14
