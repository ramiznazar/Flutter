<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

require '../config/dbh.inc.php';

// Get real user count from users table
$realUsersQuery = 'SELECT COUNT(*) as total_users FROM users';
$realUsersResult = $conn->query($realUsersQuery);
$realUsers = 0;
if ($realUsersResult && $realUsersResult->num_rows > 0) {
    $realUsersRow = $realUsersResult->fetch_assoc();
    $realUsers = intval($realUsersRow['total_users']);
}

// Get the manual/fake user count from settings table
$settingsQuery = "SELECT current_users, goal_users FROM settings LIMIT 1";
$settingsResult = $conn->query($settingsQuery);

$displayUsers = $realUsers; // Default to real users if not set
$goalUsers = 1000000;

if ($settingsResult && $settingsResult->num_rows > 0) {
    $settingsRow = $settingsResult->fetch_assoc();
    // Use manual current_users from settings if available (fake/display users)
    if (isset($settingsRow['current_users']) && $settingsRow['current_users'] !== null) {
        $displayUsers = intval($settingsRow['current_users']);
    }
    // Get goal_users if available
    if (isset($settingsRow['goal_users']) && $settingsRow['goal_users'] !== null) {
        $goalUsers = intval($settingsRow['goal_users']);
    }
}

// Return both real and display (fake) user counts
echo json_encode([
    "total_users" => $displayUsers,  // Display users (fake/manual) - shown in app
    "real_users" => $realUsers,      // Real registered users
    "goal_users" => $goalUsers        // Goal users
]);

// Close the database connection
$conn->close();
?>
