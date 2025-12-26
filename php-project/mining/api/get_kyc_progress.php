<?php
/**
 * Get KYC Progress Endpoint
 * 
 * Returns the user's progress towards KYC eligibility:
 * - Mining sessions completed/required (e.g., 9/14)
 * - Referrals completed/required (e.g., 5/10)
 * - Whether KYC can be submitted
 * 
 * Endpoint: POST /mining/api/get_kyc_progress.php
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
 *     "mining_sessions": 9,
 *     "mining_sessions_required": 14,
 *     "mining_sessions_remaining": 5,
 *     "mining_progress": "9/14",
 *     "referrals": 5,
 *     "referrals_required": 10,
 *     "referrals_remaining": 5,
 *     "referrals_progress": "5/10",
 *     "is_eligible": false,
 *     "can_submit": false,
 *     "kyc_status": null
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

    // Validate user exists and is active
    $sql = "SELECT id, account_status FROM users WHERE email = ? AND account_status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        sendJsonResponse(false, null, 'User not found or account not active', 404);
    }

    $user = $result->fetch_assoc();
    $userId = $user['id'];

    // Get KYC requirements from settings
    $settingsSql = "SELECT kyc_mining_sessions, kyc_referrals_required FROM settings LIMIT 1";
    $settingsResult = $conn->query($settingsSql);
    $settings = $settingsResult ? $settingsResult->fetch_assoc() : null;

    $miningSessionsRequired = isset($settings['kyc_mining_sessions']) ? (int)$settings['kyc_mining_sessions'] : 14;
    $referralsRequired = isset($settings['kyc_referrals_required']) ? (int)$settings['kyc_referrals_required'] : 10;

    // Get user's mining sessions from user_levels table
    $miningSql = "SELECT mining_session FROM user_levels WHERE user_id = ?";
    $miningStmt = $conn->prepare($miningSql);
    $miningStmt->bind_param('i', $userId);
    $miningStmt->execute();
    $miningResult = $miningStmt->get_result();
    $miningData = $miningResult->fetch_assoc();
    $miningSessions = $miningData ? (int)$miningData['mining_session'] : 0;

    // Get user's referrals from users table
    $referralsSql = "SELECT total_invite FROM users WHERE id = ?";
    $referralsStmt = $conn->prepare($referralsSql);
    $referralsStmt->bind_param('i', $userId);
    $referralsStmt->execute();
    $referralsResult = $referralsStmt->get_result();
    $referralsData = $referralsResult->fetch_assoc();
    $referrals = $referralsData ? (int)$referralsData['total_invite'] : 0;

    // Check if KYC already submitted
    $kycSql = "SELECT status FROM kyc_submissions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
    $kycStmt = $conn->prepare($kycSql);
    $kycStmt->bind_param('i', $userId);
    $kycStmt->execute();
    $kycResult = $kycStmt->get_result();
    $kycData = $kycResult->fetch_assoc();
    $kycStatus = $kycData ? $kycData['status'] : null;

    // Calculate remaining sessions/referrals
    $miningSessionsRemaining = max(0, $miningSessionsRequired - $miningSessions);
    $referralsRemaining = max(0, $referralsRequired - $referrals);

    // Check eligibility
    $isEligible = ($miningSessions >= $miningSessionsRequired) && ($referrals >= $referralsRequired);
    $canSubmit = $isEligible && ($kycStatus === null || $kycStatus === 'rejected');

    // Prepare response data
    $responseData = [
        'mining_sessions' => $miningSessions,
        'mining_sessions_required' => $miningSessionsRequired,
        'mining_sessions_remaining' => $miningSessionsRemaining,
        'mining_progress' => $miningSessions . '/' . $miningSessionsRequired,
        'referrals' => $referrals,
        'referrals_required' => $referralsRequired,
        'referrals_remaining' => $referralsRemaining,
        'referrals_progress' => $referrals . '/' . $referralsRequired,
        'is_eligible' => $isEligible,
        'can_submit' => $canSubmit,
        'kyc_status' => $kycStatus
    ];

    sendJsonResponse(true, $responseData, 'KYC progress retrieved successfully');

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

