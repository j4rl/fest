-- Adds admin/approval flags to users.
ALTER TABLE `users` ADD COLUMN `is_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password_hash`;
ALTER TABLE `users` ADD COLUMN `is_approved` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_admin`;
UPDATE `users` SET `is_admin` = 1, `is_approved` = 1 WHERE `username` = 'admin';
