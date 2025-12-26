<?php
/**
 * Mystery Box Watch Ad API
 * Records when user watches an ad for mystery box
 * Enforces cooldown periods between ads (configurable from admin)
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

// Get box settings from database (cooldown and ads required)
$settingsSql = "SELECT {$boxType}_box_cooldown, {$boxType}_box_ads FROM settings LIMIT 1";
$settingsResult = $conn->query($settingsSql);

if (!$settingsResult || $settingsResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Box settings not found']);
    exit;
}

$settings = $settingsResult->fetch_assoc();
$cooldownMinutes = (int)$settings["{$boxType}_box_cooldown"];
$adsRequired = (int)$settings["{$boxType}_box_ads"];

// Get or create mystery box claim record
$claimSql = "SELECT id, ads_watched, last_ad_watched_at, cooldown_until, box_opened 
             FROM mystery_box_claims 
             WHERE user_id = ? AND box_type = ? AND box_opened = 0 
             ORDER BY created_at DESC LIMIT 1";
$claimStmt = $conn->prepare($claimSql);
$claimStmt->bind_param('is', $userId, $boxType);
$claimStmt->execute();
$claimResult = $claimStmt->get_result();

$now = new DateTime();
$cooldownUntil = null;

if ($claimResult->num_rows > 0) {
    $claim = $claimResult->fetch_assoc();
    $claimId = $claim['id'];
    $adsWatched = (int)$claim['ads_watched'];
    
    // Check cooldown
    if ($claim['cooldown_until']) {
        $cooldownUntil = new DateTime($claim['cooldown_until']);
        if ($now < $cooldownUntil) {
            $secondsRemaining = $cooldownUntil->getTimestamp() - $now->getTimestamp();
            echo json_encode([
                'success' => false,
                'message' => 'Cooldown active. Please wait.',
                'seconds_remaining' => $secondsRemaining,
                'cooldown_until' => $claim['cooldown_until']
            ]);
            exit;
        }
    }
    
    // Check if box already opened
    if ($claim['box_opened']) {
        echo json_encode(['success' => false, 'message' => 'Box already opened']);
        exit;
    }
    
    // Increment ads watched
    $adsWatched++;
    
    // Calculate next cooldown
    $nextCooldown = clone $now;
    if ($cooldownMinutes > 0) {
        $nextCooldown->modify("+{$cooldownMinutes} minutes");
    }
    
    // Update claim record
    $updateSql = "UPDATE mystery_box_claims 
                  SET ads_watched = ?, last_ad_watched_at = NOW(), cooldown_until = ? 
                  WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $cooldownUntilStr = $cooldownMinutes > 0 ? $nextCooldown->format('Y-m-d H:i:s') : null;
    $updateStmt->bind_param('isi', $adsWatched, $cooldownUntilStr, $claimId);
    $updateStmt->execute();
    
} else {
    // Create new claim record
    $nextCooldown = clone $now;
    if ($cooldownMinutes > 0) {
        $nextCooldown->modify("+{$cooldownMinutes} minutes");
    }
    
    $insertSql = "INSERT INTO mystery_box_claims 
                  (user_id, box_type, ads_watched, ads_required, last_ad_watched_at, cooldown_until, created_at) 
                  VALUES (?, ?, 1, ?, NOW(), ?, NOW())";
    $insertStmt = $conn->prepare($insertSql);
    $cooldownUntilStr = $cooldownMinutes > 0 ? $nextCooldown->format('Y-m-d H:i:s') : null;
    $insertStmt->bind_param('siis', $userId, $boxType, $adsRequired, $cooldownUntilStr);
    $insertStmt->execute();
    
    $adsWatched = 1;
}

// Check if all ads watched
$canOpen = ($adsWatched >= $adsRequired);

echo json_encode([
    'success' => true,
    'message' => 'Ad watched successfully',
    'ads_watched' => $adsWatched,
    'ads_required' => $adsRequired,
    'can_open_box' => $canOpen,
    'cooldown_until' => $cooldownMinutes > 0 ? $nextCooldown->format('Y-m-d H:i:s') : null,
    'cooldown_minutes' => $cooldownMinutes
]);

$conn->close();
?>


