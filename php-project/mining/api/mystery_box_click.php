<?php
/**
 * Mystery Box Click Tracking API
 * Tracks when user clicks on a mystery box (before watching ads)
 * This helps track user engagement with mystery boxes
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

// Check if clicks column exists, if not we'll track it separately
// For now, we'll create/update a record to track clicks
// Get or create mystery box claim record to track clicks
$claimSql = "SELECT id, clicks, ads_watched, box_opened 
             FROM mystery_box_claims 
             WHERE user_id = ? AND box_type = ? 
             ORDER BY created_at DESC LIMIT 1";
$claimStmt = $conn->prepare($claimSql);
$claimStmt->bind_param('is', $userId, $boxType);
$claimStmt->execute();
$claimResult = $claimStmt->get_result();

$now = date('Y-m-d H:i:s');

if ($claimResult->num_rows > 0) {
    $claim = $claimResult->fetch_assoc();
    $claimId = $claim['id'];
    
    // Check if clicks column exists
    $checkColumn = $conn->query("SHOW COLUMNS FROM mystery_box_claims LIKE 'clicks'");
    
    if ($checkColumn && $checkColumn->num_rows > 0) {
        // Column exists, increment clicks
        $currentClicks = isset($claim['clicks']) ? (int)$claim['clicks'] : 0;
        $newClicks = $currentClicks + 1;
        
        $updateSql = "UPDATE mystery_box_claims 
                      SET clicks = ?, last_clicked_at = NOW() 
                      WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('ii', $newClicks, $claimId);
        $updateStmt->execute();
        
        $clicks = $newClicks;
    } else {
        // Column doesn't exist yet, we'll just return success
        // The migration will add the column later
        $clicks = 1;
    }
} else {
    // Create new record to track clicks
    // Get box settings to get ads_required
    $settingsSql = "SELECT {$boxType}_box_ads FROM settings LIMIT 1";
    $settingsResult = $conn->query($settingsSql);
    
    if ($settingsResult && $settingsResult->num_rows > 0) {
        $settings = $settingsResult->fetch_assoc();
        $adsRequired = (int)$settings["{$boxType}_box_ads"];
        
        // Check if clicks column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM mystery_box_claims LIKE 'clicks'");
        
        if ($checkColumn && $checkColumn->num_rows > 0) {
            $insertSql = "INSERT INTO mystery_box_claims 
                         (user_id, box_type, clicks, ads_required, ads_watched, last_clicked_at, created_at) 
                         VALUES (?, ?, 1, ?, 0, NOW(), NOW())";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param('isi', $userId, $boxType, $adsRequired);
            $insertStmt->execute();
        } else {
            // Column doesn't exist, create record without clicks column
            $insertSql = "INSERT INTO mystery_box_claims 
                         (user_id, box_type, ads_required, ads_watched, created_at) 
                         VALUES (?, ?, ?, 0, NOW())";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param('isi', $userId, $boxType, $adsRequired);
            $insertStmt->execute();
        }
    }
    $clicks = 1;
}

echo json_encode([
    'success' => true,
    'message' => 'Mystery box click tracked successfully',
    'box_type' => $boxType,
    'clicks' => $clicks,
    'clicked_at' => $now
]);

$conn->close();
?>

