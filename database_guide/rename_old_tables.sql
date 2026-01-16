-- ============================================================================
-- Helper Script: Rename Old Tables with 'old_' Prefix
-- ============================================================================
-- Use this script if you want to import both old and new databases into the
-- same database and rename old tables with 'old_' prefix
-- 
-- IMPORTANT: Run this AFTER importing my_gamez_old.sql but BEFORE importing
-- my_gamez_new.sql
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Rename all old tables with 'old_' prefix
RENAME TABLE `ads_setting` TO `old_ads_setting`;
RENAME TABLE `badges` TO `old_badges`;
RENAME TABLE `coin_settings` TO `old_coin_settings`;
RENAME TABLE `currency` TO `old_currency`;
RENAME TABLE `giveaway` TO `old_giveaway`;
RENAME TABLE `level` TO `old_level`;
RENAME TABLE `news` TO `old_news`;
RENAME TABLE `news_like` TO `old_news_like`;
RENAME TABLE `settings` TO `old_settings`;
RENAME TABLE `shop` TO `old_shop`;
RENAME TABLE `shop_views` TO `old_shop_views`;
RENAME TABLE `social_media_setting` TO `old_social_media_setting`;
RENAME TABLE `social_media_tokens` TO `old_social_media_tokens`;
RENAME TABLE `spin` TO `old_spin`;
RENAME TABLE `spin_cailmed` TO `old_spin_cailmed`;
RENAME TABLE `spin_setting` TO `old_spin_setting`;
RENAME TABLE `token_bonus_history` TO `old_token_bonus_history`;
RENAME TABLE `users` TO `old_users`;
RENAME TABLE `user_guide` TO `old_user_guide`;
RENAME TABLE `user_levels` TO `old_user_levels`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- After running this script:
-- 1. Import my_gamez_new.sql to create the new structure
-- 2. Update migrate_old_to_new.sql to use 'old_' prefixed table names
--    (change FROM `my_gamez_old_temp`.`table_name` to FROM `old_table_name`)
-- 3. Run migrate_old_to_new.sql
-- ============================================================================

