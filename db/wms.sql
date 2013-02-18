-- MySQL dump 10.13  Distrib 5.5.28, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: wms
-- ------------------------------------------------------
-- Server version	5.5.28-1

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
-- Table structure for table `address`
--

DROP TABLE IF EXISTS `address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `interface_id` int(10) unsigned NOT NULL,
  `address` int(10) unsigned NOT NULL,
  `netmask` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `interface_id` (`interface_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=857 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `device`
--

DROP TABLE IF EXISTS `device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `serial` varchar(64) DEFAULT NULL,
  `softid` char(9) DEFAULT NULL,
  `platform` varchar(16) DEFAULT NULL,
  `model` varchar(32) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `routerid` char(15) DEFAULT NULL,
  `osver` varchar(32) DEFAULT NULL,
  `bootver` varchar(32) DEFAULT NULL,
  `cpu` varchar(32) DEFAULT NULL,
  `cpufreq` smallint(5) unsigned DEFAULT NULL,
  `arch` varchar(16) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=463 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interface`
--

DROP TABLE IF EXISTS `interface`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interface` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(10) unsigned NOT NULL,
  `type` char(1) NOT NULL,
  `name` varchar(64) NOT NULL,
  `wi_mode` varchar(16) NOT NULL,
  `wi_ssid` varchar(32) NOT NULL,
  `wi_radioname` varchar(32) NOT NULL,
  `wi_frequency` mediumint(8) unsigned NOT NULL,
  `wi_protocol` char(8) NOT NULL,
  `wi_macprotocol` varchar(16) NOT NULL,
  `wi_distance` tinyint(3) unsigned DEFAULT NULL,
  `wi_retries` tinyint(3) unsigned DEFAULT NULL,
  `wi_rateselect` varchar(16) DEFAULT NULL,
  `wi_ampdupriorities` varchar(16) DEFAULT NULL,
  `wi_nv2qnum` tinyint(3) unsigned DEFAULT NULL,
  `wi_nv2qselector` varchar(16) DEFAULT NULL,
  `wi_tdmaperiod` tinyint(3) unsigned DEFAULT NULL,
  `queue` varchar(16) DEFAULT NULL,
  `ospftype` char(1) DEFAULT NULL,
  `ospfcost` mediumint(8) unsigned DEFAULT NULL,
  `bridgename` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=598 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qos_classify`
--

DROP TABLE IF EXISTS `qos_classify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qos_classify` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sort` smallint(5) unsigned NOT NULL DEFAULT '4',
  `protocol` smallint(5) unsigned NOT NULL DEFAULT '0',
  `port_min` smallint(5) unsigned NOT NULL DEFAULT '0',
  `port_max` smallint(5) unsigned NOT NULL DEFAULT '0',
  `class` varchar(8) DEFAULT NULL,
  `comment` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-02-19  0:04:24
