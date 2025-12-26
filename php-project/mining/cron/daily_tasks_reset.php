<?php
/**
 * Daily Tasks Reset Cron Job
 * Resets daily tasks every 24 hours
 * Should be run via cron: 0 0 * * * (every day at midnight)
 * Or manually triggered
 */
require '../config/dbh.inc.php';

// Get current reset time from settings
$settingsSql = "SELECT daily_tasks_reset_time FROM settings LIMIT 1";
$settingsResult = $conn->query($settingsSql);
$lastResetTime = null;

if ($settingsResult && $settingsResult->num_rows > 0) {
    $settingsRow = $settingsResult->fetch_assoc();
    $lastResetTime = $settingsRow['daily_tasks_reset_time'];
}

$now = new DateTime();
$shouldReset = false;

if ($lastResetTime === null) {
    // First time - set reset time to now
    $shouldReset = true;
} else {
    $lastReset = new DateTime($lastResetTime);
    $hoursSinceReset = ($now->getTimestamp() - $lastReset->getTimestamp()) / 3600;
    
    // Reset if 24 hours have passed
    if ($hoursSinceReset >= 24) {
        $shouldReset = true;
    }
}

if ($shouldReset) {
    // Mark all daily task completions as expired (for tracking purposes)
    // This allows users to complete daily tasks again
    $updateSql = "UPDATE task_completions 
                  SET reward_claimed = 1 
                  WHERE task_type = 'daily' AND reward_claimed = 0 
                  AND reward_available_at < NOW()";
    $conn->query($updateSql);
    
    // Update reset time
    $newResetTime = $now->format('Y-m-d H:i:s');
    $checkQuery = "SELECT COUNT(*) as count FROM settings";
    $checkResult = $conn->query($checkQuery);
    $checkRow = $checkResult->fetch_assoc();
    
    if ($checkRow['count'] > 0) {
        $conn->query("UPDATE settings SET daily_tasks_reset_time = '$newResetTime'");
    } else {
        $conn->query("INSERT INTO settings (daily_tasks_reset_time) VALUES ('$newResetTime')");
    }
    
    echo "Daily tasks reset successfully at " . $newResetTime . "\n";
} else {
    $nextReset = new DateTime($lastResetTime);
    $nextReset->modify('+24 hours');
    $hoursUntilReset = ($nextReset->getTimestamp() - $now->getTimestamp()) / 3600;
    echo "Daily tasks reset not needed. Next reset in " . round($hoursUntilReset, 2) . " hours.\n";
}

$conn->close();
?>


