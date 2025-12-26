<?php
/**
 * Submit KYC Endpoint with Didit ID Verification (Email Only)
 * 
 * Allows user to submit KYC documents (full name, DOB, front/back images)
 * after completing required tasks (14 mining sessions and 10 referrals).
 * Automatically verifies documents using Didit ID Verification API.
 * 
 * Endpoint: POST /mining/api/submit_kyc.php
 * 
 * Request Body (JSON):
 * {
 *   "email": "user@example.com",
 *   "full_name": "John Doe",
 *   "dob": "1990-01-15",
 *   "front_image": "base64_encoded_image_or_data_uri",
 *   "back_image": "base64_encoded_image_or_data_uri"
 * }
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "KYC submitted and verified successfully.",
 *   "data": {
 *     "didit_request_id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
 *     "verification_status": "Approved",
 *     "kyc_status": "pending"
 *   }
 * }
 */

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Didit API Configuration
define('DIDIT_API_KEY', '7wk_58gFnb27uqgApuMlEcpASwUurvX8IP6cKAZc4P4');
define('DIDIT_API_URL', 'https://verification.didit.me/v2/id-verification/');

// Load Guzzle HTTP Client
require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Function to send JSON response
function sendJsonResponse($success, $message = '', $data = null, $httpCode = 200) {
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

// Function to convert base64 image to temporary file
function base64ToTempFile($base64String, $prefix = 'kyc_') {
    // Remove data URI prefix if present
    if (strpos($base64String, ',') !== false) {
        $base64String = explode(',', $base64String)[1];
    }
    
    $imageData = base64_decode($base64String);
    if ($imageData === false) {
        return false;
    }
    
    $tempFile = tempnam(sys_get_temp_dir(), $prefix);
    file_put_contents($tempFile, $imageData);
    return $tempFile;
}

// Function to verify ID with Didit API
function verifyWithDidit($frontImagePath, $backImagePath, $userEmail) {
    try {
        $client = new Client([
            'timeout' => 30.0,
            'verify' => true
        ]);
        
        $multipart = [
            [
                'name' => 'front_image',
                'contents' => fopen($frontImagePath, 'r'),
                'filename' => 'front.jpg'
            ],
            [
                'name' => 'back_image',
                'contents' => fopen($backImagePath, 'r'),
                'filename' => 'back.jpg'
            ],
            [
                'name' => 'vendor_data',
                'contents' => $userEmail
            ],
            [
                'name' => 'perform_document_liveness',
                'contents' => 'true'
            ],
            [
                'name' => 'expiration_date_not_detected_action',
                'contents' => 'DECLINE'
            ],
            [
                'name' => 'invalid_mrz_action',
                'contents' => 'DECLINE'
            ],
            [
                'name' => 'inconsistent_data_action',
                'contents' => 'DECLINE'
            ]
        ];
        
        $response = $client->request('POST', DIDIT_API_URL, [
            'headers' => [
                'x-api-key' => DIDIT_API_KEY,
                'accept' => 'application/json'
            ],
            'multipart' => $multipart
        ]);
        
        $responseBody = json_decode($response->getBody()->getContents(), true);
        return [
            'success' => true,
            'data' => $responseBody
        ];
        
    } catch (RequestException $e) {
        $errorResponse = $e->getResponse();
        $errorBody = $errorResponse ? json_decode($errorResponse->getBody()->getContents(), true) : null;
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'details' => $errorBody
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

try {
    // Connect to database
    require '../config/dbh.inc.php';

    // Get input data
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    // Validate email
    if (!isset($data['email']) || empty($data['email'])) {
        sendJsonResponse(false, 'Email is required', null, 400);
    }

    $email = mysqli_real_escape_string($conn, trim($data['email']));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(false, 'Valid email is required', null, 400);
    }

    // Validate user exists and is active
    $sql = "SELECT id FROM users WHERE email = ? AND account_status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        sendJsonResponse(false, 'User not found or account not active', null, 404);
    }

    $user = $result->fetch_assoc();
    $userId = $user['id'];

    // Check eligibility first - Get KYC requirements from settings
    $settingsSql = "SELECT kyc_mining_sessions, kyc_referrals_required FROM settings LIMIT 1";
    $settingsResult = $conn->query($settingsSql);
    $settings = $settingsResult ? $settingsResult->fetch_assoc() : null;

    $miningSessionsRequired = isset($settings['kyc_mining_sessions']) ? (int)$settings['kyc_mining_sessions'] : 14;
    $referralsRequired = isset($settings['kyc_referrals_required']) ? (int)$settings['kyc_referrals_required'] : 10;

    // Get user's mining sessions
    $miningSql = "SELECT mining_session FROM user_levels WHERE user_id = ?";
    $miningStmt = $conn->prepare($miningSql);
    $miningStmt->bind_param('i', $userId);
    $miningStmt->execute();
    $miningResult = $miningStmt->get_result();
    $miningData = $miningResult->fetch_assoc();
    $miningSessions = $miningData ? (int)$miningData['mining_session'] : 0;

    // Get user's referrals
    $referralsSql = "SELECT total_invite FROM users WHERE id = ?";
    $referralsStmt = $conn->prepare($referralsSql);
    $referralsStmt->bind_param('i', $userId);
    $referralsStmt->execute();
    $referralsResult = $referralsStmt->get_result();
    $referralsData = $referralsResult->fetch_assoc();
    $referrals = $referralsData ? (int)$referralsData['total_invite'] : 0;

    // Check if user has completed required tasks
    if ($miningSessions < $miningSessionsRequired || $referrals < $referralsRequired) {
        $message = "You have not completed the required tasks. ";
        $message .= "Mining Sessions: $miningSessions/$miningSessionsRequired, ";
        $message .= "Referrals: $referrals/$referralsRequired";
        sendJsonResponse(false, $message, null, 400);
    }

    // Check if already submitted and pending/approved
    $existingSql = "SELECT id, status FROM kyc_submissions WHERE user_id = ? AND status IN ('pending', 'approved') ORDER BY created_at DESC LIMIT 1";
    $existingStmt = $conn->prepare($existingSql);
    $existingStmt->bind_param('i', $userId);
    $existingStmt->execute();
    $existingResult = $existingStmt->get_result();

    if ($existingResult->num_rows > 0) {
        $existing = $existingResult->fetch_assoc();
        sendJsonResponse(false, 'KYC already submitted and is ' . $existing['status'], null, 400);
    }

    // Validate required KYC fields
    if (!isset($data['full_name']) || empty(trim($data['full_name']))) {
        sendJsonResponse(false, 'Full name is required', null, 400);
    }

    if (!isset($data['dob']) || empty(trim($data['dob']))) {
        sendJsonResponse(false, 'Date of birth is required', null, 400);
    }

    if (!isset($data['front_image']) || empty(trim($data['front_image']))) {
        sendJsonResponse(false, 'Front image is required', null, 400);
    }

    if (!isset($data['back_image']) || empty(trim($data['back_image']))) {
        sendJsonResponse(false, 'Back image is required', null, 400);
    }

    // Validate date format (YYYY-MM-DD)
    $dob = trim($data['dob']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        sendJsonResponse(false, 'Date of birth must be in YYYY-MM-DD format', null, 400);
    }

    $fullName = mysqli_real_escape_string($conn, trim($data['full_name']));
    $frontImage = mysqli_real_escape_string($conn, trim($data['front_image']));
    $backImage = mysqli_real_escape_string($conn, trim($data['back_image']));

    // Convert base64 images to temporary files for Didit API
    $frontImagePath = base64ToTempFile($frontImage, 'kyc_front_');
    $backImagePath = base64ToTempFile($backImage, 'kyc_back_');
    
    if (!$frontImagePath || !$backImagePath) {
        sendJsonResponse(false, 'Invalid image format. Please provide valid base64 encoded images.', null, 400);
    }

    // Verify ID with Didit API (using email as vendor_data to match with user)
    $diditResult = verifyWithDidit($frontImagePath, $backImagePath, $email);
    
    // Clean up temporary files
    @unlink($frontImagePath);
    @unlink($backImagePath);
    
    $diditRequestId = null;
    $diditStatus = null;
    $diditVerificationData = null;
    $kycStatus = 'pending';
    
    if ($diditResult['success'] && isset($diditResult['data'])) {
        $diditData = $diditResult['data'];
        $diditRequestId = isset($diditData['request_id']) ? $diditData['request_id'] : null;
        
        if (isset($diditData['id_verification'])) {
            $idVerification = $diditData['id_verification'];
            $diditStatus = isset($idVerification['status']) ? $idVerification['status'] : null;
            $diditVerificationData = json_encode($diditData, JSON_UNESCAPED_UNICODE);
            
            // If Didit approves, set status to pending (admin can still review)
            // If Didit declines, set status to pending but admin will see the decline reason
            // Admin can override the status later
        }
    } else {
        // If Didit verification fails, still save the submission but mark it
        $diditVerificationData = json_encode([
            'error' => $diditResult['error'] ?? 'Verification failed',
            'details' => $diditResult['details'] ?? null
        ], JSON_UNESCAPED_UNICODE);
    }

    // Insert KYC submission with Didit verification data
    // Check if didit columns exist, if not use basic insert
    $checkColumns = "SHOW COLUMNS FROM kyc_submissions LIKE 'didit_request_id'";
    $columnCheck = $conn->query($checkColumns);
    
    if ($columnCheck && $columnCheck->num_rows > 0) {
        // Columns exist, use full insert
        $insertSql = "INSERT INTO kyc_submissions (user_id, full_name, dob, front_image, back_image, status, didit_request_id, didit_status, didit_verification_data, didit_verified_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param('issssssss', $userId, $fullName, $dob, $frontImage, $backImage, $kycStatus, $diditRequestId, $diditStatus, $diditVerificationData);
    } else {
        // Columns don't exist yet, use basic insert (run migration first)
        $insertSql = "INSERT INTO kyc_submissions (user_id, full_name, dob, front_image, back_image, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param('issss', $userId, $fullName, $dob, $frontImage, $backImage);
    }

    if ($insertStmt->execute()) {
        $responseData = [
            'kyc_status' => $kycStatus
        ];
        
        if ($diditRequestId) {
            $responseData['didit_request_id'] = $diditRequestId;
            $responseData['verification_status'] = $diditStatus ?? 'Pending';
        }
        
        $message = 'KYC submitted successfully.';
        if ($diditStatus === 'Approved') {
            $message .= ' Document verified by Didit.';
        } else if ($diditStatus === 'Declined') {
            $message .= ' Document verification declined. Admin will review.';
        } else {
            $message .= ' Verification in progress.';
        }
        
        sendJsonResponse(true, $message, $responseData);
    } else {
        sendJsonResponse(false, 'Error submitting KYC: ' . $conn->error, null, 500);
    }

} catch (Exception $e) {
    sendJsonResponse(false, 'An error occurred: ' . $e->getMessage(), null, 500);
} catch (Error $e) {
    sendJsonResponse(false, 'A fatal error occurred: ' . $e->getMessage(), null, 500);
}

// Close the database connection
if (isset($conn)) {
    $conn->close();
}
?>

