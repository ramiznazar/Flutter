<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $referrerReward = isset($_POST['referrer_reward']) ? intval($_POST['referrer_reward']) : null;
    $refereeReward = isset($_POST['referee_reward']) ? intval($_POST['referee_reward']) : null;
    $maxReferrals = isset($_POST['max_referrals']) ? intval($_POST['max_referrals']) : null;
    $bonusReward = isset($_POST['bonus_reward']) ? intval($_POST['bonus_reward']) : null;
    
    // Check if settings row exists
    $checkQuery = "SELECT COUNT(*) as count FROM settings";
    $checkResult = mysqli_query($conn, $checkQuery);
    $checkRow = mysqli_fetch_assoc($checkResult);
    $settingsExist = $checkRow['count'] > 0;
    
    // Ensure columns exist
    $columns = [
        'referrer_reward' => 'INT DEFAULT 50',
        'referee_reward' => 'INT DEFAULT 25',
        'max_referrals' => 'INT DEFAULT 100',
        'bonus_reward' => 'INT DEFAULT 500'
    ];
    
    foreach ($columns as $col => $def) {
        $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM settings LIKE '$col'");
        if (mysqli_num_rows($colCheck) == 0) {
            mysqli_query($conn, "ALTER TABLE settings ADD COLUMN $col $def");
        }
    }
    
    if ($settingsExist) {
        $updateFields = [];
        if ($referrerReward !== null) $updateFields[] = "referrer_reward = $referrerReward";
        if ($refereeReward !== null) $updateFields[] = "referee_reward = $refereeReward";
        if ($maxReferrals !== null) $updateFields[] = "max_referrals = $maxReferrals";
        if ($bonusReward !== null) $updateFields[] = "bonus_reward = $bonusReward";
        
        if (!empty($updateFields)) {
            $query = "UPDATE settings SET " . implode(', ', $updateFields);
            if (mysqli_query($conn, $query)) {
                $message = 'Referral settings updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error updating settings: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    } else {
        $query = "INSERT INTO settings (referrer_reward, referee_reward, max_referrals, bonus_reward) 
                  VALUES ($referrerReward, $refereeReward, $maxReferrals, $bonusReward)";
        if (mysqli_query($conn, $query)) {
            $message = 'Referral settings created successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error creating settings: ' . mysqli_error($conn);
            $messageType = 'danger';
        }
    }
}

// Get current settings
$currentSettings = [
    'referrer_reward' => 50,
    'referee_reward' => 25,
    'max_referrals' => 100,
    'bonus_reward' => 500
];

$query = "SELECT referrer_reward, referee_reward, max_referrals, bonus_reward FROM settings LIMIT 1";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ($row['referrer_reward'] !== null) $currentSettings['referrer_reward'] = $row['referrer_reward'];
    if ($row['referee_reward'] !== null) $currentSettings['referee_reward'] = $row['referee_reward'];
    if ($row['max_referrals'] !== null) $currentSettings['max_referrals'] = $row['max_referrals'];
    if ($row['bonus_reward'] !== null) $currentSettings['bonus_reward'] = $row['bonus_reward'];
}

$pageTitle = 'Crutox Admin - Referral Rewards Settings';
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
                                                <li class="breadcrumb-item active">Referral Rewards</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Referral Rewards Settings</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Referral Rewards Configuration</h4>
                                            <p class="text-muted mb-4 font-14">Set the rewards for referral program. Adjust rewards for referrer and referee.</p>

                                            <form action="" method="POST">
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Referrer Reward (Coins)</label>
                                                    <input type="number" class="form-control" name="referrer_reward" required placeholder="Enter referrer reward" min="0" value="<?php echo htmlspecialchars($currentSettings['referrer_reward']); ?>" />
                                                    <small class="form-text text-muted">Reward given to the person who refers (inviter).</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Referee Reward (Coins)</label>
                                                    <input type="number" class="form-control" name="referee_reward" required placeholder="Enter referee reward" min="0" value="<?php echo htmlspecialchars($currentSettings['referee_reward']); ?>" />
                                                    <small class="form-text text-muted">Reward given to the person who is referred (invitee).</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Maximum Referrals</label>
                                                    <input type="number" class="form-control" name="max_referrals" required placeholder="Enter maximum referrals" min="1" value="<?php echo htmlspecialchars($currentSettings['max_referrals']); ?>" />
                                                    <small class="form-text text-muted">Maximum number of referrals a user can have.</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Bonus Reward (Coins)</label>
                                                    <input type="number" class="form-control" name="bonus_reward" required placeholder="Enter bonus reward" min="0" value="<?php echo htmlspecialchars($currentSettings['bonus_reward']); ?>" />
                                                    <small class="form-text text-muted">Bonus reward when reaching maximum referrals.</small>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        Update Referral Settings
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
                                                        <td>Referrer Reward</td>
                                                        <td><?php echo htmlspecialchars($currentSettings['referrer_reward']); ?> coins</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Referee Reward</td>
                                                        <td><?php echo htmlspecialchars($currentSettings['referee_reward']); ?> coins</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Maximum Referrals</td>
                                                        <td><?php echo htmlspecialchars($currentSettings['max_referrals']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Bonus Reward</td>
                                                        <td><?php echo htmlspecialchars($currentSettings['bonus_reward']); ?> coins</td>
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
