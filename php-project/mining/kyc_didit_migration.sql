-- Migration: Add Didit ID Verification columns to kyc_submissions table
-- Run this SQL to add columns for storing Didit verification results

ALTER TABLE `kyc_submissions` 
ADD COLUMN IF NOT EXISTS `didit_request_id` VARCHAR(255) NULL AFTER `back_image`,
ADD COLUMN IF NOT EXISTS `didit_status` VARCHAR(50) NULL AFTER `didit_request_id`,
ADD COLUMN IF NOT EXISTS `didit_verification_data` TEXT NULL AFTER `didit_status`,
ADD COLUMN IF NOT EXISTS `didit_verified_at` DATETIME NULL AFTER `didit_verification_data`;

-- Add index for faster lookups
ALTER TABLE `kyc_submissions` 
ADD INDEX IF NOT EXISTS `idx_didit_request_id` (`didit_request_id`);

