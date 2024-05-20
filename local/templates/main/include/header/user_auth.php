<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<div class="user-header__login">
    <span class="user-header__login-link">
        <?=user()->GetLogin();?>
    </span>
</div>
<a href="?logout=yes" class="user-header__link user-header__link_logout">
    <span class="user-header__link-icon">
        <svg class="icon contacts-header__icon-canvas"><use xlink:href="#icon-logout"></use></svg>
    </span>
    <span class="user-header__link-text">
        Выйти
    </span>
</a>
