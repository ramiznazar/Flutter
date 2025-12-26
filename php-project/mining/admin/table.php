<?php
$pageTitle = 'Zoter - Responsive Bootstrap 4 Admin Dashboard';
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

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="page-title-box">
                                        <div class="btn-group float-right">
                                            <ol class="breadcrumb hide-phone p-0 m-0">
                                                <li class="breadcrumb-item"><a href="#">Zoter</a></li>
                                                <li class="breadcrumb-item"><a href="#">Tables</a></li>
                                                <li class="breadcrumb-item active">Basic</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Basic Tables</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                           
                            <div class="row">
                               
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
            
                                            <h4 class="mt-0 header-title">Hoverable rows</h4>
                                            <p class="text-muted mb-4 font-14">
                                                Use <code>.table-striped</code> to add zebra-striping to any table row
                                                within the <code>&lt;tbody&gt;</code>.
                                            </p>
            
                                            <table class="table table-hover">
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Username</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <th scope="row">1</th>
                                                    <td>Mark</td>
                                                    <td>Otto</td>
                                                    <td>@mdo</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">2</th>
                                                    <td>Jacob</td>
                                                    <td>Thornton</td>
                                                    <td>@fat</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">3</th>
                                                    <td colspan="2">Larry the Bird</td>
                                                    <td>@twitter</td>
                                                </tr>
                                                </tbody>
                                            </table>
            
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

