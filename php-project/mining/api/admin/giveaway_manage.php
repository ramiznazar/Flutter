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

// GET - List all giveaways
if ($method === 'GET') {
    $query = "SELECT * FROM giveaway ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn), 'data' => []]);
        exit;
    }
    
    $giveaways = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $giveaways[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'reward' => 0, // Reward column doesn't exist
                'redirect_link' => $row['link'],
                'start_date' => '', // Start date doesn't exist
                'end_date' => '', // End date doesn't exist
                'status' => 'active', // Status doesn't exist
                'icon' => isset($row['icon']) ? $row['icon'] : ''
            ];
        }
    }
    
    echo json_encode(['success' => true, 'data' => $giveaways]);
    exit;
}

// POST - Create giveaway
if ($method === 'POST') {
    if (empty($input['giveaway_title']) || empty($input['redirect_link'])) {
        echo json_encode(['success' => false, 'message' => 'Title and redirect link are required.']);
        exit;
    }
    
    $title = mysqli_real_escape_string($conn, trim($input['giveaway_title']));
    $description = isset($input['giveaway_description']) ? mysqli_real_escape_string($conn, trim($input['giveaway_description'])) : '';
    $redirectLink = mysqli_real_escape_string($conn, trim($input['redirect_link']));
    $icon = isset($input['icon']) && !empty($input['icon']) ? mysqli_real_escape_string($conn, trim($input['icon'])) : 'https://img.icons8.com/color/48/000000/gift.png';
    
    $query = "INSERT INTO giveaway (title, description, link, icon, created_at) 
              VALUES ('$title', '$description', '$redirectLink', '$icon', NOW())";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Giveaway created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create giveaway: ' . mysqli_error($conn)]);
    }
    exit;
}

// PUT - Update giveaway
if ($method === 'PUT') {
    if (empty($input['id']) || empty($input['giveaway_title']) || empty($input['redirect_link'])) {
        echo json_encode(['success' => false, 'message' => 'ID, title, and redirect link are required.']);
        exit;
    }
    
    $id = intval($input['id']);
    $title = mysqli_real_escape_string($conn, trim($input['giveaway_title']));
    $description = isset($input['giveaway_description']) ? mysqli_real_escape_string($conn, trim($input['giveaway_description'])) : '';
    $redirectLink = mysqli_real_escape_string($conn, trim($input['redirect_link']));
    $icon = isset($input['icon']) && !empty($input['icon']) ? mysqli_real_escape_string($conn, trim($input['icon'])) : 'https://img.icons8.com/color/48/000000/gift.png';
    
    $query = "UPDATE giveaway SET title='$title', description='$description', link='$redirectLink', icon='$icon' WHERE id=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Giveaway updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update giveaway: ' . mysqli_error($conn)]);
    }
    exit;
}

// DELETE - Delete giveaway
if ($method === 'DELETE') {
    if (empty($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'Giveaway ID is required.']);
        exit;
    }
    
    $id = intval($input['id']);
    $query = "DELETE FROM giveaway WHERE id=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Giveaway deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete giveaway: ' . mysqli_error($conn)]);
    }
    exit;
}

mysqli_close($conn);
?>

