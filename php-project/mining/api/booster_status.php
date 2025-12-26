<?php
/**
 * Booster Status API
 * Returns current active booster status for user
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

// Get active booster
$boosterSql = "SELECT id, booster_type, started_at, expires_at FROM user_boosters 
               WHERE user_id = ? AND is_active = 1 AND expires_at > NOW() 
               ORDER BY created_at DESC LIMIT 1";
$boosterStmt = $conn->prepare($boosterSql);
$boosterStmt->bind_param('i', $userId);
$boosterStmt->execute();
$boosterResult = $boosterStmt->get_result();

if ($boosterResult->num_rows > 0) {
    $booster = $boosterResult->fetch_assoc();
    $now = new DateTime();
    $expiresAt = new DateTime($booster['expires_at']);
    $secondsRemaining = $expiresAt->getTimestamp() - $now->getTimestamp();
    
    echo json_encode([
        'success' => true,
        'has_active_booster' => true,
        'booster_type' => $booster['booster_type'],
        'started_at' => $booster['started_at'],
        'expires_at' => $booster['expires_at'],
        'seconds_remaining' => $secondsRemaining
    ]);
} else {
    // Deactivate any expired boosters
    $deactivateSql = "UPDATE user_boosters SET is_active = 0 WHERE user_id = ? AND expires_at <= NOW()";
    $deactivateStmt = $conn->prepare($deactivateSql);
    $deactivateStmt->bind_param('i', $userId);
    $deactivateStmt->execute();
    
    echo json_encode([
        'success' => true,
        'has_active_booster' => false
    ]);
}

$conn->close();
?>


