CREATE TABLE `cms_pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subsystem` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `page_system_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `change_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cms_pages_translation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cms_pages_id` int DEFAULT NULL,
  `language` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`language`),
  KEY `custom_link_custom_link_id_idx` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cms_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `cms_id` int DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `cms_pages_id_` (`cms_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `cms_history`
  ADD CONSTRAINT `cms_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cms_history_ibfk_2` FOREIGN KEY (`cms_id`) REFERENCES `cms_pages` (`id`) ON DELETE SET NULL;