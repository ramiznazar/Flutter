<?php

// Allow requests from any origin
header("Access-Control-Allow-Origin: *");
// Allow DELETE method
header("Access-Control-Allow-Methods: DELETE");

require '../config/dbh.inc.php';

// Get the news ID and token from the request
$newsId = $_GET['news_id'];
$token = $_GET['token'];

// Your secret token for verification
$secretToken = '@@@';
// Check if the provided token matches the expected token
if ($token === $secretToken) {
    // SQL query to delete news item from the 'news' table
    $sql = "DELETE FROM news WHERE ID = $newsId";

    if ($conn->query($sql) === TRUE) {
        // Deletion successful
        echo "News item deleted successfully";
    } else {
        // Error occurred during deletion
        echo "Error deleting news item: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
} else {
    // Token mismatch error
    echo "Token mismatch error";
}
?>
