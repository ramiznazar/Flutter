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
    $query = "SELECT id, email, account_status, ban_date, password  FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Compare password directly (not recommended)
        if ($password === $row['password']) {
            // Get the user's ban date and account status
            $banDate = strtotime($row['ban_date']);
            $currentDate = time();
            $accountStatus = $row['account_status'];

            // Check if the account is deactivated and within the reactivation period
            if ($accountStatus === 'deactivate' && $currentDate <= $banDate) {
                // Update user's account status and reset ban details using prepared statement
                $updateQuery = "UPDATE users SET account_status = 'active', ban_reason = '', ban_date = '' WHERE email = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "s", $email);
                $updateResult = mysqli_stmt_execute($updateStmt);

                if ($updateResult) {
                    // Account reactivation successful
                    echo json_encode(array('success' => true, 'message' => 'Your account has been reactivated. Please login again.'));
                } else {
                    // Account reactivation failed
                    echo json_encode(array('success' => false, 'message' => 'Account reactivation failed.'));
                }
            } else {
                // Account is not within the reactivation period
                echo json_encode(array('success' => false, 'message' => 'Account is not eligible for reactivation.'));
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
