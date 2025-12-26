<?php

//  Connection file
require '../config/dbh.inc.php';

// Get the email and password from the API request
$email = $_POST['email'];
$password = $_POST['password'];

// Check if the email and password are empty
if (empty($email) || empty($password)) {
    $response = array("status" => "error", "message" => "Email and password are required.");
    echo json_encode($response);
    exit;
}

// Validate the email and password
$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
$result = mysqli_query($conn, $sql);

// Check if the query returned a result
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // Check if the account is active
    if ($row['account_status'] == 'active') {

        // Get the current server time
        $current_time = date('Y-m-d H:i:s');

        // Remove any records from token_bonus_history that have expired
        $sql = "DELETE FROM token_bonus_history WHERE expire_at <= '$current_time'";
        mysqli_query($conn, $sql);

        // Get all the records from token_bonus_history where to_user_id matches the user's id
        $user_id = $row['id'];
        $sql = "SELECT * FROM token_bonus_history WHERE to_user_id = $user_id";
        $result = mysqli_query($conn, $sql);

        $response_data = array();

        // Loop through the results and get the from_user_id's name from the users table
        while ($row = mysqli_fetch_assoc($result)) {
            $from_user_id = $row['from_user_id'];
            $sql = "SELECT name FROM users WHERE id = $from_user_id";
            $name_result = mysqli_query($conn, $sql);
            $name_row = mysqli_fetch_assoc($name_result);
            $from_user_name = $name_row['name'];

            $response_data[] = array(
                "id" => $row['id'],
                "to_user_id" => $row['to_user_id'],
                "from_user_name" => $from_user_name,
                "amount" => $row['amount'],
                "expire_at" => $row['expire_at']
            );
        }

        $response = array("status" => "success", "data" => $response_data);
        echo json_encode($response);
        exit;

    } else {
        $response = array("status" => "error", "message" => "Account is not active.");
        echo json_encode($response);
        exit;
    }

} else {
    $response = array("status" => "error", "message" => "Invalid email or password.");
    echo json_encode($response);
    exit;
}

?>
