<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $kycId = intval($_POST['kyc_id']);
        $status = mysqli_real_escape_string($conn, trim($_POST['status']));
        $adminNotes = isset($_POST['admin_notes']) ? mysqli_real_escape_string($conn, trim($_POST['admin_notes'])) : '';
        
        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query = "UPDATE kyc_submissions SET status = '$status', admin_notes = '$adminNotes', updated_at = NOW() WHERE id = $kycId";
            if (mysqli_query($conn, $query)) {
                $message = 'KYC status updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error updating KYC: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        } else {
            $message = 'Invalid status.';
            $messageType = 'danger';
        }
    }
}

// Get KYC submissions
$kycSubmissions = [];
$query = "SELECT k.*, u.email as user_email 
          FROM kyc_submissions k 
          LEFT JOIN users u ON k.user_id = u.id 
          ORDER BY k.created_at DESC";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kycSubmissions[] = $row;
    }
}

// Get KYC for editing if edit_id is set
$editKYC = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $editQuery = "SELECT k.*, u.email as user_email 
                  FROM kyc_submissions k 
                  LEFT JOIN users u ON k.user_id = u.id 
                  WHERE k.id = $editId";
    $editResult = mysqli_query($conn, $editQuery);
    if ($editResult && mysqli_num_rows($editResult) > 0) {
        $editKYC = mysqli_fetch_assoc($editResult);
    }
}

$pageTitle = 'Crutox Admin - KYC Management';
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
                                                <li class="breadcrumb-item active">KYC Management</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">KYC Management</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">KYC Submissions</h4>
                                            <p class="text-muted mb-4 font-14">Review and manage KYC submissions from users.</p>

                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>User ID</th>
                                                            <th>User Email</th>
                                                            <th>Full Name</th>
                                                            <th>Date of Birth</th>
                                                            <th>Front Image</th>
                                                            <th>Back Image</th>
                                                            <th>Status</th>
                                                            <th>Submitted At</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($kycSubmissions)): ?>
                                                        <tr>
                                                            <td colspan="10" class="text-center">No KYC submissions found.</td>
                                                        </tr>
                                                        <?php else: ?>
                                                        <?php foreach ($kycSubmissions as $kyc): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($kyc['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($kyc['user_id']); ?></td>
                                                            <td><?php echo htmlspecialchars($kyc['user_email'] ?: 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($kyc['full_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($kyc['dob']); ?></td>
                                                            <td><a href="<?php echo htmlspecialchars($kyc['front_image']); ?>" target="_blank">View</a></td>
                                                            <td><a href="<?php echo htmlspecialchars($kyc['back_image']); ?>" target="_blank">View</a></td>
                                                            <td><span class="badge badge-<?php echo $kyc['status'] === 'approved' ? 'success' : ($kyc['status'] === 'rejected' ? 'danger' : 'warning'); ?>"><?php echo htmlspecialchars($kyc['status']); ?></span></td>
                                                            <td><?php echo htmlspecialchars($kyc['created_at']); ?></td>
                                                            <td>
                                                                <a href="kyc-management.php?edit_id=<?php echo $kyc['id']; ?>" class="btn btn-sm btn-primary">View/Edit</a>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($editKYC): ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">KYC Details</h4>
                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="update_status" />
                                                <input type="hidden" name="kyc_id" value="<?php echo $editKYC['id']; ?>" />
                                                <div class="form-group">
                                                    <label>User ID</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($editKYC['user_id']); ?>" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label>User Email</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($editKYC['user_email'] ?: 'N/A'); ?>" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label>Full Name</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($editKYC['full_name']); ?>" readonly />
                                                </div>
                                                <div class="form-group">
                                                    <label>Date of Birth</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($editKYC['dob']); ?>" readonly />
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Front Image</label>
                                                            <div><img src="<?php echo htmlspecialchars($editKYC['front_image']); ?>" class="img-fluid" style="max-height: 200px;" /></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Back Image</label>
                                                            <div><img src="<?php echo htmlspecialchars($editKYC['back_image']); ?>" class="img-fluid" style="max-height: 200px;" /></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <select class="form-control" name="status" required>
                                                        <option value="pending" <?php echo $editKYC['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="approved" <?php echo $editKYC['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                                        <option value="rejected" <?php echo $editKYC['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Admin Notes</label>
                                                    <textarea class="form-control" name="admin_notes" rows="3" placeholder="Add notes about this KYC submission"><?php echo htmlspecialchars($editKYC['admin_notes'] ?: ''); ?></textarea>
                                                </div>
                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">Update Status</button>
                                                    <a href="kyc-management.php" class="btn btn-secondary waves-effect m-l-5">Cancel</a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div><!-- container -->

                    </div> <!-- Page content Wrapper -->

                </div> <!-- content -->

                <?php include 'includes/footer.php'; ?>

            </div>
            <!-- End Right content here -->

        </div>
        <!-- END wrapper -->

        <?php include 'includes/scripts.php'; ?>
