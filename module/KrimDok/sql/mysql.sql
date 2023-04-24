CREATE TABLE full_text_cache (
    id VARCHAR(20) NOT NULL,
    expiration DATETIME,
    full_text MEDIUMBLOB,
    PRIMARY KEY (id),
    INDEX expiration (expiration)
) CHARACTER SET utf8mb4;

CREATE TABLE full_text_cache_urls (
    id VARCHAR(20) NOT NULL,
    url VARCHAR(255) CHARACTER SET utf8 NOT NULL,
    domain VARCHAR(255) NOT NULL,
    error_message MEDIUMTEXT,
    PRIMARY KEY (id, url),
    CONSTRAINT id FOREIGN KEY (id) REFERENCES full_text_cache (id) ON DELETE CASCADE,
    INDEX domain (domain),
    INDEX error_message (error_message(100))
) CHARACTER SET utf8mb4;

ALTER TABLE user ADD krimdok_subscribed_to_newsletter BOOLEAN NOT NULL DEFAULT FALSE;
CREATE INDEX krimdok_subscribed_to_newsletter_index ON user (krimdok_subscribed_to_newsletter);
