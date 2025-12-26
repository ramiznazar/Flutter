<?php
// Get request body
$requestBody = file_get_contents('php://input');

// Decode JSON
$data = json_decode($requestBody, true);

// Validate required fields
if (!isset($data['email']) || !isset($data['password']) || !isset($data['coins'])) {
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

$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND account_status = 'active'";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo json_encode(array('success' => false, 'message' => 'Invalid account'));

    // Close database connection
    mysqli_close($conn);
    
    exit;
}

// Get user's values
$user = mysqli_fetch_assoc($result);
$is_mining = $user['is_mining'];
$mining_end_time = $user['mining_end_time'];

// Get current server time
$current_time = date('Y-m-d-H:i:s');

// Get coin settings
$sql = "SELECT * FROM coin_settings WHERE id = 1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) != 1) {
    echo json_encode(array('success' => false, 'message' => 'Coin settings not found'));

    // Close database connection
    mysqli_close($conn);

    exit;
}

$coin_settings = mysqli_fetch_assoc($result);
$seconds_per_coin = $coin_settings['seconds_per_coin'];
$max_seconds_allow = $coin_settings['max_seconds_allow'];
$add_token = $coin_settings['token'];

checkRecord($user['id'], $conn);

// Check if user is mining
if ($is_mining == 1) {
       // If current time is greater than the mining_end_time value, set token to token + 1 and is_mining to 0
       if ($current_time > $mining_end_time) {

            // Get other user's values
           $coins = $user['coin'];
           $mining_time = $time_limit_in_sec;
           $token = $user['token'];

           $token += $add_token * $mining_time;
           $is_mining = 0;
   
           // Update the user's token and is_mining values in the database
           $update_sql = "UPDATE users SET token = $token, is_mining = $is_mining WHERE id = {$user['id']}";

           if (mysqli_query($conn, $update_sql)) {
            

           } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to update user record'));

                // Close database connection
                mysqli_close($conn);

                exit;
           }
       }
       else {

            echo json_encode(array( 'success' => true,
                                    'message' => 'in_progress',
                                    'server_time' => $current_time ,
                                    'mining_end_time' => $mining_end_time,
                                    'total_team' => $user['total_invite'],
                                    'coin' => $user['coin'],
                                    'balance' => $user['token'],
                                    'token_per_sec' => $add_token,
                                    'total_mining_time_in_sec' => $time_limit_in_sec
                                ));

            // Close database connection
            mysqli_close($conn);
            
            exit;
       }
}

if($data['reason'] == "get")
{
    echo json_encode(array( 'success' => true,
                                    'message' => 'idle',
                                    'server_time' => $current_time ,
                                    'mining_end_time' => $mining_end_time,
                                    'total_team' => $user['total_invite'],
                                    'coin' => $user['coin'],
                                    'balance' => $user['token'],
                                    'token_per_sec' => $add_token,
                                    'total_mining_time_in_sec' => $time_limit_in_sec
                                ));

    // Close database connection
    mysqli_close($conn);
    
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

    // Close database connection
    mysqli_close($conn);
    
    exit;
}

// Calculate total time
$total_time = $coins_required * $seconds_per_coin;

if ($total_time > $max_seconds_allow) {
    echo json_encode(array('success' => false, 'message' => 'Maximum mining time exceeded'));

    // Close database connection
    mysqli_close($conn);

    exit;
}

// Deduct coins from user's account
$new_coins = $coins - $coins_required;
$sql = "UPDATE users SET coin = $new_coins WHERE id = {$user['id']}";
mysqli_query($conn, $sql);

//convert time 
$total_time = $total_time + $time_limit_in_sec;

// Update user's mining status
$mining_end_time = date('Y-m-d-H:i:s', strtotime("+$total_time seconds"));

$sql = "UPDATE users SET is_mining = 1, mining_end_time = '$mining_end_time', mining_time = '$total_time' WHERE id = {$user['id']}";
mysqli_query($conn, $sql);

// Bonus to invited referral
if($coins_required == 0)
{
    if($user['invite_setup'] != "skip" && $user['invite_setup'] != "not_setup")
    {
        $in_sec = 12 * $seconds_per_coin;

        $tmp_token = 0.1 * ($in_sec * $add_token);

        // Add 12 hours to the current time
        $expire_at = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +12 hours'));

        // Insert data into the table
        $sql = "UPDATE users SET token = '$tmp_token' WHERE id = {$user['invite_setup']}";

        mysqli_query($conn, $sql);
    }
}

increaseUserMiningLevel($user['id'], $conn);

// Close database connection
mysqli_close($conn);

// Return response
echo json_encode(array( 'success' => true,
    'message' => 'in_progress',
    'server_time' => $current_time ,
    'mining_end_time' => $mining_end_time,
    'total_team' => $user['total_invite'],
    'coin' => $user['coin'],
    'balance' => $user['token'],
    'token_per_sec' => $add_token,
    'total_mining_time_in_sec' => $time_limit_in_sec
));

?>