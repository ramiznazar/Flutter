<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require '../config/dbh.inc.php';

// Default values for page and limit
$Page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$PerPage = isset($_POST['perPage']) ? intval($_POST['perPage']) : 10;

// Calculate the offset for pagination
$offset = ($Page - 1) * $PerPage;

// SQL query with pagination
$sql = "SELECT * FROM giveaway ORDER BY created_at DESC LIMIT $offset, $PerPage";

$result = $conn->query($sql);

$records = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    // Calculate the total number of rows without the LIMIT clause
    $totalRowsWithoutLimit = mysqli_query($conn, "SELECT COUNT(*) FROM giveaway")->fetch_row()[0];
    // Calculate the total number of pages
    $totalPages = ceil($totalRowsWithoutLimit / $PerPage);

    // Prepare the response JSON
    $response = [
        'totalPages' => $totalPages,
        'currentPage' => $Page,
        'data' => $records,
    ];
    echo json_encode($response);
} else {
    $errorResponse = array(
        'totalPages' => $totalPages ? null : 1,
        'currentPage' => $Page,
        'data' => $records,
    );
    echo json_encode($errorResponse);
}

$conn->close();
