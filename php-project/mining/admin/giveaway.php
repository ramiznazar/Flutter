<?php
require '../config/dbh.inc.php';
require 'includes/auth.php';

// Require authentication
requireAuth();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save') {
            // Save or update giveaway
            $giveawayId = isset($_POST['giveaway_id']) ? intval($_POST['giveaway_id']) : 0;
            $title = mysqli_real_escape_string($conn, trim($_POST['giveaway_title']));
            $description = isset($_POST['giveaway_description']) ? mysqli_real_escape_string($conn, trim($_POST['giveaway_description'])) : '';
            $redirectLink = mysqli_real_escape_string($conn, trim($_POST['redirect_link']));
            $icon = isset($_POST['icon']) && !empty($_POST['icon']) ? mysqli_real_escape_string($conn, trim($_POST['icon'])) : 'https://img.icons8.com/color/48/000000/gift.png';
            
            if ($giveawayId > 0) {
                // Update existing giveaway
                $query = "UPDATE giveaway SET title='$title', description='$description', link='$redirectLink', icon='$icon' WHERE id=$giveawayId";
                if (mysqli_query($conn, $query)) {
                    $message = 'Giveaway updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating giveaway: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            } else {
                // Insert new giveaway
                $query = "INSERT INTO giveaway (title, description, link, icon, created_at) 
                          VALUES ('$title', '$description', '$redirectLink', '$icon', NOW())";
                if (mysqli_query($conn, $query)) {
                    $message = 'Giveaway created successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error creating giveaway: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            // Delete giveaway
            $giveawayId = intval($_POST['giveaway_id']);
            $query = "DELETE FROM giveaway WHERE id=$giveawayId";
            if (mysqli_query($conn, $query)) {
                $message = 'Giveaway deleted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error deleting giveaway: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    }
}

// Get giveaways for display
$giveaways = [];
$query = "SELECT * FROM giveaway ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $giveaways[] = $row;
    }
}

// Get giveaway for editing if edit_id is set
$editGiveaway = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $editQuery = "SELECT * FROM giveaway WHERE id=$editId";
    $editResult = mysqli_query($conn, $editQuery);
    if ($editResult && mysqli_num_rows($editResult) > 0) {
        $editGiveaway = mysqli_fetch_assoc($editResult);
    }
}

$pageTitle = 'Crutox Admin - Giveaway Management';
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
                                                <li class="breadcrumb-item active">Giveaway Management</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Giveaway Management</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Add/Edit Giveaway</h4>
                                            <p class="text-muted mb-4 font-14">Manage giveaways with redirected links.</p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="save" />
                                                <input type="hidden" name="giveaway_id" value="<?php echo $editGiveaway ? $editGiveaway['id'] : ''; ?>" />
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Giveaway Title</label>
                                                    <input type="text" class="form-control" name="giveaway_title" required placeholder="Enter giveaway title" value="<?php echo $editGiveaway ? htmlspecialchars($editGiveaway['title']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Description</label>
                                                    <textarea class="form-control" name="giveaway_description" rows="3" placeholder="Enter giveaway description"><?php echo $editGiveaway ? htmlspecialchars($editGiveaway['description']) : ''; ?></textarea>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Redirect Link (URL)</label>
                                                    <input type="url" class="form-control" name="redirect_link" required placeholder="https://example.com" value="<?php echo $editGiveaway ? htmlspecialchars($editGiveaway['link']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Icon URL</label>
                                                    <input type="url" class="form-control" name="icon" placeholder="https://example.com/icon.png" value="<?php echo $editGiveaway && isset($editGiveaway['icon']) ? htmlspecialchars($editGiveaway['icon']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        <?php echo $editGiveaway ? 'Update Giveaway' : 'Save Giveaway'; ?>
                                                    </button>
                                                    <a href="giveaway.php" class="btn btn-secondary waves-effect m-l-5">Cancel</a>
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
                                            <h4 class="mt-0 header-title">Giveaway List</h4>
                                            <p class="text-muted mb-4 font-14">All giveaways with their redirect links.</p>

                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Title</th>
                                                        <th>Description</th>
                                                        <th>Redirect Link</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($giveaways)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No giveaways found.</td>
                                                    </tr>
                                                    <?php else: ?>
                                                    <?php foreach ($giveaways as $index => $giveaway): ?>
                                                    <tr>
                                                        <th scope="row"><?php echo $index + 1; ?></th>
                                                        <td><?php echo htmlspecialchars($giveaway['title']); ?></td>
                                                        <td><?php echo htmlspecialchars(mb_substr($giveaway['description'], 0, 50)) . (mb_strlen($giveaway['description']) > 50 ? '...' : ''); ?></td>
                                                        <td><a href="<?php echo htmlspecialchars($giveaway['link']); ?>" target="_blank">View Link</a></td>
                                                        <td>
                                                            <a href="giveaway.php?edit_id=<?php echo $giveaway['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this giveaway?');">
                                                                <input type="hidden" name="action" value="delete" />
                                                                <input type="hidden" name="giveaway_id" value="<?php echo $giveaway['id']; ?>" />
                                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                            </form>
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

                        </div><!-- container -->

                    </div> <!-- Page content Wrapper -->

                </div> <!-- content -->

                <?php include 'includes/footer.php'; ?>

            </div>
            <!-- End Right content here -->

        </div>
        <!-- END wrapper -->

        <?php include 'includes/scripts.php'; ?>
