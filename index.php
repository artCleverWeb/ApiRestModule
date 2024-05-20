<?php
define('HIDE_BREADCRUMBS', true);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

global $APPLICATION;

$APPLICATION->SetPageProperty('title', 'Онлайн-биржа Sibflowers');
$APPLICATION->SetPageProperty('description', 'Онлайн-биржа Sibflowers для выгодного оформления заказов на имеющиеся остатки');
$APPLICATION->SetTitle("Главная");
?>

<?php if(is_authorized() === true):?>
    <?php include_file('/include/index/user_auth.php', false); ?>
<?php else:?>
    <?php include_file('/include/index/user_no_auth.php', false); ?>
<?php endif;?>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
