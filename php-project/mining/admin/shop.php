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
            // Save or update shop item
            $itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
            $itemName = mysqli_real_escape_string($conn, trim($_POST['item_name']));
            $redirectLink = mysqli_real_escape_string($conn, trim($_POST['redirect_link']));
            $itemImage = mysqli_real_escape_string($conn, trim($_POST['item_image']));
            $status = isset($_POST['status']) && $_POST['status'] === 'active' ? 1 : 0;
            $createdAt = date('Y-m-d');
            
            if ($itemId > 0) {
                // Update existing item
                $query = "UPDATE shop SET Title='$itemName', Link='$redirectLink', Image='$itemImage', Status=$status WHERE ID=$itemId";
                if (mysqli_query($conn, $query)) {
                    $message = 'Shop item updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating shop item: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            } else {
                // Insert new item
                $query = "INSERT INTO shop (Title, Link, Image, Status, CreatedAt, Likes, isliked) 
                          VALUES ('$itemName', '$redirectLink', '$itemImage', $status, '$createdAt', '0', 0)";
                if (mysqli_query($conn, $query)) {
                    $message = 'Shop item created successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error creating shop item: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            // Delete shop item
            $itemId = intval($_POST['item_id']);
            $query = "DELETE FROM shop WHERE ID=$itemId";
            if (mysqli_query($conn, $query)) {
                $message = 'Shop item deleted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error deleting shop item: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    }
}

// Get shop items for display
$shopItems = [];
$query = "SELECT ID, Title, Image, Link, Status, CreatedAt FROM shop ORDER BY ID DESC";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $shopItems[] = $row;
    }
}

// Get item for editing if edit_id is set
$editItem = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $editQuery = "SELECT * FROM shop WHERE ID=$editId";
    $editResult = mysqli_query($conn, $editQuery);
    if ($editResult && mysqli_num_rows($editResult) > 0) {
        $editItem = mysqli_fetch_assoc($editResult);
    }
}

$pageTitle = 'Crutox Admin - Shop Management';
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
                                                <li class="breadcrumb-item active">Shop Management</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">Shop Management</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Add/Edit Shop Item</h4>
                                            <p class="text-muted mb-4 font-14">Manage shop items with redirected links.</p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="save" />
                                                <input type="hidden" name="item_id" value="<?php echo $editItem ? $editItem['ID'] : ''; ?>" />
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Item Name</label>
                                                    <input type="text" class="form-control" name="item_name" required placeholder="Enter item name" value="<?php echo $editItem ? htmlspecialchars($editItem['Title']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Redirect Link (URL)</label>
                                                    <input type="url" class="form-control" name="redirect_link" required placeholder="https://example.com" value="<?php echo $editItem ? htmlspecialchars($editItem['Link']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Item Image URL</label>
                                                    <input type="url" class="form-control" name="item_image" required placeholder="https://example.com/image.jpg" value="<?php echo $editItem ? htmlspecialchars($editItem['Image']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Status</label>
                                                    <select class="form-control" name="status" required>
                                                        <option value="active" <?php echo ($editItem && $editItem['Status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo ($editItem && $editItem['Status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        <?php echo $editItem ? 'Update Shop Item' : 'Save Shop Item'; ?>
                                                    </button>
                                                    <a href="shop.php" class="btn btn-secondary waves-effect m-l-5">Cancel</a>
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
                                            <h4 class="mt-0 header-title">Shop Items List</h4>
                                            <p class="text-muted mb-4 font-14">All shop items with their details.</p>

                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Item Name</th>
                                                        <th>Redirect Link</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($shopItems)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No shop items found.</td>
                                                    </tr>
                                                    <?php else: ?>
                                                    <?php foreach ($shopItems as $index => $item): ?>
                                                    <tr>
                                                        <th scope="row"><?php echo $index + 1; ?></th>
                                                        <td><?php echo htmlspecialchars($item['Title']); ?></td>
                                                        <td><a href="<?php echo htmlspecialchars($item['Link']); ?>" target="_blank">View Link</a></td>
                                                        <td><span class="badge badge-<?php echo $item['Status'] == 1 ? 'success' : 'secondary'; ?>"><?php echo $item['Status'] == 1 ? 'active' : 'inactive'; ?></span></td>
                                                        <td>
                                                            <a href="shop.php?edit_id=<?php echo $item['ID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this shop item?');">
                                                                <input type="hidden" name="action" value="delete" />
                                                                <input type="hidden" name="item_id" value="<?php echo $item['ID']; ?>" />
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
