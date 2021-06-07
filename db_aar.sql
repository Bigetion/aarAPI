-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versi server:                 5.5.5-10.1.8-MariaDB - mariadb.org binary distribution
-- OS Server:                    Win32
-- HeidiSQL Versi:               8.0.0.4482
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table db_aar.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id_role` int(10) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `permission` text,
  PRIMARY KEY (`id_role`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- Dumping data for table db_aar.roles: ~3 rows (approximately)
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id_role`, `role_name`, `description`, `permission`) VALUES
	(1, 'Administrator', 'Memiliki Hak Akses Tertinggi Dalam Aplikasi', ''),
	(2, 'Guest', 'Pengunjung Website', 'page.home.index---base.scrap.index---base.scrap.getData---base.select.getSelectViewOptions---base.select.getData---base.service.getQueryServiceOptions---base.service.getData---base.service.executeMutation---base.sleek.getData---base.sleek.executeMutation---base.table.getTableViewOptions---base.table.getData---base.tree.getTreeViewOptions---base.tree.getData');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;


-- Dumping structure for table db_aar.short_link
DROP TABLE IF EXISTS `short_link`;
CREATE TABLE IF NOT EXISTS `short_link` (
  `id_link` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(100) NOT NULL,
  `short_link` varchar(50) NOT NULL,
  PRIMARY KEY (`id_link`),
  UNIQUE KEY `short_link` (`short_link`),
  UNIQUE KEY `link` (`link`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table db_aar.short_link: ~0 rows (approximately)
/*!40000 ALTER TABLE `short_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `short_link` ENABLE KEYS */;


-- Dumping structure for table db_aar.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `id_role` int(10) NOT NULL,
  `id_type` tinyint(1) DEFAULT NULL,
  `id_external` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- Dumping data for table db_aar.users: ~4 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id_user`, `username`, `password`, `id_role`, `id_type`, `id_external`) VALUES
	(1, 'Admin', 'Administrator', '$2y$10$iuhUFtYUoOpFG/Np5T.UieoLXK1mqfkryrL5qUyasWO0z183SASWO', 1, NULL, NULL),
	(2, 'Guest', '', '$2y$10$tl8qEPewxNHVsHWyE/Jz6OrdThHNhKcnYzcE0Q/T7htzxIf1iL85e', 2, NULL, NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
