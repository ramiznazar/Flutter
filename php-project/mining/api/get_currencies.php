<?php
// Connect to the database
require '../config/dbh.inc.php';

// Prepare the query to fetch data
$sql = "SELECT currency, value, icon FROM currency WHERE status = 1";

// Execute the query
$result = mysqli_query($conn, $sql);

// Check if the query was successful
if ($result) {
    // Fetch all the rows into an array
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    // Close the database connection
    mysqli_close($conn);

    // Prepare the success response
    $response = array(
        'success' => true,
        'data' => $data
    );

    // Convert the data to JSON format
    $json_response = json_encode($response);

    // Output the JSON data
    header('Content-Type: application/json');
    echo $json_response;
} else {
    // Handle the case where the query fails
    $response = array(
        'success' => false,
        'error' => 'Failed to fetch data.'
    );
    echo json_encode($response);
}
?>
