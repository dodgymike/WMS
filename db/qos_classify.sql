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
-- Dumping data for table `qos_classify`
--

LOCK TABLES `qos_classify` WRITE;
/*!40000 ALTER TABLE `qos_classify` DISABLE KEYS */;
INSERT INTO `qos_classify` VALUES (1,0,6,7000,7200,'bulk','Torrents'),(2,0,17,7000,7200,'bulk','Torrents'),(3,0,6,2222,2223,'bulk','DC file transfers'),(4,6,6,2000,0,'bulk','MikroTik BTest'),(5,1,6,80,0,'service','HTTP'),(6,6,47,0,0,'service','PPTP'),(7,6,6,1723,0,'service','PPTP Control'),(8,4,17,9987,0,'real','TeamSpeak'),(9,5,6,411,0,'service','DC++ Hub'),(11,6,6,1511,0,'service','ADC Hub'),(12,6,6,6667,0,'service','IRC'),(13,6,6,6697,0,'service','IRC SSL'),(14,5,6,8291,0,'service','Winbox'),(15,6,17,514,0,'real','Syslog'),(16,3,6,2350,2351,'game','Track Mania'),(17,3,17,2350,2351,'game','Track Mania'),(19,3,17,28960,0,'game','COD'),(20,3,6,3724,0,'game','WOW1'),(21,3,6,8085,0,'game','WOW1'),(22,3,6,6882,6999,'game','WOW2'),(23,3,17,2302,0,'game','DayZ'),(24,6,6,25,0,'service','SMTP'),(25,6,6,8140,0,'real','Puppet'),(26,6,6,3000,0,'service','NTop'),(27,6,17,1234,1239,'real','Netflow'),(28,6,17,40001,0,'game','LanBridger'),(29,3,6,6112,0,'game','Warcraft 3'),(30,7,6,11031,0,'game','HON TCP'),(31,3,17,11235,11335,'game','HON UDP'),(32,2,6,53,0,'service','DNS'),(33,6,17,53,0,'service','DNS'),(34,7,17,4569,0,'real','VoIP IAX'),(35,6,17,1812,1814,'service','Radius'),(36,2,17,161,0,'service','SNMP'),(37,6,6,22,23,'service','SSH/Telnet'),(38,6,17,123,0,'service','NTP'),(39,5,17,2222,0,'service','DC searches'),(41,6,6,443,0,'service','HTTPS'),(42,2,6,10000,10001,'service','Webmin'),(43,6,6,5222,5223,'service','XMPP'),(44,6,6,445,0,'bulk','Windows Share'),(45,2,1,0,0,'service','ICMP'),(46,7,58,0,0,'service','ICMPv6'),(47,6,6,3389,3390,'service','RDP'),(48,6,6,5900,5901,'service','VNC'),(49,6,6,110,0,'service','POP3'),(50,6,6,143,0,'service','IMAP');
/*!40000 ALTER TABLE `qos_classify` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-01-28 18:52:42
