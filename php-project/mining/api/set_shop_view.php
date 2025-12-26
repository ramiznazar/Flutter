<?php
header("Content-Type: application/json");

require '../config/dbh.inc.php';

// Validating inputs
$Email = isset($_POST['email']) ? $_POST['email'] : null;
$NewsID = isset($_POST['id']) ? $_POST['id'] : null;

if (!$Email || !$NewsID) {
    echo json_encode(['error' => 'Email and ShopID are required']);
    exit;
}

// Fetch user ID and check if news exists
$stmt = $conn->prepare("SELECT u.id, (SELECT COUNT(*) FROM shop WHERE ID = ?) as newsExists FROM users u WHERE email = ?");
$stmt->bind_param("is", $NewsID, $Email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0 || !($row = $result->fetch_assoc()) || $row['newsExists'] == 0) {
    echo json_encode(['error' => 'User not found or Shop does not exist']);
    exit;
}

$MyUID = $row['id'];

// Check if user has already liked the news
$stmt = $conn->prepare("SELECT * FROM shop_views WHERE Shop_ID = ? AND User_ID = ?");
$stmt->bind_param("ii", $NewsID, $MyUID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['error' => 'User has already viewed this Shop']);
    exit;
}

// Perform the like action
$CreatedAt = date("Y-m-d H:i:s");

$stmt = $conn->prepare("INSERT INTO shop_views (User_ID, Shop_ID, CreatedAt) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $MyUID, $NewsID, $CreatedAt);
$stmt->execute();

// Update likes count in news table
$stmt = $conn->prepare("UPDATE shop SET Likes = Likes + 1 WHERE ID = ?");
$stmt->bind_param("i", $NewsID);
$stmt->execute();

echo json_encode(['success' => 'Shop viewed successfully']);

mysqli_close($conn);
?>
