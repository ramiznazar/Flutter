<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

require '../config/dbh.inc.php';

// Parameters
$page = $_GET['page'] ?? 1; // Default page is 1
$perPage = 10; // Number of records per page

// Calculate the offset
$offset = ($page - 1) * $perPage;

// Get the query parameter (if available)
$query = isset($_GET['query']) ? $_GET['query'] : "SELECT * FROM users";

// Add pagination to the query
$query .= " LIMIT $perPage OFFSET $offset";

// Execute the query
$result = $conn->query($query);

if ($result) {
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
} else {
    echo json_encode(["error" => "Error executing the query: " . $conn->error]);
}

// Close the database connection
$conn->close();
?>
