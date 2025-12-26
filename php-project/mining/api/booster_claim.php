<?php
/**
 * Booster Claim API
 * Claims a 2x booster that lasts exactly 1 hour
 * Backend enforces expiry and prevents reuse until expired
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

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$email = mysqli_real_escape_string($conn, trim($data['email']));
$password = mysqli_real_escape_string($conn, trim($data['password']));

// Validate user
$sql = "SELECT id FROM users WHERE email = ? AND password = ? AND account_status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials or account not active']);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];

// Check for active booster
$activeSql = "SELECT id, expires_at FROM user_boosters 
              WHERE user_id = ? AND is_active = 1 AND expires_at > NOW() 
              ORDER BY created_at DESC LIMIT 1";
$activeStmt = $conn->prepare($activeSql);
$activeStmt->bind_param('i', $userId);
$activeStmt->execute();
$activeResult = $activeStmt->get_result();

if ($activeResult->num_rows > 0) {
    $activeBooster = $activeResult->fetch_assoc();
    $expiresAt = new DateTime($activeBooster['expires_at']);
    $now = new DateTime();
    $secondsRemaining = $expiresAt->getTimestamp() - $now->getTimestamp();
    
    echo json_encode([
        'success' => false,
        'message' => 'Booster already active',
        'expires_at' => $activeBooster['expires_at'],
        'seconds_remaining' => $secondsRemaining
    ]);
    exit;
}

// Deactivate any expired boosters
$deactivateSql = "UPDATE user_boosters SET is_active = 0 WHERE user_id = ? AND expires_at <= NOW()";
$deactivateStmt = $conn->prepare($deactivateSql);
$deactivateStmt->bind_param('i', $userId);
$deactivateStmt->execute();

// Create new booster (2x, 1 hour duration)
$now = new DateTime();
$expiresAt = clone $now;
$expiresAt->modify('+1 hour');

$insertSql = "INSERT INTO user_boosters (user_id, booster_type, started_at, expires_at, is_active, created_at) 
              VALUES (?, '2x', NOW(), ?, 1, NOW())";
$insertStmt = $conn->prepare($insertSql);
$expiresAtStr = $expiresAt->format('Y-m-d H:i:s');
$insertStmt->bind_param('is', $userId, $expiresAtStr);

if ($insertStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Booster activated successfully',
        'booster_type' => '2x',
        'expires_at' => $expiresAtStr,
        'duration_seconds' => 3600
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error activating booster: ' . $conn->error]);
}

$conn->close();
?>


