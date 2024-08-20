<?php
/**
 * @var $arResult
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

\Bitrix\Main\Loader::includeModule('kolos.studio');

$arParams["FILTER_VIEW_MODE"] = (isset($arParams["FILTER_VIEW_MODE"]) && toUpper(
        $arParams["FILTER_VIEW_MODE"]
    ) == "HORIZONTAL") ? "HORIZONTAL" : "VERTICAL";
$arParams["POPUP_POSITION"] = (isset($arParams["POPUP_POSITION"]) && in_array(
        $arParams["POPUP_POSITION"],
        array("left", "right")
    )) ? $arParams["POPUP_POSITION"] : "left";
$arResult["JS_FILTER_PARAMS"]['SEF_DEL_FILTER_URL'] = $arResult['FORM_ACTION'];

$colorCode = [];

foreach ($arResult['ITEMS'] as &$item) {
    if ($item['DISPLAY_TYPE'] == 'F') {
        if ($item['CODE'] == 'LENGTH_CODE') {
            usort($item['VALUES'], function ($a, $b) {
                return $a['VALUE'] - $b['VALUE'];
            });
        } else {
            usort($item['VALUES'], function ($a, $b) {
                return strcmp($a['VALUE'], $b['VALUE']);
            });
        }
    }

    if ($item['CODE'] == 'COLOR') {
        $colorCode = array_unique(array_column($item['VALUES'], 'URL_ID'));
    }
}

if(!empty($colorCode) && defined('HL_TABLE_DIRECTORY_COLOR')){
    $colorDirectory = new \Kolos\Studio\Helpers\HighloadBlock(HL_TABLE_DIRECTORY_COLOR);
    $colors = $colorDirectory->getFromCache(['filter' => $colorCode]);

    foreach ($colors as $color){
        $arResult['COLORS'][$color['UF_CODE']] = $color['UF_COLOR1'];
    }

}
