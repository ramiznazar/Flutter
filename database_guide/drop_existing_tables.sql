-- ============================================================================
-- Drop Existing Tables Script
-- ============================================================================
-- This script drops all existing tables to prepare for fresh import
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS ads_setting;
DROP TABLE IF EXISTS badges;
DROP TABLE IF EXISTS coin_settings;
DROP TABLE IF EXISTS currency;
DROP TABLE IF EXISTS giveaway;
DROP TABLE IF EXISTS level;
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS news_like;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS shop;
DROP TABLE IF EXISTS shop_views;
DROP TABLE IF EXISTS social_media_setting;
DROP TABLE IF EXISTS social_media_tokens;
DROP TABLE IF EXISTS spin;
DROP TABLE IF EXISTS spin_cailmed;
DROP TABLE IF EXISTS spin_setting;
DROP TABLE IF EXISTS token_bonus_history;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS user_guide;
DROP TABLE IF EXISTS user_levels;

-- Also drop any old_ prefixed tables if they exist
DROP TABLE IF EXISTS old_ads_setting;
DROP TABLE IF EXISTS old_badges;
DROP TABLE IF EXISTS old_coin_settings;
DROP TABLE IF EXISTS old_currency;
DROP TABLE IF EXISTS old_giveaway;
DROP TABLE IF EXISTS old_level;
DROP TABLE IF EXISTS old_news;
DROP TABLE IF EXISTS old_news_like;
DROP TABLE IF EXISTS old_settings;
DROP TABLE IF EXISTS old_shop;
DROP TABLE IF EXISTS old_shop_views;
DROP TABLE IF EXISTS old_social_media_setting;
DROP TABLE IF EXISTS old_social_media_tokens;
DROP TABLE IF EXISTS old_spin;
DROP TABLE IF EXISTS old_spin_cailmed;
DROP TABLE IF EXISTS old_spin_setting;
DROP TABLE IF EXISTS old_token_bonus_history;
DROP TABLE IF EXISTS old_users;
DROP TABLE IF EXISTS old_user_guide;
DROP TABLE IF EXISTS old_user_levels;

-- Drop new structure tables if they exist
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS failed_jobs;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS kyc_submissions;
DROP TABLE IF EXISTS migrations;
DROP TABLE IF EXISTS mystery_box_claims;
DROP TABLE IF EXISTS personal_access_tokens;
DROP TABLE IF EXISTS task_completions;
DROP TABLE IF EXISTS user_boosters;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'All existing tables dropped successfully!' as 'Status';

