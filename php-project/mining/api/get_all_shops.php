<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require '../config/dbh.inc.php';

$Email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : null;
$Page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$PerPage = isset($_POST['perPage']) ? intval($_POST['perPage']) : 10;

if (empty($Email)) {
    echo json_encode(['error' => 'Email is required']);
    exit;
}

// Fetch user ID using prepared statement
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $Email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

$row = $result->fetch_assoc();
$MyUID = $row["id"];

// Calculate the offset for pagination
$offset = ($Page - 1) * $PerPage;

// SQL query to fetch news, like status with pagination
$sql = "SELECT shop.*,
               IF(shop_views.Shop_ID IS NOT NULL, 1, 0) AS isliked
        FROM shop
        LEFT JOIN shop_views ON shop.ID = shop_views.Shop_ID AND shop_views.User_ID = ?
        ORDER BY shop.ID DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $MyUID, $offset, $PerPage);
$stmt->execute();
$result = $stmt->get_result();

$newsData = [];
while ($row = $result->fetch_assoc()) {
    $newsID = $row['ID'];

    // Fetch top 3 likers for each news item
    $likers_sql = "SELECT u.ban_reason 
                   FROM shop_views nl
                   JOIN users u ON nl.User_ID = u.id
                   WHERE nl.Shop_ID = ?
                   ORDER BY nl.CreatedAt DESC 
                   LIMIT 3";

    $likers_stmt = $conn->prepare($likers_sql);
    $likers_stmt->bind_param("i", $newsID);
    $likers_stmt->execute();
    $likers_result = $likers_stmt->get_result();

    $likers = [];
    while ($liker_row = $likers_result->fetch_assoc()) {
        $likers[] = $liker_row['ban_reason'] ?: null;
    }

    $newsData[] = [
        'id' => $row['ID'],
        'image' => $row['Image'],
        'title' => $row['Title'],
        'webLink' => $row['Link'],
        'createdAt' => $row['CreatedAt'],
        'views' => $row['Likes'],
        'isViewed' => $row['isliked'],
        'lastViewers' => $likers
    ];
}

// Calculate the total number of rows without the LIMIT clause
$totalRowsWithoutLimit = mysqli_query($conn, "SELECT COUNT(*) FROM shop")->fetch_row()[0];

// Calculate the total number of pages
$totalPages = ceil($totalRowsWithoutLimit / $PerPage);


// Prepare the response JSON
$response = [
    'totalPages' => $totalPages,
    'currentPage' => $Page,
    'data' => $newsData,
];

echo json_encode($response);

mysqli_close($conn);
?>
