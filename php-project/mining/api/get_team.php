<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require '../config/dbh.inc.php';

// Get JSON input from the API request
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

if (isset($data['email']) && isset($data['password']) && isset($data['page']) && isset($data['perPage'])) {
    $email = $data['email'];
    $password = $data['password'];

    // Default values for page and limit
    $Page = isset($data['page']) ? intval($data['page']) : 1;
    $PerPage = isset($data['perPage']) ? intval($data['perPage']) : 10;

    // Calculate the offset for pagination
    $offset = ($Page - 1) * $PerPage;

    // Query to check user credentials and get the user's ID
    $sql = "SELECT id FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user_records = array(); // Initialize an array to store multiple records

        while ($row = $result->fetch_assoc()) {
            $user_id = $row['id'];

            // Query to fetch the user's record from "users" table based on "invite_setup" field
            $sql_invite_setup = "SELECT id, ban_reason, name, token, total_invite, mining_end_time, ban_date, username FROM users WHERE invite_setup = '$user_id' LIMIT $offset, $PerPage";
            $result_invite_setup = $conn->query($sql_invite_setup);

            if ($result_invite_setup->num_rows > 0) {
                while ($row_invite_setup = $result_invite_setup->fetch_assoc()) {
                    // Add the user's record from "users" table to the array
                    $user_record = array(
                        'uid' => $row_invite_setup['id'],
                        'name' => $row_invite_setup['name'],
                        'token' => $row_invite_setup['token'],
                        'username' => $row_invite_setup['username'],
                        'profile_url' => $row_invite_setup['ban_reason'],
                        'is_mining' => $row_invite_setup['mining_end_time']>date('Y-m-d-H:i:s'),
                        'is_ping_available' => strtotime($row_invite_setup['ban_date']) < (time() - (12 * 3600)),
                        'total_invite' => $row_invite_setup['total_invite']
                    );

                    $user_records[] = $user_record;
                }
            }
        }

        // Calculate the total number of rows without the LIMIT clause
        $totalRowsWithoutLimit = mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE invite_setup = '$user_id'")->fetch_row()[0];
        // Calculate the total number of pages
        $totalPages = ceil($totalRowsWithoutLimit / $PerPage);

        // Prepare the response JSON
        $response = [
            'totalPages' => $totalPages,
            'currentPage' => $Page,
            'data' => $user_records,
        ];
        echo json_encode($response);
    } else {
        echo json_encode(array('error' => 'Invalid email or password.'));
    }
} else {
    echo json_encode(array('error' => 'Missing email, password, page or perPage parameters.'));
}

// Close the database connection
$conn->close();
