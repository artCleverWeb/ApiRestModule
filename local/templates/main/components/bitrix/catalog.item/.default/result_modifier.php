<?php

/** @var array $arResult */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$item = &$arResult['ITEM'];
if ($item['DISPLAY_PROPERTIES']['COLOR']['VALUE']) {
    $color = getColorProduct($item['DISPLAY_PROPERTIES']['COLOR']['VALUE']);

    if (isset($color['UF_COLOR1'])) {
        $item['DISPLAY_PROPERTIES']['COLOR']['COLOR'] = $color['UF_COLOR1'];
    } elseif (isset($color['UF_COLOR2'])) {
        $item['DISPLAY_PROPERTIES']['COLOR']['COLOR'] = $color['UF_COLOR2'];
    } else {
        $item['DISPLAY_PROPERTIES']['COLOR']['COLOR'] = '';
    }
}

$item['DISPLAY_PROPERTIES']['AMOUNT_IN_PALLET']['DISPLAY_VALUE'] = $item['DISPLAY_PROPERTIES']['AMOUNT_IN_PALLET']['DISPLAY_VALUE'] > 0 ? $item['DISPLAY_PROPERTIES']['AMOUNT_IN_PALLET']['DISPLAY_VALUE'] : '';
$item['DISPLAY_PROPERTIES']['AMOUNT_IN_PACK']['DISPLAY_VALUE'] = $item['DISPLAY_PROPERTIES']['AMOUNT_IN_PACK']['DISPLAY_VALUE'] > 0 ? $item['DISPLAY_PROPERTIES']['AMOUNT_IN_PACK']['DISPLAY_VALUE'] : '';
$item['PRODUCT']['QUANTITY'] = $item['PRODUCT']['QUANTITY'] > 0 ? $item['PRODUCT']['QUANTITY'] : 0;

if (isset($item['PREVIEW_PICTURE']['SRC'])) {
    $item['PREVIEW_PICTURE']['SRC'] = CFile::ResizeImageGet(
        $item['PREVIEW_PICTURE']['ID'],
        [
            'width' => 100,
            'height' => 100,
        ],
        BX_RESIZE_IMAGE_PROPORTIONAL
    )['src'];
}
else{
    $item['PREVIEW_PICTURE']['SRC'] = SITE_TEMPLATE_PATH . "/assets/img/no_img.png";
}