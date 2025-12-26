<?php
$pageTitle = 'Zoter - Responsive Bootstrap 4 Admin Dashboard';
$extraScripts = [
    'assets/plugins/parsleyjs/parsley.min.js'
];
$inlineScripts = '
        <script type="text/javascript">
            $(document).ready(function() {
                $(\'form\').parsley();
            });
        </script> ';
include 'includes/head.php';
?>

<body class="fixed-left">

    <!-- Loader -->
    <div id="preloader">
        <div id="status">
            <div class="spinner"></div>
        </div>
    </div>

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

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="page-title-box">
                                    <div class="btn-group float-right">
                                        <ol class="breadcrumb hide-phone p-0 m-0">
                                            <li class="breadcrumb-item"><a href="#">Zoter</a></li>
                                            <li class="breadcrumb-item"><a href="#">Forms</a></li>
                                            <li class="breadcrumb-item active">Validation</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Form Validation</h4>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <!-- end page title end breadcrumb -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="mt-0 header-title">Validation type</h4>
                                        <p class="text-muted mb-4 font-14">Parsley is a javascript form validation
                                            library. It helps you provide your users with feedback on their form
                                            submission before sending it to your server.</p>

                                        <form class="" action="#">
                                            <div class="form-group mb-0">
                                                <label class="mb-2 pb-1">Required</label>
                                                <input type="text" class="form-control" required placeholder="Type something" />
                                            </div>

                                            <div class="form-group mb-0">
                                                <label class="my-2 py-1">Equal To</label>
                                                <div>
                                                    <input type="password" id="pass2" class="form-control" required
                                                        placeholder="Password" />
                                                </div>
                                                <div class="m-t-10">
                                                    <input type="password" class="form-control" required
                                                        data-parsley-equalto="#pass2"
                                                        placeholder="Re-Type Password" />
                                                </div>
                                            </div>

                                            <div class="form-group mb-0">
                                                <label class="my-2 py-1">E-Mail</label>
                                                <div>
                                                    <input type="email" class="form-control" required
                                                        parsley-type="email" placeholder="Enter a valid e-mail" />
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="my-2 py-1">URL</label>
                                                <div>
                                                    <input parsley-type="url" type="url" class="form-control"
                                                        required placeholder="URL" />
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="my-2 py-1">Digits</label>
                                                <div>
                                                    <input data-parsley-type="digits" type="text"
                                                        class="form-control" required
                                                        placeholder="Enter only digits" />
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label>Number</label>
                                                <div>
                                                    <input data-parsley-type="number" type="text"
                                                        class="form-control" required
                                                        placeholder="Enter only numbers" />
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="my-2 py-1">Alphanumeric</label>
                                                <div>
                                                    <input data-parsley-type="alphanum" type="text"
                                                        class="form-control" required
                                                        placeholder="Enter alphanumeric value" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="my-2 py-1">Textarea</label>
                                                <div>
                                                    <textarea required class="form-control" rows="5"></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <div>
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        Submit
                                                    </button>
                                                    <button type="reset" class="btn btn-secondary waves-effect m-l-5">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->

                    </div><!-- container -->


                </div> <!-- Page content Wrapper -->

            </div> <!-- content -->

            <?php include 'includes/footer.php'; ?>

        </div>
        <!-- End Right content here -->

    </div>
    <!-- END wrapper -->

    <?php include 'includes/scripts.php'; ?>