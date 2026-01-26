-- Adds a flag to enable/disable new submissions.
ALTER TABLE `parties` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `apply_deadline`;
