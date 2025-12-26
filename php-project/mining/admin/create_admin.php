<?php
/**
 * Admin User Creation Script
 * Use this script to create a new admin user
 * 
 * Usage: Run this file once via browser or command line
 * Example: http://localhost/flutter-api/backend/crutox/mining/admin/create_admin.php
 * 
 * SECURITY: Delete this file after creating your admin user!
 */

require '../config/dbh.inc.php';

// Only allow this script to run if admin table is empty or via command line
$checkQuery = "SELECT COUNT(*) as count FROM admin";
$checkResult = mysqli_query($conn, $checkQuery);
$checkRow = mysqli_fetch_assoc($checkResult);
$adminExists = $checkRow['count'] > 0;

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));

    if (empty($username) || empty($email) || empty($password) || empty($name)) {
        $message = 'All fields are required.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
        $messageType = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $messageType = 'danger';
    } else {
        // Check if username or email already exists
        $checkQuery = "SELECT id FROM admin WHERE username = '$username' OR email = '$email'";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $message = 'Username or email already exists.';
            $messageType = 'danger';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert admin user
            $query = "INSERT INTO admin (username, email, password, name) VALUES ('$username', '$email', '$hashedPassword', '$name')";
            if (mysqli_query($conn, $query)) {
                $message = 'Admin user created successfully! You can now login.';
                $messageType = 'success';
                $adminExists = true; // Update flag
            } else {
                $message = 'Error creating admin user: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - Crutox Admin</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/icons.css" rel="stylesheet" type="text/css">
    <link href="assets/css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="accountbg"></div>
    <div class="wrapper-page">
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <h4 class="m-t-0">Create Admin User</h4>
                </div>

                <?php if ($adminExists && !isset($_POST['username'])): ?>
                    <div class="alert alert-info">
                        <strong>Info:</strong> Admin user(s) already exist. You can still create additional admin users.
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($messageType === 'success'): ?>
                    <div class="text-center m-t-20">
                        <a href="login.php" class="btn btn-primary waves-effect waves-light">Go to Login</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="create_admin.php" class="p-3">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required placeholder="Enter username">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="Enter email">
                        </div>

                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="Enter full name">
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password (min 6 characters)" minlength="6">
                        </div>

                        <div class="form-group text-center m-t-20">
                            <button type="submit" class="btn btn-danger btn-block waves-effect waves-light">Create Admin User</button>
                        </div>

                        <div class="text-center m-t-20">
                            <a href="login.php" class="text-muted">Already have an account? Login</a>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="alert alert-warning m-t-20">
                    <small><strong>Security Note:</strong> Delete this file (create_admin.php) after creating your admin user!</small>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>


