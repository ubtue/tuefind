ALTER TABLE ixtheo_journal_subscriptions DROP CONSTRAINT ixtheo_journal_subscriptions_ibfk_1;
ALTER TABLE ixtheo_journal_subscriptions DROP PRIMARY KEY;
ALTER TABLE ixtheo_journal_subscriptions ADD COLUMN id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE ixtheo_journal_subscriptions ADD CONSTRAINT user_subscription UNIQUE (user_id, journal_control_number_or_bundle_name);
ALTER TABLE ixtheo_journal_subscriptions ADD CONSTRAINT user_id_subscription FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE;
