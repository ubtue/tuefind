CREATE TABLE ixtheo_id_result_sets (
    id BIGINT UNSIGNED NOT NULL,
    ids VARCHAR(128) NOT NULL,
    CONSTRAINT `ixtheo_id_result_sets_ibfk_1` FOREIGN KEY (id) REFERENCES search(id) ON DELETE CASCADE
);

CREATE TABLE ixtheo_journal_subscriptions (
    user_id INT(11) NOT NULL,
    journal_control_number_or_bundle_name VARCHAR(255) NOT NULL,
    max_last_modification_time DATETIME NOT NULL,
    CONSTRAINT `ixtheo_journal_subscriptions_ibfk_1` FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id,journal_control_number_or_bundle_name)
) DEFAULT CHARSET=utf8;

CREATE TABLE ixtheo_journal_bundles (
    bundle_name VARCHAR(255) NOT NULL,
    journal_control_number VARCHAR(255) NOT NULL,
    max_last_modification_time DATETIME NOT NULL,
    PRIMARY KEY (bundle_name, journal_control_number)
) DEFAULT CHARSET=utf8;

CREATE TABLE ixtheo_pda_subscriptions (
    id INT(11) NOT NULL,
    book_title VARCHAR(255) NOT NULL,
    book_author VARCHAR(255) NOT NULL,
    book_year VARCHAR(32) NOT NULL,
    book_ppn VARCHAR(10) NOT NULL,
    book_isbn VARCHAR(13) NOT NULL,
    CONSTRAINT `ixtheo_pda_subscriptions_ibfk_1` FOREIGN KEY (id) REFERENCES user(id) ON DELETE CASCADE,
    PRIMARY KEY (id, book_ppn)
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
