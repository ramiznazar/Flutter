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

// Include the database connection file
require '../config/dbh.inc.php';

// Initialize the response array
$response = array('success' => false, 'message' => 'Missing required parameters.');

// Decode the JSON data into PHP array
$input = file_get_contents('php://input');
$requestData = json_decode($input, true);

// Check if email, old_password, and new_password are present in the JSON data
if (isset($requestData['email']) && isset($requestData['old_password']) && isset($requestData['new_password'])) {
    // Sanitize and validate the input
    $email = trim($requestData['email']);
    $oldPassword = trim($requestData['old_password']);
    $newPassword = trim($requestData['new_password']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format.';
        echo json_encode($response);
        exit;
    }

    // Check if the old password matches the one in the database
    $sql = "SELECT id, password FROM users WHERE email = ? AND account_status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $userId = $row['id'];
        $storedPassword = $row['password'];
        
        if ($oldPassword === $storedPassword) {
            // Check if the new password length is greater than 8 characters
            if (strlen($newPassword) >= 8) {
                // Update the password in the database
                // $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateSql = "UPDATE users SET password = ? WHERE id = ? AND account_status = 'active'";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param('si', $newPassword, $userId);
                
                if ($updateStmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Password updated successfully.';
                } else {
                    $response['message'] = 'Failed to update password. Please try again.';
                }
            } else {
                $response['message'] = 'New password must be at least 8 characters long.';
            }
        } else {
            $response['message'] = 'Old password is incorrect.';
        }
    } else {
        $response['message'] = 'User not found or account is not active.';
    }
}

// Close the database connection
$conn->close();

// Encode the response as JSON and return it
echo json_encode($response);
?>
