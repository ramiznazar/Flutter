<?php

// Connect to database
require '../config/dbh.inc.php';

// Read input from POST request
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array('success' => false, 'message' => 'Email is not valid. Please check your email address.'));
    exit();
}

$email = $input['email'];

// Check if email exists and account status is active
$query = "SELECT * FROM users WHERE email = ? AND account_status = 'unverified'";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(array('success' => false, 'message' => 'Email not found or account is already verified.'));
    exit();
}

// Generate random 6-digit OTP
$otp = mt_rand(100000, 999999);

// Update OTP in the database
$updateQuery = "UPDATE users SET otp = ? WHERE email = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param('is', $otp, $email);
$updateStmt->execute();

// echo json_encode(array('success' => true, 'message' => 'OTP generated successfully.'));

// OTP code and recipient email
$otpCode = $otp; // Assuming $otp contains the generated OTP
$recipient = $email; // Assuming $email contains the recipient's email

// Prepare POST data
$postData = array(
    'code' => $otpCode,
    'recipient' => $recipient
);

// Initialize cURL session
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,'https://crutox.com/sendmail/email_confirm_otp.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Execute cURL request
$response = curl_exec($ch);

// Check for errors
if ($response === false) {
    echo json_encode(array('success' => false, 'message' => 'cURL error: ' . curl_error($ch)));
    exit();
}

// Close cURL session
curl_close($ch);

// Process the response from the external API
$responseData = json_decode($response, true);

if (isset($responseData['status']) && $responseData['status'] === 'success') {
    echo json_encode(array('success' => true, 'message' => 'OTP sent successfully.'));
} else {
    echo json_encode(array('success' => false, 'message' => $response));
}

?>