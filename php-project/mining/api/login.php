<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get and validate input
$input = file_get_contents("php://input");
$data = json_decode($input);

// Validate inputs
if(empty($data) || empty($data->email) || empty($data->password)){
    http_response_code(400);
    echo json_encode(["status" => 400, "message" => "Please provide email and password."]);
    exit;
}

// Connect to database
require '../config/dbh.inc.php';

// Check if user exists with the given email and password
$email = mysqli_real_escape_string($conn, trim($data->email));
$password = mysqli_real_escape_string($conn, trim($data->password));

// Check account status
$query = "SELECT * FROM users WHERE email='$email' AND password='$password' AND account_status='active'";
$result = mysqli_query($conn, $query);

$userGuide[] = null;

if(mysqli_num_rows($result) == 1){
    $row = mysqli_fetch_assoc($result);
    $response = [
        "id" => $row['id'],
        "name" => $row['name'],
        "email" => $row['email'],
        "password" => $row['password'],
        "phone" => $row['phone'],
        "country" => $row['country'],
        "token" => $row['token'],
        "coin" => $row['coin'],
        "is_mining" => $row['is_mining'],
        "mining_end_time" => $row['mining_end_time'],
        "last_active" => $row['last_active'],
        "mining_time" => $row['mining_time'],
        "username" => $row['username'],
        "username_count" => $row['username_count'],
        "total_invite" => $row['total_invite'],
        "invite_setup" => $row['invite_setup'],
        "account_status" => $row['account_status'],
        "ban_reason" => $row['ban_reason'],
        "ban_date" => $row['ban_date'],
        "join_date" => $row['join_date'],
    ];

    // Fetch user_guide data for the user
    $userID = $row['id'];
    $query_user_guide = "SELECT * FROM user_guide WHERE userID='$userID'";
    $result_user_guide = mysqli_query($conn, $query_user_guide);
    
    // Check if there is a record in user_guide
    if(mysqli_num_rows($result_user_guide) > 0) {
        $row_user_guide = mysqli_fetch_assoc($result_user_guide);
        // Include user_guide data
        $userGuide = [
            "home" => (boolean) $row_user_guide['home'],
            "mining" => (boolean) $row_user_guide['mining'],
            "wallet" => (boolean) $row_user_guide['wallet'],
            "badges" => (boolean) $row_user_guide['badges'],
            "level" => (boolean) $row_user_guide['level'],
            "teamProfile" => (boolean) $row_user_guide['teamProfile'],
            "news" => (boolean) $row_user_guide['news'],
            "shop" => (boolean) $row_user_guide['shop'],
            "userProfile" => (boolean) $row_user_guide['userProfile']
        ];
    } else {
        // If no record in user_guide, set all user_guide fields to false
        $userGuide = [
            "home" => true,
            "mining" => true,
            "wallet" => true,
            "badges" => true,
            "level" => true,
            "teamProfile" => true,
            "news" => true,
            "shop" => true,
            "userProfile" => true
        ];
    }

    $response += [
        "userGuide" => $userGuide
    ];

    http_response_code(200);
    echo json_encode($response);
} else {
    // Check if user exists but password is wrong
    $checkEmailQuery = "SELECT id FROM users WHERE email='$email'";
    $checkEmailResult = mysqli_query($conn, $checkEmailQuery);
    
    if(mysqli_num_rows($checkEmailResult) > 0) {
        // Email exists but password is wrong
        http_response_code(401);
        echo json_encode(["status" => 401, "message" => "Invalid password."]);
    } else {
        // Email doesn't exist
        http_response_code(401);
        echo json_encode(["status" => 401, "message" => "Invalid email or password."]);
    }
}

// Close database connection
mysqli_close($conn);