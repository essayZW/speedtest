SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET global time_zone = "+08:00";

DROP DATABASE  IF EXISTS `speedtest`;
CREATE DATABASE speedtest;
USE `speedtest`;
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
CREATE TABLE `speedtest_users` (
    `number` varchar(15) NOT NULL,
    `name` varchar(50) NOT NULL,
    PRIMARY KEY(`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--
-- Database: `speedtest`
--

-- --------------------------------------------------------

--
-- Table structure for table `speedtest_users`
--

CREATE TABLE `speedtest_infos` (
  `id` int(11) NOT NULL,
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
  `unumber` varchar(20) NOT NULL,
  KEY `info_user` (`unumber`),
  CONSTRAINT `info_user` FOREIGN KEY (`unumber`) REFERENCES `speedtest_users` (`number`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `speedtest_users`
--
ALTER TABLE `speedtest_infos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `speedtest_users`
--
ALTER TABLE `speedtest_infos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
