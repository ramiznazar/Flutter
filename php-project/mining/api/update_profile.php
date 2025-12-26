<?php
require '../config/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get request body
    $requestBody = file_get_contents('php://input');

    // Decode JSON
    $data = json_decode($requestBody, true);

    // Validate required fields
    if (!isset($data['full_name'])
         || !isset($data['old_email'])
         || !isset($data['new_email'])
         || !isset($data['country'])
         || !isset($data['phone_number'])
         || !isset($data['profile_url'])
         || !isset($data['password'])) {
      // Handle empty parameters error
      $response = array('success' => false, 'message' => 'Missing required parameters.');
      echo json_encode($response);
      exit;
    }

    $full_name = $data['full_name'];
    $old_email = $data['old_email'];
    $new_email = $data['new_email'];
    $country = $data['country'];
    $phone_number = $data['phone_number'];
    $password = $data['password'];
    $profile_url = $data['profile_url'];

    // Validate email format
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $response = array('success' => false, 'message' => 'Email is not valid. Please check your email address.');
        echo json_encode($response);
        exit;
    }

    // Validate phone number format with country code (assuming country code starts with '+')
    if (!preg_match('/^\+[0-9]+$/', $phone_number)) {
        $response = array('success' => false, 'message' => 'Phone number is not valid. Please check your Phone number.');
        echo json_encode($response);
        exit;
    }

    // Check if the password is correct in the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $old_email);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    $stmt->fetch();
    $stmt->close();

    if ($password !== $stored_password) {
        $response = array('success' => false, 'message' => 'Incorrect password.');
        echo json_encode($response);
        exit;
    }

    // Check if the new_email is different from the old_email
    if ($old_email !== $new_email) {
        // Check if the new_email already exists in the database
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $new_email);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();

        if ($user_id) {
            $response = array('success' => false, 'message' => 'The new email is already registered.');
            echo json_encode($response);
            exit;
        }
    }

    // Update user data in the 'users' table
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, country = ?, phone = ?, ban_reason = ? WHERE email = ?");
    $stmt->bind_param("ssssss", $full_name, $new_email, $country, $phone_number, $profile_url, $old_email);
    $stmt->execute();
    $stmt->close();

    // Return success message
    $response = array('success' => true, 'message' => 'User information updated successfully.');
    echo json_encode($response);
    exit;
} else {
    $response = array('success' => false, 'message' => 'Method not allowed.');
    echo json_encode($response);
    exit;
}
?>
