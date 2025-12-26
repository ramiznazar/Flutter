<?php

require '../config/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);

    $email = $postData['email'];
    $password = $postData['password'];
    $socialMediaId = $postData['ID'];

    // Validate the user credentials from the 'users' table
    $query = "SELECT id, token FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $userId = $user['id'];
        $userToken = $user['token'];

        // Check if the user has already claimed tokens for the given social media ID
        $checkClaimQuery = "SELECT id FROM social_media_tokens WHERE user_id = $userId AND social_media_id = $socialMediaId";
        $checkClaimResult = mysqli_query($conn, $checkClaimQuery);

        if ($checkClaimResult && mysqli_num_rows($checkClaimResult) == 0) {
            // User has not claimed tokens for the given social media ID
            // Fetch token information from 'social_media_setting' table
            $tokenQuery = "SELECT Token FROM social_media_setting WHERE ID = $socialMediaId";
            $tokenResult = mysqli_query($conn, $tokenQuery);

            if ($tokenResult && mysqli_num_rows($tokenResult) > 0) {
                $tokenRow = mysqli_fetch_assoc($tokenResult);
                $tokensToAdd = $tokenRow['Token'];

                // Update user's token balance in 'users' table
                $newTokenBalance = $userToken + $tokensToAdd;
                $updateTokenQuery = "UPDATE users SET token = $newTokenBalance WHERE id = $userId";
                $updateTokenResult = mysqli_query($conn, $updateTokenQuery);

                // Record the token claim in 'social_media_tokens' table
                $recordClaimQuery = "INSERT INTO social_media_tokens (user_id, social_media_id) VALUES ($userId, $socialMediaId)";
                $recordClaimResult = mysqli_query($conn, $recordClaimQuery);

                if ($updateTokenResult && $recordClaimResult) {
                    echo json_encode(array('success' => true, 'message' => 'Tokens claimed successfully.'));
                } else {
                    echo json_encode(array('success' => false, 'message' => 'Token claim failed.'));
                }
            } else {
                echo json_encode(array('success' => false, 'message' => 'Invalid social media ID.'));
            }
        } else {
            echo json_encode(array('success' => false, 'message' => 'User has already claimed tokens for this social media ID.'));
        }
    } else {
        echo json_encode(array('success' => false, 'message' => 'Invalid credentials.'));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
}

// Close the database connection here, if needed
mysqli_close($conn);

?>
