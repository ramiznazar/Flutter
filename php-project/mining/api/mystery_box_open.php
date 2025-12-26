<?php
/**
 * Mystery Box Open API
 * Opens mystery box and gives random reward within min/max range
 * Only works after all required ads are watched
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../config/dbh.inc.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['box_type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$email = mysqli_real_escape_string($conn, trim($data['email']));
$boxType = mysqli_real_escape_string($conn, trim($data['box_type']));

// Validate box type
if (!in_array($boxType, ['common', 'rare', 'epic', 'legendary'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid box type']);
    exit;
}

// Validate user by email only
$sql = "SELECT id FROM users WHERE email = ? AND account_status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(['success' => false, 'message' => 'User not found or account not active']);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];

// Get box settings
$settingsSql = "SELECT {$boxType}_box_ads, {$boxType}_box_min_coins, {$boxType}_box_max_coins 
                FROM settings LIMIT 1";
$settingsResult = $conn->query($settingsSql);

if (!$settingsResult || $settingsResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Box settings not found']);
    exit;
}

$settings = $settingsResult->fetch_assoc();
$adsRequired = (int)$settings["{$boxType}_box_ads"];
$minCoins = (float)$settings["{$boxType}_box_min_coins"];
$maxCoins = (float)$settings["{$boxType}_box_max_coins"];

// Get claim record
$claimSql = "SELECT id, ads_watched, box_opened FROM mystery_box_claims 
             WHERE user_id = ? AND box_type = ? 
             ORDER BY created_at DESC LIMIT 1";
$claimStmt = $conn->prepare($claimSql);
$claimStmt->bind_param('is', $userId, $boxType);
$claimStmt->execute();
$claimResult = $claimStmt->get_result();

if ($claimResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Please watch ads first']);
    exit;
}

$claim = $claimResult->fetch_assoc();

// Check if already opened
if ($claim['box_opened']) {
    echo json_encode(['success' => false, 'message' => 'Box already opened']);
    exit;
}

// Check if all ads watched
if ($claim['ads_watched'] < $adsRequired) {
    echo json_encode([
        'success' => false,
        'message' => 'Not enough ads watched',
        'ads_watched' => $claim['ads_watched'],
        'ads_required' => $adsRequired
    ]);
    exit;
}

// Calculate random reward
$reward = round($minCoins + (mt_rand() / mt_getrandmax()) * ($maxCoins - $minCoins), 2);

$conn->begin_transaction();

try {
    // Mark box as opened
    $updateSql = "UPDATE mystery_box_claims 
                  SET box_opened = 1, reward_coins = ?, opened_at = NOW() 
                  WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('di', $reward, $claim['id']);
    $updateStmt->execute();
    
    // Add reward to user
    $rewardSql = "UPDATE users SET token = token + ? WHERE id = ?";
    $rewardStmt = $conn->prepare($rewardSql);
    $rewardStmt->bind_param('di', $reward, $userId);
    $rewardStmt->execute();
    
    // Get updated balance
    $balanceSql = "SELECT token FROM users WHERE id = ?";
    $balanceStmt = $conn->prepare($balanceSql);
    $balanceStmt->bind_param('i', $userId);
    $balanceStmt->execute();
    $balanceResult = $balanceStmt->get_result();
    $userBalance = $balanceResult->fetch_assoc()['token'];
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Box opened successfully',
        'reward' => $reward,
        'new_balance' => (float)$userBalance,
        'box_type' => $boxType
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error opening box: ' . $e->getMessage()]);
}

$conn->close();
?>


