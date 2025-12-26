<?php
/**
 * Admin API - Reset Mystery Box Data for Users
 * Allows admin to reset mystery box claims, clicks, and progress for specific users
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../../config/dbh.inc.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// POST - Reset mystery box data for a user
if ($method === 'POST') {
    if (empty($input['user_identifier'])) {
        echo json_encode(['success' => false, 'message' => 'User identifier is required.']);
        exit;
    }
    
    $identifier = mysqli_real_escape_string($conn, trim($input['user_identifier']));
    $boxType = isset($input['box_type']) ? mysqli_real_escape_string($conn, trim($input['box_type'])) : null;
    
    // Validate box type if provided
    if ($boxType && !in_array($boxType, ['common', 'rare', 'epic', 'legendary', 'all'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid box type. Use: common, rare, epic, legendary, or all']);
        exit;
    }
    
    // Find user by email, username, or ID
    $query = "SELECT id, email, username FROM users WHERE (email = '$identifier' OR username = '$identifier' OR id = '$identifier') AND account_status = 'active'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found or account is not active.']);
        exit;
    }
    
    $user = mysqli_fetch_assoc($result);
    $userId = $user['id'];
    
    // Reset mystery box data
    if ($boxType === 'all' || !$boxType) {
        // Reset all mystery box claims for this user
        $resetQuery = "DELETE FROM mystery_box_claims WHERE user_id = $userId";
        $affectedRows = 0;
        if (mysqli_query($conn, $resetQuery)) {
            $affectedRows = mysqli_affected_rows($conn);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'All mystery box data reset successfully for user.',
            'user_id' => $userId,
            'user_email' => $user['email'],
            'user_username' => $user['username'],
            'affected_records' => $affectedRows,
            'reset_type' => 'all'
        ]);
    } else {
        // Reset specific box type
        $resetQuery = "DELETE FROM mystery_box_claims WHERE user_id = $userId AND box_type = '$boxType'";
        $affectedRows = 0;
        if (mysqli_query($conn, $resetQuery)) {
            $affectedRows = mysqli_affected_rows($conn);
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Mystery box data for '$boxType' reset successfully for user.",
            'user_id' => $userId,
            'user_email' => $user['email'],
            'user_username' => $user['username'],
            'box_type' => $boxType,
            'affected_records' => $affectedRows,
            'reset_type' => 'specific'
        ]);
    }
    exit;
}

// GET - Get mystery box data for a user
if ($method === 'GET') {
    if (empty($_GET['user_identifier'])) {
        echo json_encode(['success' => false, 'message' => 'User identifier is required.']);
        exit;
    }
    
    $identifier = mysqli_real_escape_string($conn, trim($_GET['user_identifier']));
    
    // Find user
    $query = "SELECT id, email, username FROM users WHERE (email = '$identifier' OR username = '$identifier' OR id = '$identifier') AND account_status = 'active'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found or account is not active.']);
        exit;
    }
    
    $user = mysqli_fetch_assoc($result);
    $userId = $user['id'];
    
    // Get mystery box data
    // First check if clicks column exists
    $checkClicksColumn = mysqli_query($conn, "SHOW COLUMNS FROM mystery_box_claims LIKE 'clicks'");
    $clicksColumnExists = $checkClicksColumn && mysqli_num_rows($checkClicksColumn) > 0;
    
    // Build query based on available columns
    if ($clicksColumnExists) {
        $boxQuery = "SELECT box_type, clicks, ads_watched, ads_required, box_opened, reward_coins, 
                            last_clicked_at, last_ad_watched_at, cooldown_until, opened_at, created_at
                     FROM mystery_box_claims 
                     WHERE user_id = $userId 
                     ORDER BY box_type, created_at DESC";
    } else {
        $boxQuery = "SELECT box_type, ads_watched, ads_required, box_opened, reward_coins, 
                            last_ad_watched_at, cooldown_until, opened_at, created_at
                     FROM mystery_box_claims 
                     WHERE user_id = $userId 
                     ORDER BY box_type, created_at DESC";
    }
    
    $boxResult = mysqli_query($conn, $boxQuery);
    
    $mysteryBoxData = [];
    if ($boxResult && mysqli_num_rows($boxResult) > 0) {
        while ($row = mysqli_fetch_assoc($boxResult)) {
            $mysteryBoxData[] = [
                'box_type' => $row['box_type'],
                'clicks' => ($clicksColumnExists && isset($row['clicks'])) ? (int)$row['clicks'] : 0,
                'ads_watched' => (int)$row['ads_watched'],
                'ads_required' => (int)$row['ads_required'],
                'box_opened' => (bool)$row['box_opened'],
                'reward_coins' => $row['reward_coins'] ? (float)$row['reward_coins'] : null,
                'last_clicked_at' => ($clicksColumnExists && isset($row['last_clicked_at'])) ? $row['last_clicked_at'] : null,
                'last_ad_watched_at' => $row['last_ad_watched_at'],
                'cooldown_until' => $row['cooldown_until'],
                'opened_at' => $row['opened_at'],
                'created_at' => $row['created_at']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'user_id' => $userId,
        'user_email' => $user['email'],
        'user_username' => $user['username'],
        'mystery_box_data' => $mysteryBoxData
    ]);
    exit;
}

mysqli_close($conn);
?>

