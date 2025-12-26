<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../../config/dbh.inc.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// GET - List all news
if ($method === 'GET') {
    // Check if Link column exists
    $checkLink = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'Link'");
    $hasLink = mysqli_num_rows($checkLink) > 0;
    
    if ($hasLink) {
        $query = "SELECT ID, Title, Description, Image, Link, Status, CreatedAt FROM news ORDER BY ID DESC";
    } else {
        $query = "SELECT ID, Title, Description, Image, Status, CreatedAt FROM news ORDER BY ID DESC";
    }
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn), 'data' => []]);
        exit;
    }
    
    $news = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $news[] = [
                'id' => $row['ID'],
                'title' => $row['Title'],
                'content' => $row['Description'],
                'redirect_link' => isset($row['Link']) ? $row['Link'] : '',
                'image' => $row['Image'],
                'status' => $row['Status'] == 1 ? 'active' : 'inactive',
                'created_at' => $row['CreatedAt']
            ];
        }
    }
    
    echo json_encode(['success' => true, 'data' => $news]);
    exit;
}

// POST - Create news
if ($method === 'POST') {
    if (empty($input['news_title']) || empty($input['news_content']) || empty($input['redirect_link'])) {
        echo json_encode(['success' => false, 'message' => 'Title, content, and redirect link are required.']);
        exit;
    }
    
    $title = mysqli_real_escape_string($conn, trim($input['news_title']));
    $content = mysqli_real_escape_string($conn, trim($input['news_content']));
    $redirectLink = mysqli_real_escape_string($conn, trim($input['redirect_link']));
    $status = isset($input['status']) && $input['status'] === 'active' ? 1 : 0;
    $image = isset($input['image']) ? mysqli_real_escape_string($conn, trim($input['image'])) : '';
    $createdAt = date('Y-m-d');
    
    // Check if Link column exists
    $checkLink = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'Link'");
    $hasLink = mysqli_num_rows($checkLink) > 0;
    
    if ($hasLink) {
        $query = "INSERT INTO news (Title, Description, Link, Image, Status, CreatedAt, AdShow, RAdShow, Likes, isliked) 
                  VALUES ('$title', '$content', '$redirectLink', '$image', $status, '$createdAt', 0, 0, 0, 0)";
    } else {
        // If Link column doesn't exist, add it first
        mysqli_query($conn, "ALTER TABLE news ADD COLUMN Link TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER Description");
        $query = "INSERT INTO news (Title, Description, Link, Image, Status, CreatedAt, AdShow, RAdShow, Likes, isliked) 
                  VALUES ('$title', '$content', '$redirectLink', '$image', $status, '$createdAt', 0, 0, 0, 0)";
    }
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'News created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create news: ' . mysqli_error($conn)]);
    }
    exit;
}

// PUT - Update news
if ($method === 'PUT') {
    if (empty($input['id']) || empty($input['news_title']) || empty($input['news_content']) || empty($input['redirect_link'])) {
        echo json_encode(['success' => false, 'message' => 'ID, title, content, and redirect link are required.']);
        exit;
    }
    
    $id = intval($input['id']);
    $title = mysqli_real_escape_string($conn, trim($input['news_title']));
    $content = mysqli_real_escape_string($conn, trim($input['news_content']));
    $redirectLink = mysqli_real_escape_string($conn, trim($input['redirect_link']));
    $status = isset($input['status']) && $input['status'] === 'active' ? 1 : 0;
    $image = isset($input['image']) ? mysqli_real_escape_string($conn, trim($input['image'])) : '';
    
    // Check if Link column exists, if not add it
    $checkLink = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'Link'");
    if (mysqli_num_rows($checkLink) == 0) {
        mysqli_query($conn, "ALTER TABLE news ADD COLUMN Link TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER Description");
    }
    
    $query = "UPDATE news SET Title='$title', Description='$content', Link='$redirectLink', Image='$image', Status=$status WHERE ID=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'News updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update news: ' . mysqli_error($conn)]);
    }
    exit;
}

// DELETE - Delete news
if ($method === 'DELETE') {
    if (empty($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'News ID is required.']);
        exit;
    }
    
    $id = intval($input['id']);
    $query = "DELETE FROM news WHERE ID=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'News deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete news: ' . mysqli_error($conn)]);
    }
    exit;
}

mysqli_close($conn);
?>


