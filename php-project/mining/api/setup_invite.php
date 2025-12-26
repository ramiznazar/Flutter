<?php

// Connect to database
require '../config/dbh.inc.php';

// Get request body
$requestBody = file_get_contents('php://input');

// Decode JSON
$data = json_decode($requestBody, true);

$email = $data['email'];
$password = $data['password'];
$username = $data['username'];
$reason = $data['reason'];

// Validate email and password
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array('success' => false, 'message' => 'Invalid email.'));

    // Close database connection
    $conn->close();

    exit;
}

if (strlen($password) < 8) {
    echo json_encode(array('success' => false, 'message' => 'Password must be at least 8 characters long.'));

    // Close database connection
    $conn->close();

    exit;
}

// Check if account is active
$sql = "SELECT total_invite FROM users WHERE email='$email' AND password='$password' AND account_status = 'active' AND invite_setup = 'not_setup'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo json_encode(
        array(
        'success' => false, 
        'message' => "Email or password is incorrect or account is not active or invite is not eligible for you."
        ));

    // Close database connection
    $conn->close();

    exit;
}

$row = $result->fetch_assoc();

if ($reason == "invite") {
    // Check if username already exists
    $sql = "SELECT id, total_invite FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row["id"];

        $total_invite = $row["total_invite"];

        $total_invite = $total_invite + 1;

        // Check if the user is not trying to use their own referral code
        $sql = "SELECT id FROM users WHERE email='$email' AND password='$password' AND id='$user_id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo json_encode(array('success' => false, 'message' => 'You cannot use your own referral code.'));

            // Close database connection
            $conn->close();

            exit;
        }

        // Update invite_setup column with user_id
        $sql = "UPDATE users SET invite_setup='$user_id' WHERE email='$email' AND password='$password'";
        $conn->query($sql);
        
        $reward = 0.5;
        $sql = "UPDATE users SET total_invite='$total_invite', token = token + '$reward' WHERE username='$username'";
        $conn->query($sql);
    } else {
        // Show message username not found.
        echo json_encode(array('success' => false, 'message' => 'Username not found.'));

        // Close database connection
        $conn->close();

        exit;
    }
} else {
    // Update invite_setup column with 'skip'
    $sql = "UPDATE users SET invite_setup='skip' WHERE email='$email' AND password='$password'";
    $conn->query($sql);
}

echo json_encode(array('success' => true, 'message' => 'Username successfully setup.'));

// Close database connection
$conn->close();

?>