<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'give_coins') {
        $identifier = mysqli_real_escape_string($conn, trim($_POST['user_identifier']));
        $coinAmount = floatval($_POST['coin_amount']);
        $reason = isset($_POST['reason']) ? mysqli_real_escape_string($conn, trim($_POST['reason'])) : 'Admin adjustment';
        
        // Find user by email, username, or ID
        $query = "SELECT id, coin FROM users WHERE (email = '$identifier' OR username = '$identifier' OR id = '$identifier') AND account_status = 'active'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 0) {
            $message = 'User not found or account is not active.';
            $messageType = 'danger';
        } else {
            $user = mysqli_fetch_assoc($result);
            $userId = $user['id'];
            $currentCoins = floatval($user['coin']);
            $newCoins = $currentCoins + $coinAmount;
            
            if ($newCoins < 0) {
                $message = 'Insufficient coins. User has ' . $currentCoins . ' coins.';
                $messageType = 'danger';
            } else {
                $updateQuery = "UPDATE users SET coin = $newCoins WHERE id = $userId";
                if (mysqli_query($conn, $updateQuery)) {
                    $message = 'Coins updated successfully. Previous balance: ' . $currentCoins . ', New balance: ' . $newCoins . '.';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating coins: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'give_booster') {
        $identifier = mysqli_real_escape_string($conn, trim($_POST['booster_user_identifier']));
        $boosterType = mysqli_real_escape_string($conn, trim($_POST['booster_type']));
        $durationHours = floatval($_POST['booster_duration']);
        $reason = isset($_POST['booster_reason']) ? mysqli_real_escape_string($conn, trim($_POST['booster_reason'])) : 'Admin assigned booster';
        
        // Validate duration
        if ($durationHours <= 0 || $durationHours > 24) {
            $message = 'Duration must be between 0.1 and 24 hours.';
            $messageType = 'danger';
        } else {
            // Find user by email, username, or ID
            $query = "SELECT id FROM users WHERE (email = '$identifier' OR username = '$identifier' OR id = '$identifier') AND account_status = 'active'";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) === 0) {
                $message = 'User not found or account is not active.';
                $messageType = 'danger';
            } else {
                $user = mysqli_fetch_assoc($result);
                $userId = $user['id'];
                
                // Check if user_boosters table exists, if not create it
                $tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'user_boosters'");
                if (mysqli_num_rows($tableCheck) == 0) {
                    $createTable = "CREATE TABLE IF NOT EXISTS `user_boosters` (
                        `id` INT NOT NULL AUTO_INCREMENT,
                        `user_id` INT NOT NULL,
                        `booster_type` VARCHAR(50) DEFAULT '2x',
                        `started_at` DATETIME NOT NULL,
                        `expires_at` DATETIME NOT NULL,
                        `is_active` TINYINT(1) DEFAULT 1,
                        `created_at` DATETIME NOT NULL,
                        PRIMARY KEY (`id`),
                        KEY `user_id` (`user_id`),
                        KEY `expires_at` (`expires_at`),
                        KEY `is_active` (`is_active`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
                    mysqli_query($conn, $createTable);
                }
                
                // Deactivate any expired boosters for this user
                mysqli_query($conn, "UPDATE user_boosters SET is_active = 0 WHERE user_id = $userId AND expires_at <= NOW()");
                
                // Calculate expiry time
                $durationSeconds = intval($durationHours * 3600);
                $expiresAt = date('Y-m-d H:i:s', strtotime("+$durationSeconds seconds"));
                
                // Insert new booster
                $insertQuery = "INSERT INTO user_boosters (user_id, booster_type, started_at, expires_at, is_active, created_at) 
                               VALUES ($userId, '$boosterType', NOW(), '$expiresAt', 1, NOW())";
                
                if (mysqli_query($conn, $insertQuery)) {
                    $message = "Booster ($boosterType) assigned successfully. Expires at: " . date('Y-m-d H:i:s', strtotime($expiresAt));
                    $messageType = 'success';
                } else {
                    $message = 'Error assigning booster: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'reset_mystery_box') {
        $identifier = mysqli_real_escape_string($conn, trim($_POST['mystery_box_user_identifier']));
        $boxType = isset($_POST['mystery_box_type']) ? mysqli_real_escape_string($conn, trim($_POST['mystery_box_type'])) : 'all';
        
        // Validate box type if provided
        if ($boxType !== 'all' && !in_array($boxType, ['common', 'rare', 'epic', 'legendary'])) {
            $message = 'Invalid box type. Use: common, rare, epic, legendary, or all';
            $messageType = 'danger';
        } else {
            // Find user by email, username, or ID
            $query = "SELECT id, email, username FROM users WHERE (email = '$identifier' OR username = '$identifier' OR id = '$identifier') AND account_status = 'active'";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) === 0) {
                $message = 'User not found or account is not active.';
                $messageType = 'danger';
            } else {
                $user = mysqli_fetch_assoc($result);
                $userId = $user['id'];
                
                // Reset mystery box data
                if ($boxType === 'all') {
                    $resetQuery = "DELETE FROM mystery_box_claims WHERE user_id = $userId";
                    $affectedRows = 0;
                    if (mysqli_query($conn, $resetQuery)) {
                        $affectedRows = mysqli_affected_rows($conn);
                    }
                    $message = "All mystery box data reset successfully for user {$user['email']}. Affected records: $affectedRows";
                    $messageType = 'success';
                } else {
                    $resetQuery = "DELETE FROM mystery_box_claims WHERE user_id = $userId AND box_type = '$boxType'";
                    $affectedRows = 0;
                    if (mysqli_query($conn, $resetQuery)) {
                        $affectedRows = mysqli_affected_rows($conn);
                    }
                    $message = "Mystery box data for '$boxType' reset successfully for user {$user['email']}. Affected records: $affectedRows";
                    $messageType = 'success';
                }
            }
        }
    }
}

// Get search query
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$whereClause = "WHERE account_status = 'active'";
if (!empty($search)) {
    $whereClause .= " AND (email LIKE '%$search%' OR username LIKE '%$search%' OR id = '$search' OR name LIKE '%$search%')";
}

// Get users
$users = [];
$query = "SELECT id, name, email, username, coin, account_status, join_date FROM users $whereClause ORDER BY id DESC LIMIT $offset, $perPage";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get active boosters for this user
        $boosterQuery = "SELECT booster_type, expires_at FROM user_boosters 
                        WHERE user_id = {$row['id']} AND is_active = 1 AND expires_at > NOW() 
                        ORDER BY expires_at DESC LIMIT 1";
        $boosterResult = mysqli_query($conn, $boosterQuery);
        $row['active_booster'] = null;
        if ($boosterResult && mysqli_num_rows($boosterResult) > 0) {
            $row['active_booster'] = mysqli_fetch_assoc($boosterResult);
        }
        
        // Get mystery box data for this user
        // First check if clicks column exists
        $checkClicksColumn = mysqli_query($conn, "SHOW COLUMNS FROM mystery_box_claims LIKE 'clicks'");
        $clicksColumnExists = $checkClicksColumn && mysqli_num_rows($checkClicksColumn) > 0;
        
        // Build query based on available columns
        if ($clicksColumnExists) {
            $mysteryBoxQuery = "SELECT box_type, clicks, ads_watched, ads_required, box_opened, reward_coins, 
                                      last_clicked_at, last_ad_watched_at, cooldown_until, opened_at, created_at
                               FROM mystery_box_claims 
                               WHERE user_id = {$row['id']} 
                               ORDER BY box_type, created_at DESC";
        } else {
            $mysteryBoxQuery = "SELECT box_type, ads_watched, ads_required, box_opened, reward_coins, 
                                      last_ad_watched_at, cooldown_until, opened_at, created_at
                               FROM mystery_box_claims 
                               WHERE user_id = {$row['id']} 
                               ORDER BY box_type, created_at DESC";
        }
        
        $mysteryBoxResult = mysqli_query($conn, $mysteryBoxQuery);
        $row['mystery_box_data'] = [];
        if ($mysteryBoxResult && mysqli_num_rows($mysteryBoxResult) > 0) {
            while ($mbRow = mysqli_fetch_assoc($mysteryBoxResult)) {
                $row['mystery_box_data'][] = [
                    'box_type' => $mbRow['box_type'],
                    'clicks' => ($clicksColumnExists && isset($mbRow['clicks'])) ? (int)$mbRow['clicks'] : 0,
                    'ads_watched' => (int)$mbRow['ads_watched'],
                    'ads_required' => (int)$mbRow['ads_required'],
                    'box_opened' => (bool)$mbRow['box_opened'],
                    'reward_coins' => $mbRow['reward_coins'] ? (float)$mbRow['reward_coins'] : null,
                    'last_clicked_at' => ($clicksColumnExists && isset($mbRow['last_clicked_at'])) ? $mbRow['last_clicked_at'] : null,
                    'last_ad_watched_at' => $mbRow['last_ad_watched_at'],
                    'cooldown_until' => $mbRow['cooldown_until'],
                    'opened_at' => $mbRow['opened_at'],
                    'created_at' => $mbRow['created_at']
                ];
            }
        }
        
        $users[] = $row;
    }
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
$countResult = mysqli_query($conn, $countQuery);
$total = 0;
if ($countResult && mysqli_num_rows($countResult) > 0) {
    $total = mysqli_fetch_assoc($countResult)['total'];
}
$totalPages = ceil($total / $perPage);

