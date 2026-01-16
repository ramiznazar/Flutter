#!/bin/bash

# ============================================================================
# Complete Database Migration Script
# ============================================================================
# This script automates the entire migration process:
# 1. Backs up current database
# 2. Imports old database
# 3. Renames old tables
# 4. Imports new structure
# 5. Migrates all data
# 6. Verifies migration
# ============================================================================

# Configuration
DB_NAME="my_gamez"
DB_USER="root"
DB_PASS="NEW_STRONG_PASSWORD"  # Update this with your actual password
OLD_SQL_FILE="my_gamez_old.sql"
NEW_SQL_FILE="my_gamez_new.sql"
BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"

# Function to run mysql commands without showing password
mysql_cmd() {
    mysql -u "$DB_USER" -p"$DB_PASS" "$@" 2>&1 | grep -v "Using a password on the command line"
    return ${PIPESTATUS[0]}
}

mysqldump_cmd() {
    mysqldump -u "$DB_USER" -p"$DB_PASS" "$@" 2>&1 | grep -v "Using a password on the command line"
    return ${PIPESTATUS[0]}
}

echo "=========================================="
echo "Database Migration Script"
echo "=========================================="
echo "Database: $DB_NAME"
echo "Old SQL: $OLD_SQL_FILE"
echo "New SQL: $NEW_SQL_FILE"
echo ""

# Step 1: Backup current database
echo "[1/8] Creating backup..."
mysqldump_cmd "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✓ Backup created: $BACKUP_FILE"
else
    echo "⚠ Warning: Backup may have failed (database might not exist yet)"
fi
echo ""

# Step 1.5: Drop existing tables if they exist
echo "[2/8] Dropping existing tables (if any)..."
mysql_cmd "$DB_NAME" < drop_existing_tables.sql
if [ $? -eq 0 ]; then
    echo "✓ Existing tables dropped (or none existed)"
else
    echo "⚠ Warning: Some tables may not have been dropped (this is OK if database is empty)"
fi
echo ""

# Step 2: Import old database
echo "[3/8] Importing old database structure and data..."
echo "   This may take a few minutes (48MB file)..."
mysql_cmd "$DB_NAME" < "$OLD_SQL_FILE"
if [ $? -eq 0 ]; then
    echo "✓ Old database imported successfully"
else
    echo "✗ Error importing old database"
    exit 1
fi
echo ""

# Step 3: Rename old tables
echo "[4/8] Renaming old tables with 'old_' prefix..."
mysql_cmd "$DB_NAME" < rename_old_tables.sql
if [ $? -eq 0 ]; then
    echo "✓ Old tables renamed successfully"
else
    echo "✗ Error renaming tables"
    exit 1
fi
echo ""

# Step 4: Import new database structure
echo "[5/8] Importing new database structure..."
mysql_cmd "$DB_NAME" < "$NEW_SQL_FILE"
if [ $? -eq 0 ]; then
    echo "✓ New database structure imported successfully"
else
    echo "✗ Error importing new database structure"
    exit 1
fi
echo ""

# Step 5: Run migration (using pre-created renamed version)
echo "[6/8] Migrating data from old structure to new structure..."
echo "   This may take a few minutes (migrating all data)..."
mysql_cmd "$DB_NAME" < migrate_old_to_new_renamed.sql
if [ $? -eq 0 ]; then
    echo "✓ Data migration completed successfully"
else
    echo "✗ Error during data migration"
    exit 1
fi
echo ""

# Step 6: Verification
echo "[7/8] Verifying migration..."
mysql_cmd "$DB_NAME" <<EOF 2>/dev/null
SELECT 
    'users' as table_name,
    (SELECT COUNT(*) FROM old_users) as old_count,
    (SELECT COUNT(*) FROM users) as new_count,
    CASE 
        WHEN (SELECT COUNT(*) FROM old_users) = (SELECT COUNT(*) FROM users) 
        THEN '✓' 
        ELSE '✗' 
    END as status
UNION ALL
SELECT 
    'news',
    (SELECT COUNT(*) FROM old_news),
    (SELECT COUNT(*) FROM news),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_news) = (SELECT COUNT(*) FROM news) 
        THEN '✓' 
        ELSE '✗' 
    END
UNION ALL
SELECT 
    'badges',
    (SELECT COUNT(*) FROM old_badges),
    (SELECT COUNT(*) FROM badges),
    CASE 
        WHEN (SELECT COUNT(*) FROM old_badges) = (SELECT COUNT(*) FROM badges) 
        THEN '✓' 
        ELSE '✗' 
    END;
EOF

echo ""
echo "[8/8] Running detailed verification..."
mysql_cmd "$DB_NAME" < verify_migration.sql

echo ""
echo "=========================================="
echo "Migration Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Review the verification results above"
echo "2. Test your application thoroughly"
echo "3. Once verified, you can drop old tables:"
echo "   mysql -u $DB_USER -p'$DB_PASS' $DB_NAME < cleanup_old_tables.sql"
echo ""
echo "Backup saved as: $BACKUP_FILE"
echo ""

