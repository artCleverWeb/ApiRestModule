<?php
use Bitrix\Main\Loader;

Loader::includeModule('sale');
Loader::includeModule('catalog');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/lib/CatalogProductProviderCustom.php';

Loader::includeModule('iblock');
Loader::includeModule('kolos.studio');
