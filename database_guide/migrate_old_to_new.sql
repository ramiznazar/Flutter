-- ============================================================================
-- Migration Script: Old Database to New Database Structure
-- ============================================================================
-- This script migrates data from my_gamez_old.sql to my_gamez_new.sql structure
-- 
-- IMPORTANT INSTRUCTIONS:
-- 1. First, import my_gamez_new.sql to create the new structure in your database
-- 2. Import my_gamez_old.sql to a TEMPORARY database named 'my_gamez_old_temp'
--    OR rename all old tables with prefix 'old_' (e.g., old_users, old_news, etc.)
-- 3. Update the FROM clauses below to match your setup:
--    - If using temp database: FROM `my_gamez_old_temp`.`table_name`
--    - If using renamed tables: FROM `old_table_name`
-- 4. Make sure to backup your database before running this migration
-- 5. Disable foreign key checks if needed: SET FOREIGN_KEY_CHECKS = 0;
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================================
-- CONFIGURATION: Choose your migration source
-- ============================================================================
-- Option 1: If old database is in separate database 'my_gamez_old_temp'
--   Use: FROM `my_gamez_old_temp`.`table_name`
-- Option 2: If old tables are renamed with 'old_' prefix
--   Use: FROM `old_table_name`
-- 
-- Update the FROM clauses in each INSERT statement below accordingly
-- ============================================================================

-- ============================================================================
-- STEP 1: Migrate ads_setting table
-- ============================================================================
-- Old: id (int), new: id (bigint UNSIGNED)
-- Columns match, just need to handle ID conversion

