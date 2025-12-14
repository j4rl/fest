-- Adds customization fields for invitations.
ALTER TABLE `parties` ADD COLUMN `theme_accent` VARCHAR(20) NULL AFTER `share_code`;
ALTER TABLE `parties` ADD COLUMN `header_image` TEXT NULL AFTER `theme_accent`;
