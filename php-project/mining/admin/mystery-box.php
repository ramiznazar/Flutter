<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['box_type'])) {
        $boxType = mysqli_real_escape_string($conn, trim($_POST['box_type']));
        $cooldown = isset($_POST['cooldown']) ? intval($_POST['cooldown']) : 0;
        $adsRequired = isset($_POST['ads_required']) ? intval($_POST['ads_required']) : 1;
        $minCoins = isset($_POST['min_coins']) ? floatval($_POST['min_coins']) : 0;
        $maxCoins = isset($_POST['max_coins']) ? floatval($_POST['max_coins']) : 0;
        
        // Check if settings row exists
        $checkQuery = "SELECT COUNT(*) as count FROM settings";
        $checkResult = mysqli_query($conn, $checkQuery);
        $checkRow = mysqli_fetch_assoc($checkResult);
        $settingsExist = $checkRow['count'] > 0;
        
        // Ensure columns exist
        $fieldPrefix = $boxType . '_box_';
        $columns = [
            $fieldPrefix . 'cooldown' => 'INT DEFAULT 0',
            $fieldPrefix . 'ads' => 'INT DEFAULT 1',
            $fieldPrefix . 'min_coins' => 'DECIMAL(10,2) DEFAULT 1.00',
            $fieldPrefix . 'max_coins' => 'DECIMAL(10,2) DEFAULT 5.00'
        ];
        
        foreach ($columns as $col => $def) {
            $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM settings LIKE '$col'");
            if (mysqli_num_rows($colCheck) == 0) {
                mysqli_query($conn, "ALTER TABLE settings ADD COLUMN $col $def");
            }
        }
        
        if ($settingsExist) {
            $query = "UPDATE settings SET 
                      {$fieldPrefix}cooldown = $cooldown,
                      {$fieldPrefix}ads = $adsRequired,
                      {$fieldPrefix}min_coins = $minCoins,
                      {$fieldPrefix}max_coins = $maxCoins";
            if (mysqli_query($conn, $query)) {
                $message = ucfirst($boxType) . ' mystery box settings updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error updating settings: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        } else {
            $query = "INSERT INTO settings ({$fieldPrefix}cooldown, {$fieldPrefix}ads, {$fieldPrefix}min_coins, {$fieldPrefix}max_coins) 
                      VALUES ($cooldown, $adsRequired, $minCoins, $maxCoins)";
            if (mysqli_query($conn, $query)) {
                $message = ucfirst($boxType) . ' mystery box settings created successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error creating settings: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    }
}

// Get current settings
$boxSettings = [
    'common' => ['cooldown' => 0, 'ads' => 1, 'min_coins' => 1.00, 'max_coins' => 5.00],
    'rare' => ['cooldown' => 5, 'ads' => 3, 'min_coins' => 5.00, 'max_coins' => 15.00],
    'epic' => ['cooldown' => 10, 'ads' => 6, 'min_coins' => 15.00, 'max_coins' => 50.00],
    'legendary' => ['cooldown' => 30, 'ads' => 10, 'min_coins' => 50.00, 'max_coins' => 200.00]
];

$query = "SELECT * FROM settings LIMIT 1";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    foreach (['common', 'rare', 'epic', 'legendary'] as $type) {
        $prefix = $type . '_box_';
        if (isset($row[$prefix . 'cooldown']) && $row[$prefix . 'cooldown'] !== null) {
            $boxSettings[$type]['cooldown'] = $row[$prefix . 'cooldown'];
        }
        if (isset($row[$prefix . 'ads']) && $row[$prefix . 'ads'] !== null) {
            $boxSettings[$type]['ads'] = $row[$prefix . 'ads'];
        }
        if (isset($row[$prefix . 'min_coins']) && $row[$prefix . 'min_coins'] !== null) {
            $boxSettings[$type]['min_coins'] = $row[$prefix . 'min_coins'];
        }
        if (isset($row[$prefix . 'max_coins']) && $row[$prefix . 'max_coins'] !== null) {
            $boxSettings[$type]['max_coins'] = $row[$prefix . 'max_coins'];
        }
    }
}

$pageTitle = 'Crutox Admin - Mystery Box Settings';
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
                                                <li class="breadcrumb-item active">Mystery Box Settings</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Mystery Box Settings</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <?php 
                            $boxTypes = [
                                'common' => ['title' => 'Common Mystery Box', 'default_cooldown' => 0, 'default_ads' => 1],
                                'rare' => ['title' => 'Rare Mystery Box', 'default_cooldown' => 5, 'default_ads' => 3],
                                'epic' => ['title' => 'Epic Mystery Box', 'default_cooldown' => 10, 'default_ads' => 6],
                                'legendary' => ['title' => 'Legendary Mystery Box', 'default_cooldown' => 30, 'default_ads' => 10]
                            ];
                            foreach ($boxTypes as $boxType => $boxInfo): 
                            ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title"><?php echo $boxInfo['title']; ?></h4>
                                            <p class="text-muted mb-4 font-14">
                                                Configure <?php echo strtolower($boxInfo['title']); ?> settings. 
                                                <strong>Cooldown period can be adjusted anytime from this panel without app update.</strong>
                                            </p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="box_type" value="<?php echo $boxType; ?>" />
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="mb-2">Cooldown Period Between Ads (Minutes)</label>
                                                            <input type="number" class="form-control" name="cooldown" required placeholder="Enter cooldown in minutes" min="0" value="<?php echo htmlspecialchars($boxSettings[$boxType]['cooldown']); ?>" />
                                                            <small class="form-text text-muted">
                                                                <strong>Cooldown between each ad watch.</strong> 
                                                                <br>• Set to <strong>0</strong> to remove cooldown (users can watch ads immediately)
                                                                <br>• Set to any number (e.g., 5) to add cooldown between ads
                                                                <br>• Changes take effect immediately - no app update needed
                                                                <br>• Current: <?php echo $boxSettings[$boxType]['cooldown']; ?> minutes between ads
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="mb-2">Ads Required</label>
                                                            <input type="number" class="form-control" name="ads_required" required placeholder="Enter number of ads" min="1" value="<?php echo htmlspecialchars($boxSettings[$boxType]['ads']); ?>" />
                                                            <small class="form-text text-muted">Number of ads user must watch to open box.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="mb-2">Min Coins Reward</label>
                                                            <input type="number" class="form-control" name="min_coins" required placeholder="Enter minimum coins" min="0" step="0.01" value="<?php echo htmlspecialchars($boxSettings[$boxType]['min_coins']); ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="mb-2">Max Coins Reward</label>
                                                            <input type="number" class="form-control" name="max_coins" required placeholder="Enter maximum coins" min="0" step="0.01" value="<?php echo htmlspecialchars($boxSettings[$boxType]['max_coins']); ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        Update <?php echo $boxInfo['title']; ?>
                                                    </button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Current Mystery Box Settings</h4>
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Box Type</th>
                                                        <th>Cooldown (Minutes)</th>
                                                        <th>Ads Required</th>
                                                        <th>Min Coins</th>
                                                        <th>Max Coins</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($boxTypes as $boxType => $boxInfo): ?>
                                                    <tr>
                                                        <td><span class="badge badge-<?php echo $boxType === 'common' ? 'secondary' : ($boxType === 'rare' ? 'info' : ($boxType === 'epic' ? 'purple' : 'warning')); ?>"><?php echo $boxInfo['title']; ?></span></td>
                                                        <td><?php echo htmlspecialchars($boxSettings[$boxType]['cooldown']); ?></td>
                                                        <td><?php echo htmlspecialchars($boxSettings[$boxType]['ads']); ?></td>
                                                        <td><?php echo htmlspecialchars($boxSettings[$boxType]['min_coins']); ?></td>
                                                        <td><?php echo htmlspecialchars($boxSettings[$boxType]['max_coins']); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
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
