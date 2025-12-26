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

// GET - List all shop items
if ($method === 'GET') {
    $query = "SELECT ID, Title, Image, Link, Status, CreatedAt FROM shop ORDER BY ID DESC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn), 'data' => []]);
        exit;
    }
    
    $items = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = [
                'id' => $row['ID'],
                'item_name' => $row['Title'],
                'description' => '', // Description column doesn't exist
                'price' => 0, // Price column doesn't exist
                'redirect_link' => $row['Link'],
                'item_image' => $row['Image'],
                'status' => $row['Status'] == 1 ? 'active' : 'inactive',
                'created_at' => $row['CreatedAt']
            ];
        }
    }
    
    echo json_encode(['success' => true, 'data' => $items]);
    exit;
}

// POST - Create shop item
if ($method === 'POST') {
    if (empty($input['item_name']) || empty($input['redirect_link'])) {
        echo json_encode(['success' => false, 'message' => 'Item name and redirect link are required.']);
        exit;
    }
    
    $name = mysqli_real_escape_string($conn, trim($input['item_name']));
    $redirectLink = mysqli_real_escape_string($conn, trim($input['redirect_link']));
    $image = isset($input['item_image']) && !empty($input['item_image']) ? mysqli_real_escape_string($conn, trim($input['item_image'])) : 'https://via.placeholder.com/300';
    $status = isset($input['status']) && $input['status'] === 'active' ? 1 : 0;
    $createdAt = date('Y-m-d');
    
    $query = "INSERT INTO shop (Title, Link, Image, Status, CreatedAt, Likes, isliked) 
              VALUES ('$name', '$redirectLink', '$image', $status, '$createdAt', '0', 0)";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Shop item created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create shop item: ' . mysqli_error($conn)]);
    }
    exit;
}

// PUT - Update shop item
if ($method === 'PUT') {
    if (empty($input['id']) || empty($input['item_name']) || empty($input['redirect_link'])) {
        echo json_encode(['success' => false, 'message' => 'ID, item name, and redirect link are required.']);
        exit;
    }
    
    $id = intval($input['id']);
    $name = mysqli_real_escape_string($conn, trim($input['item_name']));
    $redirectLink = mysqli_real_escape_string($conn, trim($input['redirect_link']));
    $image = isset($input['item_image']) && !empty($input['item_image']) ? mysqli_real_escape_string($conn, trim($input['item_image'])) : 'https://via.placeholder.com/300';
    $status = isset($input['status']) && $input['status'] === 'active' ? 1 : 0;
    
    $query = "UPDATE shop SET Title='$name', Link='$redirectLink', Image='$image', Status=$status WHERE ID=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Shop item updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update shop item: ' . mysqli_error($conn)]);
    }
    exit;
}

// DELETE - Delete shop item
if ($method === 'DELETE') {
    if (empty($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'Shop item ID is required.']);
        exit;
    }
    
    $id = intval($input['id']);
    $query = "DELETE FROM shop WHERE ID=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Shop item deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete shop item: ' . mysqli_error($conn)]);
    }
    exit;
}

mysqli_close($conn);
?>

