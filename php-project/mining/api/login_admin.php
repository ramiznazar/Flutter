<?php
// Allow requests from any origin
header("Access-Control-Allow-Origin: *");

// Allow specified headers and methods
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");

// Set content type to JSON
header("Content-Type: application/json");

// Hard-coded email and password
$hardcodedEmail = "admin@crutox.com";
$hardcodedPassword = "admin$$$@@@";

// Get user input from the request
$data = json_decode(file_get_contents("php://input"));

// Check if email and password are provided in the request
if(isset($data->email) && isset($data->password)) {
    $inputEmail = $data->email;
    $inputPassword = $data->password;

    // Check if provided email and password match the hard-coded values
    if($inputEmail == $hardcodedEmail && $inputPassword == $hardcodedPassword) {
        // Successful login response
        echo json_encode(array("status" => "ok", "message" => "Login successful"));
    } else {
        // Invalid credentials response
        echo json_encode(array("status" => "error", "message" => "Invalid email or password. Please try again."));
    }
} else {
    // Incomplete input response
    echo json_encode(array("status" => "error", "message" => "Email and password are required."));
}
?>
