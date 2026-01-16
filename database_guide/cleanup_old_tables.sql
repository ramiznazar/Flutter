-- ============================================================================
-- Cleanup Script: Drop Old Tables After Migration Verification
-- ============================================================================
-- Run this ONLY after you've verified the migration was successful
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

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

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Old tables dropped successfully!' as 'Status';

