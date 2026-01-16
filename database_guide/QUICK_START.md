# Quick Start Migration Guide

This is a simplified guide to migrate your old database to the new structure.

## Option 1: Using Separate Temporary Database (Recommended)

### Step 1: Backup
```bash
mysqldump -u root -p my_gamez > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Import New Structure
```bash
mysql -u root -p my_gamez < my_gamez_new.sql
```

### Step 3: Create Temporary Database and Import Old Data
```bash
mysql -u root -p -e "CREATE DATABASE my_gamez_old_temp;"
mysql -u root -p my_gamez_old_temp < my_gamez_old.sql
```

### Step 4: Run Migration
```bash
mysql -u root -p my_gamez < migrate_old_to_new.sql
```

### Step 5: Verify and Cleanup
```sql
-- Verify counts
SELECT 'users' as table_name, COUNT(*) as count FROM users;
SELECT 'news' as table_name, COUNT(*) as count FROM news;
-- Add more verification queries as needed
```

```bash
# Drop temporary database after verification
mysql -u root -p -e "DROP DATABASE my_gamez_old_temp;"
```

## Option 2: Using Renamed Tables (Alternative)

### Step 1: Backup
```bash
mysqldump -u root -p my_gamez > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Import Old Database
```bash
mysql -u root -p my_gamez < my_gamez_old.sql
```

### Step 3: Rename Old Tables
```bash
mysql -u root -p my_gamez < rename_old_tables.sql
```

### Step 4: Import New Structure
```bash
mysql -u root -p my_gamez < my_gamez_new.sql
```

### Step 5: Update Migration Script
Edit `migrate_old_to_new.sql` and change all:
```sql
FROM `my_gamez_old_temp`.`table_name`
```
to:
```sql
FROM `old_table_name`
```

### Step 6: Run Migration
```bash
mysql -u root -p my_gamez < migrate_old_to_new.sql
```

### Step 7: Cleanup Old Tables (After Verification)
```sql
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
```

## What Gets Migrated

✅ All data from these tables:
- ads_setting
- badges
- coin_settings
- currency
- giveaway
- level
- news
- news_like
- settings
- shop
- shop_views
- social_media_setting
- social_media_tokens
- spin
- spin_cailmed
- spin_setting
- users
- user_guide
- user_levels

⚠️ **NOT Migrated** (doesn't exist in new structure):
- token_bonus_history (backup script included if needed)

## Key Changes Handled Automatically

1. **ID Type Conversion**: `int` → `bigint UNSIGNED`
2. **NULL Handling**: Empty strings → NULL where appropriate
3. **New Columns**: `custom_coin_speed` in users table (set to NULL)
4. **Data Type Preservation**: All data types properly converted

## Troubleshooting

### Error: Table doesn't exist
- Make sure you've imported the old database first
- Check table names match (case-sensitive)

### Error: Duplicate entry
- The migration uses `ON DUPLICATE KEY UPDATE` to handle duplicates
- If errors persist, check for conflicting IDs

### Error: Foreign key constraint
- The migration script disables foreign key checks
- If still getting errors, verify foreign key relationships

## Need Help?

See `MIGRATION_GUIDE.md` for detailed documentation.

