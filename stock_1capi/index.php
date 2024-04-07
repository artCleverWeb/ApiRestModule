<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/json');
/** @var $APPLICATION */
?>
<?php $APPLICATION->IncludeComponent(
    "kolos.studio:api.route",
    "",
    [],
    []
);?>
