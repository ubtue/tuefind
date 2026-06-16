-- user table
ALTER TABLE `user`
DROP INDEX `username`,
DROP INDEX `cat_id`,
ADD UNIQUE INDEX `user_username_idx` (`username`(190)),
ADD UNIQUE INDEX `user_cat_id_idx` (`cat_id`(190));
