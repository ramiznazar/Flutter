<?php

require '../config/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);

    $email = $postData['email'];
    $password = $postData['password'];

    // Validate the user credentials from the 'users' table
    $query = "SELECT id FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $userId = mysqli_fetch_assoc($result)['id'];

        // Get all social media settings and check if the user has claimed tokens
        $socialMediaQuery = "SELECT s.ID, s.Name, s.Icon, s.Link, s.Token, 
                                    CASE WHEN t.user_id IS NOT NULL THEN 1 ELSE 0 END AS claimed
                             FROM social_media_setting s
                             LEFT JOIN social_media_tokens t ON s.ID = t.social_media_id AND t.user_id = $userId";
        
        $socialMediaResult = mysqli_query($conn, $socialMediaQuery);

        if ($socialMediaResult) {
            $socialMediaData = mysqli_fetch_all($socialMediaResult, MYSQLI_ASSOC);
            echo json_encode(array('success' => true, 'social_media_setting' => $socialMediaData));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Failed to fetch social media settings.'));
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
