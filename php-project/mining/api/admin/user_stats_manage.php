<?php
/**
 * User Stats Management API
 * 
 * Admin endpoint to get and update user mining sessions and referrals.
 * 
 * GET: Get user stats by email
 * POST: Update user stats
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

require '../../config/dbh.inc.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// GET - Get user stats
if ($method === 'GET') {
    $email = isset($_GET['email']) ? mysqli_real_escape_string($conn, trim($_GET['email'])) : '';
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }
    
    // Get user information
    $sql = "SELECT id, email, total_invite FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $referrals = (int)$user['total_invite'];
    
    // Get mining sessions
    $miningSql = "SELECT mining_session FROM user_levels WHERE user_id = ?";
    $miningStmt = $conn->prepare($miningSql);
    $miningStmt->bind_param('i', $userId);
    $miningStmt->execute();
    $miningResult = $miningStmt->get_result();
    $miningData = $miningResult->fetch_assoc();
    $miningSessions = $miningData ? (int)$miningData['mining_session'] : 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'email' => $user['email'],
            'user_id' => $userId,
            'mining_sessions' => $miningSessions,
            'referrals' => $referrals
        ]
    ]);
    exit;
}

// POST - Update user stats
if ($method === 'POST') {
    if (empty($input['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }
    
    $email = mysqli_real_escape_string($conn, trim($input['email']));
    $miningSessions = isset($input['mining_sessions']) ? (int)$input['mining_sessions'] : null;
    $referrals = isset($input['referrals']) ? (int)$input['referrals'] : null;
    
    // Get user
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    
    $updates = [];
    
    // Update mining sessions
    if ($miningSessions !== null && $miningSessions >= 0) {
        // Check if user_levels record exists
        $checkSql = "SELECT user_id FROM user_levels WHERE user_id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('i', $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $updateMiningSql = "UPDATE user_levels SET mining_session = ? WHERE user_id = ?";
            $updateMiningStmt = $conn->prepare($updateMiningSql);
            $updateMiningStmt->bind_param('ii', $miningSessions, $userId);
            $updateMiningStmt->execute();
        } else {
            // Create user_levels record if it doesn't exist
            $insertMiningSql = "INSERT INTO user_levels (user_id, mining_session, spin_wheel, current_level, achieved_at) VALUES (?, ?, 0, 1, NOW())";
            $insertMiningStmt = $conn->prepare($insertMiningSql);
            $insertMiningStmt->bind_param('ii', $userId, $miningSessions);
            $insertMiningStmt->execute();
        }
        $updates[] = 'mining_sessions';
    }
    
    // Update referrals
    if ($referrals !== null && $referrals >= 0) {
        $updateReferralsSql = "UPDATE users SET total_invite = ? WHERE id = ?";
        $updateReferralsStmt = $conn->prepare($updateReferralsSql);
        $updateReferralsStmt->bind_param('ii', $referrals, $userId);
        $updateReferralsStmt->execute();
        $updates[] = 'referrals';
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No valid updates provided']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User stats updated successfully',
        'updated_fields' => $updates
    ]);
    exit;
}

mysqli_close($conn);
?>

