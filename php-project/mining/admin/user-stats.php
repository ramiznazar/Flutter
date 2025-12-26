<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';
$userStats = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'get_stats') {
        $email = mysqli_real_escape_string($conn, trim($_POST['user_email']));
        
        if (empty($email)) {
            $message = 'Please enter a user email.';
            $messageType = 'danger';
        } else {
            // Get user information
            $sql = "SELECT id, email, total_invite FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows !== 1) {
                $message = 'User not found.';
                $messageType = 'danger';
            } else {
                $user = $result->fetch_assoc();
                $userId = $user['id'];
                $referrals = (int)$user['total_invite'];
                
                // Get mining sessions
                $miningSql = "SELECT mining_session FROM user_levels WHERE user_id = ?";
                $miningStmt = $conn->prepare($miningSql);
                $miningStmt->bind_param('i', $userId);
                $miningStmt->execute();
                $miningResult = $miningStmt->get_result();
                $miningData = $miningResult->fetch_assoc();
                $miningSessions = $miningData ? (int)$miningData['mining_session'] : 0;
                
                $userStats = [
                    'email' => $user['email'],
                    'user_id' => $userId,
                    'mining_sessions' => $miningSessions,
                    'referrals' => $referrals
                ];
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_stats') {
        $email = mysqli_real_escape_string($conn, trim($_POST['update_email']));
        $miningSessions = isset($_POST['mining_sessions']) ? (int)$_POST['mining_sessions'] : null;
        $referrals = isset($_POST['referrals']) ? (int)$_POST['referrals'] : null;
        
        if (empty($email)) {
            $message = 'Email is required.';
            $messageType = 'danger';
        } else {
            // Get user
            $sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows !== 1) {
                $message = 'User not found.';
                $messageType = 'danger';
            } else {
                $user = $result->fetch_assoc();
                $userId = $user['id'];
                $updates = [];
                
                // Update mining sessions
                if ($miningSessions !== null && $miningSessions >= 0) {
                    // Check if user_levels record exists
                    $checkSql = "SELECT user_id FROM user_levels WHERE user_id = ?";
                    $checkStmt = $conn->prepare($checkSql);
                    $checkStmt->bind_param('i', $userId);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();
                    
                    if ($checkResult->num_rows > 0) {
                        $updateMiningSql = "UPDATE user_levels SET mining_session = ? WHERE user_id = ?";
                        $updateMiningStmt = $conn->prepare($updateMiningSql);
                        $updateMiningStmt->bind_param('ii', $miningSessions, $userId);
                        $updateMiningStmt->execute();
                    } else {
                        // Create user_levels record if it doesn't exist
                        $insertMiningSql = "INSERT INTO user_levels (user_id, mining_session, spin_wheel, current_level, achieved_at) VALUES (?, ?, 0, 1, NOW())";
                        $insertMiningStmt = $conn->prepare($insertMiningSql);
                        $insertMiningStmt->bind_param('ii', $userId, $miningSessions);
                        $insertMiningStmt->execute();
                    }
                    $updates[] = 'Mining Sessions: ' . $miningSessions;
                }
                
                // Update referrals
                if ($referrals !== null && $referrals >= 0) {
                    $updateReferralsSql = "UPDATE users SET total_invite = ? WHERE id = ?";
                    $updateReferralsStmt = $conn->prepare($updateReferralsSql);
                    $updateReferralsStmt->bind_param('ii', $referrals, $userId);
                    $updateReferralsStmt->execute();
                    $updates[] = 'Referrals: ' . $referrals;
                }
                
                if (empty($updates)) {
                    $message = 'No valid updates provided.';
                    $messageType = 'danger';
                } else {
                    $message = 'User stats updated successfully. Updated: ' . implode(', ', $updates);
                    $messageType = 'success';
                    $userStats = null; // Reset to allow new search
                }
            }
        }
    }
}

$pageTitle = 'Crutox Admin - User Stats Management';
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
                                                <li class="breadcrumb-item active">User Stats</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">User Stats Management</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Get User Stats</h4>
                                            <p class="text-muted mb-4 font-14">Enter user email to view their mining sessions and referrals count.</p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="get_stats" />
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">User Email</label>
                                                    <input type="email" class="form-control" name="user_email" required placeholder="Enter user email" value="<?php echo isset($_POST['user_email']) ? htmlspecialchars($_POST['user_email']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        Get Stats
                                                    </button>
                                                </div>
                                            </form>

                                            <?php if ($userStats): ?>
                                            <div class="mt-4">
                                                <h5>User Statistics</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <th>Email</th>
                                                            <td><?php echo htmlspecialchars($userStats['email']); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>User ID</th>
                                                            <td><?php echo $userStats['user_id']; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Mining Sessions</th>
                                                            <td><strong><?php echo $userStats['mining_sessions']; ?></strong></td>
                                                        </tr>
                                                        <tr>
                                                            <th>Referrals</th>
                                                            <td><strong><?php echo $userStats['referrals']; ?></strong></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Update User Stats</h4>
                                            <p class="text-muted mb-4 font-14">Update mining sessions and referrals count for any user.</p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="update_stats" />
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">User Email</label>
                                                    <input type="email" class="form-control" name="update_email" required placeholder="Enter user email" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Mining Sessions</label>
                                                    <input type="number" class="form-control" name="mining_sessions" min="0" placeholder="Enter new mining sessions count (leave empty to skip)" />
                                                    <small class="form-text text-muted">Current value will be replaced with this number.</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Referrals</label>
                                                    <input type="number" class="form-control" name="referrals" min="0" placeholder="Enter new referrals count (leave empty to skip)" />
                                                    <small class="form-text text-muted">Current value will be replaced with this number.</small>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-success waves-effect waves-light">
                                                        Update Stats
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

                        </div>
                        <!-- container -->

                    </div>
                    <!-- Page content Wrapper -->

                </div>
                <!-- content -->

                <?php include 'includes/footer.php'; ?>

            </div>
            <!-- End Right content here -->

        </div>
        <!-- END wrapper -->

        <?php include 'includes/scripts.php'; ?>

    </body>
</html>

