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

// GET - Get tasks
if ($method === 'GET') {
    $type = isset($_GET['type']) ? $_GET['type'] : 'all';
    
    if ($type === 'daily') {
        // Get first 3 tasks as daily tasks (since table doesn't have task_type column)
        $query = "SELECT * FROM social_media_setting ORDER BY ID ASC LIMIT 3";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn), 'data' => [], 'reset_time' => null]);
            exit;
        }
        
        $tasks = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $tasks[] = [
                    'id' => $row['ID'],
                    'name' => $row['Name'],
                    'reward' => $row['Token'],
                    'redirect_link' => $row['Link'],
                    'icon' => $row['Icon']
                ];
            }
        }
        
        // Get reset time from settings (if column exists)
        $resetTime = null;
        $settingsQuery = "SHOW COLUMNS FROM settings LIKE 'daily_tasks_reset_time'";
        $colCheck = mysqli_query($conn, $settingsQuery);
        if ($colCheck && mysqli_num_rows($colCheck) > 0) {
            $settingsResult = mysqli_query($conn, "SELECT daily_tasks_reset_time FROM settings LIMIT 1");
            if ($settingsResult && mysqli_num_rows($settingsResult) > 0) {
                $settingsRow = mysqli_fetch_assoc($settingsResult);
                $resetTime = $settingsRow['daily_tasks_reset_time'];
            }
        }
        
        echo json_encode(['success' => true, 'data' => $tasks, 'reset_time' => $resetTime]);
    } else if ($type === 'onetime') {
        // Get tasks after first 3 as one-time tasks
        $query = "SELECT * FROM social_media_setting ORDER BY ID DESC";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn), 'data' => []]);
            exit;
        }
        
        $tasks = [];
        $count = 0;
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $count++;
                if ($count > 3) { // Skip first 3 (daily tasks)
                    $tasks[] = [
                        'id' => $row['ID'],
                        'name' => $row['Name'],
                        'reward' => $row['Token'],
                        'redirect_link' => $row['Link'],
                        'icon' => $row['Icon'],
                        'status' => 'active'
                    ];
                }
            }
        }
        
        echo json_encode(['success' => true, 'data' => $tasks]);
    } else {
        // Get all tasks
        $allQuery = "SELECT * FROM social_media_setting ORDER BY ID ASC";
        $allResult = mysqli_query($conn, $allQuery);
        
        if (!$allResult) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn), 'daily_tasks' => [], 'onetime_tasks' => []]);
            exit;
        }
        
        $dailyTasks = [];
        $onetimeTasks = [];
        $count = 0;
        
        if ($allResult && mysqli_num_rows($allResult) > 0) {
            while ($row = mysqli_fetch_assoc($allResult)) {
                $count++;
                if ($count <= 3) {
                    $dailyTasks[] = $row;
                } else {
                    $onetimeTasks[] = $row;
                }
            }
        }
        
        echo json_encode(['success' => true, 'daily_tasks' => $dailyTasks, 'onetime_tasks' => $onetimeTasks]);
    }
    exit;
}

