<?php
header("Access-Control-Allow-Origin: *");

require '../config/dbh.inc.php';

// Get data from the HTML form
$title = $_POST['Title'];
$description = $_POST['Description'];
$createdAt = date("Y-m-d"); // Current server date in the format 'YYYY-MM-DD'
$adShow = 0;
$rAdShow = 0;
$likes = 0;
$isLiked = 0;
$status = 1;

// Handle file upload
$imagePath = ''; // Initialize empty image path
if (isset($_FILES['Image']) && $_FILES['Image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'images/'; // Specify the directory where you want to store uploaded images
    $uploadFile = $uploadDir . basename($_FILES['Image']['name']);

    if (move_uploaded_file($_FILES['Image']['tmp_name'], $uploadFile)) {
        // File successfully uploaded, set the image path
        $imagePath = $uploadFile;
        $imagePath = "https://gamez.altervista.org/mining/api/" . $imagePath;
    } else {
        // Handle file upload error
        echo "File upload failed.";
        exit();
    }
} else {
    // Handle missing or error in file upload
    echo "Image file is missing or invalid.";
    exit();
}

// Prepare SQL query to insert data into the 'news' table
$sql = "INSERT INTO news (Title, Description, Image, CreatedAt, AdShow, RAdShow, Likes, isliked, Status) VALUES ('$title', '$description', '$imagePath', '$createdAt', '$adShow', '$rAdShow', '$likes', '$isLiked', '$status')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the database connection
$conn->close();
?>
