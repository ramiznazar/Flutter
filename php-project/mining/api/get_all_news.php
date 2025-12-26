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
$sql = "SELECT news.*,
               IF(news_like.News_ID IS NOT NULL, 1, 0) AS isliked
        FROM news
        LEFT JOIN news_like ON news.ID = news_like.News_ID AND news_like.User_ID = ?
        ORDER BY news.ID DESC
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
                   FROM news_like nl
                   JOIN users u ON nl.User_ID = u.id
                   WHERE nl.News_ID = ?
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
        'webLink' => "https://crutox.com/" . $row['ID'],
        'createdAt' => $row['CreatedAt'],
        'views' => $row['Likes'],
        'isViewed' => $row['isliked'],
        'lastViewers' => $likers
    ];
}

// Calculate the total number of rows without the LIMIT clause
$totalRowsWithoutLimit = mysqli_query($conn, "SELECT COUNT(*) FROM news")->fetch_row()[0];

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
