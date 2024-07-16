<?php
use Bitrix\Main\Loader;

Loader::includeModule('sale');
Loader::includeModule('catalog');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/lib/CatalogProductProviderCustom.php';
require_once __DIR__ . '/userTypeProperty/usertypeyesno.php';

Loader::includeModule('iblock');
Loader::includeModule('kolos.studio');

Loader::registerAutoLoadClasses(null, [
    'CUserTypeYesNo' => __DIR__ . '/usertype/CUserTypeTimesheet.php',
]);

AddEventHandler('iblock', 'OnIBlockPropertyBuildList', ['CUserTypeYesNo', 'GetUserTypeDescription']);
