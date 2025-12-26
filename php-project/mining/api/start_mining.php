<?php
// Get request body
$requestBody = file_get_contents('php://input');

// Decode JSON
$data = json_decode($requestBody, true);

// Validate required fields
if (!isset($data['email'], $data['password'], $data['coins'])) {
    echo json_encode(array('success' => false, 'message' => 'Missing required fields'));
    exit;
}

// Connect to database
require '../config/dbh.inc.php';
require '../api/check_levels.php';

$time_limit_in_sec = 43200;

// Validate user account
$email = mysqli_real_escape_string($conn, $data['email']);
$password = mysqli_real_escape_string($conn, $data['password']);

$sql = "SELECT * FROM users WHERE email = ? AND password = ? AND account_status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    echo json_encode(array('success' => false, 'message' => 'Invalid account'));
    exit;
}

// Get user's values
$user = $result->fetch_assoc();
$is_mining = $user['is_mining'];
$mining_end_time = $user['mining_end_time'];

$perks = getUserPerks($user['id'], $conn);

$currentLevel = $perks['current_level'];
$perkCrutoxPerTime = $perks['perk_crutox_per_time'];
$perkMiningTime = $perks['perk_mining_time'];

$time_limit_in_sec = $perkMiningTime * 3600;
$perkCrutoxPerTime = $perkCrutoxPerTime / $time_limit_in_sec;
$perkCrutoxPerTime = number_format($perkCrutoxPerTime, 10);

// Get current server time
$current_time = date('Y-m-d-H:i:s');

// Get coin settings
$sql = "SELECT * FROM coin_settings WHERE id = 1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo json_encode(array('success' => false, 'message' => 'Coin settings not found'));
    exit;
}

$coin_settings = mysqli_fetch_assoc($result);
$seconds_per_coin = $coin_settings['seconds_per_coin'];
$max_seconds_allow = $coin_settings['max_seconds_allow'];
$add_token = $perkCrutoxPerTime;
$usdt = (double) $coin_settings['token_price'];

$token = $user['token'];

// Check if user is mining
if ($is_mining == 1) {
    if ($current_time > $mining_end_time) {
        $coins = $user['coin'];
        $mining_time = $time_limit_in_sec;

        $token += $add_token * $mining_time;
        $is_mining = 0;

        $update_sql = "UPDATE users SET token = ?, is_mining = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sii", $token, $is_mining, $user['id']);
        if ($stmt->execute()) {
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to update user record'));
            exit;
        }
    } else {
        echo json_encode(array(
            'success' => true,
            'message' => 'in_progress',
            'server_time' => $current_time,
            'mining_end_time' => $mining_end_time,
            'total_team' => (String) $user['total_invite'],
            'coin' => $user['coin'],
            'balance' => $user['token'],
            'token_per_sec' => $add_token,
            'usdt' => $usdt,
            'total_mining_time_in_sec' => $time_limit_in_sec
        ));
        exit;
    }
}

if ($data['reason'] == "get") {
    echo json_encode(array(
        'success' => true,
        'message' => 'idle',
        'server_time' => $current_time,
        'mining_end_time' => $mining_end_time,
        'total_team' => (String) $user['total_invite'],
        'coin' => $user['coin'],
        'balance' => "$token",
        'token_per_sec' => $add_token,
        'usdt' => $usdt,
        'total_mining_time_in_sec' => $time_limit_in_sec
    ));
    exit;
}

// Get other user's values
$coins = $user['coin'];
$mining_time = $time_limit_in_sec;
$token = $user['token'];

// Check if user has enough coins
$coins_required = $data['coins'];

if ($coins < $coins_required) {
    echo json_encode(array('success' => false, 'message' => 'Insufficient coins'));
    exit;
}

// Calculate total time
$total_time = $coins_required * $seconds_per_coin;

if ($total_time > $max_seconds_allow) {
    echo json_encode(array('success' => false, 'message' => 'Maximum mining time exceeded'));
    exit;
}

// Deduct coins from user's account
$new_coins = $coins - $coins_required;
$sql = "UPDATE users SET coin = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $new_coins, $user['id']);
$stmt->execute();

// Convert time 
$total_time = $total_time + $time_limit_in_sec;

// Update user's mining status
$mining_end_time = date('Y-m-d-H:i:s', strtotime("+$total_time seconds"));

$sql = "UPDATE users SET is_mining = 1, mining_end_time = ?, mining_time = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $mining_end_time, $total_time, $user['id']);
$stmt->execute();

// Bonus to invited referral
if ($coins_required == 0) {
    if ($user['invite_setup'] != "skip" && $user['invite_setup'] != "not_setup") {
        $in_sec = 12 * $seconds_per_coin;
        $tmp_token = 0.1 * ($in_sec * $add_token);
        $expire_at = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +12 hours'));
        $sql = "UPDATE users SET token = token + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $tmp_token, $user['invite_setup']);
        $stmt->execute();
    }
}

increaseUserMiningLevel($user['id'], $conn);

// Close database connection
$conn->close();

// Return response
echo json_encode(array(
    'success' => true,
    'message' => 'in_progress',
    'server_time' => $current_time,
    'mining_end_time' => $mining_end_time,
    'total_team' => (String) $user['total_invite'],
    'coin' => $user['coin'],
    'balance' => $user['token'],
    'token_per_sec' => $add_token,
    'usdt' => $usdt,
    'total_mining_time_in_sec' => $time_limit_in_sec
));

?>
