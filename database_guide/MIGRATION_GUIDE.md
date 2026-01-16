# Database Migration Guide: Old to New Structure

This guide will help you migrate data from `my_gamez_old.sql` to `my_gamez_new.sql` structure.

## Overview

The new database structure has several changes:
- **ID Types**: Most tables changed from `int` to `bigint UNSIGNED`
- **NULL Handling**: Many columns changed from `NOT NULL` to `DEFAULT NULL`
- **Collation**: Changed from `utf8mb4_general_ci` to `utf8mb4_unicode_ci`
- **New Tables**: Added `admin`, `failed_jobs`, `jobs`, `kyc_submissions`, `migrations`, `mystery_box_claims`, `personal_access_tokens`, `task_completions`, `user_boosters`
- **Removed Tables**: `token_bonus_history` (exists in old but not in new)

## Migration Steps

### Step 1: Backup Your Current Database
```bash
mysqldump -u username -p my_gamez > backup_before_migration.sql
```

### Step 2: Import the New Database Structure
```bash
mysql -u username -p my_gamez < my_gamez_new.sql
```

### Step 3: Import the Old Database to a Temporary Database
```bash
# Create temporary database
mysql -u username -p -e "CREATE DATABASE my_gamez_old_temp;"

# Import old database
mysql -u username -p my_gamez_old_temp < my_gamez_old.sql
```

### Step 4: Run the Migration Script
```bash
mysql -u username -p my_gamez < migrate_old_to_new.sql
```

**Note**: Before running the migration script, you need to update the table names in `migrate_old_to_new.sql` to reference the temporary database tables. See the script for details.

### Step 5: Verify Data Migration
Run verification queries to ensure all data was migrated correctly.

### Step 6: Clean Up
```bash
# Drop temporary database after verification
mysql -u username -p -e "DROP DATABASE my_gamez_old_temp;"
```

## Key Differences Between Old and New Structure

### Table: `users`
- **Old**: `id` is `int NOT NULL`
- **New**: `id` is `bigint UNSIGNED NOT NULL`
- **New Column**: `custom_coin_speed` (decimal, nullable)

### Table: `ads_setting`, `badges`, `coin_settings`, `currency`, `giveaway`
- **Old**: `id` is `int NOT NULL`
- **New**: `id` is `bigint UNSIGNED NOT NULL`

### Table: `social_media_tokens`, `user_levels`
- **Old**: `id` and `user_id` are `int NOT NULL`
- **New**: `id` and `user_id` are `bigint UNSIGNED DEFAULT NULL`

### Table: `user_guide`
- **Old**: `userID` is `int NOT NULL`
- **New**: `userID` is `bigint UNSIGNED DEFAULT NULL`

### Table: `level`
- **Old**: All columns are `NOT NULL`
- **New**: All columns are `DEFAULT NULL`
- **ID Type**: Stays as `int` (not changed to bigint)

## Tables Not Migrated

### `token_bonus_history`
This table exists in the old database but not in the new structure. If you need to preserve this data:
1. Create a backup table before migration
2. Or create a custom table to store this data separately

## Important Notes

1. **Foreign Key Constraints**: The migration script uses `ON DUPLICATE KEY UPDATE` to handle existing records. Make sure foreign keys are properly maintained.

2. **Empty Strings to NULL**: The migration converts empty strings to NULL where appropriate to match the new structure's nullable columns.

3. **ID Conversion**: All `int` IDs are converted to `bigint UNSIGNED` using `CAST(id AS UNSIGNED)`.

4. **Timestamps**: For tables with timestamp columns, the migration uses `COALESCE` to handle NULL values and set defaults.

5. **Data Integrity**: After migration, verify:
   - Record counts match
   - Foreign key relationships are intact
   - No data loss occurred
   - All required fields are populated

## Troubleshooting

### Issue: Foreign Key Constraint Errors
**Solution**: Disable foreign key checks temporarily:
```sql
SET FOREIGN_KEY_CHECKS = 0;
-- Run migration
SET FOREIGN_KEY_CHECKS = 1;
```

### Issue: Duplicate Key Errors
**Solution**: The migration script uses `ON DUPLICATE KEY UPDATE` to handle duplicates. If you still get errors, check for conflicting IDs.

### Issue: Data Type Conversion Errors
**Solution**: Ensure all IDs in the old database are positive integers (for UNSIGNED conversion).

## Verification Queries

After migration, run these queries to verify data:

```sql
-- Check record counts
SELECT 'ads_setting' as table_name, COUNT(*) as old_count FROM my_gamez_old_temp.ads_setting
UNION ALL
SELECT 'ads_setting', COUNT(*) FROM my_gamez.ads_setting;

-- Check users table
SELECT COUNT(*) FROM my_gamez.users;
SELECT MIN(id), MAX(id) FROM my_gamez.users;

-- Verify foreign keys
SELECT COUNT(*) FROM my_gamez.news_like nl
LEFT JOIN my_gamez.news n ON nl.News_ID = n.ID
WHERE n.ID IS NULL;
```

