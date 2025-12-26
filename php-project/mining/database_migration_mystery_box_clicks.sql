-- Database Migration for Mystery Box Click Tracking
-- Run this script to add click tracking columns to mystery_box_claims table

-- Add clicks and last_clicked_at columns to mystery_box_claims table
ALTER TABLE `mystery_box_claims` 
ADD COLUMN IF NOT EXISTS `clicks` INT DEFAULT 0 AFTER `box_type`,
ADD COLUMN IF NOT EXISTS `last_clicked_at` DATETIME NULL AFTER `clicks`;

-- Add index for better query performance
ALTER TABLE `mystery_box_claims` 
ADD INDEX IF NOT EXISTS `idx_clicks` (`clicks`),
ADD INDEX IF NOT EXISTS `idx_last_clicked_at` (`last_clicked_at`);

-- Note: MySQL doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
-- If you get errors about duplicate columns, those columns already exist and can be ignored

