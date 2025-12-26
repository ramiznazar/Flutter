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

// Check eligibility first
$settingsSql = "SELECT kyc_mining_sessions, kyc_referrals_required FROM settings LIMIT 1";
$settingsResult = $conn->query($settingsSql);
$settings = $settingsResult->fetch_assoc();

$miningSessionsRequired = isset($settings['kyc_mining_sessions']) ? (int)$settings['kyc_mining_sessions'] : 14;
$referralsRequired = isset($settings['kyc_referrals_required']) ? (int)$settings['kyc_referrals_required'] : 10;

$miningSql = "SELECT mining_session FROM user_levels WHERE user_id = ?";
$miningStmt = $conn->prepare($miningSql);
$miningStmt->bind_param('i', $userId);
$miningStmt->execute();
$miningResult = $miningStmt->get_result();
$miningData = $miningResult->fetch_assoc();
$miningSessions = $miningData ? (int)$miningData['mining_session'] : 0;

$referralsSql = "SELECT total_invite FROM users WHERE id = ?";
$referralsStmt = $conn->prepare($referralsSql);
$referralsStmt->bind_param('i', $userId);
$referralsStmt->execute();
$referralsResult = $referralsStmt->get_result();
$referralsData = $referralsResult->fetch_assoc();
$referrals = $referralsData ? (int)$referralsData['total_invite'] : 0;

if ($miningSessions < $miningSessionsRequired || $referrals < $referralsRequired) {
    echo json_encode(['success' => false, 'message' => 'You have not completed the required tasks to submit KYC']);
    exit;
}

// Check if already submitted and pending/approved
$existingSql = "SELECT id, status FROM kyc_submissions WHERE user_id = ? AND status IN ('pending', 'approved') ORDER BY created_at DESC LIMIT 1";
$existingStmt = $conn->prepare($existingSql);
$existingStmt->bind_param('i', $userId);
$existingStmt->execute();
$existingResult = $existingStmt->get_result();

if ($existingResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'KYC already submitted and is pending or approved']);
    exit;
}

// Validate required fields
if (!isset($data['full_name']) || !isset($data['dob']) || !isset($data['front_image']) || !isset($data['back_image'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required KYC fields']);
    exit;
}

$fullName = mysqli_real_escape_string($conn, trim($data['full_name']));
$dob = mysqli_real_escape_string($conn, trim($data['dob']));
$frontImage = mysqli_real_escape_string($conn, trim($data['front_image']));
$backImage = mysqli_real_escape_string($conn, trim($data['back_image']));

// Insert KYC submission
$insertSql = "INSERT INTO kyc_submissions (user_id, full_name, dob, front_image, back_image, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param('issss', $userId, $fullName, $dob, $frontImage, $backImage);

if ($insertStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'KYC submitted successfully. It will be reviewed by admin.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error submitting KYC: ' . $conn->error]);
}

$conn->close();
?>


