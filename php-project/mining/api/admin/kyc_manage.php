<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require '../../config/dbh.inc.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get single KYC submission by ID
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $sql = "SELECT k.*, u.email as user_email 
                FROM kyc_submissions k 
                LEFT JOIN users u ON k.user_id = u.id 
                WHERE k.id = $id";
        $result = mysqli_query($conn, $sql);
        
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
            exit;
        }
        
        if (mysqli_num_rows($result) > 0) {
            $kyc = mysqli_fetch_assoc($result);
            echo json_encode(['success' => true, 'data' => $kyc]);
        } else {
            echo json_encode(['success' => false, 'message' => 'KYC submission not found']);
        }
    } else {
        // Get all KYC submissions
        $sql = "SELECT k.*, u.email as user_email 
                FROM kyc_submissions k 
                LEFT JOIN users u ON k.user_id = u.id 
                ORDER BY k.created_at DESC";
        $result = mysqli_query($conn, $sql);
        
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn), 'data' => []]);
            exit;
        }
        
        $submissions = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $submissions[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $submissions]);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['status'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $id = (int)$data['id'];
    $status = mysqli_real_escape_string($conn, $data['status']);
    $adminNotes = isset($data['admin_notes']) ? mysqli_real_escape_string($conn, $data['admin_notes']) : null;
    
    // Validate status
    if (!in_array($status, ['pending', 'approved', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    $adminNotesEscaped = mysqli_real_escape_string($conn, $adminNotes);
    $sql = "UPDATE kyc_submissions SET status = '$status', admin_notes = " . ($adminNotesEscaped ? "'$adminNotesEscaped'" : "NULL") . ", updated_at = NOW() WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true, 'message' => 'KYC status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating KYC: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();
?>

