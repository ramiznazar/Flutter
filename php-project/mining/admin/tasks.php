<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save_daily') {
            // Save daily tasks (first 3 tasks)
            if (empty($_POST['task1_name']) || empty($_POST['task1_reward']) || empty($_POST['task1_link']) ||
                empty($_POST['task2_name']) || empty($_POST['task2_reward']) || empty($_POST['task2_link']) ||
                empty($_POST['task3_name']) || empty($_POST['task3_reward']) || empty($_POST['task3_link'])) {
                $message = 'All three daily tasks are required.';
                $messageType = 'danger';
            } else {
                // Get IDs of first 3 tasks
                $getIdsQuery = "SELECT ID FROM social_media_setting ORDER BY ID ASC LIMIT 3";
                $idsResult = mysqli_query($conn, $getIdsQuery);
                $ids = [];
                while ($row = mysqli_fetch_assoc($idsResult)) {
                    $ids[] = $row['ID'];
                }
                
                // Delete existing first 3 tasks
                if (!empty($ids)) {
                    $idsStr = implode(',', $ids);
                    mysqli_query($conn, "DELETE FROM social_media_setting WHERE ID IN ($idsStr)");
                }
                
                // Insert new daily tasks
                $tasks = [
                    ['name' => mysqli_real_escape_string($conn, trim($_POST['task1_name'])), 
                     'reward' => intval($_POST['task1_reward']), 
                     'link' => mysqli_real_escape_string($conn, trim($_POST['task1_link'])),
                     'icon' => isset($_POST['task1_icon']) ? mysqli_real_escape_string($conn, trim($_POST['task1_icon'])) : 'https://img.icons8.com/color/48/000000/task.png'],
                    ['name' => mysqli_real_escape_string($conn, trim($_POST['task2_name'])), 
                     'reward' => intval($_POST['task2_reward']), 
                     'link' => mysqli_real_escape_string($conn, trim($_POST['task2_link'])),
                     'icon' => isset($_POST['task2_icon']) ? mysqli_real_escape_string($conn, trim($_POST['task2_icon'])) : 'https://img.icons8.com/color/48/000000/task.png'],
                    ['name' => mysqli_real_escape_string($conn, trim($_POST['task3_name'])), 
                     'reward' => intval($_POST['task3_reward']), 
                     'link' => mysqli_real_escape_string($conn, trim($_POST['task3_link'])),
                     'icon' => isset($_POST['task3_icon']) ? mysqli_real_escape_string($conn, trim($_POST['task3_icon'])) : 'https://img.icons8.com/color/48/000000/task.png']
                ];
                
                foreach ($tasks as $task) {
                    $query = "INSERT INTO social_media_setting (Name, Link, Token, Icon) 
                              VALUES ('{$task['name']}', '{$task['link']}', '{$task['reward']}', '{$task['icon']}')";
                    mysqli_query($conn, $query);
                }
                
                // Update reset time
                if (isset($_POST['reset_time'])) {
                    $resetTime = mysqli_real_escape_string($conn, trim($_POST['reset_time']));
                    $checkQuery = "SELECT COUNT(*) as count FROM settings";
                    $checkResult = mysqli_query($conn, $checkQuery);
                    $checkRow = mysqli_fetch_assoc($checkResult);
                    
                    // Ensure column exists
                    $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM settings LIKE 'daily_tasks_reset_time'");
                    if (mysqli_num_rows($colCheck) == 0) {
                        mysqli_query($conn, "ALTER TABLE settings ADD COLUMN daily_tasks_reset_time DATETIME NULL");
                    }
                    
                    if ($checkRow['count'] > 0) {
                        mysqli_query($conn, "UPDATE settings SET daily_tasks_reset_time = '$resetTime'");
                    } else {
                        mysqli_query($conn, "INSERT INTO settings (daily_tasks_reset_time) VALUES ('$resetTime')");
                    }
                }
                
                $message = 'Daily tasks updated successfully.';
                $messageType = 'success';
            }
        } elseif ($_POST['action'] === 'save_onetime') {
            // Save one-time task
            $name = mysqli_real_escape_string($conn, trim($_POST['task_name']));
            $reward = intval($_POST['reward']);
            $link = mysqli_real_escape_string($conn, trim($_POST['redirect_link']));
            $icon = isset($_POST['icon']) ? mysqli_real_escape_string($conn, trim($_POST['icon'])) : 'https://img.icons8.com/color/48/000000/task.png';
            
            $query = "INSERT INTO social_media_setting (Name, Link, Token, Icon) 
                      VALUES ('$name', '$link', '$reward', '$icon')";
            if (mysqli_query($conn, $query)) {
                $message = 'One-time task created successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error creating task: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        } elseif ($_POST['action'] === 'update_onetime') {
            // Update one-time task
            $id = intval($_POST['task_id']);
            $name = mysqli_real_escape_string($conn, trim($_POST['task_name']));
            $reward = intval($_POST['reward']);
            $link = mysqli_real_escape_string($conn, trim($_POST['redirect_link']));
            $icon = isset($_POST['icon']) ? mysqli_real_escape_string($conn, trim($_POST['icon'])) : '';
            
            $query = "UPDATE social_media_setting SET Name='$name', Link='$link', Token='$reward', Icon='$icon' WHERE ID=$id";
            if (mysqli_query($conn, $query)) {
                $message = 'Task updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error updating task: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        } elseif ($_POST['action'] === 'delete_onetime') {
            // Delete one-time task
            $id = intval($_POST['task_id']);
            
            // Check if it's not a daily task (first 3)
            $checkQuery = "SELECT ID FROM social_media_setting ORDER BY ID ASC LIMIT 3";
            $checkResult = mysqli_query($conn, $checkQuery);
            $dailyIds = [];
            while ($row = mysqli_fetch_assoc($checkResult)) {
                $dailyIds[] = intval($row['ID']);
            }
            
            if (in_array($id, $dailyIds)) {
                $message = 'Cannot delete daily tasks. Update them instead.';
                $messageType = 'danger';
            } else {
                $query = "DELETE FROM social_media_setting WHERE ID=$id";
                if (mysqli_query($conn, $query)) {
                    $message = 'Task deleted successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting task: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            }
        }
    }
}

// Get daily tasks (first 3)
$dailyTasks = [];
$dailyQuery = "SELECT * FROM social_media_setting ORDER BY ID ASC LIMIT 3";
$dailyResult = mysqli_query($conn, $dailyQuery);
if ($dailyResult && mysqli_num_rows($dailyResult) > 0) {
    while ($row = mysqli_fetch_assoc($dailyResult)) {
        $dailyTasks[] = $row;
    }
}

// Get reset time
$resetTime = null;
$colCheck = mysqli_query($conn, "SHOW COLUMNS FROM settings LIKE 'daily_tasks_reset_time'");
if (mysqli_num_rows($colCheck) > 0) {
    $settingsResult = mysqli_query($conn, "SELECT daily_tasks_reset_time FROM settings LIMIT 1");
    if ($settingsResult && mysqli_num_rows($settingsResult) > 0) {
        $settingsRow = mysqli_fetch_assoc($settingsResult);
        $resetTime = $settingsRow['daily_tasks_reset_time'];
    }
}

// Get one-time tasks (all tasks after first 3)
$onetimeTasks = [];
$allQuery = "SELECT * FROM social_media_setting ORDER BY ID DESC";
$allResult = mysqli_query($conn, $allQuery);
$count = 0;
if ($allResult && mysqli_num_rows($allResult) > 0) {
    while ($row = mysqli_fetch_assoc($allResult)) {
        $count++;
        if ($count > 3) {
            $onetimeTasks[] = $row;
        }
    }
}

// Get task for editing if edit_id is set
$editTask = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $editQuery = "SELECT * FROM social_media_setting WHERE ID=$editId";
    $editResult = mysqli_query($conn, $editQuery);
    if ($editResult && mysqli_num_rows($editResult) > 0) {
        $editTask = mysqli_fetch_assoc($editResult);
    }
}

$pageTitle = 'Crutox Admin - Tasks Management';
include 'includes/head.php';
?>

    <body class="fixed-left">

        <!-- Loader -->
        <div id="preloader"><div id="status"><div class="spinner"></div></div></div>

        <!-- Begin page -->
        <div id="wrapper">

            <?php include 'includes/sidebar.php'; ?>

            <!-- Start right Content here -->

            <div class="content-page">
                <!-- Start content -->
                <div class="content">

                    <?php include 'includes/header.php'; ?>

                    <div class="page-content-wrapper ">

                        <div class="container-fluid">

                            <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="page-title-box">
                                        <div class="btn-group float-right">
                                            <ol class="breadcrumb hide-phone p-0 m-0">
                                                <li class="breadcrumb-item"><a href="index.php">Crutox</a></li>
                                                <li class="breadcrumb-item active">Tasks Management</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Tasks Management</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Daily Tasks Settings (3 Tasks)</h4>
                                            <p class="text-muted mb-4 font-14">
                                                <strong>Daily Tasks:</strong> Manage the 3 daily tasks that reset every 24 hours. 
                                                <br>• Users start a task and get a <strong>5-minute timer</strong>
                                                <br>• After 5 minutes, users can claim their reward
                                                <br>• Tasks reset every 24 hours automatically
                                                <br>• All 3 tasks can be manually changed from this panel
                                            </p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="save_daily" />
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Task 1 Name</label>
                                                    <input type="text" class="form-control" name="task1_name" required placeholder="e.g., Follow Instagram" value="<?php echo isset($dailyTasks[0]) ? htmlspecialchars($dailyTasks[0]['Name']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Task 1 Reward (Coins)</label>
                                                    <input type="number" class="form-control" name="task1_reward" required placeholder="e.g., 2" min="0" value="<?php echo isset($dailyTasks[0]) ? htmlspecialchars($dailyTasks[0]['Token']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Task 1 Redirect Link</label>
                                                    <input type="url" class="form-control" name="task1_link" required placeholder="https://instagram.com/..." value="<?php echo isset($dailyTasks[0]) ? htmlspecialchars($dailyTasks[0]['Link']) : ''; ?>" />
                                                </div>

                                                <hr>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Task 2 Name</label>
                                                    <input type="text" class="form-control" name="task2_name" required placeholder="e.g., Tweet on Twitter" value="<?php echo isset($dailyTasks[1]) ? htmlspecialchars($dailyTasks[1]['Name']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Task 2 Reward (Coins)</label>
                                                    <input type="number" class="form-control" name="task2_reward" required placeholder="e.g., 2" min="0" value="<?php echo isset($dailyTasks[1]) ? htmlspecialchars($dailyTasks[1]['Token']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Task 2 Redirect Link</label>
                                                    <input type="url" class="form-control" name="task2_link" required placeholder="https://twitter.com/..." value="<?php echo isset($dailyTasks[1]) ? htmlspecialchars($dailyTasks[1]['Link']) : ''; ?>" />
                                                </div>

                                                <hr>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Task 3 Name</label>
                                                    <input type="text" class="form-control" name="task3_name" required placeholder="e.g., Watch Ad" value="<?php echo isset($dailyTasks[2]) ? htmlspecialchars($dailyTasks[2]['Name']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Task 3 Reward (Coins)</label>
                                                    <input type="number" class="form-control" name="task3_reward" required placeholder="e.g., 2" min="0" value="<?php echo isset($dailyTasks[2]) ? htmlspecialchars($dailyTasks[2]['Token']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Task 3 Redirect Link</label>
                                                    <input type="url" class="form-control" name="task3_link" required placeholder="https://..." value="<?php echo isset($dailyTasks[2]) ? htmlspecialchars($dailyTasks[2]['Link']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Next Reset Time (24 hours cycle)</label>
                                                    <input type="datetime-local" class="form-control" name="reset_time" required value="<?php echo $resetTime ? date('Y-m-d\TH:i', strtotime($resetTime)) : date('Y-m-d\TH:i', strtotime('+24 hours')); ?>" />
                                                    <small class="form-text text-muted">
                                                        Set when daily tasks should reset next. Tasks will reset every 24 hours after this time. 
                                                        <?php if ($resetTime): ?>
                                                            <br><strong>Current Reset Time:</strong> <?php echo date('Y-m-d H:i:s', strtotime($resetTime)); ?>
                                                            <?php 
                                                            $nextReset = new DateTime($resetTime);
                                                            $nextReset->modify('+24 hours');
                                                            $now = new DateTime();
                                                            if ($now < $nextReset) {
                                                                $hoursUntil = ($nextReset->getTimestamp() - $now->getTimestamp()) / 3600;
                                                                echo "<br><strong>Next Reset In:</strong> " . round($hoursUntil, 1) . " hours";
                                                            }
                                                            ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        Save Daily Tasks
                                                    </button>
                                                    <button type="reset" class="btn btn-secondary waves-effect m-l-5">
                                                        Reset
                                                    </button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">One-Time Tasks</h4>
                                            <p class="text-muted mb-4 font-14">
                                                <strong>One-Time Tasks:</strong> Tasks that can only be completed once per user.
                                                <br>• Users start a task and get a <strong>1-hour timer</strong>
                                                <br>• Timer shows that system will check if task is completed
                                                <br>• After 1 hour, users get reward <strong>automatically regardless of completion</strong>
                                                <br>• Rewards are distributed automatically via cron job
                                            </p>

                                            <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addOneTimeTask">Add One-Time Task</button>

                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Task Name</th>
                                                        <th>Reward (Coins)</th>
                                                        <th>Redirect Link</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($onetimeTasks)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No one-time tasks found.</td>
                                                    </tr>
                                                    <?php else: ?>
                                                    <?php foreach ($onetimeTasks as $index => $task): ?>
                                                    <tr>
                                                        <th scope="row"><?php echo $index + 1; ?></th>
                                                        <td><?php echo htmlspecialchars($task['Name']); ?></td>
                                                        <td><?php echo htmlspecialchars($task['Token']); ?></td>
                                                        <td><a href="<?php echo htmlspecialchars($task['Link']); ?>" target="_blank">View Link</a></td>
                                                        <td>
                                                            <a href="tasks.php?edit_id=<?php echo $task['ID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                                <input type="hidden" name="action" value="delete_onetime" />
                                                                <input type="hidden" name="task_id" value="<?php echo $task['ID']; ?>" />
                                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>

                                            <!-- Modal for Add/Edit One-Time Task -->
                                            <div class="modal fade" id="addOneTimeTask" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"><?php echo $editTask ? 'Edit' : 'Add'; ?> One-Time Task</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="" method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="action" value="<?php echo $editTask ? 'update_onetime' : 'save_onetime'; ?>" />
                                                                <?php if ($editTask): ?>
                                                                <input type="hidden" name="task_id" value="<?php echo $editTask['ID']; ?>" />
                                                                <?php endif; ?>
                                                                <div class="form-group">
                                                                    <label>Task Name</label>
                                                                    <input type="text" class="form-control" name="task_name" required value="<?php echo $editTask ? htmlspecialchars($editTask['Name']) : ''; ?>" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Reward (Coins)</label>
                                                                    <input type="number" class="form-control" name="reward" required min="0" value="<?php echo $editTask ? htmlspecialchars($editTask['Token']) : ''; ?>" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Redirect Link</label>
                                                                    <input type="url" class="form-control" name="redirect_link" required value="<?php echo $editTask ? htmlspecialchars($editTask['Link']) : ''; ?>" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Icon URL (Optional)</label>
                                                                    <input type="url" class="form-control" name="icon" value="<?php echo $editTask && isset($editTask['Icon']) ? htmlspecialchars($editTask['Icon']) : ''; ?>" />
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary"><?php echo $editTask ? 'Update' : 'Save'; ?> Task</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- container -->

                    </div> <!-- Page content Wrapper -->

                </div> <!-- content -->

                <?php include 'includes/footer.php'; ?>

            </div>
            <!-- End Right content here -->

        </div>
        <!-- END wrapper -->

        <?php 
        if ($editTask): ?>
        <script>
            $(document).ready(function() {
                $('#addOneTimeTask').modal('show');
            });
        </script>
        <?php endif; ?>
        <?php include 'includes/scripts.php'; ?>
