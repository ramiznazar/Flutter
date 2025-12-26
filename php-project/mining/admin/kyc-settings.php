<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $miningSessions = isset($_POST['kyc_mining_sessions']) ? intval($_POST['kyc_mining_sessions']) : null;
    $referralsRequired = isset($_POST['kyc_referrals_required']) ? intval($_POST['kyc_referrals_required']) : null;
    
    // Check if settings row exists
    $checkQuery = "SELECT COUNT(*) as count FROM settings";
    $checkResult = mysqli_query($conn, $checkQuery);
    $checkRow = mysqli_fetch_assoc($checkResult);
    $settingsExist = $checkRow['count'] > 0;
    
    // Ensure columns exist
    $columns = [
        'kyc_mining_sessions' => 'INT DEFAULT 14',
        'kyc_referrals_required' => 'INT DEFAULT 10'
    ];
    
    foreach ($columns as $col => $def) {
        $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM settings LIKE '$col'");
        if (mysqli_num_rows($colCheck) == 0) {
            mysqli_query($conn, "ALTER TABLE settings ADD COLUMN $col $def");
        }
    }
    
    if ($settingsExist) {
        $updateFields = [];
        if ($miningSessions !== null) $updateFields[] = "kyc_mining_sessions = $miningSessions";
        if ($referralsRequired !== null) $updateFields[] = "kyc_referrals_required = $referralsRequired";
        
        if (!empty($updateFields)) {
            $query = "UPDATE settings SET " . implode(', ', $updateFields);
            if (mysqli_query($conn, $query)) {
                $message = 'KYC settings updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error updating settings: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    } else {
        $query = "INSERT INTO settings (kyc_mining_sessions, kyc_referrals_required) 
                  VALUES ($miningSessions, $referralsRequired)";
        if (mysqli_query($conn, $query)) {
            $message = 'KYC settings created successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error creating settings: ' . mysqli_error($conn);
            $messageType = 'danger';
        }
    }
}

// Get current settings
$currentSettings = [
    'kyc_mining_sessions' => 14,
    'kyc_referrals_required' => 10
];

$query = "SELECT kyc_mining_sessions, kyc_referrals_required FROM settings LIMIT 1";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ($row['kyc_mining_sessions'] !== null) $currentSettings['kyc_mining_sessions'] = $row['kyc_mining_sessions'];
    if ($row['kyc_referrals_required'] !== null) $currentSettings['kyc_referrals_required'] = $row['kyc_referrals_required'];
}

$pageTitle = 'Crutox Admin - KYC Settings';
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
                                                <li class="breadcrumb-item active">KYC Settings</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">KYC Settings</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">KYC Requirements</h4>
                                            <p class="text-muted mb-4 font-14">Configure the requirements users must complete before they can submit KYC verification.</p>

                                            <form action="" method="POST">
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Mining Sessions Required</label>
                                                    <input type="number" class="form-control" name="kyc_mining_sessions" required placeholder="Enter required mining sessions" min="1" value="<?php echo htmlspecialchars($currentSettings['kyc_mining_sessions']); ?>" />
                                                    <small class="form-text text-muted">Number of mining sessions user must complete before KYC unlock. Default: 14</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Referrals Required</label>
                                                    <input type="number" class="form-control" name="kyc_referrals_required" required placeholder="Enter required referrals" min="1" value="<?php echo htmlspecialchars($currentSettings['kyc_referrals_required']); ?>" />
                                                    <small class="form-text text-muted">Number of referrals user must get before KYC unlock. Default: 10</small>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        Update KYC Settings
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
