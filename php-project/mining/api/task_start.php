<?php
/**
 * Task Start API
 * Records when a user starts a task (daily or one-time)
 * Backend enforces timer logic - daily tasks: 5 min, one-time: 1 hour
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

// Check if task exists
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

// Check if user already has an active completion for this task
$checkSql = "SELECT id, reward_available_at, reward_claimed FROM task_completions 
             WHERE user_id = ? AND task_id = ? AND task_type = ? AND reward_claimed = 0 
             ORDER BY created_at DESC LIMIT 1";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param('iis', $userId, $taskId, $taskType);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $existing = $checkResult->fetch_assoc();
    $now = new DateTime();
    $availableAt = new DateTime($existing['reward_available_at']);
    
    if ($now >= $availableAt) {
        // Reward is available but not claimed yet
        echo json_encode([
            'success' => true,
            'message' => 'Task already started. Reward is available.',
            'reward_available' => true,
            'reward_available_at' => $existing['reward_available_at']
        ]);
        exit;
    } else {
        // Task in progress, reward not yet available
        echo json_encode([
            'success' => true,
            'message' => 'Task already in progress.',
            'reward_available' => false,
            'reward_available_at' => $existing['reward_available_at'],
            'seconds_remaining' => ($availableAt->getTimestamp() - $now->getTimestamp())
        ]);
        exit;
    }
}

// For daily tasks, check if user already completed today (after last reset)
if ($taskType === 'daily') {
    // Get last reset time
    $resetSql = "SELECT daily_tasks_reset_time FROM settings LIMIT 1";
    $resetResult = $conn->query($resetSql);
    $resetTime = null;
    if ($resetResult && $resetResult->num_rows > 0) {
        $resetRow = $resetResult->fetch_assoc();
        $resetTime = $resetRow['daily_tasks_reset_time'];
    }
    
    // If reset time exists, check if user completed after reset
    if ($resetTime) {
        $dailyCheckSql = "SELECT id FROM task_completions 
                          WHERE user_id = ? AND task_id = ? AND task_type = 'daily' 
                          AND started_at >= ? AND reward_claimed = 1";
        $dailyCheckStmt = $conn->prepare($dailyCheckSql);
        $dailyCheckStmt->bind_param('iis', $userId, $taskId, $resetTime);
        $dailyCheckStmt->execute();
        $dailyCheckResult = $dailyCheckStmt->get_result();
        
        if ($dailyCheckResult->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Daily task already completed today']);
            exit;
        }
    }
}

// Calculate reward available time based on task type
$now = new DateTime();
$rewardAvailableAt = clone $now;

if ($taskType === 'daily') {
    // Daily tasks: 5 minutes
    $rewardAvailableAt->modify('+5 minutes');
} else {
    // One-time tasks: 1 hour
    $rewardAvailableAt->modify('+1 hour');
}

// Insert task completion record
$insertSql = "INSERT INTO task_completions (user_id, task_id, task_type, started_at, reward_available_at, created_at) 
              VALUES (?, ?, ?, NOW(), ?, NOW())";
$insertStmt = $conn->prepare($insertSql);
$rewardAvailableAtStr = $rewardAvailableAt->format('Y-m-d H:i:s');
$insertStmt->bind_param('iiss', $userId, $taskId, $taskType, $rewardAvailableAtStr);

if ($insertStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Task started successfully',
        'reward_available_at' => $rewardAvailableAtStr,
        'seconds_remaining' => ($rewardAvailableAt->getTimestamp() - $now->getTimestamp()),
        'task_type' => $taskType,
        'reward' => (float)$task['Token']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error starting task: ' . $conn->error]);
}

$conn->close();
?>