// POST - Create/Update daily tasks
if ($method === 'POST' && isset($input['task_type']) && $input['task_type'] === 'daily') {
    if (empty($input['task1_name']) || empty($input['task1_reward']) || empty($input['task1_link']) ||
        empty($input['task2_name']) || empty($input['task2_reward']) || empty($input['task2_link']) ||
        empty($input['task3_name']) || empty($input['task3_reward']) || empty($input['task3_link'])) {
        echo json_encode(['success' => false, 'message' => 'All three daily tasks are required.']);
        exit;
    }
    
    // Update or insert daily tasks
    $tasks = [
        ['name' => $input['task1_name'], 'reward' => intval($input['task1_reward']), 'link' => $input['task1_link']],
        ['name' => $input['task2_name'], 'reward' => intval($input['task2_reward']), 'link' => $input['task2_link']],
        ['name' => $input['task3_name'], 'reward' => intval($input['task3_reward']), 'link' => $input['task3_link']]
    ];
    
    // Get IDs of first 3 tasks
    $getIdsQuery = "SELECT ID FROM social_media_setting ORDER BY ID ASC LIMIT 3";
    $idsResult = mysqli_query($conn, $getIdsQuery);
    $ids = [];
    while ($row = mysqli_fetch_assoc($idsResult)) {
        $ids[] = $row['ID'];
    }
    // Delete existing first 3 tasks (daily tasks)
    if (!empty($ids)) {
        $idsStr = implode(',', $ids);
        mysqli_query($conn, "DELETE FROM social_media_setting WHERE ID IN ($idsStr)");
    }
    
    // Insert new daily tasks
    foreach ($tasks as $index => $task) {
        $name = mysqli_real_escape_string($conn, trim($task['name']));
        $reward = $task['reward'];
        $link = mysqli_real_escape_string($conn, trim($task['link']));
        $icon = isset($input['task' . ($index + 1) . '_icon']) ? mysqli_real_escape_string($conn, trim($input['task' . ($index + 1) . '_icon'])) : 'https://img.icons8.com/color/48/000000/task.png';
        
        $query = "INSERT INTO social_media_setting (Name, Link, Token, Icon) 
                  VALUES ('$name', '$link', '$reward', '$icon')";
        mysqli_query($conn, $query);
    }
    
    // Update reset time
    if (isset($input['reset_time'])) {
        $resetTime = mysqli_real_escape_string($conn, trim($input['reset_time']));
        // Check if settings row exists
        $checkQuery = "SELECT COUNT(*) as count FROM settings";
        $checkResult = mysqli_query($conn, $checkQuery);
        $checkRow = mysqli_fetch_assoc($checkResult);
        
        if ($checkRow['count'] > 0) {
            mysqli_query($conn, "UPDATE settings SET daily_tasks_reset_time = '$resetTime'");
        } else {
            mysqli_query($conn, "INSERT INTO settings (daily_tasks_reset_time) VALUES ('$resetTime')");
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Daily tasks updated successfully.']);
    exit;
}

// POST - Create one-time task
if ($method === 'POST' && isset($input['task_type']) && $input['task_type'] === 'onetime') {
    if (empty($input['task_name']) || empty($input['reward']) || empty($input['redirect_link'])) {
        echo json_encode(['success' => false, 'message' => 'Task name, reward, and redirect link are required.']);
        exit;
    }
    
    $name = mysqli_real_escape_string($conn, trim($input['task_name']));
    $reward = intval($input['reward']);
    $link = mysqli_real_escape_string($conn, trim($input['redirect_link']));
    $icon = isset($input['icon']) ? mysqli_real_escape_string($conn, trim($input['icon'])) : '';
    $status = isset($input['status']) && $input['status'] === 'active' ? 1 : 0;
    
    $query = "INSERT INTO social_media_setting (Name, Link, Token, Icon) 
              VALUES ('$name', '$link', '$reward', '$icon')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'One-time task created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create task: ' . mysqli_error($conn)]);
    }
    exit;
}

// PUT - Update task
if ($method === 'PUT') {
    if (empty($input['id']) || empty($input['task_name']) || empty($input['reward']) || empty($input['redirect_link'])) {
        echo json_encode(['success' => false, 'message' => 'ID, task name, reward, and redirect link are required.']);
        exit;
    }
    
    $id = intval($input['id']);
    $name = mysqli_real_escape_string($conn, trim($input['task_name']));
    $reward = intval($input['reward']);
    $link = mysqli_real_escape_string($conn, trim($input['redirect_link']));
    $icon = isset($input['icon']) ? mysqli_real_escape_string($conn, trim($input['icon'])) : '';
    $status = isset($input['status']) && $input['status'] === 'active' ? 1 : 0;
    
    $query = "UPDATE social_media_setting SET Name='$name', Link='$link', Token='$reward', Icon='$icon' WHERE ID=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Task updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update task: ' . mysqli_error($conn)]);
    }
    exit;
}

// DELETE - Delete task
if ($method === 'DELETE') {
    if (empty($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'Task ID is required.']);
        exit;
    }
    
    $id = intval($input['id']);
    // Only allow deleting tasks that are not in the first 3 (daily tasks)
    $checkQuery = "SELECT ID FROM social_media_setting ORDER BY ID ASC LIMIT 3";
    $checkResult = mysqli_query($conn, $checkQuery);
    $dailyIds = [];
    while ($row = mysqli_fetch_assoc($checkResult)) {
        $dailyIds[] = intval($row['ID']);
    }
    
    if (in_array($id, $dailyIds)) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete daily tasks. Update them instead.']);
        exit;
    }
    
    $query = "DELETE FROM social_media_setting WHERE ID=$id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Task deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete task: ' . mysqli_error($conn)]);
    }
    exit;
}

mysqli_close($conn);
?>