INSERT INTO `ads_setting` (`id`, `applovin_sdk_key`, `applovin_inter_id`, `applovin_reward_id`, `applovin_native_id`, `status`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    `applovin_sdk_key`,
    `applovin_inter_id`,
    `applovin_reward_id`,
    `applovin_native_id`,
    `status`
FROM `my_gamez_old_temp`.`ads_setting`
-- Alternative if using renamed tables: FROM `old_ads_setting`
ON DUPLICATE KEY UPDATE
    `applovin_sdk_key` = VALUES(`applovin_sdk_key`),
    `applovin_inter_id` = VALUES(`applovin_inter_id`),
    `applovin_reward_id` = VALUES(`applovin_reward_id`),
    `applovin_native_id` = VALUES(`applovin_native_id`),
    `status` = VALUES(`status`);

-- ============================================================================
-- STEP 2: Migrate badges table
-- ============================================================================
-- Old: id (int), new: id (bigint UNSIGNED)

INSERT INTO `badges` (`id`, `badge_name`, `mining_sessions_required`, `spin_wheel_required`, `invite_friends_required`, `crutox_in_wallet_required`, `social_media_task_completed`, `badges_icon`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    `badge_name`,
    `mining_sessions_required`,
    `spin_wheel_required`,
    `invite_friends_required`,
    `crutox_in_wallet_required`,
    `social_media_task_completed`,
    `badges_icon`
FROM `my_gamez_old_temp`.`badges`
-- Alternative if using renamed tables: FROM `old_badges`
ON DUPLICATE KEY UPDATE
    `badge_name` = VALUES(`badge_name`),
    `mining_sessions_required` = VALUES(`mining_sessions_required`),
    `spin_wheel_required` = VALUES(`spin_wheel_required`),
    `invite_friends_required` = VALUES(`invite_friends_required`),
    `crutox_in_wallet_required` = VALUES(`crutox_in_wallet_required`),
    `social_media_task_completed` = VALUES(`social_media_task_completed`),
    `badges_icon` = VALUES(`badges_icon`);

-- ============================================================================
-- STEP 3: Migrate coin_settings table
-- ============================================================================

INSERT INTO `coin_settings` (`id`, `seconds_per_coin`, `max_seconds_allow`, `claim_time_in_sec`, `max_coin_claim_allow`, `token`, `token_price`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    `seconds_per_coin`,
    `max_seconds_allow`,
    `claim_time_in_sec`,
    `max_coin_claim_allow`,
    `token`,
    `token_price`
FROM `my_gamez_old_temp`.`coin_settings`
-- Alternative if using renamed tables: FROM `old_coin_settings`
ON DUPLICATE KEY UPDATE
    `seconds_per_coin` = VALUES(`seconds_per_coin`),
    `max_seconds_allow` = VALUES(`max_seconds_allow`),
    `claim_time_in_sec` = VALUES(`claim_time_in_sec`),
    `max_coin_claim_allow` = VALUES(`max_coin_claim_allow`),
    `token` = VALUES(`token`),
    `token_price` = VALUES(`token_price`);

-- ============================================================================
-- STEP 4: Migrate currency table
-- ============================================================================

INSERT INTO `currency` (`id`, `currency`, `value`, `icon`, `status`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    `currency`,
    `value`,
    `icon`,
    `status`
FROM `my_gamez_old_temp`.`currency`
-- Alternative if using renamed tables: FROM `old_currency`
ON DUPLICATE KEY UPDATE
    `currency` = VALUES(`currency`),
    `value` = VALUES(`value`),
    `icon` = VALUES(`icon`),
    `status` = VALUES(`status`);

-- ============================================================================
-- STEP 5: Migrate giveaway table
-- ============================================================================
-- Old: id (int), new: id (bigint UNSIGNED)
-- New has created_at with DEFAULT CURRENT_TIMESTAMP

INSERT INTO `giveaway` (`id`, `icon`, `title`, `description`, `link`, `created_at`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    `icon`,
    `title`,
    `description`,
    `link`,
    COALESCE(`created_at`, CURRENT_TIMESTAMP) as `created_at`
FROM `my_gamez_old_temp`.`giveaway`
-- Alternative if using renamed tables: FROM `old_giveaway`
ON DUPLICATE KEY UPDATE
    `icon` = VALUES(`icon`),
    `title` = VALUES(`title`),
    `description` = VALUES(`description`),
    `link` = VALUES(`link`),
    `created_at` = VALUES(`created_at`);

-- ============================================================================
-- STEP 6: Migrate level table
-- ============================================================================
-- Old: all columns NOT NULL, new: all columns DEFAULT NULL
-- ID stays as int (not bigint)

INSERT INTO `level` (`id`, `lvl_name`, `mining_sessions`, `spin_wheel`, `total_invite`, `user_account_old`, `perk_crutox_per_time`, `perk_mining_time`, `perk_crutox_reward`, `perk_other_access`, `is_ads_block`)
SELECT 
    `id`,
    NULLIF(`lvl_name`, '') as `lvl_name`,
    `mining_sessions`,
    `spin_wheel`,
    `total_invite`,
    `user_account_old`,
    NULLIF(`perk_crutox_per_time`, '') as `perk_crutox_per_time`,
    `perk_mining_time`,
    NULLIF(`perk_crutox_reward`, '') as `perk_crutox_reward`,
    NULLIF(`perk_other_access`, '') as `perk_other_access`,
    `is_ads_block`
FROM `my_gamez_old_temp`.`level`
-- Alternative if using renamed tables: FROM `old_level`
ON DUPLICATE KEY UPDATE
    `lvl_name` = VALUES(`lvl_name`),
    `mining_sessions` = VALUES(`mining_sessions`),
    `spin_wheel` = VALUES(`spin_wheel`),
    `total_invite` = VALUES(`total_invite`),
    `user_account_old` = VALUES(`user_account_old`),
    `perk_crutox_per_time` = VALUES(`perk_crutox_per_time`),
    `perk_mining_time` = VALUES(`perk_mining_time`),
    `perk_crutox_reward` = VALUES(`perk_crutox_reward`),
    `perk_other_access` = VALUES(`perk_other_access`),
    `is_ads_block` = VALUES(`is_ads_block`);

-- ============================================================================
-- STEP 7: Migrate news table
-- ============================================================================
-- Old: ID (int), new: ID (int) - same
-- Old: all NOT NULL, new: all DEFAULT NULL

INSERT INTO `news` (`ID`, `Image`, `Title`, `Description`, `CreatedAt`, `AdShow`, `RAdShow`, `Likes`, `isliked`, `Status`)
SELECT 
    `ID`,
    NULLIF(`Image`, '') as `Image`,
    NULLIF(`Title`, '') as `Title`,
    NULLIF(`Description`, '') as `Description`,
    NULLIF(`CreatedAt`, '') as `CreatedAt`,
    `AdShow`,
    `RAdShow`,
    NULLIF(`Likes`, '') as `Likes`,
    `isliked`,
    `Status`
FROM `my_gamez_old_temp`.`news`
-- Alternative if using renamed tables: FROM `old_news`
ON DUPLICATE KEY UPDATE
    `Image` = VALUES(`Image`),
    `Title` = VALUES(`Title`),
    `Description` = VALUES(`Description`),
    `CreatedAt` = VALUES(`CreatedAt`),
    `AdShow` = VALUES(`AdShow`),
    `RAdShow` = VALUES(`RAdShow`),
    `Likes` = VALUES(`Likes`),
    `isliked` = VALUES(`isliked`),
    `Status` = VALUES(`Status`);

-- ============================================================================
-- STEP 8: Migrate news_like table
-- ============================================================================

INSERT INTO `news_like` (`ID`, `News_ID`, `User_ID`, `CreatedAt`)
SELECT 
    `ID`,
    `News_ID`,
    `User_ID`,
    NULLIF(`CreatedAt`, '') as `CreatedAt`
FROM `my_gamez_old_temp`.`news_like`
-- Alternative if using renamed tables: FROM `old_news_like`
ON DUPLICATE KEY UPDATE
    `News_ID` = VALUES(`News_ID`),
    `User_ID` = VALUES(`User_ID`),
    `CreatedAt` = VALUES(`CreatedAt`);

-- ============================================================================
-- STEP 9: Migrate settings table
-- ============================================================================
-- Old: id (int), new: id (bigint UNSIGNED)

INSERT INTO `settings` (`id`, `update_version`, `maintenance`, `force_update`, `update_message`, `maintenance_message`, `update_link`, `pirvacy_policy_link`, `term_n_condition_link`, `support_email`, `faq_link`, `white_paper_link`, `road_map_link`, `about_us_link`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    NULLIF(`update_version`, '') as `update_version`,
    NULLIF(`maintenance`, '') as `maintenance`,
    NULLIF(`force_update`, '') as `force_update`,
    NULLIF(`update_message`, '') as `update_message`,
    NULLIF(`maintenance_message`, '') as `maintenance_message`,
    NULLIF(`update_link`, '') as `update_link`,
    NULLIF(`pirvacy_policy_link`, '') as `pirvacy_policy_link`,
    NULLIF(`term_n_condition_link`, '') as `term_n_condition_link`,
    NULLIF(`support_email`, '') as `support_email`,
    NULLIF(`faq_link`, '') as `faq_link`,
    NULLIF(`white_paper_link`, '') as `white_paper_link`,
    NULLIF(`road_map_link`, '') as `road_map_link`,
    NULLIF(`about_us_link`, '') as `about_us_link`
FROM `my_gamez_old_temp`.`settings`
-- Alternative if using renamed tables: FROM `old_settings`
ON DUPLICATE KEY UPDATE
    `update_version` = VALUES(`update_version`),
    `maintenance` = VALUES(`maintenance`),
    `force_update` = VALUES(`force_update`),
    `update_message` = VALUES(`update_message`),
    `maintenance_message` = VALUES(`maintenance_message`),
    `update_link` = VALUES(`update_link`),
    `pirvacy_policy_link` = VALUES(`pirvacy_policy_link`),
    `term_n_condition_link` = VALUES(`term_n_condition_link`),
    `support_email` = VALUES(`support_email`),
    `faq_link` = VALUES(`faq_link`),
    `white_paper_link` = VALUES(`white_paper_link`),
    `road_map_link` = VALUES(`road_map_link`),
    `about_us_link` = VALUES(`about_us_link`);

-- ============================================================================
-- STEP 10: Migrate shop table
-- ============================================================================

INSERT INTO `shop` (`ID`, `Image`, `Title`, `Link`, `Likes`, `isliked`, `Status`, `CreatedAt`)
SELECT 
    `ID`,
    NULLIF(`Image`, '') as `Image`,
    NULLIF(`Title`, '') as `Title`,
    NULLIF(`Link`, '') as `Link`,
    NULLIF(`Likes`, '') as `Likes`,
    `isliked`,
    `Status`,
    NULLIF(`CreatedAt`, '') as `CreatedAt`
FROM `my_gamez_old_temp`.`shop`
-- Alternative if using renamed tables: FROM `old_shop`
ON DUPLICATE KEY UPDATE
    `Image` = VALUES(`Image`),
    `Title` = VALUES(`Title`),
    `Link` = VALUES(`Link`),
    `Likes` = VALUES(`Likes`),
    `isliked` = VALUES(`isliked`),
    `Status` = VALUES(`Status`),
    `CreatedAt` = VALUES(`CreatedAt`);

-- ============================================================================
-- STEP 11: Migrate shop_views table
-- ============================================================================

INSERT INTO `shop_views` (`ID`, `Shop_ID`, `User_ID`, `CreatedAt`)
SELECT 
    `ID`,
    `Shop_ID`,
    `User_ID`,
    NULLIF(`CreatedAt`, '') as `CreatedAt`
FROM `my_gamez_old_temp`.`shop_views`
-- Alternative if using renamed tables: FROM `old_shop_views`
ON DUPLICATE KEY UPDATE
    `Shop_ID` = VALUES(`Shop_ID`),
    `User_ID` = VALUES(`User_ID`),
    `CreatedAt` = VALUES(`CreatedAt`);

-- ============================================================================
-- STEP 12: Migrate social_media_setting table
-- ============================================================================

INSERT INTO `social_media_setting` (`ID`, `Name`, `Icon`, `Link`, `Token`)
SELECT 
    `ID`,
    NULLIF(`Name`, '') as `Name`,
    NULLIF(`Icon`, '') as `Icon`,
    NULLIF(`Link`, '') as `Link`,
    NULLIF(`Token`, '') as `Token`
FROM `my_gamez_old_temp`.`social_media_setting`
-- Alternative if using renamed tables: FROM `old_social_media_setting`
ON DUPLICATE KEY UPDATE
    `Name` = VALUES(`Name`),
    `Icon` = VALUES(`Icon`),
    `Link` = VALUES(`Link`),
    `Token` = VALUES(`Token`);

-- ============================================================================
-- STEP 13: Migrate social_media_tokens table
-- ============================================================================
-- Old: id (int), user_id (int), new: id (bigint UNSIGNED), user_id (bigint UNSIGNED)

INSERT INTO `social_media_tokens` (`id`, `user_id`, `social_media_id`, `claim_date`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    CAST(`user_id` AS UNSIGNED) as `user_id`,
    `social_media_id`,
    COALESCE(`claim_date`, CURRENT_TIMESTAMP) as `claim_date`
FROM `my_gamez_old_temp`.`social_media_tokens`
-- Alternative if using renamed tables: FROM `old_social_media_tokens`
ON DUPLICATE KEY UPDATE
    `user_id` = VALUES(`user_id`),
    `social_media_id` = VALUES(`social_media_id`),
    `claim_date` = VALUES(`claim_date`);

-- ============================================================================
-- STEP 14: Migrate spin table
-- ============================================================================

INSERT INTO `spin` (`ID`, `Prize`, `Type`, `Color`, `CreatedAt`, `Status`)
SELECT 
    `ID`,
    NULLIF(`Prize`, '') as `Prize`,
    NULLIF(`Type`, '') as `Type`,
    NULLIF(`Color`, '') as `Color`,
    NULLIF(`CreatedAt`, '') as `CreatedAt`,
    `Status`
FROM `my_gamez_old_temp`.`spin`
-- Alternative if using renamed tables: FROM `old_spin`
ON DUPLICATE KEY UPDATE
    `Prize` = VALUES(`Prize`),
    `Type` = VALUES(`Type`),
    `Color` = VALUES(`Color`),
    `CreatedAt` = VALUES(`CreatedAt`),
    `Status` = VALUES(`Status`);

-- ============================================================================
-- STEP 15: Migrate spin_cailmed table
-- ============================================================================
-- Old: UserID (int), new: UserID (int) - same but can be NULL

INSERT INTO `spin_cailmed` (`UserID`, `Total`, `EndAt`, `StartedAt`)
SELECT 
    `UserID`,
    `Total`,
    NULLIF(`EndAt`, '') as `EndAt`,
    NULLIF(`StartedAt`, '') as `StartedAt`
FROM `my_gamez_old_temp`.`spin_cailmed`
-- Alternative if using renamed tables: FROM `old_spin_cailmed`
ON DUPLICATE KEY UPDATE
    `Total` = VALUES(`Total`),
    `EndAt` = VALUES(`EndAt`),
    `StartedAt` = VALUES(`StartedAt`);

-- ============================================================================
-- STEP 16: Migrate spin_setting table
-- ============================================================================

INSERT INTO `spin_setting` (`ID`, `ShowAd`, `AdType`, `MaxLimit`, `Time`, `SpinShow`)
SELECT 
    `ID`,
    `ShowAd`,
    NULLIF(`AdType`, '') as `AdType`,
    `MaxLimit`,
    NULLIF(`Time`, '') as `Time`,
    `SpinShow`
FROM `my_gamez_old_temp`.`spin_setting`
-- Alternative if using renamed tables: FROM `old_spin_setting`
ON DUPLICATE KEY UPDATE
    `ShowAd` = VALUES(`ShowAd`),
    `AdType` = VALUES(`AdType`),
    `MaxLimit` = VALUES(`MaxLimit`),
    `Time` = VALUES(`Time`),
    `SpinShow` = VALUES(`SpinShow`);

-- ============================================================================
-- STEP 17: Migrate users table
-- ============================================================================
-- CRITICAL: Old has id (int), new has id (bigint UNSIGNED)
-- New has additional columns: custom_coin_speed (can be NULL), auth_token (not in new structure, handled by migrations)
-- Old: all NOT NULL, new: all DEFAULT NULL

INSERT INTO `users` (
    `id`, `name`, `email`, `phone`, `country`, `password`, `token`, 
    `custom_coin_speed`, `coin`, `is_mining`, `mining_end_time`, 
    `coin_end_time`, `total_coin_claim`, `last_active`, `mining_time`, 
    `username`, `username_count`, `total_invite`, `invite_setup`, 
    `account_status`, `ban_reason`, `ban_date`, `otp`, `join_date`
)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    NULLIF(`name`, '') as `name`,
    NULLIF(`email`, '') as `email`,
    NULLIF(`phone`, '') as `phone`,
    NULLIF(`country`, '') as `country`,
    NULLIF(`password`, '') as `password`,
    NULLIF(`token`, '') as `token`,
    NULL as `custom_coin_speed`,  -- New column, set to NULL initially
    NULLIF(`coin`, '') as `coin`,
    NULLIF(`is_mining`, '') as `is_mining`,
    NULLIF(`mining_end_time`, '') as `mining_end_time`,
    NULLIF(`coin_end_time`, '') as `coin_end_time`,
    NULLIF(`total_coin_claim`, '') as `total_coin_claim`,
    NULLIF(`last_active`, '') as `last_active`,
    NULLIF(`mining_time`, '') as `mining_time`,
    NULLIF(`username`, '') as `username`,
    NULLIF(`username_count`, '') as `username_count`,
    `total_invite`,
    NULLIF(`invite_setup`, '') as `invite_setup`,
    NULLIF(`account_status`, '') as `account_status`,
    NULLIF(`ban_reason`, '') as `ban_reason`,
    NULLIF(`ban_date`, '') as `ban_date`,
    NULLIF(`otp`, '') as `otp`,
    NULLIF(`join_date`, '') as `join_date`
FROM `my_gamez_old_temp`.`users`
-- Alternative if using renamed tables: FROM `old_users`
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `email` = VALUES(`email`),
    `phone` = VALUES(`phone`),
    `country` = VALUES(`country`),
    `password` = VALUES(`password`),
    `token` = VALUES(`token`),
    `coin` = VALUES(`coin`),
    `is_mining` = VALUES(`is_mining`),
    `mining_end_time` = VALUES(`mining_end_time`),
    `coin_end_time` = VALUES(`coin_end_time`),
    `total_coin_claim` = VALUES(`total_coin_claim`),
    `last_active` = VALUES(`last_active`),
    `mining_time` = VALUES(`mining_time`),
    `username` = VALUES(`username`),
    `username_count` = VALUES(`username_count`),
    `total_invite` = VALUES(`total_invite`),
    `invite_setup` = VALUES(`invite_setup`),
    `account_status` = VALUES(`account_status`),
    `ban_reason` = VALUES(`ban_reason`),
    `ban_date` = VALUES(`ban_date`),
    `otp` = VALUES(`otp`),
    `join_date` = VALUES(`join_date`);

-- ============================================================================
-- STEP 18: Migrate user_guide table
-- ============================================================================
-- Old: userID (int NOT NULL), new: userID (bigint UNSIGNED DEFAULT NULL)

INSERT INTO `user_guide` (`userID`, `home`, `mining`, `wallet`, `badges`, `level`, `teamProfile`, `news`, `shop`, `userProfile`)
SELECT 
    CAST(`userID` AS UNSIGNED) as `userID`,
    `home`,
    `mining`,
    `wallet`,
    `badges`,
    `level`,
    `teamProfile`,
    `news`,
    `shop`,
    `userProfile`
FROM `my_gamez_old_temp`.`user_guide`
-- Alternative if using renamed tables: FROM `old_user_guide`
ON DUPLICATE KEY UPDATE
    `home` = VALUES(`home`),
    `mining` = VALUES(`mining`),
    `wallet` = VALUES(`wallet`),
    `badges` = VALUES(`badges`),
    `level` = VALUES(`level`),
    `teamProfile` = VALUES(`teamProfile`),
    `news` = VALUES(`news`),
    `shop` = VALUES(`shop`),
    `userProfile` = VALUES(`userProfile`);

-- ============================================================================
-- STEP 19: Migrate user_levels table
-- ============================================================================
-- Old: id (int), user_id (int), new: id (bigint UNSIGNED), user_id (bigint UNSIGNED)
-- Old: current_level (int NOT NULL), new: current_level (int DEFAULT NULL)

INSERT INTO `user_levels` (`id`, `user_id`, `mining_session`, `spin_wheel`, `current_level`, `achieved_at`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    CAST(`user_id` AS UNSIGNED) as `user_id`,
    `mining_session`,
    `spin_wheel`,
    `current_level`,
    NULLIF(`achieved_at`, '') as `achieved_at`
FROM `my_gamez_old_temp`.`user_levels`
-- Alternative if using renamed tables: FROM `old_user_levels`
ON DUPLICATE KEY UPDATE
    `user_id` = VALUES(`user_id`),
    `mining_session` = VALUES(`mining_session`),
    `spin_wheel` = VALUES(`spin_wheel`),
    `current_level` = VALUES(`current_level`),
    `achieved_at` = VALUES(`achieved_at`);

-- ============================================================================
-- STEP 20: Handle token_bonus_history table (if exists in old but not in new)
-- ============================================================================
-- NOTE: This table exists in old database but not in new structure
-- If you need to preserve this data, you may need to create a backup table
-- or migrate it to a different location

-- ============================================================================
-- STEP 21: Backup token_bonus_history (if needed)
-- ============================================================================
-- NOTE: This table exists in old database but not in new structure
-- Uncomment below if you need to preserve this data

-- CREATE TABLE IF NOT EXISTS `token_bonus_history_backup` AS 
-- SELECT * FROM `my_gamez_old_temp`.`token_bonus_history`;
-- Alternative if using renamed tables: FROM `old_token_bonus_history`

-- ============================================================================
-- Migration Complete
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

-- ============================================================================
-- VERIFICATION QUERIES (Run these after migration to verify data)
-- ============================================================================

-- Check record counts for each table
-- SELECT 'ads_setting' as table_name, 
--        (SELECT COUNT(*) FROM my_gamez_old_temp.ads_setting) as old_count,
--        (SELECT COUNT(*) FROM ads_setting) as new_count;
-- 
-- SELECT 'users' as table_name,
--        (SELECT COUNT(*) FROM my_gamez_old_temp.users) as old_count,
--        (SELECT COUNT(*) FROM users) as new_count;
-- 
-- -- Check ID ranges
-- SELECT 'users' as table_name,
--        (SELECT MIN(id) FROM my_gamez_old_temp.users) as old_min_id,
--        (SELECT MAX(id) FROM my_gamez_old_temp.users) as old_max_id,
--        (SELECT MIN(id) FROM users) as new_min_id,
--        (SELECT MAX(id) FROM users) as new_max_id;