$pageTitle = 'Crutox Admin - Users & Coins Management';
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
                                                <li class="breadcrumb-item active">Users & Coins</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Users & Coins Management</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Give Crutox Coins to User</h4>
                                            <p class="text-muted mb-4 font-14">Add or remove Crutox coins from users.</p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="give_coins" />
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">User ID / Username / Email</label>
                                                    <input type="text" class="form-control" name="user_identifier" required placeholder="Enter user ID, username, or email" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Amount (Coins)</label>
                                                    <input type="number" class="form-control" name="coin_amount" required placeholder="Enter coin amount" step="0.01" />
                                                    <small class="form-text text-muted">Use positive number to add, negative to remove coins.</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Reason / Note</label>
                                                    <textarea class="form-control" name="reason" rows="3" placeholder="Enter reason for giving coins (optional)"></textarea>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        Give Coins
                                                    </button>
                                                    <button type="reset" class="btn btn-secondary waves-effect m-l-5">
                                                        Reset
                                                    </button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Give Booster to User</h4>
                                            <p class="text-muted mb-4 font-14">Assign mining boosters to users. Boosters multiply mining speed.</p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="give_booster" />
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">User ID / Username / Email</label>
                                                    <input type="text" class="form-control" name="booster_user_identifier" required placeholder="Enter user ID, username, or email" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Booster Type</label>
                                                    <select class="form-control" name="booster_type" required>
                                                        <option value="2x">2x Booster (Double Mining Speed)</option>
                                                        <option value="3x">3x Booster (Triple Mining Speed)</option>
                                                        <option value="5x">5x Booster (5x Mining Speed)</option>
                                                    </select>
                                                    <small class="form-text text-muted">Select the booster multiplier.</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Duration (Hours)</label>
                                                    <input type="number" class="form-control" name="booster_duration" required placeholder="Enter duration in hours" min="0.1" max="24" step="0.1" value="1" />
                                                    <small class="form-text text-muted">Duration must be between 0.1 and 24 hours.</small>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Reason / Note</label>
                                                    <textarea class="form-control" name="booster_reason" rows="3" placeholder="Enter reason for giving booster (optional)"></textarea>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-success waves-effect waves-light">
                                                        Give Booster
                                                    </button>
                                                    <button type="reset" class="btn btn-secondary waves-effect m-l-5">
                                                        Reset
                                                    </button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Reset Mystery Box Data</h4>
                                            <p class="text-muted mb-4 font-14">Reset mystery box clicks, ads watched, and progress for users.</p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="reset_mystery_box" />
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">User ID / Username / Email</label>
                                                    <input type="text" class="form-control" name="mystery_box_user_identifier" required placeholder="Enter user ID, username, or email" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Box Type</label>
                                                    <select class="form-control" name="mystery_box_type" required>
                                                        <option value="all">All Box Types</option>
                                                        <option value="common">Common Box</option>
                                                        <option value="rare">Rare Box</option>
                                                        <option value="epic">Epic Box</option>
                                                        <option value="legendary">Legendary Box</option>
                                                    </select>
                                                    <small class="form-text text-muted">Select which box type to reset, or all types.</small>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-warning waves-effect waves-light" onclick="return confirm('Are you sure you want to reset mystery box data? This action cannot be undone.');">
                                                        Reset Mystery Box
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
                                            <h4 class="mt-0 header-title">Users List</h4>
                                            <p class="text-muted mb-4 font-14">All users with their coin balances.</p>

                                            <form action="" method="GET" class="mb-3">
                                                <div class="form-group">
                                                    <input type="text" class="form-control" name="search" placeholder="Search by ID, username, or email..." value="<?php echo htmlspecialchars($search); ?>" />
                                                </div>
                                                <button type="submit" class="btn btn-primary">Search</button>
                                                <a href="users.php" class="btn btn-secondary">Clear</a>
                                            </form>

                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>User ID</th>
                                                        <th>Username</th>
                                                        <th>Email</th>
                                                        <th>Name</th>
                                                        <th>Coins Balance</th>
                                                        <th>Active Booster</th>
                                                        <th>Mystery Box Data</th>
                                                        <th>Join Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($users)): ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center">No users found.</td>
                                                    </tr>
                                                    <?php else: ?>
                                                    <?php foreach ($users as $index => $user): ?>
                                                    <tr>
                                                        <th scope="row"><?php echo $offset + $index + 1; ?></th>
                                                        <td>USR<?php echo str_pad($user['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                                        <td><?php echo htmlspecialchars($user['username'] ?: 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                        <td><?php echo number_format(floatval($user['coin']), 2); ?></td>
                                                        <td>
                                                            <?php if (isset($user['active_booster']) && $user['active_booster']): ?>
                                                                <span class="badge badge-success">
                                                                    <?php echo htmlspecialchars($user['active_booster']['booster_type']); ?>
                                                                </span>
                                                                <br>
                                                                <small class="text-muted">
                                                                    Expires: <?php echo date('M d, H:i', strtotime($user['active_booster']['expires_at'])); ?>
                                                                </small>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">None</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($user['mystery_box_data'])): ?>
                                                                <div style="max-width: 300px;">
                                                                    <?php foreach ($user['mystery_box_data'] as $mbData): ?>
                                                                        <div class="mb-2 p-2 border rounded" style="background-color: #f8f9fa;">
                                                                            <strong><?php echo ucfirst($mbData['box_type']); ?>:</strong><br>
                                                                            <small>
                                                                                Clicks: <?php echo $mbData['clicks']; ?> | 
                                                                                Ads: <?php echo $mbData['ads_watched']; ?>/<?php echo $mbData['ads_required']; ?><br>
                                                                                <?php if ($mbData['box_opened']): ?>
                                                                                    <span class="badge badge-success">Opened</span>
                                                                                    <?php if ($mbData['reward_coins']): ?>
                                                                                        (<?php echo number_format($mbData['reward_coins'], 2); ?> coins)
                                                                                    <?php endif; ?>
                                                                                <?php else: ?>
                                                                                    <span class="badge badge-warning">Not Opened</span>
                                                                                <?php endif; ?>
                                                                                <?php if ($mbData['last_clicked_at']): ?>
                                                                                    <br><small class="text-muted">Last Click: <?php echo date('M d, H:i', strtotime($mbData['last_clicked_at'])); ?></small>
                                                                                <?php endif; ?>
                                                                            </small>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary">No Data</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($user['join_date']); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>

                                            <?php if ($totalPages > 1): ?>
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination">
                                                    <?php if ($page > 1): ?>
                                                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a></li>
                                                    <?php endif; ?>
                                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a></li>
                                                    <?php endfor; ?>
                                                    <?php if ($page < $totalPages): ?>
                                                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </nav>
                                            <?php endif; ?>

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
