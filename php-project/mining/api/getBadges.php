<?php

// Get request body
$requestBody = file_get_contents('php://input');

// Decode JSON
$data = json_decode($requestBody, true);

// Validate required fields
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(array('success' => false, 'message' => 'Missing required fields'));
    exit;
}

// Connect to the database
require '../config/dbh.inc.php';
require '../api/check_levels.php';

// Validate user account
$email = mysqli_real_escape_string($conn, $data['email']);
$password = mysqli_real_escape_string($conn, $data['password']);

$sql = "SELECT id FROM users WHERE email = '$email' AND password = '$password' AND account_status = 'active'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo json_encode(array('success' => false, 'message' => 'Invalid account'));

    // Close database connection
    mysqli_close($conn);
    
    exit;
}

// Get user's ID
$userRow = mysqli_fetch_assoc($result);
$userId = $userRow['id'];

checkRecord($userId, $conn);

// Call getUserLevel function with the obtained user ID
echo getBadges($userId, $conn);

// Close database connection
mysqli_close($conn);

?>
