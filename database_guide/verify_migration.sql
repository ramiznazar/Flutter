-- ============================================================================
-- Migration Verification Script
-- ============================================================================
-- Run this after migration to verify all data was migrated correctly
-- ============================================================================

SELECT '========================================' as '';
SELECT 'MIGRATION VERIFICATION REPORT' as '';
SELECT '========================================' as '';
SELECT '' as '';

-- Count comparison for all tables
SELECT 
    'Table' as 'Table Name',
    'Old Count' as 'Old Count',
    'New Count' as 'New Count',
    'Status' as 'Status'
UNION ALL
SELECT 
    'ads_setting',
    CAST((SELECT COUNT(*) FROM old_ads_setting) AS CHAR),
    CAST((SELECT COUNT(*) FROM ads_setting) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_ads_setting) = (SELECT COUNT(*) FROM ads_setting) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'badges',
    CAST((SELECT COUNT(*) FROM old_badges) AS CHAR),
    CAST((SELECT COUNT(*) FROM badges) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_badges) = (SELECT COUNT(*) FROM badges) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'coin_settings',
    CAST((SELECT COUNT(*) FROM old_coin_settings) AS CHAR),
    CAST((SELECT COUNT(*) FROM coin_settings) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_coin_settings) = (SELECT COUNT(*) FROM coin_settings) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'currency',
    CAST((SELECT COUNT(*) FROM old_currency) AS CHAR),
    CAST((SELECT COUNT(*) FROM currency) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_currency) = (SELECT COUNT(*) FROM currency) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'giveaway',
    CAST((SELECT COUNT(*) FROM old_giveaway) AS CHAR),
    CAST((SELECT COUNT(*) FROM giveaway) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_giveaway) = (SELECT COUNT(*) FROM giveaway) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'level',
    CAST((SELECT COUNT(*) FROM old_level) AS CHAR),
    CAST((SELECT COUNT(*) FROM level) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_level) = (SELECT COUNT(*) FROM level) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'news',
    CAST((SELECT COUNT(*) FROM old_news) AS CHAR),
    CAST((SELECT COUNT(*) FROM news) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_news) = (SELECT COUNT(*) FROM news) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'news_like',
    CAST((SELECT COUNT(*) FROM old_news_like) AS CHAR),
    CAST((SELECT COUNT(*) FROM news_like) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_news_like) = (SELECT COUNT(*) FROM news_like) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'settings',
    CAST((SELECT COUNT(*) FROM old_settings) AS CHAR),
    CAST((SELECT COUNT(*) FROM settings) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_settings) = (SELECT COUNT(*) FROM settings) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'shop',
    CAST((SELECT COUNT(*) FROM old_shop) AS CHAR),
    CAST((SELECT COUNT(*) FROM shop) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_shop) = (SELECT COUNT(*) FROM shop) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'shop_views',
    CAST((SELECT COUNT(*) FROM old_shop_views) AS CHAR),
    CAST((SELECT COUNT(*) FROM shop_views) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_shop_views) = (SELECT COUNT(*) FROM shop_views) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'social_media_setting',
    CAST((SELECT COUNT(*) FROM old_social_media_setting) AS CHAR),
    CAST((SELECT COUNT(*) FROM social_media_setting) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_social_media_setting) = (SELECT COUNT(*) FROM social_media_setting) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'social_media_tokens',
    CAST((SELECT COUNT(*) FROM old_social_media_tokens) AS CHAR),
    CAST((SELECT COUNT(*) FROM social_media_tokens) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_social_media_tokens) = (SELECT COUNT(*) FROM social_media_tokens) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'spin',
    CAST((SELECT COUNT(*) FROM old_spin) AS CHAR),
    CAST((SELECT COUNT(*) FROM spin) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_spin) = (SELECT COUNT(*) FROM spin) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'spin_cailmed',
    CAST((SELECT COUNT(*) FROM old_spin_cailmed) AS CHAR),
    CAST((SELECT COUNT(*) FROM spin_cailmed) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_spin_cailmed) = (SELECT COUNT(*) FROM spin_cailmed) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'spin_setting',
    CAST((SELECT COUNT(*) FROM old_spin_setting) AS CHAR),
    CAST((SELECT COUNT(*) FROM spin_setting) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_spin_setting) = (SELECT COUNT(*) FROM spin_setting) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'users',
    CAST((SELECT COUNT(*) FROM old_users) AS CHAR),
    CAST((SELECT COUNT(*) FROM users) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_users) = (SELECT COUNT(*) FROM users) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'user_guide',
    CAST((SELECT COUNT(*) FROM old_user_guide) AS CHAR),
    CAST((SELECT COUNT(*) FROM user_guide) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_user_guide) = (SELECT COUNT(*) FROM user_guide) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END
UNION ALL
SELECT 
    'user_levels',
    CAST((SELECT COUNT(*) FROM old_user_levels) AS CHAR),
    CAST((SELECT COUNT(*) FROM user_levels) AS CHAR),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_user_levels) = (SELECT COUNT(*) FROM user_levels) 
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END;

SELECT '' as '';
SELECT '========================================' as '';
SELECT 'ID RANGE VERIFICATION' as '';
SELECT '========================================' as '';
SELECT '' as '';

-- Check ID ranges for users table
SELECT 
    'users' as 'Table',
    (SELECT MIN(id) FROM old_users) as 'Old Min ID',
    (SELECT MAX(id) FROM old_users) as 'Old Max ID',
    (SELECT MIN(id) FROM users) as 'New Min ID',
    (SELECT MAX(id) FROM users) as 'New Max ID',
    CASE 
        WHEN (SELECT MIN(id) FROM old_users) = (SELECT MIN(id) FROM users) 
         AND (SELECT MAX(id) FROM old_users) = (SELECT MAX(id) FROM users)
        THEN '✓ MATCH' 
        ELSE '✗ MISMATCH' 
    END as 'Status';

SELECT '' as '';
SELECT '========================================' as '';
SELECT 'FOREIGN KEY VERIFICATION' as '';
SELECT '========================================' as '';
SELECT '' as '';

-- Check foreign key integrity
SELECT 
    'news_like -> news' as 'Relationship',
    COUNT(*) as 'Orphaned Records',
    CASE 
        WHEN COUNT(*) = 0 THEN '✓ VALID' 
        ELSE '✗ INVALID' 
    END as 'Status'
FROM news_like nl
LEFT JOIN news n ON nl.News_ID = n.ID
WHERE n.ID IS NULL
UNION ALL
SELECT 
    'user_levels -> users' as 'Relationship',
    COUNT(*) as 'Orphaned Records',
    CASE 
        WHEN COUNT(*) = 0 THEN '✓ VALID' 
        ELSE '✗ INVALID' 
    END as 'Status'
FROM user_levels ul
LEFT JOIN users u ON ul.user_id = u.id
WHERE u.id IS NULL
UNION ALL
SELECT 
    'user_guide -> users' as 'Relationship',
    COUNT(*) as 'Orphaned Records',
    CASE 
        WHEN COUNT(*) = 0 THEN '✓ VALID' 
        ELSE '✗ INVALID' 
    END as 'Status'
FROM user_guide ug
LEFT JOIN users u ON ug.userID = u.id
WHERE u.id IS NULL AND ug.userID IS NOT NULL;

SELECT '' as '';
SELECT '========================================' as '';
SELECT 'VERIFICATION COMPLETE' as '';
SELECT '========================================' as '';

