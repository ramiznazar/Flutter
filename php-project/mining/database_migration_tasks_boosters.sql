-- Database Migration for Task System, Boosters, and Mystery Box Cooldowns
-- Run this script to add required tables and columns

-- Task Completion Tracking Table
-- Tracks when users start/complete tasks and their timer status
CREATE TABLE IF NOT EXISTS `task_completions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `task_id` INT NOT NULL,
    `task_type` ENUM('daily', 'onetime') NOT NULL,
    `started_at` DATETIME NOT NULL,
    `reward_available_at` DATETIME NOT NULL,
    `reward_claimed` TINYINT(1) DEFAULT 0,
    `reward_claimed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `task_id` (`task_id`),
    KEY `task_type` (`task_type`),
    KEY `reward_available_at` (`reward_available_at`),
    CONSTRAINT `task_completions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `task_completions_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `social_media_setting` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Booster Tracking Table
-- Tracks active boosters for users (2x multiplier, 1 hour duration)
CREATE TABLE IF NOT EXISTS `user_boosters` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `booster_type` VARCHAR(50) DEFAULT '2x',
    `started_at` DATETIME NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `expires_at` (`expires_at`),
    KEY `is_active` (`is_active`),
    CONSTRAINT `user_boosters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Mystery Box Claims Tracking Table
-- Tracks when users claim mystery boxes and enforces cooldowns
CREATE TABLE IF NOT EXISTS `mystery_box_claims` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `box_type` ENUM('common', 'rare', 'epic', 'legendary') NOT NULL,
    `ads_watched` INT DEFAULT 0,
    `ads_required` INT NOT NULL,
    `last_ad_watched_at` DATETIME NULL,
    `cooldown_until` DATETIME NULL,
    `box_opened` TINYINT(1) DEFAULT 0,
    `reward_coins` DECIMAL(10,2) NULL,
    `opened_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `box_type` (`box_type`),
    KEY `cooldown_until` (`cooldown_until`),
    CONSTRAINT `mystery_box_claims_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add ad waterfall configuration columns to settings table
ALTER TABLE `settings` 
ADD COLUMN IF NOT EXISTS `ad_waterfall_order` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'JSON array: ["admob", "meta", "unity", "applovin"]',
ADD COLUMN IF NOT EXISTS `ad_waterfall_enabled` TINYINT(1) DEFAULT 1;

-- Note: MySQL doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
-- If you get errors about duplicate columns, those columns already exist and can be ignored


