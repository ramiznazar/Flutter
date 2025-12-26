<?php
/**
 * Logout Handler
 * Destroys session and redirects to login page
 */

require 'includes/auth.php';

// Destroy session
session_start();
session_unset();
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/');
}

// Redirect to login
header('Location: login.php');
exit();


