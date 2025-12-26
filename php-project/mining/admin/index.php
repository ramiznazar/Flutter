<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle form submission (both current_users and goal_users can be manually set)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentUsers = isset($_POST['current_users']) ? intval($_POST['current_users']) : null;
    $goalUsers = isset($_POST['goal_users']) ? intval($_POST['goal_users']) : null;
    
    // Check if settings row exists
    $checkQuery = "SELECT COUNT(*) as count FROM settings";
    $checkResult = mysqli_query($conn, $checkQuery);
    $checkRow = mysqli_fetch_assoc($checkResult);
    $settingsExist = $checkRow['count'] > 0;
    
    // Ensure columns exist
    $columns = [
        'current_users' => 'INT DEFAULT 99000',
        'goal_users' => 'INT DEFAULT 1000000'
    ];
    
    foreach ($columns as $col => $def) {
        $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM settings LIKE '$col'");
        if (mysqli_num_rows($colCheck) == 0) {
            mysqli_query($conn, "ALTER TABLE settings ADD COLUMN $col $def");
        }
    }
    
    if ($settingsExist) {
        $updateFields = [];
        if ($currentUsers !== null && $currentUsers >= 0) $updateFields[] = "current_users = $currentUsers";
        if ($goalUsers !== null && $goalUsers > 0) $updateFields[] = "goal_users = $goalUsers";
        
        if (!empty($updateFields)) {
            $query = "UPDATE settings SET " . implode(', ', $updateFields);
            if (mysqli_query($conn, $query)) {
                $message = 'User count updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error updating user count: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    } else {
        $currentUsers = $currentUsers !== null ? $currentUsers : 99000;
        $goalUsers = $goalUsers !== null ? $goalUsers : 1000000;
        $query = "INSERT INTO settings (current_users, goal_users) VALUES ($currentUsers, $goalUsers)";
        if (mysqli_query($conn, $query)) {
            $message = 'User count created successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error creating user count: ' . mysqli_error($conn);
            $messageType = 'danger';
        }
    }
}

// Get actual user count from users table (real users)
$userCountQuery = "SELECT COUNT(*) as total FROM users";
$userCountResult = mysqli_query($conn, $userCountQuery);
$actualUserCount = 0;
if ($userCountResult && mysqli_num_rows($userCountResult) > 0) {
    $userCountRow = mysqli_fetch_assoc($userCountResult);
    $actualUserCount = intval($userCountRow['total']);
}

// Get current settings (manual/fake values from settings table)
$currentSettings = [
    'current_users' => 99000,  // Fake/display users
    'goal_users' => 1000000,
    'real_users' => $actualUserCount  // Real registered users
];

$query = "SELECT current_users, goal_users FROM settings LIMIT 1";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ($row['current_users'] !== null) $currentSettings['current_users'] = $row['current_users'];
    if ($row['goal_users'] !== null) $currentSettings['goal_users'] = $row['goal_users'];
}

$progressPercent = $currentSettings['goal_users'] > 0 ? ($currentSettings['current_users'] / $currentSettings['goal_users'] * 100) : 0;
if ($progressPercent > 100) $progressPercent = 100;

$pageTitle = 'Crutox Admin - Dashboard';
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
                                                <li class="breadcrumb-item"><a href="#">Crutox</a></li>
                                                <li class="breadcrumb-item active">Dashboard</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Dashboard</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex flex-row">
                                                <div class="col-3 align-self-center">
                                                    <div class="round">
                                                        <i class="mdi mdi-account-multiple"></i>
                                                    </div>
                                                </div>
                                                <div class="col-9 align-self-center text-right">
                                                    <div class="m-l-10">
                                                        <h5 class="mt-0"><?php echo number_format($currentSettings['current_users']); ?></h5>
                                                        <p class="mb-0 text-muted">Display Users (Fake)</p>
                                                        <small class="text-muted">Real: <?php echo number_format($currentSettings['real_users']); ?></small>
                                                    </div>
                                                </div>                                                                                          
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex flex-row">
                                                <div class="col-3 align-self-center">
                                                    <div class="round">
                                                        <i class="mdi mdi-target"></i>
                                                    </div>
                                                </div>
                                                <div class="col-9 align-self-center text-right">
                                                    <div class="m-l-10">
                                                        <h5 class="mt-0"><?php echo number_format($currentSettings['goal_users']); ?></h5>
                                                        <p class="mb-0 text-muted">Goal Users</p>
                                                    </div>
                                                </div>                                                                                          
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex flex-row">
                                                <div class="col-3 align-self-center">
                                                    <div class="round">
                                                        <i class="mdi mdi-percent"></i>
                                                    </div>
                                                </div>
                                                <div class="col-9 align-self-center text-right">
                                                    <div class="m-l-10">
                                                        <h5 class="mt-0"><?php echo number_format($progressPercent, 1); ?>%</h5>
                                                        <p class="mb-0 text-muted">Progress</p>
                                                    </div>
                                                </div>                                                                                          
                                            </div>
                                            <div class="progress mt-3" style="height:3px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progressPercent; ?>%;" aria-valuenow="<?php echo $progressPercent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">User Count Management</h4>
                                            <p class="text-muted mb-4 font-14">Manually set the display user count and goal. This will be shown on the app. You can set any number you want (e.g., 99,000/1,000,000) regardless of actual registered users.</p>

                                            <form action="" method="POST">
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Real Users (Actual Registered)</label>
                                                    <input type="text" class="form-control" readonly value="<?php echo number_format($currentSettings['real_users']); ?>" />
                                                    <small class="form-text text-muted">This is the actual number of registered users in the database. This value is read-only and automatically calculated.</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Display Users (Fake/Manual)</label>
                                                    <input type="number" class="form-control" name="current_users" required placeholder="Enter display user count" min="0" value="<?php echo htmlspecialchars($currentSettings['current_users']); ?>" />
                                                    <small class="form-text text-muted">Manually set the user count to display in the app (e.g., 99,000). This can be different from real registered users.</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Goal Users</label>
                                                    <input type="number" class="form-control" name="goal_users" required placeholder="Enter goal user count" min="1" value="<?php echo htmlspecialchars($currentSettings['goal_users']); ?>" />
                                                    <small class="form-text text-muted">Target number of users to reach (e.g., 1,000,000).</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Progress Display</label>
                                                    <div class="alert alert-info">
                                                        <strong>Display Progress (Fake):</strong> 
                                                        <?php echo number_format($currentSettings['current_users']); ?> / <?php echo number_format($currentSettings['goal_users']); ?> users (<?php echo number_format($progressPercent, 1); ?>%)
                                                        <br>
                                                        <strong>Real Users:</strong> <?php echo number_format($currentSettings['real_users']); ?> registered users
                                                    </div>
                                                    <div class="progress" style="height:25px;">
                                                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $progressPercent; ?>%;" aria-valuenow="<?php echo $progressPercent; ?>" aria-valuemin="0" aria-valuemax="100">
                                                            <?php echo number_format($progressPercent, 1); ?>%
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        Update User Count
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

                        </div><!-- container -->

                    </div> <!-- Page content Wrapper -->

                </div> <!-- content -->

                <?php include 'includes/footer.php'; ?>

            </div>
            <!-- End Right content here -->

        </div>
        <!-- END wrapper -->

        <?php include 'includes/scripts.php'; ?>
