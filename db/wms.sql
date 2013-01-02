-- MySQL dump 10.13  Distrib 5.5.28, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: wms
-- ------------------------------------------------------
-- Server version	5.5.28-1
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO,NO_TABLE_OPTIONS' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `device`
--

DROP TABLE IF EXISTS `device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial` varchar(64) DEFAULT NULL,
  `softid` char(9) DEFAULT NULL,
  `platform` varchar(16) DEFAULT NULL,
  `model` varchar(32) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `routerid` char(15) DEFAULT NULL,
  `os` varchar(64) DEFAULT NULL,
  `lastip` varchar(40) DEFAULT NULL,
  `updatever` int(10) unsigned DEFAULT NULL,
  `upgradever` int(10) unsigned DEFAULT NULL,
  `ct` tinyint(3) unsigned DEFAULT NULL,
  `contact` varchar(64) DEFAULT NULL,
  `firstseen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastseen` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_softid` (`softid`),
  KEY `idx_serial` (`serial`)
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qos_classify`
--

DROP TABLE IF EXISTS `qos_classify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qos_classify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort` smallint(5) unsigned NOT NULL DEFAULT '4',
  `protocol` smallint(5) unsigned NOT NULL DEFAULT '0',
  `port_min` smallint(5) unsigned NOT NULL DEFAULT '0',
  `port_max` smallint(5) unsigned NOT NULL DEFAULT '0',
  `class` varchar(8) DEFAULT NULL,
  `comment` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
);
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-01-02  3:12:20
