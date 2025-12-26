<?php
/**
 * Run Mystery Box Clicks Migration
 * This script adds the clicks and last_clicked_at columns to mystery_box_claims table
 * 
 * Usage: php run_mystery_box_migration.php
 * Or access via browser: http://your-domain.com/backend/crutox/mining/run_mystery_box_migration.php
 */

require 'config/dbh.inc.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Mystery Box Clicks Migration</h2>";
echo "<pre>";

// Check if columns already exist
$checkClicks = mysqli_query($conn, "SHOW COLUMNS FROM mystery_box_claims LIKE 'clicks'");
$checkLastClicked = mysqli_query($conn, "SHOW COLUMNS FROM mystery_box_claims LIKE 'last_clicked_at'");

$clicksExists = $checkClicks && mysqli_num_rows($checkClicks) > 0;
$lastClickedExists = $checkLastClicked && mysqli_num_rows($checkLastClicked) > 0;

if ($clicksExists && $lastClickedExists) {
    echo "✓ Columns 'clicks' and 'last_clicked_at' already exist.\n";
    echo "Migration not needed.\n";
} else {
    echo "Starting migration...\n\n";
    
    // Add clicks column if it doesn't exist
    if (!$clicksExists) {
        $sql1 = "ALTER TABLE `mystery_box_claims` ADD COLUMN `clicks` INT DEFAULT 0 AFTER `box_type`";
        if (mysqli_query($conn, $sql1)) {
            echo "✓ Successfully added 'clicks' column.\n";
        } else {
            echo "✗ Error adding 'clicks' column: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "✓ Column 'clicks' already exists.\n";
    }
    
    // Add last_clicked_at column if it doesn't exist
    if (!$lastClickedExists) {
        $sql2 = "ALTER TABLE `mystery_box_claims` ADD COLUMN `last_clicked_at` DATETIME NULL AFTER `clicks`";
        if (mysqli_query($conn, $sql2)) {
            echo "✓ Successfully added 'last_clicked_at' column.\n";
        } else {
            echo "✗ Error adding 'last_clicked_at' column: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "✓ Column 'last_clicked_at' already exists.\n";
    }
    
    // Add indexes
    echo "\nAdding indexes...\n";
    
    $index1 = "ALTER TABLE `mystery_box_claims` ADD INDEX `idx_clicks` (`clicks`)";
    $index2 = "ALTER TABLE `mystery_box_claims` ADD INDEX `idx_last_clicked_at` (`last_clicked_at`)";
    
    // Check if indexes exist first
    $checkIndex1 = mysqli_query($conn, "SHOW INDEX FROM mystery_box_claims WHERE Key_name = 'idx_clicks'");
    $checkIndex2 = mysqli_query($conn, "SHOW INDEX FROM mystery_box_claims WHERE Key_name = 'idx_last_clicked_at'");
    
    if (!$checkIndex1 || mysqli_num_rows($checkIndex1) == 0) {
        if (mysqli_query($conn, $index1)) {
            echo "✓ Successfully added index 'idx_clicks'.\n";
        } else {
            echo "✗ Error adding index 'idx_clicks': " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "✓ Index 'idx_clicks' already exists.\n";
    }
    
    if (!$checkIndex2 || mysqli_num_rows($checkIndex2) == 0) {
        if (mysqli_query($conn, $index2)) {
            echo "✓ Successfully added index 'idx_last_clicked_at'.\n";
        } else {
            echo "✗ Error adding index 'idx_last_clicked_at': " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "✓ Index 'idx_last_clicked_at' already exists.\n";
    }
    
    echo "\n✓ Migration completed successfully!\n";
}

echo "</pre>";
echo "<p><a href='admin/users.php'>Go to Users Management</a></p>";

mysqli_close($conn);
?>

