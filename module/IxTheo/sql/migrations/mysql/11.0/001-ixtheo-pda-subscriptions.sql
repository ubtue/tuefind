ALTER TABLE ixtheo_pda_subscriptions DROP CONSTRAINT ixtheo_pda_subscriptions_ibfk_1;
ALTER TABLE ixtheo_pda_subscriptions ADD COLUMN user_id INT NOT NULL AFTER id;
ALTER TABLE ixtheo_pda_subscriptions ADD CONSTRAINT user_id_pda_subscription FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE;
UPDATE ixtheo_pda_subscriptions SET user_id=id;

ALTER TABLE ixtheo_pda_subscriptions DROP PRIMARY KEY;
ALTER TABLE ixtheo_pda_subscriptions DROP COLUMN id;
ALTER TABLE ixtheo_pda_subscriptions ADD COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE ixtheo_pda_subscriptions ADD CONSTRAINT user_pda_subscription UNIQUE (user_id, book_ppn);
