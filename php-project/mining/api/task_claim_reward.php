<?php
/**
 * Task Claim Reward API
 * Claims reward for a completed task after timer expires
 * Backend enforces that reward is only available after timer
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

if (!isset($data['email']) || !isset($data['password']) || !isset($data['task_id']) || !isset($data['task_type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$email = mysqli_real_escape_string($conn, trim($data['email']));
$password = mysqli_real_escape_string($conn, trim($data['password']));
$taskId = (int)$data['task_id'];
$taskType = mysqli_real_escape_string($conn, trim($data['task_type']));

// Validate task type
if (!in_array($taskType, ['daily', 'onetime'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid task type']);
    exit;
}

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

// Get task reward
$taskSql = "SELECT ID, Token FROM social_media_setting WHERE ID = ?";
$taskStmt = $conn->prepare($taskSql);
$taskStmt->bind_param('i', $taskId);
$taskStmt->execute();
$taskResult = $taskStmt->get_result();

if ($taskResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Task not found']);
    exit;
}

$task = $taskResult->fetch_assoc();
$reward = (float)$task['Token'];

// Find unclaimed completion for this task
$completionSql = "SELECT id, reward_available_at FROM task_completions 
                  WHERE user_id = ? AND task_id = ? AND task_type = ? AND reward_claimed = 0 
                  ORDER BY created_at DESC LIMIT 1";
$completionStmt = $conn->prepare($completionSql);
$completionStmt->bind_param('iis', $userId, $taskId, $taskType);
$completionStmt->execute();
$completionResult = $completionStmt->get_result();

if ($completionResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No active task found. Please start the task first.']);
    exit;
}

$completion = $completionResult->fetch_assoc();
$now = new DateTime();
$availableAt = new DateTime($completion['reward_available_at']);

// Backend enforces timer - reward only available after timer expires
if ($now < $availableAt) {
    $secondsRemaining = $availableAt->getTimestamp() - $now->getTimestamp();
    echo json_encode([
        'success' => false,
        'message' => 'Reward not yet available. Timer still running.',
        'seconds_remaining' => $secondsRemaining,
        'reward_available_at' => $completion['reward_available_at']
    ]);
    exit;
}

// Mark reward as claimed and give coins to user
$conn->begin_transaction();

try {
    // Update completion record
    $updateSql = "UPDATE task_completions SET reward_claimed = 1, reward_claimed_at = NOW() WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('i', $completion['id']);
    $updateStmt->execute();
    
    // Add reward to user's token balance
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
        'message' => 'Reward claimed successfully',
        'reward' => $reward,
        'new_balance' => (float)$userBalance
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error claiming reward: ' . $e->getMessage()]);
}

$conn->close();
?>


