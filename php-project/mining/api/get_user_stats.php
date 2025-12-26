<?php
/**
 * Get User Stats Endpoint
 * 
 * Returns user's mining sessions and referrals count by email.
 * 
 * Endpoint: POST /mining/api/get_user_stats.php
 * 
 * Request Body (JSON):
 * {
 *   "email": "user@example.com"
 * }
 * 
 * Response:
 * {
 *   "success": true,
 *   "data": {
 *     "email": "user@example.com",
 *     "user_id": 123,
 *     "mining_sessions": 9,
 *     "referrals": 5
 *   }
 * }
 */

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

// Function to send JSON response
function sendJsonResponse($success, $data = null, $message = '', $httpCode = 200) {
    http_response_code($httpCode);
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Connect to database
    require '../config/dbh.inc.php';

    // Get input data
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);
    
    // Support both POST body and GET parameter
    if (empty($data) && isset($_GET['email'])) {
        $email = trim($_GET['email']);
    } else if (isset($data['email'])) {
        $email = trim($data['email']);
    } else {
        sendJsonResponse(false, null, 'Email is required', 400);
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(false, null, 'Valid email is required', 400);
    }

    $email = mysqli_real_escape_string($conn, $email);

    // Get user information
    $sql = "SELECT id, email, total_invite FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        sendJsonResponse(false, null, 'User not found', 404);
    }

    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $referrals = (int)$user['total_invite'];

    // Get user's mining sessions from user_levels table
    $miningSql = "SELECT mining_session FROM user_levels WHERE user_id = ?";
    $miningStmt = $conn->prepare($miningSql);
    $miningStmt->bind_param('i', $userId);
    $miningStmt->execute();
    $miningResult = $miningStmt->get_result();
    $miningData = $miningResult->fetch_assoc();
    $miningSessions = $miningData ? (int)$miningData['mining_session'] : 0;

    // Prepare response data
    $responseData = [
        'email' => $user['email'],
        'user_id' => $userId,
        'mining_sessions' => $miningSessions,
        'referrals' => $referrals
    ];

    sendJsonResponse(true, $responseData, 'User stats retrieved successfully');

} catch (Exception $e) {
    sendJsonResponse(false, null, 'An error occurred: ' . $e->getMessage(), 500);
} catch (Error $e) {
    sendJsonResponse(false, null, 'A fatal error occurred: ' . $e->getMessage(), 500);
}

// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>

