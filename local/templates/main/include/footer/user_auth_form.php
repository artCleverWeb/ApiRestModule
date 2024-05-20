<?php
/** @var  $APPLICATION */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<div class="popup auth-popup auth-popup_login js-popup" id="auth-popup">
    <div class="popup__overlay"></div>
    <div class="popup__data">
        <?php $APPLICATION->IncludeComponent(
            "bitrix:main.auth.form",
            "",
            Array(
                "AUTH_FORGOT_PASSWORD_URL" => "",
                "AUTH_REGISTER_URL" => "",
                "AUTH_SUCCESS_URL" => "/"
            )
        );?>
        <button type="button" class="popup__button-close">
            <svg class="icon-svg popup__button-close-icon">
                <use xlink:href="#icon-close"></use>
            </svg>
        </button>
    </div>
</div>
<script>
    $(document).ready(function() {
        popupOpen("#auth-popup");
    })
</script>
