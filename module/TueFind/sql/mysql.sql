CREATE TABLE tuefind_publications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    control_number VARCHAR(255) NOT NULL,
    external_document_id VARCHAR(255) NOT NULL,
    external_document_guid VARCHAR(255) DEFAULT NULL,
    doi VARCHAR(255) DEFAULT NULL,
    doi_notification_datetime DATETIME DEFAULT NULL,
    terms_date DATE NOT NULL,
    publication_datetime TIMESTAMP DEFAULT NOW() NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY publication_control_number (control_number),
    UNIQUE KEY publication_external_document_id (external_document_id),
    UNIQUE KEY publication_external_document_guid (external_document_guid),
    UNIQUE KEY publication_doi (doi),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin;


CREATE TABLE tuefind_redirect (
    url VARCHAR(1000) NOT NULL,
    group_name VARCHAR(1000) DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT NOW() NOT NULL
) DEFAULT CHARSET=utf8;


CREATE TABLE tuefind_rss_feeds (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    feed_name VARCHAR(200) NOT NULL,
    subsystem_types SET('krimdok', 'ixtheo', 'relbib') NOT NULL,
    type enum('news','calendar') NOT NULL DEFAULT 'news',
    feed_url VARCHAR(1000) NOT NULL,
    website_url VARCHAR(1000) NOT NULL,
    title_suppression_regex VARCHAR(200) DEFAULT NULL,
    descriptions_and_substitutions VARCHAR(1000) DEFAULT NULL,
    strptime_format VARCHAR(50) DEFAULT NULL,
    downloader_time_limit INT NOT NULL DEFAULT 30,
    active TINYINT(1) NOT NULL DEFAULT '1',
    CONSTRAINT id_constraint UNIQUE (id),
    CONSTRAINT feed_name_constraint UNIQUE (feed_name),
    CONSTRAINT feed_url_constraint UNIQUE (feed_url(768)),
    CONSTRAINT website_url_constraint UNIQUE (website_url(768)),
    KEY `type_index` (`type`)
) DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin;


CREATE TABLE tuefind_rss_items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    rss_feeds_id INT UNSIGNED NOT NULL,
    item_id VARCHAR(768) NOT NULL,
    item_url VARCHAR(1000) NOT NULL,
    item_title VARCHAR(1000) NOT NULL,
    item_description MEDIUMTEXT NOT NULL,
    pub_date DATETIME NOT NULL,
    insertion_time TIMESTAMP DEFAULT NOW() NOT NULL,
    FOREIGN KEY (rss_feeds_id) REFERENCES tuefind_rss_feeds(id) ON DELETE CASCADE,
    CONSTRAINT id_constraint UNIQUE (id),
    CONSTRAINT tuefind_rss_items_item_id UNIQUE (item_id),
    INDEX tuefind_rss_items_item_id_index(item_id(768)),
    INDEX tuefind_rss_items_item_url_index(item_url(768)),
    INDEX tuefind_rss_items_pub_date_index(pub_date),
    INDEX tuefind_rss_items_insertion_time_index(insertion_time)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;


CREATE TABLE tuefind_rss_subscriptions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    rss_feeds_id INT UNSIGNED NOT NULL,
    user_id INT NOT NULL,
    CONSTRAINT id_constraint UNIQUE (id),
    FOREIGN KEY (rss_feeds_id) REFERENCES tuefind_rss_feeds(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;


CREATE TABLE tuefind_user_authorities (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    authority_id VARCHAR(255) NOT NULL,
    access_state ENUM('requested', 'granted'),
    requested_datetime TIMESTAMP DEFAULT NOW() NOT NULL,
    granted_datetime TIMESTAMP DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY user_authority (authority_id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;

CREATE TABLE tuefind_user_authorities_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    authority_id VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    admin_id INT NULL,
    access_state ENUM('requested', 'granted', 'declined') NOT NULL,
    request_user_date TIMESTAMP DEFAULT NOW() NOT NULL,
    process_admin_date TIMESTAMP DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (admin_id) REFERENCES user(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;

ALTER TABLE vufind.user ADD COLUMN tuefind_country VARCHAR(255) DEFAULT NULL;
ALTER TABLE vufind.user ADD tuefind_institution VARCHAR(255) DEFAULT NULL;

ALTER TABLE user ADD tuefind_uuid CHAR(36) NOT NULL;
ALTER TABLE user ADD CONSTRAINT tuefind_user_uuid UNIQUE (tuefind_uuid);
CREATE TRIGGER before_user_insert BEFORE INSERT ON user FOR EACH ROW SET NEW.tuefind_uuid = UUID();

ALTER TABLE user ADD tuefind_license_access_locked BOOLEAN DEFAULT FALSE AFTER tuefind_uuid;

ALTER TABLE user ADD tuefind_rss_feed_send_emails BOOLEAN NOT NULL DEFAULT FALSE;
CREATE INDEX tuefind_rss_feed_send_emails_index ON user (tuefind_rss_feed_send_emails);
ALTER TABLE user ADD tuefind_rss_feed_last_notification TIMESTAMP DEFAULT NOW();

ALTER TABLE user ADD tuefind_rights SET('admin', 'user_authorities') DEFAULT NULL;
