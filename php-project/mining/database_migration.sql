-- Database Migration Script for Crutox Admin Panel
-- Run this script to add missing columns required by the admin panel

-- Add Link column to news table (for redirect links)
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `Link` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER `Description`;

-- Add columns to settings table for mining, referral, user count, and mystery box settings
ALTER TABLE `settings` 
ADD COLUMN IF NOT EXISTS `mining_speed` DECIMAL(10,2) DEFAULT 10.00 AFTER `about_us_link`,
ADD COLUMN IF NOT EXISTS `base_mining_rate` DECIMAL(10,2) DEFAULT 5.00 AFTER `mining_speed`,
ADD COLUMN IF NOT EXISTS `max_mining_speed` DECIMAL(10,2) DEFAULT 50.00 AFTER `base_mining_rate`,
ADD COLUMN IF NOT EXISTS `referrer_reward` INT DEFAULT 50 AFTER `max_mining_speed`,
ADD COLUMN IF NOT EXISTS `referee_reward` INT DEFAULT 25 AFTER `referrer_reward`,
ADD COLUMN IF NOT EXISTS `max_referrals` INT DEFAULT 100 AFTER `referee_reward`,
ADD COLUMN IF NOT EXISTS `bonus_reward` INT DEFAULT 500 AFTER `max_referrals`,
ADD COLUMN IF NOT EXISTS `current_users` INT DEFAULT 99000 AFTER `bonus_reward`,
ADD COLUMN IF NOT EXISTS `goal_users` INT DEFAULT 1000000 AFTER `current_users`,
ADD COLUMN IF NOT EXISTS `daily_tasks_reset_time` DATETIME NULL AFTER `goal_users`,
ADD COLUMN IF NOT EXISTS `common_box_cooldown` INT DEFAULT 5 AFTER `daily_tasks_reset_time`,
ADD COLUMN IF NOT EXISTS `common_box_ads` INT DEFAULT 1 AFTER `common_box_cooldown`,
ADD COLUMN IF NOT EXISTS `common_box_min_coins` DECIMAL(10,2) DEFAULT 1.00 AFTER `common_box_ads`,
ADD COLUMN IF NOT EXISTS `common_box_max_coins` DECIMAL(10,2) DEFAULT 5.00 AFTER `common_box_min_coins`,
ADD COLUMN IF NOT EXISTS `rare_box_cooldown` INT DEFAULT 5 AFTER `common_box_max_coins`,
ADD COLUMN IF NOT EXISTS `rare_box_ads` INT DEFAULT 3 AFTER `rare_box_cooldown`,
ADD COLUMN IF NOT EXISTS `rare_box_min_coins` DECIMAL(10,2) DEFAULT 5.00 AFTER `rare_box_ads`,
ADD COLUMN IF NOT EXISTS `rare_box_max_coins` DECIMAL(10,2) DEFAULT 15.00 AFTER `rare_box_min_coins`,
ADD COLUMN IF NOT EXISTS `epic_box_cooldown` INT DEFAULT 10 AFTER `rare_box_max_coins`,
ADD COLUMN IF NOT EXISTS `epic_box_ads` INT DEFAULT 6 AFTER `epic_box_cooldown`,
ADD COLUMN IF NOT EXISTS `epic_box_min_coins` DECIMAL(10,2) DEFAULT 15.00 AFTER `epic_box_ads`,
ADD COLUMN IF NOT EXISTS `epic_box_max_coins` DECIMAL(10,2) DEFAULT 50.00 AFTER `epic_box_min_coins`,
ADD COLUMN IF NOT EXISTS `legendary_box_cooldown` INT DEFAULT 30 AFTER `epic_box_max_coins`,
ADD COLUMN IF NOT EXISTS `legendary_box_ads` INT DEFAULT 10 AFTER `legendary_box_cooldown`,
ADD COLUMN IF NOT EXISTS `legendary_box_min_coins` DECIMAL(10,2) DEFAULT 50.00 AFTER `legendary_box_ads`,
ADD COLUMN IF NOT EXISTS `legendary_box_max_coins` DECIMAL(10,2) DEFAULT 200.00 AFTER `legendary_box_min_coins`;

-- Add KYC settings columns to settings table
ALTER TABLE `settings` 
ADD COLUMN IF NOT EXISTS `kyc_mining_sessions` INT DEFAULT 14 AFTER `legendary_box_max_coins`,
ADD COLUMN IF NOT EXISTS `kyc_referrals_required` INT DEFAULT 10 AFTER `kyc_mining_sessions`;

-- Create KYC submissions table
CREATE TABLE IF NOT EXISTS `kyc_submissions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `full_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `dob` DATE NOT NULL,
    `front_image` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `back_image` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `admin_notes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    CONSTRAINT `kyc_submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Note: MySQL doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
-- If you get errors about duplicate columns, those columns already exist and can be ignored




