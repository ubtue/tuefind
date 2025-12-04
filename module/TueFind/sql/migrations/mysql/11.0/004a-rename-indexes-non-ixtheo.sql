-- user table
ALTER TABLE `user`
DROP INDEX `username`,
DROP INDEX `cat_id`,
DROP INDEX `email`,
DROP INDEX `verify_hash`,
ADD UNIQUE INDEX `user_username_idx` (`username`(190)),
ADD UNIQUE INDEX `user_cat_id_idx` (`cat_id`(190)),
ADD INDEX `user_email_idx` (`email`(190)),
ADD INDEX `user_verify_hash_idx` (`verify_hash`);
