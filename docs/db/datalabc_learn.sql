-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 08-03-2020 a las 10:04:46
-- Versión del servidor: 10.3.22-MariaDB
-- Versión de PHP: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `datalabc_learn`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permalink` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'highlights of lesson',
  `subtitle` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tag line, Highlights, etc ',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'who created it and owns it',
  `type_flag` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=not set, 1=english, 2=spanish, 3=french, 4=tech, 99=other',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `main_photo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_flag` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=not set, 1=draft, 2=approved, 10=published, 99=other ',
  `wip_flag` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=not set, 1=not finished, 9=backburner, 10=finished, 99=other ',
  `deleted_flag` tinyint(4) NOT NULL DEFAULT 0,
  `display_date` date DEFAULT NULL,
  `display_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `deleted_flag` tinyint(4) NOT NULL DEFAULT 0,
  `site_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_flag` tinyint(4) NOT NULL COMMENT '1=Info, 2=Warning, 3=Error, 4=Exception, 99=other',
  `model_flag` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_flag` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `updates` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extraInfo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lessons`
--

CREATE TABLE `lessons` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(11) NOT NULL COMMENT 'Parent is Course: Subject / Course / Lesson / Section',
  `lesson_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `section_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_chapter` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permalink` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'highlights of lesson',
  `text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'the lesson',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'who created it and owns it',
  `type_flag` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=not set, 1=text, 2=vocab list, 3=fib, 4=multiple choice, 99=other',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `main_photo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reps` smallint(6) DEFAULT NULL,
  `seconds` smallint(6) DEFAULT NULL,
  `break_seconds` smallint(6) DEFAULT NULL,
  `published_flag` tinyint(4) NOT NULL DEFAULT 0,
  `approved_flag` tinyint(4) NOT NULL DEFAULT 0,
  `finished_flag` tinyint(4) DEFAULT 0,
  `deleted_flag` tinyint(4) NOT NULL DEFAULT 0,
  `format_flag` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=not set/default, 1=auto-format, 99=other',
  `options` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'lesson completed by student at',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `site_id` smallint(6) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_type` smallint(6) NOT NULL DEFAULT 0,
  `view_id` tinyint(4) NOT NULL DEFAULT -1,
  `blocked_flag` tinyint(4) DEFAULT 1,
  `ip_register` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitors`
--

CREATE TABLE `visitors` (
  `id` int(10) UNSIGNED NOT NULL,
  `site_id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `host_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referrer` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `continent` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_region` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visit_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `domain_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `deleted_flag` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vocab_lists`
--

CREATE TABLE `vocab_lists` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `permalink` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'who created it and owns it',
  `type_flag` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'not used yet',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `main_photo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_flag` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'see RELEASE_* values',
  `wip_flag` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'see WIP_* values',
  `display_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indices de la tabla `vocab_lists`
--
ALTER TABLE `vocab_lists`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `words`
--

CREATE TABLE `words` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(11) DEFAULT NULL COMMENT 'parent lesson',
  `vocab_list_id` int(11) DEFAULT NULL COMMENT 'parent vocab list',
  `vocab_id` int(11) DEFAULT NULL COMMENT 'vocab word that this word refers to',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `examples` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permalink` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_flag` tinyint(4) DEFAULT 0 COMMENT '0=not set, 1=lesson list - no definition, 2=lesson list - users copy with definition, 3=no lesson parent - users private list, 99=other',
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_viewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `words`
--
ALTER TABLE `words`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vocab_lists`
--
ALTER TABLE `vocab_lists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `words`
--
ALTER TABLE `words`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
