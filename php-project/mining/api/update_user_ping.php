<?php

// Connect to database
require '../config/dbh.inc.php';

// Read input from POST request
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['user_id'])) {
    // Sanitize and validate input
    $user_id = mysqli_real_escape_string($conn, $input['user_id']);

    // Use prepared statement to prevent SQL injection
    $query = "SELECT id FROM users WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        // Update ban_date column with current server time
        $updateQuery = "UPDATE users SET ban_date = NOW() WHERE id = ?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "i", $user_id);
        $updateResult = mysqli_stmt_execute($updateStmt);

        if ($updateResult) {
            // Ban date updated successfully
            echo json_encode(array('success' => true, 'message' => 'User account has been ping.'));
        } else {
            // Failed to update ban date
            echo json_encode(array('success' => false, 'message' => 'Failed to update ban date.'));
        }
    } else {
        // User not found
        echo json_encode(array('success' => false, 'message' => 'Invalid user ID.'));
    }
} else {
    // Invalid input parameters
    echo json_encode(array('success' => false, 'message' => 'Invalid parameters.'));
}
