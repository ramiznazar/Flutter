<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../../config/dbh.inc.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// GET - Search users
if ($method === 'GET') {
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perPage = isset($_GET['perPage']) ? intval($_GET['perPage']) : 20;
    $offset = ($page - 1) * $perPage;
    
    $whereClause = "WHERE account_status = 'active'";
    if (!empty($search)) {
        $whereClause .= " AND (email LIKE '%$search%' OR username LIKE '%$search%' OR id = '$search' OR name LIKE '%$search%')";
    }
    
    $query = "SELECT id, name, email, username, coin, account_status, join_date FROM users $whereClause ORDER BY id DESC LIMIT $offset, $perPage";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn), 'data' => [], 'total' => 0, 'page' => $page, 'perPage' => $perPage]);
        exit;
    }
    
    $users = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = [
                'id' => $row['id'],
                'user_id' => 'USR' . str_pad($row['id'], 3, '0', STR_PAD_LEFT),
                'username' => $row['username'] ?: 'N/A',
                'email' => $row['email'],
                'name' => $row['name'],
                'coins_balance' => floatval($row['coin']),
                'type' => 'user', // Can be determined by other logic
                'join_date' => $row['join_date']
            ];
        }
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
    $countResult = mysqli_query($conn, $countQuery);
    $total = 0;
    if ($countResult && mysqli_num_rows($countResult) > 0) {
        $total = mysqli_fetch_assoc($countResult)['total'];
    }
    
    echo json_encode(['success' => true, 'data' => $users, 'total' => $total, 'page' => $page, 'perPage' => $perPage]);
    exit;
}

// POST - Give coins to user or give booster
if ($method === 'POST') {
    // Check if it's a booster request
    if (isset($input['action']) && $input['action'] === 'give_booster') {
        if (empty($input['user_identifier']) || empty($input['booster_type']) || empty($input['booster_duration'])) {
            echo json_encode(['success' => false, 'message' => 'User identifier, booster type, and duration are required.']);
            exit;
        }
        
        $identifier = mysqli_real_escape_string($conn, trim($input['user_identifier']));
        $boosterType = mysqli_real_escape_string($conn, trim($input['booster_type']));
        $durationHours = floatval($input['booster_duration']);
        $reason = isset($input['reason']) ? mysqli_real_escape_string($conn, trim($input['reason'])) : 'Admin assigned booster';
        
        // Validate duration
        if ($durationHours <= 0 || $durationHours > 24) {
            echo json_encode(['success' => false, 'message' => 'Duration must be between 0.1 and 24 hours.']);
            exit;
        }
        
        // Find user by email, username, or ID
        $query = "SELECT id FROM users WHERE (email = '$identifier' OR username = '$identifier' OR id = '$identifier') AND account_status = 'active'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'message' => 'User not found or account is not active.']);
            exit;
        }
        
        $user = mysqli_fetch_assoc($result);
        $userId = $user['id'];
        
        // Check if user_boosters table exists, if not create it
        $tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'user_boosters'");
        if (mysqli_num_rows($tableCheck) == 0) {
            $createTable = "CREATE TABLE IF NOT EXISTS `user_boosters` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `user_id` INT NOT NULL,
                `booster_type` VARCHAR(50) DEFAULT '2x',
                `started_at` DATETIME NOT NULL,
                `expires_at` DATETIME NOT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `user_id` (`user_id`),
                KEY `expires_at` (`expires_at`),
                KEY `is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            mysqli_query($conn, $createTable);
        }
        
        // Deactivate any expired boosters for this user
        mysqli_query($conn, "UPDATE user_boosters SET is_active = 0 WHERE user_id = $userId AND expires_at <= NOW()");
        
        // Calculate expiry time
        $durationSeconds = intval($durationHours * 3600);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+$durationSeconds seconds"));
        
        // Insert new booster
        $insertQuery = "INSERT INTO user_boosters (user_id, booster_type, started_at, expires_at, is_active, created_at) 
                       VALUES ($userId, '$boosterType', NOW(), '$expiresAt', 1, NOW())";
        
        if (mysqli_query($conn, $insertQuery)) {
            echo json_encode([
                'success' => true,
                'message' => 'Booster assigned successfully.',
                'booster_type' => $boosterType,
                'expires_at' => $expiresAt,
                'duration_hours' => $durationHours
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to assign booster: ' . mysqli_error($conn)]);
        }
        exit;
    }
    
    // Default: Give coins to user
    if (empty($input['user_identifier']) || empty($input['coin_amount'])) {
        echo json_encode(['success' => false, 'message' => 'User identifier and coin amount are required.']);
        exit;
    }
    
    $identifier = mysqli_real_escape_string($conn, trim($input['user_identifier']));
    $coinAmount = floatval($input['coin_amount']);
    $reason = isset($input['reason']) ? mysqli_real_escape_string($conn, trim($input['reason'])) : 'Admin adjustment';
    
    // Find user by email, username, or ID
    $query = "SELECT id, coin FROM users WHERE (email = '$identifier' OR username = '$identifier' OR id = '$identifier') AND account_status = 'active'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found or account is not active.']);
        exit;
    }
    
    $user = mysqli_fetch_assoc($result);
    $userId = $user['id'];
    $currentCoins = floatval($user['coin']);
    $newCoins = $currentCoins + $coinAmount;
    
    if ($newCoins < 0) {
        echo json_encode(['success' => false, 'message' => 'Insufficient coins. User has ' . $currentCoins . ' coins.']);
        exit;
    }
    
    // Update user coins
    $updateQuery = "UPDATE users SET coin = $newCoins WHERE id = $userId";
    
    if (mysqli_query($conn, $updateQuery)) {
        // Log transaction (optional - create admin_transactions table if needed)
        echo json_encode([
            'success' => true, 
            'message' => 'Coins updated successfully.',
            'previous_balance' => $currentCoins,
            'new_balance' => $newCoins,
            'amount' => $coinAmount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update coins: ' . mysqli_error($conn)]);
    }
    exit;
}

mysqli_close($conn);
?>





