<?php
// Connect to the database
require '../config/dbh.inc.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Extract parameters
$email = $data['email'];
$code = $data['code'];

// Initialize response
$response = array('success' => false, 'message' => '');

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Email address is not valid. Please re-check your email and try again';
    echo json_encode($response);
    exit;
}

// Check if email exists in the database and account status is 'active'
$sql = "SELECT * FROM users WHERE email = ? AND account_status = 'unverified'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response['message'] = 'Email not found or account is already verified.';
    echo json_encode($response);
    exit;
}

$userData = $result->fetch_assoc();

// Fetch OTP from database and compare with provided code
$storedOtp = $userData['otp'];

if ($storedOtp !== $code) {
    $response['message'] = 'You entered an invalid OTP. Please check your email for a valid OTP code.';
    echo json_encode($response);
    exit;
}

// Update 'account_status' to 'active'
$updateSql = "UPDATE users SET account_status = 'active' WHERE email = ?";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param('s', $email);
$updateStmt->execute();

$response['success'] = true;
$response['message'] = 'OTP verified. Account activated.';
echo json_encode($response);
    
?>
