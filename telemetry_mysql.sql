SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET global time_zone = "+08:00";

DROP DATABASE  IF EXISTS `speedtest`;
CREATE DATABASE speedtest;
USE `speedtest`;


--
-- Table structure for table `speedtest_cidrinfo`
--

DROP TABLE IF EXISTS `speedtest_cidrinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `speedtest_cidrinfo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cidr` varchar(140) NOT NULL,
  `position` varchar(128) DEFAULT NULL,
  `accessmethod` varchar(45) DEFAULT NULL,
  `isp` varchar(128) DEFAULT NULL,
  `ispinfo` text,
  `index` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `speedtest_cidrinfo`
--

LOCK TABLES `speedtest_cidrinfo` WRITE;
/*!40000 ALTER TABLE `speedtest_cidrinfo` DISABLE KEYS */;
INSERT INTO `speedtest_cidrinfo` VALUES (1,'::1/128',NULL,NULL,'localhost IPv6 access',NULL,0),(2,'fe80::/10',NULL,NULL,'link-local IPv6 access',NULL,0),(3,'127.0.0.0/8',NULL,NULL,'localhost IPv4 access',NULL,0),(4,'10.0.0.0/8',NULL,NULL,'private IPv4 access',NULL,0),(5,'172.16.0.0/12',NULL,NULL,'private IPv4 access',NULL,0),(6,'192.168.0.0/16',NULL,NULL,'private IPv4 access',NULL,0),(7,'169.254.0.0/16',NULL,NULL,'link-local IPv4 access',NULL,0);
/*!40000 ALTER TABLE `speedtest_cidrinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `speedtest_users`
--

DROP TABLE IF EXISTS `speedtest_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `speedtest_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `number` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
LOCK TABLES `speedtest_users` WRITE;
INSERT INTO `speedtest_users` (`name`, `number`)VALUES ('游客', '1234567890');
UNLOCK TABLES;
--
-- Table structure for table `speedtest_infos`
--

DROP TABLE IF EXISTS `speedtest_infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `speedtest_infos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` text NOT NULL,
  `ispinfo` text,
  `extra` text,
  `ua` text NOT NULL,
  `lang` text NOT NULL,
  `dl` text,
  `ul` text,
  `ping` text,
  `jitter` text,
  `log` longtext,
  `userid` int DEFAULT NULL,
  `testpointid` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `speedtest_testpoints`
--

DROP TABLE IF EXISTS `speedtest_testpoints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `speedtest_testpoints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `server` varchar(128) DEFAULT NULL,
  `port` int DEFAULT NULL,
  `dlURL` varchar(128) DEFAULT NULL,
  `ulURL` varchar(128) DEFAULT NULL,
  `pingURL` varchar(128) DEFAULT NULL,
  `getIpURL` varchar(128) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
