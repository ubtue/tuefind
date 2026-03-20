CREATE TABLE ixtheo_id_result_sets (
    id BIGINT UNSIGNED NOT NULL,
    ids VARCHAR(128) NOT NULL,
    CONSTRAINT `ixtheo_id_result_sets_ibfk_1` FOREIGN KEY (id) REFERENCES search(id) ON DELETE CASCADE
);

CREATE TABLE ixtheo_journal_subscriptions (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    journal_control_number_or_bundle_name VARCHAR(256) NOT NULL,
    max_last_modification_time DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY user_subscription (user_id,journal_control_number_or_bundle_name),
    CONSTRAINT user_id_subscription FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8;

CREATE TABLE ixtheo_journal_bundles (
    bundle_name VARCHAR(255) NOT NULL,
    journal_control_number VARCHAR(255) NOT NULL,
    max_last_modification_time DATETIME NOT NULL,
    PRIMARY KEY (bundle_name, journal_control_number)
) DEFAULT CHARSET=utf8;

CREATE TABLE ixtheo_pda_subscriptions (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_title VARCHAR(255) NOT NULL,
    book_author VARCHAR(255) NOT NULL,
    book_year VARCHAR(32) NOT NULL,
    book_ppn VARCHAR(10) NOT NULL,
    book_isbn VARCHAR(13) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY `user_pda_subscription` (`user_id`,`book_ppn`),
    CONSTRAINT `user_id_pda_subscription` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8;

ALTER TABLE user ADD COLUMN ixtheo_user_type ENUM('ixtheo', 'relbib', 'bibstudies', 'churchlaw') NOT NULL DEFAULT 'ixtheo';
ALTER TABLE user ADD COLUMN ixtheo_appellation VARCHAR(64) DEFAULT NULL;
ALTER TABLE user ADD COLUMN ixtheo_title VARCHAR(64) DEFAULT NULL;
ALTER TABLE user ADD COLUMN ixtheo_can_use_tad BOOLEAN DEFAULT FALSE;
ALTER TABLE user ADD COLUMN ixtheo_journal_subscription_format ENUM ('meistertask') DEFAULT NULL;

ALTER TABLE user DROP INDEX `user_username_idx`;
ALTER TABLE user DROP INDEX `user_email_idx`;
CREATE UNIQUE INDEX `subsystem_username` ON user (`ixtheo_user_type`, `username`);
CREATE UNIQUE INDEX `subsystem_email` ON user (`ixtheo_user_type`, `email`);

CREATE TABLE `tuefind_subsystems` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subsystem` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_subsystem` (`subsystem`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tuefind_cms_pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `subsystem_id` int unsigned NOT NULL,
  `page_system_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `language` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
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

INSERT INTO tuefind_subsystems (subsystem)
VALUES 
  ('ixtheo'),
  ('relbib'),
  ('bibstudies'),
  ('churchlaw');
