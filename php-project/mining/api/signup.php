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

// Get JSON input
$input = file_get_contents("php://input");
$data = json_decode($input);

// Validate inputs - check both JSON and POST for compatibility
$name = isset($data->name) ? $data->name : (isset($_POST['name']) ? $_POST['name'] : '');
$email = isset($data->email) ? $data->email : (isset($_POST['email']) ? $_POST['email'] : '');
$phone = isset($data->phone) ? $data->phone : (isset($_POST['phone']) ? $_POST['phone'] : '');
$country = isset($data->country) ? $data->country : (isset($_POST['country']) ? $_POST['country'] : '');
$password = isset($data->password) ? $data->password : (isset($_POST['password']) ? $_POST['password'] : '');

if(empty($name) || empty($email) || empty($phone) || empty($country) || empty($password)){
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Please provide all required fields."));
    exit;
}

//  Connection file
require '../config/dbh.inc.php';

// Check if user already exists with the given email or phone
$email = mysqli_real_escape_string($conn, trim($email));
$phone = mysqli_real_escape_string($conn, trim($phone));
$query = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $query);

$email_found = false;
$phone_found = false;

if(mysqli_num_rows($result) > 0){
    $email_found = true;
}

$query = "SELECT * FROM users WHERE phone='$phone'";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) > 0){
    $phone_found = true;
}

if($email_found || $phone_found)
{
    http_response_code(402);
    echo json_encode(array('success' => false,
     'is_email_found' => $email_found,
     'is_phone_found' => $phone_found
    ));
    exit;
}


// Generate unique token and coin values
$token = 0;
$coin = 0;

// Set default values for other fields
$mining_end_time = null;
$last_active = null;
$is_mining = 0;
$mining_time = 0;
$username = null;
$username_count = 0;
$total_invite = 0;
$invite_setup = "not_setup";
$account_status = "active";
$ban_reason = null;
$ban_date = null;
$join_date = date('Y-m-d H:i:s');
$coin_end_time = null;
$total_coin_claim = 0;
$otp = null;

// Create user account in database
$name = mysqli_real_escape_string($conn, trim($name));
$country = mysqli_real_escape_string($conn, trim($country));
$password = mysqli_real_escape_string($conn, trim($password));
$query = "INSERT INTO users (name, email, phone, country, password, token, coin, is_mining, mining_end_time, last_active, mining_time, username, username_count, total_invite, invite_setup, account_status, ban_reason, ban_date, join_date, coin_end_time, otp, total_coin_claim)
          VALUES ('$name', '$email', '$phone', '$country', '$password', '$token', '$coin', '$is_mining', '$mining_end_time', '$last_active', '$mining_time', '$username', '$username_count', '$total_invite', '$invite_setup', '$account_status', '$ban_reason', '$ban_date', '$join_date', '$coin_end_time', '$otp', '$total_coin_claim')";

if(mysqli_query($conn, $query)){
    http_response_code(200);
    echo json_encode(array('success' => true, 'message' => 'User account created successfully.'));
}else{
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Failed to create user account.'));
}

// Close database connection
mysqli_close($conn);
?>
