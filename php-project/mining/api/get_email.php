<?php
// Connect to database
require '../config/dbh.inc.php';

// Check if email and otp are set in GET parameters
if (!isset($_GET['email']) || !isset($_GET['otp'])) {
    echo json_encode(array('success' => false, 'message' => 'Email or OTP not provided.'));
    exit();
}

// Retrieve and sanitize the GET parameters
$email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
$otp = filter_var($_GET['otp'], FILTER_SANITIZE_NUMBER_INT);

// Validate the email and OTP
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array('success' => false, 'message' => 'Invalid email format.'));
    exit();
}

if (!is_numeric($otp)) {
    echo json_encode(array('success' => false, 'message' => 'Invalid OTP format.'));
    exit();
}

// Check if email exists and account status is active
$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(array('success' => false, 'message' => 'Email not found or account is not verified.'));
    exit();
}

// Update OTP in the database
$updateQuery = "UPDATE users SET otp = ? WHERE email = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param('is', $otp, $email);
$updateStmt->execute();

if ($updateStmt->affected_rows > 0) {
    echo json_encode(array('success' => true, 'message' => 'OTP updated successfully.'));
} else {
    echo json_encode(array('success' => false, 'message' => 'Failed to update OTP.'));
}
