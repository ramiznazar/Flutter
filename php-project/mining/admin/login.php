<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Redirect if already logged in
requireGuest();

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Check admin credentials
        $query = "SELECT id, username, email, password, name FROM admin WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['name'];

                // Update last login
                $updateQuery = "UPDATE admin SET last_login = NOW() WHERE id = " . $admin['id'];
                mysqli_query($conn, $updateQuery);

                // Set remember me cookie if checked
                if ($remember) {
                    setcookie('admin_remember', $admin['id'], time() + (86400 * 30), '/'); // 30 days
                }

                // Redirect to dashboard
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$pageTitle = 'Crutox Admin - Login';
include 'includes/head.php';
?>

    <body>

    <!-- Begin page -->
    <div class="accountbg"></div>
    <div class="wrapper-page">

        <div class="card">
            <div class="card-body">

                <div class="text-center">
                    <a href="index.php" class="logo logo-admin" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none;">
                        <img src="assets/images/logo.png" height="50" alt="Flutter Logo">
                        <span style="color: #333; font-size: 24px; font-weight: bold;">Crutox</span>
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="px-3 pb-3">
                    <form class="form-horizontal m-t-20" method="POST" action="login.php">

                        <div class="form-group row">
                            <div class="col-12">
                                <input class="form-control" type="text" name="username" required placeholder="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-12">
                                <div class="position-relative">
                                    <input class="form-control" type="password" name="password" id="password" required placeholder="Password" style="padding-right: 45px;">
                                    <button type="button" class="btn btn-link position-absolute" id="togglePassword" style="right: 0; top: 50%; transform: translateY(-50%); padding: 0.375rem 0.75rem; color: #6c757d; text-decoration: none; border: none; background: transparent; cursor: pointer;">
                                        <i class="mdi mdi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-12">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="customCheck1" name="remember">
                                    <label class="custom-control-label" for="customCheck1">Remember me</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-center row m-t-20">
                            <div class="col-12">
                                <button class="btn btn-danger btn-block waves-effect waves-light" type="submit">Log In</button>
                            </div>
                        </div>

                        <div class="form-group m-t-10 mb-0 row">
                            <div class="col-12 m-t-20 text-center">
                                <small class="text-muted">Admin access only. Registration is disabled.</small>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

        <?php include 'includes/scripts.php'; ?>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const togglePassword = document.getElementById('togglePassword');
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle eye icon
                    if (type === 'password') {
                        eyeIcon.classList.remove('mdi-eye-off');
                        eyeIcon.classList.add('mdi-eye');
                    } else {
                        eyeIcon.classList.remove('mdi-eye');
                        eyeIcon.classList.add('mdi-eye-off');
                    }
                });
            });
        </script>

