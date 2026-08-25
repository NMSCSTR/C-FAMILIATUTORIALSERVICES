ALTER TABLE `users`
    ADD COLUMN `birthday` date DEFAULT NULL AFTER `profile_pic`,
    ADD COLUMN `cellphone_no` varchar(30) DEFAULT NULL AFTER `birthday`,
    ADD COLUMN `address` text DEFAULT NULL AFTER `cellphone_no`,
    ADD COLUMN `parents_name_guardian` varchar(150) DEFAULT NULL AFTER `address`,
    ADD COLUMN `parents_phone_no` varchar(30) DEFAULT NULL AFTER `parents_name_guardian`,
    ADD COLUMN `fb_messenger_account` varchar(150) DEFAULT NULL AFTER `parents_phone_no`;
