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
            // Save or update news
            $newsId = isset($_POST['news_id']) ? intval($_POST['news_id']) : 0;
            $title = mysqli_real_escape_string($conn, trim($_POST['news_title']));
            $content = mysqli_real_escape_string($conn, trim($_POST['news_content']));
            $redirectLink = mysqli_real_escape_string($conn, trim($_POST['redirect_link']));
            $image = isset($_POST['image']) ? mysqli_real_escape_string($conn, trim($_POST['image'])) : '';
            $status = isset($_POST['status']) && $_POST['status'] === 'active' ? 1 : 0;
            $createdAt = date('Y-m-d');
            
            // Check if Link column exists
            $checkLink = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'Link'");
            $hasLink = mysqli_num_rows($checkLink) > 0;
            if (!$hasLink) {
                mysqli_query($conn, "ALTER TABLE news ADD COLUMN Link TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER Description");
            }
            
            if ($newsId > 0) {
                // Update existing news
                $query = "UPDATE news SET Title='$title', Description='$content', Link='$redirectLink', Image='$image', Status=$status WHERE ID=$newsId";
                if (mysqli_query($conn, $query)) {
                    $message = 'News updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating news: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            } else {
                // Insert new news
                $query = "INSERT INTO news (Title, Description, Link, Image, Status, CreatedAt, AdShow, RAdShow, Likes, isliked) 
                          VALUES ('$title', '$content', '$redirectLink', '$image', $status, '$createdAt', 0, 0, 0, 0)";
                if (mysqli_query($conn, $query)) {
                    $message = 'News created successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Error creating news: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            // Delete news
            $newsId = intval($_POST['news_id']);
            $query = "DELETE FROM news WHERE ID=$newsId";
            if (mysqli_query($conn, $query)) {
                $message = 'News deleted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error deleting news: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    }
}

// Get news items for display
$newsItems = [];
$checkLink = mysqli_query($conn, "SHOW COLUMNS FROM news LIKE 'Link'");
$hasLink = mysqli_num_rows($checkLink) > 0;
if ($hasLink) {
    $query = "SELECT ID, Title, Description, Image, Link, Status, CreatedAt FROM news ORDER BY ID DESC";
} else {
    $query = "SELECT ID, Title, Description, Image, Status, CreatedAt FROM news ORDER BY ID DESC";
}
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $newsItems[] = $row;
    }
}

// Get news for editing if edit_id is set
$editNews = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $editQuery = "SELECT * FROM news WHERE ID=$editId";
    $editResult = mysqli_query($conn, $editQuery);
    if ($editResult && mysqli_num_rows($editResult) > 0) {
        $editNews = mysqli_fetch_assoc($editResult);
    }
}

$pageTitle = 'Crutox Admin - News Management';
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
                                                <li class="breadcrumb-item active">News Management</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">News Management</h4>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <!-- end page title end breadcrumb -->

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">Add/Edit News</h4>
                                            <p class="text-muted mb-4 font-14">Manage news articles with redirected links. These will be displayed in the app.</p>

                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="save" />
                                                <input type="hidden" name="news_id" value="<?php echo $editNews ? $editNews['ID'] : ''; ?>" />
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">News Title</label>
                                                    <input type="text" class="form-control" name="news_title" required placeholder="Enter news title" value="<?php echo $editNews ? htmlspecialchars($editNews['Title']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">News Content</label>
                                                    <textarea class="form-control" name="news_content" rows="5" required placeholder="Enter news content"><?php echo $editNews ? htmlspecialchars($editNews['Description']) : ''; ?></textarea>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Redirect Link (URL)</label>
                                                    <input type="url" class="form-control" name="redirect_link" required placeholder="https://example.com" value="<?php echo $editNews && isset($editNews['Link']) ? htmlspecialchars($editNews['Link']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Image URL (Optional)</label>
                                                    <input type="url" class="form-control" name="image" placeholder="https://example.com/image.jpg" value="<?php echo $editNews ? htmlspecialchars($editNews['Image']) : ''; ?>" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Status</label>
                                                    <select class="form-control" name="status" required>
                                                        <option value="active" <?php echo ($editNews && $editNews['Status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?php echo ($editNews && $editNews['Status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                        <?php echo $editNews ? 'Update News' : 'Save News'; ?>
                                                    </button>
                                                    <a href="news.php" class="btn btn-secondary waves-effect m-l-5">Cancel</a>
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
                                            <h4 class="mt-0 header-title">News List</h4>
                                            <p class="text-muted mb-4 font-14">All news articles with their redirect links.</p>

                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Title</th>
                                                        <th>Content</th>
                                                        <th>Redirect Link</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($newsItems)): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No news found.</td>
                                                    </tr>
                                                    <?php else: ?>
                                                    <?php foreach ($newsItems as $index => $news): ?>
                                                    <tr>
                                                        <th scope="row"><?php echo $index + 1; ?></th>
                                                        <td><?php echo htmlspecialchars($news['Title']); ?></td>
                                                        <td><?php echo htmlspecialchars(mb_substr($news['Description'], 0, 50)) . (mb_strlen($news['Description']) > 50 ? '...' : ''); ?></td>
                                                        <td><a href="<?php echo htmlspecialchars(isset($news['Link']) ? $news['Link'] : '#'); ?>" target="_blank">View Link</a></td>
                                                        <td><span class="badge badge-<?php echo $news['Status'] == 1 ? 'success' : 'secondary'; ?>"><?php echo $news['Status'] == 1 ? 'active' : 'inactive'; ?></span></td>
                                                        <td>
                                                            <a href="news.php?edit_id=<?php echo $news['ID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                            <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this news?');">
                                                                <input type="hidden" name="action" value="delete" />
                                                                <input type="hidden" name="news_id" value="<?php echo $news['ID']; ?>" />
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
