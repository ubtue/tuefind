ALTER TABLE `tuefind_cms_pages`
  ADD COLUMN `custom_js` TEXT DEFAULT NULL AFTER `page_system_id`,
  ADD COLUMN `custom_css` TEXT DEFAULT NULL AFTER `custom_js`;