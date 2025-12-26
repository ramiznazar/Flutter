<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get request body
$requestBody = file_get_contents('php://input');

// Decode JSON
$data = json_decode($requestBody, true);

// Retrieve email, password, and reason from JSON
$email = $data['email'];
$password = $data['password'];
$reason = $data['reason'];

// Connect to the database
require '../config/dbh.inc.php';

// Validate user credentials
$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    // User is authenticated, retrieve claim_time_in_sec and max_coin_claim_allow
    $settings_query = mysqli_query($conn, "SELECT claim_time_in_sec, max_coin_claim_allow FROM coin_settings");
    $settings_row = mysqli_fetch_assoc($settings_query);
    $claim_time_in_sec = $settings_row['claim_time_in_sec'];
    $max_coin_claim_allow = $settings_row['max_coin_claim_allow'];

    // Get value from users
    $row = mysqli_fetch_assoc($result);
    $total_coin_claim = $row['total_coin_claim'];
    $coin = $row['coin'];
    $_coin_end_time = $row['coin_end_time'];

    // Get server time
    $current_time = date('Y-m-d-H:i:s');

    if ($reason === 'get') {
        // Check if the time is over
        if ($current_time > $_coin_end_time) {
            // Reset to zero if times end
            $total_coin_claim = 0;
            $_coin_end_time = '0000-00-00 00:00:00';

            $update_query = mysqli_query($conn, "UPDATE users SET coin_end_time='$_coin_end_time', total_coin_claim='$total_coin_claim' WHERE email='$email'");
            if (!$update_query) {
                echo json_encode(array('success' => false, 'message' => 'Failed to update records'));
                exit;
            }
        }

        // Fetch the latest record from the database
        $latest_query = mysqli_query($conn, "SELECT coin_end_time, total_coin_claim FROM users WHERE email='$email'");
        $latest_row = mysqli_fetch_assoc($latest_query);
        $latest_coin_end_time = $latest_row['coin_end_time'];
        $latest_total_coin_claim = $latest_row['total_coin_claim'];

        // Determine 'progress' value
        $progress = $current_time > $latest_coin_end_time ? 'idle' : 'in_progress';

        $response = array(
            'success' => true,
            'server_time' => $current_time,
            'coin_end_time' => $latest_coin_end_time,
            'total_coin_claim' => $latest_total_coin_claim,
            'progress' => $progress
        );

        echo json_encode($response);
    } elseif ($reason === 'start') {
        // Increment total_coin_claim & coins
        $total_coin_claim = $total_coin_claim + 1;
        $coin = $coin + 1;

        // Check time if it's over
        if ($current_time > $_coin_end_time) {
            // Reset to zero if times end
            $total_coin_claim = 0;
            // Calculate and set coin_end_time
            $coin_end_time = date('Y-m-d-H:i:s', strtotime("+$claim_time_in_sec seconds"));
        } else {
            $coin_end_time = $_coin_end_time;
        }

        if ($total_coin_claim >= $max_coin_claim_allow) {
            echo json_encode(array('success' => false, 'message' => 'Limit Exceeded', 'server_time' => $current_time, 'coin_end_time' => $_coin_end_time, 'total_coin_claim' => $total_coin_claim, 'progress' => 'in_progress'));
            exit;
        }

        $update_query = mysqli_query($conn, "UPDATE users SET coin_end_time='$coin_end_time', total_coin_claim='$total_coin_claim', coin='$coin' WHERE email='$email'");

        if ($update_query) {
            echo json_encode(array('success' => true, 'message' => 'Successfully claimed', 'server_time' => $current_time, 'coin_end_time' => $coin_end_time, 'total_coin_claim' => $total_coin_claim, 'progress' => 'in_progress'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to claim', 'server_time' => $current_time, 'coin_end_time' => $_coin_end_time, 'total_coin_claim' => $total_coin_claim, 'progress' => 'in_progress'));
        }
    } else {
        // Invalid reason
        echo json_encode(array('success' => false, 'message' => 'Invalid reason'));
    }
} else {
    // Invalid user credentials
    echo json_encode(array('success' => false, 'message' => 'Invalid email or password'));
}

mysqli_close($conn);
