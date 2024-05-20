</main>
</div>
    <?php if(is_authorized() !== true):?>
        <?php include_file('/include/footer/user_auth_form.php', true); ?>
    <?php endif;?>
<?php
assets()->addJs(SITE_TEMPLATE_PATH . '/assets/js/vendors/jquery.min.js');
assets()->addJs(SITE_TEMPLATE_PATH . '/assets/js/vendors/owl.carousel.js');
assets()->addJs(SITE_TEMPLATE_PATH . '/assets/js/vendors/nouislider.min.js');
assets()->addJs(SITE_TEMPLATE_PATH . '/assets/js/vendors/select-custom.js');
assets()->addJs(SITE_TEMPLATE_PATH . '/assets/js/vendors/tooltip-custom.js');
assets()->addJs(SITE_TEMPLATE_PATH . '/assets/vendors/timer.jquery.js');
assets()->addJs(SITE_TEMPLATE_PATH . '/assets/js/common.js');
?>
</body>
</html>