-- ============================================================================
-- Migration Script: Old Database to New Database Structure
-- ============================================================================
-- This version uses renamed tables with 'old_' prefix
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================================
-- STEP 1: Migrate ads_setting table
-- ============================================================================
INSERT INTO `ads_setting` (`id`, `applovin_sdk_key`, `applovin_inter_id`, `applovin_reward_id`, `applovin_native_id`, `status`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    `applovin_sdk_key`,
    `applovin_inter_id`,
    `applovin_reward_id`,
    `applovin_native_id`,
    `status`
FROM `old_ads_setting`
ON DUPLICATE KEY UPDATE
    `applovin_sdk_key` = VALUES(`applovin_sdk_key`),
    `applovin_inter_id` = VALUES(`applovin_inter_id`),
    `applovin_reward_id` = VALUES(`applovin_reward_id`),
    `applovin_native_id` = VALUES(`applovin_native_id`),
    `status` = VALUES(`status`);

-- ============================================================================
-- STEP 2: Migrate badges table
-- ============================================================================
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
FROM `old_badges`
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
FROM `old_coin_settings`
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
FROM `old_currency`
ON DUPLICATE KEY UPDATE
    `currency` = VALUES(`currency`),
    `value` = VALUES(`value`),
    `icon` = VALUES(`icon`),
    `status` = VALUES(`status`);

-- ============================================================================
-- STEP 5: Migrate giveaway table
-- ============================================================================
INSERT INTO `giveaway` (`id`, `icon`, `title`, `description`, `link`, `created_at`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    `icon`,
    `title`,
    `description`,
    `link`,
    COALESCE(`created_at`, CURRENT_TIMESTAMP) as `created_at`
FROM `old_giveaway`
ON DUPLICATE KEY UPDATE
    `icon` = VALUES(`icon`),
    `title` = VALUES(`title`),
    `description` = VALUES(`description`),
    `link` = VALUES(`link`),
    `created_at` = VALUES(`created_at`);

-- ============================================================================
-- STEP 6: Migrate level table
-- ============================================================================
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
FROM `old_level`
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
FROM `old_news`
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
FROM `old_news_like`
ON DUPLICATE KEY UPDATE
    `News_ID` = VALUES(`News_ID`),
    `User_ID` = VALUES(`User_ID`),
    `CreatedAt` = VALUES(`CreatedAt`);

-- ============================================================================
-- STEP 9: Migrate settings table
-- ============================================================================
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
FROM `old_settings`
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
FROM `old_shop`
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
FROM `old_shop_views`
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
FROM `old_social_media_setting`
ON DUPLICATE KEY UPDATE
    `Name` = VALUES(`Name`),
    `Icon` = VALUES(`Icon`),
    `Link` = VALUES(`Link`),
    `Token` = VALUES(`Token`);

-- ============================================================================
-- STEP 13: Migrate social_media_tokens table
-- ============================================================================
INSERT INTO `social_media_tokens` (`id`, `user_id`, `social_media_id`, `claim_date`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    CAST(`user_id` AS UNSIGNED) as `user_id`,
    `social_media_id`,
    COALESCE(`claim_date`, CURRENT_TIMESTAMP) as `claim_date`
FROM `old_social_media_tokens`
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
FROM `old_spin`
ON DUPLICATE KEY UPDATE
    `Prize` = VALUES(`Prize`),
    `Type` = VALUES(`Type`),
    `Color` = VALUES(`Color`),
    `CreatedAt` = VALUES(`CreatedAt`),
    `Status` = VALUES(`Status`);

-- ============================================================================
-- STEP 15: Migrate spin_cailmed table
-- ============================================================================
INSERT INTO `spin_cailmed` (`UserID`, `Total`, `EndAt`, `StartedAt`)
SELECT 
    `UserID`,
    `Total`,
    NULLIF(`EndAt`, '') as `EndAt`,
    NULLIF(`StartedAt`, '') as `StartedAt`
FROM `old_spin_cailmed`
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
FROM `old_spin_setting`
ON DUPLICATE KEY UPDATE
    `ShowAd` = VALUES(`ShowAd`),
    `AdType` = VALUES(`AdType`),
    `MaxLimit` = VALUES(`MaxLimit`),
    `Time` = VALUES(`Time`),
    `SpinShow` = VALUES(`SpinShow`);

-- ============================================================================
-- STEP 17: Migrate users table (CRITICAL - Largest table)
-- ============================================================================
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
    NULL as `custom_coin_speed`,
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
FROM `old_users`
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
FROM `old_user_guide`
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
INSERT INTO `user_levels` (`id`, `user_id`, `mining_session`, `spin_wheel`, `current_level`, `achieved_at`)
SELECT 
    CAST(`id` AS UNSIGNED) as `id`,
    CAST(`user_id` AS UNSIGNED) as `user_id`,
    `mining_session`,
    `spin_wheel`,
    `current_level`,
    NULLIF(`achieved_at`, '') as `achieved_at`
FROM `old_user_levels`
ON DUPLICATE KEY UPDATE
    `user_id` = VALUES(`user_id`),
    `mining_session` = VALUES(`mining_session`),
    `spin_wheel` = VALUES(`spin_wheel`),
    `current_level` = VALUES(`current_level`),
    `achieved_at` = VALUES(`achieved_at`);

-- ============================================================================
-- Migration Complete
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

