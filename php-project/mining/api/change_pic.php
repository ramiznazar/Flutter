<?php

require '../config/dbh.inc.php';

// Assuming you received JSON data from the user
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Extracting values from JSON
$email = $data['email'];
$password = $data['password'];
$picture = $data['picture'];

// Validate email and password
// (You should implement more thorough validation)
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Password validation can be implemented here
    // For simplicity, let's assume the password is valid

    // Check if the account is active
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND account_status = 'active'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Update picture value in ban_reason
        $updateSql = "UPDATE users SET ban_reason = '$picture' WHERE email = '$email'";
        if ($conn->query($updateSql) === TRUE) {
            // Success message in JSON format
            $response = array('status' => 'success', 'message' => 'Picture updated successfully');
            echo json_encode($response);
        } else {
            // Error updating picture
            $response = array('status' => 'error', 'message' => 'Error updating picture: ' . $conn->error);
            echo json_encode($response);
        }
    } else {
        // Account not active
        $response = array('status' => 'error', 'message' => 'Account is not active');
        echo json_encode($response);
    }
} else {
    // Invalid email
    $response = array('status' => 'error', 'message' => 'Invalid email');
    echo json_encode($response);
}

// Close the database connection
$conn->close();

?>
