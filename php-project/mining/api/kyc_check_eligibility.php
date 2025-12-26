<?php
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
$sql = "SELECT id, account_status FROM users WHERE email = ? AND password = ? AND account_status = 'active'";
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

// Get KYC requirements from settings
$settingsSql = "SELECT kyc_mining_sessions, kyc_referrals_required FROM settings LIMIT 1";
$settingsResult = $conn->query($settingsSql);
$settings = $settingsResult->fetch_assoc();

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

// Check if KYC already submitted
$kycSql = "SELECT status FROM kyc_submissions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
$kycStmt = $conn->prepare($kycSql);
$kycStmt->bind_param('i', $userId);
$kycStmt->execute();
$kycResult = $kycStmt->get_result();
$kycData = $kycResult->fetch_assoc();
$kycStatus = $kycData ? $kycData['status'] : null;

$isEligible = ($miningSessions >= $miningSessionsRequired) && ($referrals >= $referralsRequired);
$canSubmit = $isEligible && ($kycStatus === null || $kycStatus === 'rejected');

echo json_encode([
    'success' => true,
    'data' => [
        'mining_sessions' => $miningSessions,
        'mining_sessions_required' => $miningSessionsRequired,
        'referrals' => $referrals,
        'referrals_required' => $referralsRequired,
        'is_eligible' => $isEligible,
        'can_submit' => $canSubmit,
        'kyc_status' => $kycStatus,
        'mining_progress' => $miningSessions . '/' . $miningSessionsRequired,
        'referrals_progress' => $referrals . '/' . $referralsRequired
    ]
]);

$conn->close();
?>


