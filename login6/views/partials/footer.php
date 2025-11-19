    <!-- JavaScript -->
    <script src="<?php echo asset('assets/js/validations.js'); ?>"></script>
    
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo asset($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
