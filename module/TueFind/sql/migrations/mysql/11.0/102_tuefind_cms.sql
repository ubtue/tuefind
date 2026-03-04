CREATE TABLE `cms_pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subsystem` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ixtheo',
  `page_system_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `create_date` datetime NOT NULL,
  `change_date` datetime NOT NULL,

  PRIMARY KEY (`id`),

  UNIQUE KEY `uniq_subsystem_page_system_id` (`subsystem`, `page_system_id`),

  KEY `idx_subsystem` (`subsystem`),
  KEY `idx_create_date` (`create_date`),
  KEY `idx_change_date` (`change_date`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cms_pages_translation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cms_pages_id` int unsigned NOT NULL,
  `language` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,

  PRIMARY KEY (`id`),

  UNIQUE KEY `uniq_page_language` (`cms_pages_id`, `language`),

  KEY `idx_cms_pages_id` (`cms_pages_id`),
  KEY `idx_language` (`language`),

  CONSTRAINT `fk_translation_page`
    FOREIGN KEY (`cms_pages_id`)
    REFERENCES `cms_pages` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cms_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `cms_id` int unsigned DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),

  KEY `idx_user_id` (`user_id`),
  KEY `idx_cms_id` (`cms_id`),
  KEY `idx_created` (`created`),

  CONSTRAINT `fk_history_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE SET NULL,

  CONSTRAINT `fk_history_cms`
    FOREIGN KEY (`cms_id`)
    REFERENCES `cms_pages` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `user`
  MODIFY `tuefind_rights`
  SET('admin','editor','cms')
  COLLATE utf8mb4_unicode_ci;
