-- Adds a deadline for RSVP submissions.
ALTER TABLE `parties` ADD COLUMN `apply_deadline` DATE NULL AFTER `max_guests`;
