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

// Get latest KYC submission
$kycSql = "SELECT id, full_name, dob, front_image, back_image, status, admin_notes, created_at, updated_at FROM kyc_submissions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
$kycStmt = $conn->prepare($kycSql);
$kycStmt->bind_param('i', $userId);
$kycStmt->execute();
$kycResult = $kycStmt->get_result();

if ($kycResult->num_rows > 0) {
    $kyc = $kycResult->fetch_assoc();
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $kyc['id'],
            'full_name' => $kyc['full_name'],
            'dob' => $kyc['dob'],
            'front_image' => $kyc['front_image'],
            'back_image' => $kyc['back_image'],
            'status' => $kyc['status'],
            'admin_notes' => $kyc['admin_notes'],
            'created_at' => $kyc['created_at'],
            'updated_at' => $kyc['updated_at']
        ]
    ]);
} else {
    echo json_encode(['success' => true, 'data' => null]);
}

$conn->close();
?>


