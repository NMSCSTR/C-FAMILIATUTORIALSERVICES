ALTER TABLE `announcements`
    ADD COLUMN `audience` enum('General','Students') NOT NULL DEFAULT 'General' AFTER `category`;
