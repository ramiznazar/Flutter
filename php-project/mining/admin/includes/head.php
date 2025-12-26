<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title><?php echo isset($pageTitle) ? $pageTitle : 'Zoter - Responsive Bootstrap 4 Admin Dashboard'; ?></title>
        <meta content="Admin Dashboard" name="description" />
        <meta content="Mannatthemes" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <?php if (isset($extraCSS) && is_array($extraCSS)): ?>
            <?php foreach ($extraCSS as $css): ?>
                <link href="<?php echo $css; ?>" rel="stylesheet" type="text/css">
            <?php endforeach; ?>
        <?php endif; ?>

        <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="assets/css/icons.css" rel="stylesheet" type="text/css">
        <link href="assets/css/style.css" rel="stylesheet" type="text/css">

        <style>
            /* Reduce gap between sidebar icons and text */
            #sidebar-menu > ul > li > a > i {
                margin-right: 2px !important;
            }
        </style>

    </head>

