<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<!DOCTYPE HTML>
<html xml:lang="<?= LANGUAGE_ID ?>" lang="<?= LANGUAGE_ID ?>">
<head>
    <title><?php
        $APPLICATION->ShowProperty('title'); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="author" content="">
    <meta name="description" content="">
    <meta name="keywords" content="">

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>

    <meta name="viewport" content="width=device-width, maximum-scale=2">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,500,500i,600,600i,700,700i,800,800i&family=Roboto:wght@400;500;700&display=swap">

    <?php
    assets()->addCss(SITE_TEMPLATE_PATH . '/assets/css/normalize.css');
    assets()->addCss(SITE_TEMPLATE_PATH . '/assets/css/effects.css');
    assets()->addCss(SITE_TEMPLATE_PATH . '/assets/css/nouislider.css');
    assets()->addCss(SITE_TEMPLATE_PATH . '/assets/css/common.css');
    assets()->addCss(SITE_TEMPLATE_PATH . '/assets/css/responsive.css');

    $APPLICATION->ShowMeta('robots');
    $APPLICATION->ShowHeadStrings();
    $APPLICATION->ShowHeadScripts();
    $APPLICATION->ShowCSS();
    ?>
</head>

<body>

<?php
$APPLICATION->ShowPanel(); ?>
<?php
include_file('/include/header/svg.php', true); ?>

<div class="page">
    <header class="header">
        <div class="cnt header__cnt">
            <div class="header__grid header__grid_1">
                <div class="header__grid-item header__grid-item_1">
                    <a href="/" class="logo-main">
                        <svg class="icon logo-main__canvas">
                            <use xlink:href="#icon-logo"></use>
                        </svg>
                        <div class="logo-main__text">
                            Биржа Гринвилль
                        </div>
                    </a>
                </div>
                <div class="header__grid-item header__grid-item_2">
                    <div class="contacts-header js-contacts-header">
                        <svg class="icon contacts-header__icon">
                            <use xlink:href="#icon-phone"></use>
                        </svg>
                        <div class="contacts-header__value">
                            <?php
                            include_edit_file_text('/include/header/phone.php', true); ?>
                        </div>
                    </div>
                </div>
                <div class="header__grid-item header__grid-item_3">
                    <div class="user-header">
                        <?php if(is_authorized() === true):?>
                            <?php include_file('/include/header/user_auth.php', true); ?>
                        <?php else:?>
                            <?php include_file('/include/header/user_no_auth.php', true); ?>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <?php
            $APPLICATION->IncludeComponent(
                "bitrix:menu",
                "main",
                array(
                    "ROOT_MENU_TYPE" => "top",
                    "MAX_LEVEL" => "1",
                    "CHILD_MENU_TYPE" => "sub",
                    "USE_EXT" => "N",
                    "DELAY" => "N",
                    "ALLOW_MULTI_SELECT" => "N",
                    "MENU_CACHE_TYPE" => "A",
                    "MENU_CACHE_TIME" => "36000000",
                    "MENU_CACHE_USE_GROUPS" => "N",
                    "MENU_CACHE_GET_VARS" => "",
                ),
                false
            ); ?>

        </div>
    </header>

    <main class="content">