<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Redirect if already logged in
requireGuest();

$pageTitle = 'Crutox Admin - Registration';
include 'includes/head.php';
?>

    <body class="fixed-left">

        <!-- Begin page -->
        <div class="accountbg"></div>
        <div class="wrapper-page">

            <div class="card">
                <div class="card-body">

                    <div class="text-center">
                        <a href="index.php" class="logo logo-admin"><img src="assets/images/e-logo.png" height="20" alt="logo"></a>
                    </div>

                    <div class="p-3">
                        <div class="alert alert-warning" role="alert">
                            <h5 class="alert-heading"><i class="mdi mdi-alert-circle"></i> Registration Disabled</h5>
                            <p>Admin registration is not available. Please contact the system administrator to create an admin account.</p>
                            <hr>
                            <p class="mb-0">If you already have an account, please <a href="login.php" class="alert-link">login here</a>.</p>
                        </div>

                        <div class="form-group m-t-10 mb-0 row">
                            <div class="col-12 m-t-20 text-center">
                                <a href="login.php" class="btn btn-danger waves-effect waves-light">
                                    <i class="mdi mdi-login"></i> Go to Login
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <?php include 'includes/scripts.php'; ?>

