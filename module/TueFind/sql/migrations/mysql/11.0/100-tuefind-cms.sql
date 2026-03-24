CREATE TABLE `tuefind_subsystems` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subsystem` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_subsystem` (`subsystem`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tuefind_cms_pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subsystem_id` int unsigned NOT NULL,
  `page_system_id` varchar(50) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_subsystem_idx` (`subsystem_id`),
  UNIQUE KEY `page_system_id` (`page_system_id`),
  KEY `created` (`created`),
  KEY `changed` (`changed`),
  CONSTRAINT `fk_subsystem`
    FOREIGN KEY (`subsystem_id`)
    REFERENCES `tuefind_subsystems` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tuefind_cms_pages_translation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `cms_pages_id` int unsigned NOT NULL,
  `language` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cms_pages_language` (`cms_pages_id`, `language`),
  KEY `cms_pages_id` (`cms_pages_id`),
  KEY `language` (`language`),
  CONSTRAINT `fk_translation_page`
    FOREIGN KEY (`cms_pages_id`)
    REFERENCES `tuefind_cms_pages` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tuefind_cms_pages_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `cms_id` int unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `cms_id` (`cms_id`),
  KEY `created` (`created`),
  CONSTRAINT `fk_history_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_history_cms`
    FOREIGN KEY (`cms_id`)
    REFERENCES `tuefind_cms_pages` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `user`
  MODIFY `tuefind_rights`
  set('admin','user_authorities','cms')
  DEFAULT NULL;
