DROP TABLE IF EXISTS `words`;
CREATE TABLE IF NOT EXISTS `words` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'optional: lesson is parent',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permalink` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_flag` tinyint(4) DEFAULT '0' COMMENT 'not used',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `deleted_flag` tinyint(4) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
