<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $miningSpeed = isset($_POST['mining_speed']) ? floatval($_POST['mining_speed']) : null;
    $baseRate = isset($_POST['base_rate']) ? floatval($_POST['base_rate']) : null;
    $maxSpeed = isset($_POST['max_speed']) ? floatval($_POST['max_speed']) : null;
    
    // Check if settings row exists
    $checkQuery = "SELECT COUNT(*) as count FROM settings";
    $checkResult = mysqli_query($conn, $checkQuery);
    $checkRow = mysqli_fetch_assoc($checkResult);
    $settingsExist = $checkRow['count'] > 0;
    
    // Ensure columns exist
    $columns = [
        'mining_speed' => 'DECIMAL(10,2) DEFAULT 10.00',
        'base_mining_rate' => 'DECIMAL(10,2) DEFAULT 5.00',
        'max_mining_speed' => 'DECIMAL(10,2) DEFAULT 50.00'
    ];
    
    foreach ($columns as $col => $def) {
        $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM settings LIKE '$col'");
        if (mysqli_num_rows($colCheck) == 0) {
            mysqli_query($conn, "ALTER TABLE settings ADD COLUMN $col $def");
        }
    }
    
    if ($settingsExist) {
        $updateFields = [];
        if ($miningSpeed !== null) $updateFields[] = "mining_speed = $miningSpeed";
        if ($baseRate !== null) $updateFields[] = "base_mining_rate = $baseRate";
        if ($maxSpeed !== null) $updateFields[] = "max_mining_speed = $maxSpeed";
        
        if (!empty($updateFields)) {
            $query = "UPDATE settings SET " . implode(', ', $updateFields);
            if (mysqli_query($conn, $query)) {
                $message = 'Mining settings updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error updating settings: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    } else {
        $query = "INSERT INTO settings (mining_speed, base_mining_rate, max_mining_speed) 
                  VALUES ($miningSpeed, $baseRate, $maxSpeed)";
        if (mysqli_query($conn, $query)) {
            $message = 'Mining settings created successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error creating settings: ' . mysqli_error($conn);
            $messageType = 'danger';
        }
    }
}

// Get current settings
$currentSettings = [
    'mining_speed' => 10,
    'base_mining_rate' => 5,
    'max_mining_speed' => 50
];

$query = "SELECT mining_speed, base_mining_rate, max_mining_speed FROM settings LIMIT 1";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ($row['mining_speed'] !== null) $currentSettings['mining_speed'] = $row['mining_speed'];
    if ($row['base_mining_rate'] !== null) $currentSettings['base_mining_rate'] = $row['base_mining_rate'];
    if ($row['max_mining_speed'] !== null) $currentSettings['max_mining_speed'] = $row['max_mining_speed'];
}

$pageTitle = 'Crutox Admin - Mining Speed Settings';
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
                                                <li class="breadcrumb-item active">Mining Speed Settings</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Mining Speed Settings</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Crutox Mining Speed Configuration</h4>
                                            <p class="text-muted mb-4 font-14">Adjust the mining speed for Crutox coins. Changes will be applied immediately.</p>

                                            <form action="" method="POST">
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Mining Speed (Coins per Hour)</label>
                                                    <input type="number" class="form-control" name="mining_speed" required placeholder="Enter mining speed" min="0" step="0.01" value="<?php echo htmlspecialchars($currentSettings['mining_speed']); ?>" />
                                                    <small class="form-text text-muted">Enter the number of Crutox coins mined per hour.</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Base Mining Rate</label>
                                                    <input type="number" class="form-control" name="base_rate" required placeholder="Enter base rate" min="0" step="0.01" value="<?php echo htmlspecialchars($currentSettings['base_mining_rate']); ?>" />
                                                    <small class="form-text text-muted">Base mining rate without any boosters.</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Maximum Mining Speed</label>
                                                    <input type="number" class="form-control" name="max_speed" required placeholder="Enter maximum speed" min="0" step="0.01" value="<?php echo htmlspecialchars($currentSettings['max_mining_speed']); ?>" />
                                                    <small class="form-text text-muted">Maximum possible mining speed with all boosters.</small>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        Update Mining Speed
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
                                            <h4 class="mt-0 header-title">Current Settings</h4>
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Setting</th>
                                                        <th>Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Mining Speed</td>
                                                        <td><?php echo htmlspecialchars($currentSettings['mining_speed']); ?> coins/hour</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Base Rate</td>
                                                        <td><?php echo htmlspecialchars($currentSettings['base_mining_rate']); ?> coins/hour</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Maximum Speed</td>
                                                        <td><?php echo htmlspecialchars($currentSettings['max_mining_speed']); ?> coins/hour</td>
                                                    </tr>
                                                </tbody>
                                            </table>
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
