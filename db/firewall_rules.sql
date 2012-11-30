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
-- Table structure for table `firewall_rules`
--

DROP TABLE IF EXISTS `firewall_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firewall_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` text,
  `chain` text,
  `comment` text,
  `disabled` enum('yes','no') DEFAULT NULL,
  `protocol` text,
  `jumptarget` text,
  `passthru` enum('yes','no') DEFAULT NULL,
  `ports` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firewall_rules`
--

LOCK TABLES `firewall_rules` WRITE;
/*!40000 ALTER TABLE `firewall_rules` DISABLE KEYS */;
INSERT INTO `firewall_rules` VALUES (1,'jump','prerouting','TCP','yes','tcp','pre-tcp',NULL,NULL),(2,'jump','prerouting','UDP','yes','udp','pre-udp',NULL,NULL),(3,'jump','output','TCP','yes','tcp','pre-tcp',NULL,NULL),(4,'jump','input','TCP','yes','tcp','pre-tcp',NULL,NULL),(5,'jump','output','UDP','yes','udp','pre-udp',NULL,NULL),(6,'jump','input','UDP','yes','udp','pre-udp',NULL,NULL),(7,'jump','pre-tcp','Torrents TCP','yes','tcp','pre-bulk','no','7000-7200'),(8,'jump','pre-udp','Torrents UDP','yes','udp','pre-bulk','no','7000-7200'),(11,'jump','pre-tcp','DC - TX TCP','yes','tcp','pre-bulk','no','2222-2223'),(12,'jump','pre-tcp','HTTP','yes','tcp','pre-service','no','80'),(13,'jump','prerouting','PPTP','yes','gre','pre-game','no',NULL),(14,'jump','pre-udp','DC - HUB (udp)','yes','udp','pre-service','no','411'),(15,'jump','pre-tcp','DC - HUB','yes','tcp','pre-service','no','411'),(16,'jump','pre-tcp','IRC','yes','tcp','pre-service','no','6667'),(17,'jump','pre-tcp','Winbox','yes','tcp','pre-service','no','8291'),(18,'jump','pre-udp','syslog','yes','udp','pre-real','no','514'),(19,'jump','pre-tcp','TMF TCP','yes','tcp','pre-game','no','2350-2351'),(20,'jump','pre-udp','Hon2','yes','udp','pre-game','no','11443'),(21,'jump','pre-udp','TMF UDP','yes','udp','pre-game','no','2350-2351'),(22,'jump','pre-udp','COD','yes','udp','pre-game','no','28960'),(23,'jump','pre-tcp','WOW1','yes','tcp','pre-game','no','3724,8085'),(24,'jump','pre-tcp','WOW2','yes','tcp','pre-game','no','6882-6999'),(25,'jump','pre-udp','DayZ Udp','yes','udp','pre-game','no','2302'),(26,'jump','pre-tcp','ROSE online','yes','tcp','pre-game','no','29000,29100,29200'),(27,'jump','pre-tcp','SMTP','yes','tcp','pre-service','no','25'),(28,'jump','pre-tcp','puppet','yes','tcp','pre-real','no','8140'),(29,'jump','pre-tcp','ntop - http port','yes','tcp','pre-service','no','3000'),(30,'jump','pre-udp','netflows','yes','udp','pre-real','no','1234-1239'),(31,'jump','pre-udp','LanBridger','yes','udp','pre-game','no','40001'),(32,'jump','pre-tcp','Warcraft','yes','tcp','pre-game','no','6112'),(33,'jump','pre-tcp','HOHTCP','yes','tcp','pre-game','no','11031'),(34,'jump','pre-tcp','PPTP TCP','yes','tcp','pre-game','no','1723'),(35,'jump','pre-tcp','DNS - transfer','yes','tcp','pre-service','no','53'),(36,'jump','pre-udp','VOIP IAX','yes','udp','pre-real','no','4569'),(37,'jump','pre-udp','Radius','yes','udp','pre-service','no','1812-1814'),(38,'jump','pre-udp','SNMP','yes','udp','pre-service','no','161'),(39,'jump','pre-tcp','IRC SSL','yes','tcp','pre-service','no','6697'),(40,'jump','pre-tcp','IRC SSL2','yes','tcp','pre-service','no','6668'),(41,'jump','pre-tcp','SSH','yes','tcp','pre-service','no','22-23'),(42,'jump','pre-udp','NTP','yes','udp','pre-service','no','123'),(43,'jump','pre-udp','DC - TX UDP','yes','udp','pre-bulk','no','2222-2223'),(44,'jump','prerouting','OSPF','yes','ospf','pre-service','no',NULL),(45,'jump','pre-udp','DNS','yes','udp','pre-service','no','53'),(46,'jump','pre-tcp','Webmin','yes','tcp','pre-service','no','10000-10001,11112,8082'),(47,'jump','pre-tcp','HTTPS','yes','tcp','pre-service','no','443'),(48,'jump','pre-tcp','Jabber','yes','tcp','pre-real','no','5222-5223'),(49,'jump','pre-tcp','MTik BTest','yes','tcp','pre-bulk','no','2000'),(50,'jump','pre-tcp','Windows Share','yes','tcp','pre-bulk','no','445'),(51,'jump','pre-udp','HON','yes','udp','pre-game','no','11235-11335'),(52,'jump','prerouting','ICMP','yes','icmp','pre-service','no',NULL),(53,'jump','output','ICMP','yes','icmp','pre-service','no',NULL),(54,'jump','pre-tcp','VNC RDP','yes','tcp','pre-service','no','3389,3390,5900'),(55,'jump','pre-tcp','FTP','yes','tcp','pre-bulk','no','20-21');
/*!40000 ALTER TABLE `firewall_rules` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-11-30 10:50:39
