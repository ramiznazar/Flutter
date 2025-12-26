<?php

// Get the JSON data sent by the user
$json_data = file_get_contents('php://input');

// Decode the JSON data into an associative array
$update_data = json_decode($json_data, true);

// Extract email and password from the JSON data
$email = $update_data['email'];
$password = $update_data['password'];

// Remove email and password from the update data
unset($update_data['email']);
unset($update_data['password']);

// Validate email and password
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email format"]);
    exit;
}

// Establish database connection
require '../config/dbh.inc.php'; // Adjust the path as per your project structure

// Check connection
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Query to check email and password and get user ID
$sql_user = "SELECT id FROM users WHERE email = '$email' AND password = '$password'";
$result_user = $conn->query($sql_user);

if ($result_user->num_rows > 0) {
    // Fetch user ID
    $row_user = $result_user->fetch_assoc();
    $userID = $row_user['id'];

   // Query to check if a record exists for the user
$sql_check = "SELECT COUNT(*) AS count FROM user_guide WHERE userID = '$userID'";
$result_check = $conn->query($sql_check);
$row_check = $result_check->fetch_assoc();
$count = $row_check['count'];

if ($count > 0) {
    // If a record exists, perform an update
    $sql_update = "UPDATE user_guide SET ";
    $update_pairs = [];
    foreach ($update_data as $column => $value) {
        $update_pairs[] = "$column = '$value'";
    }
    if (!empty($update_pairs)) {
        $sql_update .= implode(', ', $update_pairs);
        // Add condition to update only for the specific user
        $sql_update .= " WHERE userID = '$userID'";
        
        // Execute the update query
        if ($conn->query($sql_update) === TRUE) {
            echo json_encode(["success" => "Record updated successfully"]);
        } else {
            echo json_encode(["error" => "Error updating record: " . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "No data provided for update"]);
    }
} else {
    // If no record exists, perform an insert
    $sql_insert = "INSERT INTO user_guide (userID, ";
    $columns = implode(", ", array_keys($update_data));
    $values = "'" . implode("', '", $update_data) . "'";
    $sql_insert .= $columns . ") VALUES ('$userID', " . $values . ")";
    
    // Execute the insert query
    if ($conn->query($sql_insert) === TRUE) {
        echo json_encode(["success" => "Record inserted successfully"]);
    } else {
        echo json_encode(["error" => "Error inserting record: " . $conn->error]);
    }
}

} else {
    echo json_encode(["error" => "Invalid email or password"]);
}

// Close connection
$conn->close();
