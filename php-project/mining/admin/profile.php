<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $adminId = getAdminId();
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
        $messageType = 'danger';
    } else {
        // Check if email is already taken by another admin
        $emailCheckQuery = "SELECT id FROM admin WHERE email = '$email' AND id != $adminId";
        $emailCheckResult = mysqli_query($conn, $emailCheckQuery);
        
        if ($emailCheckResult && mysqli_num_rows($emailCheckResult) > 0) {
            $message = 'Email is already taken by another admin.';
            $messageType = 'danger';
        } else {
            $updateFields = [];
            $updateFields[] = "name = '$name'";
            $updateFields[] = "email = '$email'";

            // Update password if provided
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $message = 'Please enter current password to change password.';
                    $messageType = 'danger';
                } elseif ($newPassword !== $confirmPassword) {
                    $message = 'New password and confirm password do not match.';
                    $messageType = 'danger';
                } elseif (strlen($newPassword) < 6) {
                    $message = 'New password must be at least 6 characters long.';
                    $messageType = 'danger';
                } else {
                    // Verify current password
                    $adminQuery = "SELECT password FROM admin WHERE id = $adminId";
                    $adminResult = mysqli_query($conn, $adminQuery);
                    if ($adminResult && mysqli_num_rows($adminResult) > 0) {
                        $admin = mysqli_fetch_assoc($adminResult);
                        if (password_verify($currentPassword, $admin['password'])) {
                            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                            $updateFields[] = "password = '$hashedPassword'";
                        } else {
                            $message = 'Current password is incorrect.';
                            $messageType = 'danger';
                        }
                    }
                }
            }

            // Update profile if no errors
            if ($messageType !== 'danger') {
                $query = "UPDATE admin SET " . implode(', ', $updateFields) . " WHERE id = $adminId";
                if (mysqli_query($conn, $query)) {
                    // Update session variables
                    $_SESSION['admin_name'] = $name;
                    $_SESSION['admin_email'] = $email;
                    
                    $message = 'Profile updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating profile: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            }
        }
    }
}

// Fetch current admin data
$adminId = getAdminId();
$query = "SELECT id, username, email, name, created_at, last_login FROM admin WHERE id = $adminId";
$result = mysqli_query($conn, $query);
$admin = null;
if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
} else {
    header('Location: logout.php');
    exit();
}

$pageTitle = 'Crutox Admin - Profile';
include 'includes/head.php';
?>

    <body class="fixed-left">

        <!-- Begin page -->
        <div id="wrapper">

            <?php include 'includes/sidebar.php'; ?>

            <!-- Start right Content here -->
            <div class="content-page">
                <div class="content">

                    <?php include 'includes/header.php'; ?>

                    <!-- Start Page content -->
                    <div class="container-fluid">

                        <div class="row">
                            <div class="col-12">
                                <div class="card-box">
                                    <h4 class="m-t-0 header-title">My Profile</h4>
                                    <p class="text-muted font-14 m-b-30">
                                        Update your profile information and password.
                                    </p>

                                    <?php if ($message): ?>
                                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                            <?php echo htmlspecialchars($message); ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="profile.php">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
                                            <small class="form-text text-muted">Username cannot be changed.</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="name">Full Name</label>
                                            <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($admin['name']); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($admin['email']); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Account Information</label>
                                            <div class="form-control" style="height: auto; background-color: #f8f9fa;">
                                                <small class="text-muted">
                                                    <strong>Created:</strong> <?php echo date('F j, Y, g:i a', strtotime($admin['created_at'])); ?><br>
                                                    <strong>Last Login:</strong> <?php echo $admin['last_login'] ? date('F j, Y, g:i a', strtotime($admin['last_login'])) : 'Never'; ?>
                                                </small>
                                            </div>
                                        </div>

                                        <hr>

                                        <h5>Change Password</h5>
                                        <p class="text-muted">Leave blank if you don't want to change your password.</p>

                                        <div class="form-group">
                                            <label for="current_password">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password">
                                        </div>

                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password (min 6 characters)">
                                        </div>

                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                                        </div>

                                        <button type="submit" name="update_profile" class="btn btn-primary waves-effect waves-light">
                                            <i class="mdi mdi-content-save"></i> Update Profile
                                        </button>
                                        <a href="index.php" class="btn btn-secondary waves-effect waves-light">
                                            <i class="mdi mdi-arrow-left"></i> Back to Dashboard
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div> <!-- container -->

                </div> <!-- content -->

                <?php include 'includes/footer.php'; ?>

            </div>
            <!-- End Right content here -->

        </div>
        <!-- END wrapper -->

        <?php include 'includes/scripts.php'; ?>

    </body>
</html>


