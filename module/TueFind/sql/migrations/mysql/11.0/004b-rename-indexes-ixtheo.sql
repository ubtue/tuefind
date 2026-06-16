-- user table
ALTER TABLE `user`
DROP INDEX `cat_id`,
ADD UNIQUE INDEX `user_cat_id_idx` (`cat_id`(190));
