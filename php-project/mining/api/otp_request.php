<?php
// Disable error display to prevent output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode(array(
        'success' => $success,
        'message' => $message
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Connect to database
    require '../config/dbh.inc.php';

    // Read input from POST request
    $inputData = file_get_contents('php://input');
    
    if (empty($inputData)) {
        sendJsonResponse(false, 'No data received.', 400);
    }
    
    $input = json_decode($inputData, true);
    
    // Check if JSON decode was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonResponse(false, 'Invalid JSON format: ' . json_last_error_msg(), 400);
    }

    // Validate input
    if (empty($input) || !isset($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(false, 'Email is not valid. Please check your email address.', 400);
    }

    $email = trim($input['email']);

    // Check if email exists and account status is active
    $query = "SELECT * FROM users WHERE email = ? AND account_status = 'active'";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        sendJsonResponse(false, 'Database query preparation failed.', 500);
    }
    
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendJsonResponse(false, 'Email not found or account is not active.', 404);
    }

    // Generate random 6-digit OTP
    $otp = mt_rand(100000, 999999);

    // Update OTP in the database
    $updateQuery = "UPDATE users SET otp = ? WHERE email = ?";
    $updateStmt = $conn->prepare($updateQuery);
    
    if (!$updateStmt) {
        sendJsonResponse(false, 'Database update preparation failed.', 500);
    }
    
    $updateStmt->bind_param('is', $otp, $email);
    $updateStmt->execute();

    // OTP code and recipient email
    $otpCode = $otp;
    $recipient = $email;

    // Prepare POST data
    $postData = array(
        'code' => $otpCode,
        'recipient' => $recipient
    );

    // Initialize cURL session
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://crutox.com/sendmail/forget_password.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    // Check for cURL errors
    if ($response === false || !empty($curlError)) {
        curl_close($ch);
        // Still return success since OTP is saved in database
        // The email service might be temporarily unavailable
        sendJsonResponse(true, 'OTP generated successfully. Email service may be temporarily unavailable.');
    }

    // Close cURL session
    curl_close($ch);

    // Process the response from the external API
    $responseData = json_decode($response, true);

    if (isset($responseData['status']) && $responseData['status'] === 'success') {
        sendJsonResponse(true, 'OTP sent successfully.');
    } else {
        // If external API fails, but OTP is saved, still return success
        // The user can still verify with the OTP in database
        sendJsonResponse(true, 'OTP generated successfully. Please check your email.');
    }

} catch (Exception $e) {
    sendJsonResponse(false, 'An error occurred: ' . $e->getMessage(), 500);
} catch (Error $e) {
    sendJsonResponse(false, 'A fatal error occurred: ' . $e->getMessage(), 500);
}

?>