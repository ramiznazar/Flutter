<?php

// Connect to database
require '../config/dbh.inc.php';

// Read input from POST request
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['email']) && isset($input['password'])) {
    // Sanitize and validate input
    $email = mysqli_real_escape_string($conn, $input['email']);
    $password = mysqli_real_escape_string($conn, $input['password']);

    // Use prepared statement to prevent SQL injection
    $query = "SELECT id, email, password FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Compare password directly (not recommended)
        if ($password === $row['password']) {
            // Delete user account using prepared statement
            $deleteQuery = "DELETE FROM users WHERE email = ?";
            $deleteStmt = mysqli_prepare($conn, $deleteQuery);
            mysqli_stmt_bind_param($deleteStmt, "s", $email);
            $deleteResult = mysqli_stmt_execute($deleteStmt);

            if ($deleteResult) {
                // Account deletion successful
                echo json_encode(array('success' => true, 'message' => 'Your account has been permanently deleted.'));
            } else {
                // Account deletion failed
                echo json_encode(array('success' => false, 'message' => 'Account deletion failed.'));
            }
        } else {
            // Invalid password
            echo json_encode(array('success' => false, 'message' => 'Invalid password.'));
        }
    } else {
        // User not found
        echo json_encode(array('success' => false, 'message' => 'Invalid email.'));
    }
} else {
    // Invalid input parameters
    echo json_encode(array('success' => false, 'message' => 'Invalid parameters.'));
}

?>
