DROP TABLE IF EXISTS `lessons`;
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL COMMENT 'Parent is Course: Subject / Course / Lesson / Section',
  `lesson_number` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `section_number` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `permalink` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'highlights of lesson',
  `text` text COLLATE utf8mb4_unicode_ci COMMENT 'the lesson',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'who created it and owns it',
  `type_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=not set, 1=text, 2=saving vocab, 3=fib, 4=multiple choice, 99=other',
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
