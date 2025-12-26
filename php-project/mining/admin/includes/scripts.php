        <!-- jQuery  -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/modernizr.min.js"></script>
        <script src="assets/js/detect.js"></script>
        <script src="assets/js/fastclick.js"></script>
        <script src="assets/js/jquery.blockUI.js"></script>
        <script src="assets/js/waves.js"></script>
        <script src="assets/js/jquery.nicescroll.js"></script>
        
        <!-- Admin API Helper -->
        <script src="assets/js/admin-api.js"></script>

        <?php if (isset($extraScripts) && is_array($extraScripts)): ?>
            <?php foreach ($extraScripts as $script): ?>
                <script src="<?php echo $script; ?>"></script>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (isset($inlineScripts) && !empty($inlineScripts)): ?>
            <?php echo $inlineScripts; ?>
        <?php endif; ?>

        <!-- App js -->
        <script src="assets/js/app.js"></script>

    </body>
</html>



