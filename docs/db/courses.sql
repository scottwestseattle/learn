SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `datalabc_learn`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
CREATE TABLE IF NOT EXISTS `courses` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `permalink` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'highlights of lesson',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'who created it and owns it',
  `type_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=not set, 1=english, 2=spanish, 3=french, 4=tech, 99=other',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `main_photo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `published_flag` tinyint(4) NOT NULL DEFAULT '0',
  `approved_flag` tinyint(4) NOT NULL DEFAULT '0',
  `finished_flag` tinyint(4) DEFAULT '0',
  `deleted_flag` tinyint(4) NOT NULL DEFAULT '0',
  `display_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;