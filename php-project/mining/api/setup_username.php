<?php

// Connect to database
require '../config/dbh.inc.php';

// Decode JSON
$data = json_decode(file_get_contents("php://input"));

// Get user input
$username = $data->username;
$email = $data->email;
$password = $data->password;

// Validate username
if (!preg_match('/^[a-zA-Z0-9]{4,14}$/', $username)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid username']));
}

// Check user exists and account is active
$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND account_status = 'active'";
$user = mysqli_fetch_assoc(mysqli_query($conn, $sql));
if (!$user) {
    exit(json_encode(['success' => false, 'message' => 'Invalid credentials or inactive account']));
}

// Check if username can be changed
if ($user['username_count'] > 0) {
    exit(json_encode(['success' => false, 'message' => 'Username cannot be changed']));
}

// Check if username already exists
$sql = "SELECT * FROM users WHERE username = '$username'";
$existingUser = mysqli_fetch_assoc(mysqli_query($conn, $sql));
if ($existingUser) {
    exit(json_encode(['success' => false, 'message' => 'Username already exists']));
}

// Update username
$sql = "UPDATE users SET username = '$username', username_count = 1 WHERE id = " . $user['id'];
mysqli_query($conn, $sql);

exit(json_encode(['success' => true, 'message' => 'Username updated successfully']));

?>