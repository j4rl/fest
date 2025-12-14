-- Adds max_guests column to parties for per-submission attendee limits.
ALTER TABLE `parties` ADD COLUMN `max_guests` INT NOT NULL DEFAULT 1 AFTER `share_code`;
