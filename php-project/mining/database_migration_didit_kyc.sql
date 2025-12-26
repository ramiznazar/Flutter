-- Database Migration: Add Didit Verification Fields to KYC Submissions
-- Run this script to add Didit API integration fields to kyc_submissions table

-- Add Didit-related columns to kyc_submissions table
ALTER TABLE `kyc_submissions` 
ADD COLUMN IF NOT EXISTS `didit_request_id` VARCHAR(255) NULL AFTER `admin_notes`,
ADD COLUMN IF NOT EXISTS `didit_status` VARCHAR(50) NULL AFTER `didit_request_id`,
ADD COLUMN IF NOT EXISTS `didit_verification_data` TEXT NULL AFTER `didit_status`,
ADD COLUMN IF NOT EXISTS `didit_verified_at` DATETIME NULL AFTER `didit_verification_data`;

-- Add index for faster lookups by Didit request ID
CREATE INDEX IF NOT EXISTS `idx_didit_request_id` ON `kyc_submissions` (`didit_request_id`);

-- Add index for Didit status filtering
CREATE INDEX IF NOT EXISTS `idx_didit_status` ON `kyc_submissions` (`didit_status`);

