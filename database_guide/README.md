# Database Migration Package

This package contains everything you need to migrate data from `my_gamez_old.sql` to `my_gamez_new.sql` structure.

## Files in This Package

### 📄 Core Migration Files

1. **`migrate_old_to_new.sql`** - Main migration script
   - Migrates all data from old structure to new structure
   - Handles data type conversions (int → bigint UNSIGNED)
   - Converts empty strings to NULL where appropriate
   - Uses `ON DUPLICATE KEY UPDATE` to handle existing records
   - Supports both temporary database and renamed table approaches

2. **`rename_old_tables.sql`** - Helper script for table renaming
   - Renames all old tables with 'old_' prefix
   - Use this if importing both databases into the same database

### 📚 Documentation Files

3. **`QUICK_START.md`** - Quick reference guide
   - Step-by-step instructions for both migration approaches
   - Minimal setup required
   - Quick troubleshooting tips

4. **`MIGRATION_GUIDE.md`** - Comprehensive documentation
   - Detailed explanation of all changes
   - Table-by-table comparison
   - Verification queries
   - Troubleshooting guide

5. **`README.md`** - This file
   - Overview of all files
   - Quick reference

### 📊 Source Files

6. **`my_gamez_old.sql`** - Your old database structure (48MB)
   - Original database with all data
   - Contains 19 tables

7. **`my_gamez_new.sql`** - Your new database structure (27KB)
   - New database structure with migrations
   - Contains 25 tables (6 new tables added)

## Quick Start

**Recommended Approach (Separate Database):**

```bash
# 1. Backup
mysqldump -u root -p my_gamez > backup.sql

# 2. Import new structure
mysql -u root -p my_gamez < my_gamez_new.sql

# 3. Create temp database and import old data
mysql -u root -p -e "CREATE DATABASE my_gamez_old_temp;"
mysql -u root -p my_gamez_old_temp < my_gamez_old.sql

# 4. Run migration
mysql -u root -p my_gamez < migrate_old_to_new.sql

# 5. Verify and cleanup
mysql -u root -p -e "DROP DATABASE my_gamez_old_temp;"
```

For detailed instructions, see **`QUICK_START.md`**.

## What Gets Migrated

### ✅ Tables Migrated (19 tables)
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

### ⚠️ Tables Not in New Structure
- **token_bonus_history** - Exists in old but not in new structure
  - Backup script included in migration file (commented out)
  - Uncomment if you need to preserve this data

### ✨ New Tables in New Structure (Not Migrated - Empty)
- admin
- failed_jobs
- jobs
- kyc_submissions
- migrations
- mystery_box_claims
- personal_access_tokens
- task_completions
- user_boosters

## Key Changes Handled

1. **ID Type Conversion**
   - `int` → `bigint UNSIGNED` for most tables
   - Preserves all ID values

2. **NULL Handling**
   - Converts empty strings to NULL where columns are nullable
   - Handles NOT NULL → DEFAULT NULL changes

3. **New Columns**
   - `users.custom_coin_speed` - Set to NULL for migrated records
   - Other new columns use default values

4. **Data Preservation**
   - All data is preserved
   - Foreign key relationships maintained
   - No data loss

## Migration Safety Features

- ✅ Uses transactions (rollback on error)
- ✅ Handles duplicate keys gracefully
- ✅ Preserves all foreign key relationships
- ✅ Type-safe conversions
- ✅ Verification queries included

## Support

If you encounter issues:

1. Check **`MIGRATION_GUIDE.md`** for detailed troubleshooting
2. Verify your database connection and permissions
3. Ensure both old and new databases are properly imported
4. Check error messages for specific table/column issues

## File Sizes

- `my_gamez_old.sql`: ~48MB (637,996 lines)
- `my_gamez_new.sql`: ~27KB (947 lines)
- Migration script: ~15KB

## Next Steps After Migration

1. ✅ Verify data counts match
2. ✅ Test application functionality
3. ✅ Check foreign key relationships
4. ✅ Verify user authentication works
5. ✅ Test all CRUD operations
6. ✅ Clean up temporary databases/tables

---

**Created**: January 2026  
**Purpose**: Migrate from old database structure to new Laravel-based structure

