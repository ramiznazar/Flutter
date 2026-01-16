# Simple Migration Instructions

Since you already have both SQL files, here's the simplest way to migrate:

## One-Command Migration (Recommended)

I've created an automated script that does everything for you:

```bash
cd /var/www/my_gamez/database_guide
./complete_migration.sh
```

This script will:
1. ✅ Backup your current database
2. ✅ Import the old database
3. ✅ Rename old tables with 'old_' prefix
4. ✅ Import the new structure
5. ✅ Migrate all data automatically
6. ✅ Verify the migration

## Manual Step-by-Step (If you prefer)

### Step 1: Backup
```bash
mysqldump -u root -pNEW_STRONG_PASSWORD my_gamez > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Import Old Database
```bash
mysql -u root -pNEW_STRONG_PASSWORD my_gamez < my_gamez_old.sql
```

### Step 3: Rename Old Tables
```bash
mysql -u root -pNEW_STRONG_PASSWORD my_gamez < rename_old_tables.sql
```

### Step 4: Import New Structure
```bash
mysql -u root -pNEW_STRONG_PASSWORD my_gamez < my_gamez_new.sql
```

### Step 5: Migrate Data
```bash
mysql -u root -pNEW_STRONG_PASSWORD my_gamez < migrate_old_to_new_renamed.sql
```

### Step 6: Verify Migration
```bash
mysql -u root -pNEW_STRONG_PASSWORD my_gamez < verify_migration.sql
```

## What Gets Migrated

All data from these 19 tables:
- ✅ ads_setting
- ✅ badges  
- ✅ coin_settings
- ✅ currency
- ✅ giveaway
- ✅ level
- ✅ news
- ✅ news_like
- ✅ settings
- ✅ shop
- ✅ shop_views
- ✅ social_media_setting
- ✅ social_media_tokens
- ✅ spin
- ✅ spin_cailmed
- ✅ spin_setting
- ✅ users (largest table - all user data)
- ✅ user_guide
- ✅ user_levels

## After Migration

Once verified, you can clean up old tables:

```bash
mysql -u root -pNEW_STRONG_PASSWORD my_gamez <<EOF
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
EOF
```

## Troubleshooting

### If migration fails:
1. Check the error message
2. Verify both SQL files are in the current directory
3. Make sure MySQL user has proper permissions
4. Check disk space (old database is 48MB)

### If verification shows mismatches:
- Check the specific table mentioned
- Review the migration logs
- You can re-run individual table migrations if needed

## Important Notes

- **Password**: The script uses `NEW_STRONG_PASSWORD` - update it in `complete_migration.sh` if different
- **No Data Loss**: All data is preserved and converted properly
- **ID Conversion**: All IDs are converted from `int` to `bigint UNSIGNED` automatically
- **Foreign Keys**: All relationships are maintained

