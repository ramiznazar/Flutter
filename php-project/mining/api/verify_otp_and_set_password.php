<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Connect to the database
require '../config/dbh.inc.php';

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Initialize response
$response = array('success' => false, 'message' => '');

// Validate input data exists
if(empty($data) || !isset($data['email']) || !isset($data['reason'])){
    $response['message'] = 'Missing required parameters.';
    echo json_encode($response);
    exit;
}

// Extract parameters
$email = isset($data['email']) ? trim($data['email']) : '';
$reason = isset($data['reason']) ? trim($data['reason']) : '';
$code = isset($data['code']) ? trim($data['code']) : '';
$newPassword = isset($data['new_password']) ? trim($data['new_password']) : '';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Email address is not valid. Please re-check your email and try again';
    echo json_encode($response);
    exit;
}

// Check if email exists in the database and account status is 'active'
$sql = "SELECT * FROM users WHERE email = ? AND account_status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response['message'] = 'Email not found or account not active.';
    echo json_encode($response);
    exit;
}

$userData = $result->fetch_assoc();

// If 'reason' is 'get', check OTP
if ($reason === 'get') {
    // Validate code is provided
    if(empty($code)){
        $response['message'] = 'OTP code is required.';
        echo json_encode($response);
        exit;
    }
    
    // Fetch OTP from database and compare with provided code
    $storedOtp = $userData['otp'];

    if (empty($storedOtp) || $storedOtp !== $code) {
        $response['message'] = 'You entered an invalid OTP. Please check your email for a valid OTP code.';
        echo json_encode($response);
        exit;
    }

    $response['success'] = true;
    $response['message'] = 'OTP verified.';
    echo json_encode($response);
    exit;
}

// If 'reason' is 'set', check OTP and update password
if ($reason === 'set') {
    // Validate required fields
    if(empty($code) || empty($newPassword)){
        $response['message'] = 'OTP code and new password are required.';
        echo json_encode($response);
        exit;
    }
    
    // Fetch OTP from database and compare with provided code
    $storedOtp = $userData['otp'];

    if (empty($storedOtp) || $storedOtp !== $code) {
        $response['message'] = 'Invalid or expired OTP. Please request a new OTP.';
        echo json_encode($response);
        exit;
    }

    // Validate password length
    if (strlen($newPassword) < 8) {
        $response['message'] = 'Password must be at least 8 characters long.';
        echo json_encode($response);
        exit;
    }

    $empty = "";

    // Update password in the database
    // $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateSql = "UPDATE users SET password = ?, otp = ? WHERE email = ? AND account_status = 'active'";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('sss', $newPassword, $empty, $email);

    if ($updateStmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Password updated successfully.';
    } else {
        $response['message'] = 'Password update failed. Please try again.';
    }

    echo json_encode($response);
    exit;
}

// Invalid reason
$response['message'] = 'Invalid reason.';
echo json_encode($response);
?>
