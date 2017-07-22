-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.5-10.1.21-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             8.0.0.4482
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table db_aar.pages
CREATE TABLE IF NOT EXISTS `pages` (
  `id_page` varchar(50) NOT NULL,
  `content` longblob NOT NULL,
  PRIMARY KEY (`id_page`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Dumping data for table db_aar.pages: 0 rows
DELETE FROM `pages`;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;


-- Dumping structure for table db_aar.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id_role` int(10) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `permission` text,
  PRIMARY KEY (`id_role`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=latin1;

-- Dumping data for table db_aar.roles: ~2 rows (approximately)
DELETE FROM `roles`;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id_role`, `role_name`, `description`, `permission`) VALUES
	(1, 'Administrator', 'Memiliki Hak Akses Tertinggi Dalam Aplikasi', ''),
	(2, 'Guest', 'Pengunjung Website', ''),
	(73, 'Pengguna 2', 'Pengguna 2', NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;


-- Dumping structure for table db_aar.short_link
CREATE TABLE IF NOT EXISTS `short_link` (
  `id_link` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(100) NOT NULL,
  `short_link` varchar(50) NOT NULL,
  PRIMARY KEY (`id_link`),
  UNIQUE KEY `short_link` (`short_link`),
  UNIQUE KEY `link` (`link`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table db_aar.short_link: ~0 rows (approximately)
DELETE FROM `short_link`;
/*!40000 ALTER TABLE `short_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `short_link` ENABLE KEYS */;


-- Dumping structure for table db_aar.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `id_role` int(10) NOT NULL,
  `id_type` tinyint(1) DEFAULT NULL,
  `id_external` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- Dumping data for table db_aar.users: ~4 rows (approximately)
DELETE FROM `users`;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id_user`, `username`, `password`, `id_role`, `id_type`, `id_external`) VALUES
	(1, 'Admin', '$2y$10$sFJusqGGoMRRSBjrN67Tpu0z1hULz8x7xxfrM8lAsZPowgsoi0GQi', 1, NULL, NULL),
	(2, 'Guest', '$2y$10$Bpp32AirNgts0k17hLQTD.ausYcpjo3f5xNtLkCy6KNxlMHvoZt3K', 2, NULL, NULL),
	(3, 'Ady Suprianto', '', 1, NULL, NULL),
	(4, 'Elya Antariksana Bachmida', '', 1, NULL, NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
