<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogProductsViewedComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);

if (isset($arResult['ITEM'])) {
    $item = $arResult['ITEM'];
    $areaId = $arResult['AREA_ID'];
    $actualItem = $item;
    $priceArr = current($item['ITEM_PRICES']);
    $price = isset($priceArr['PRINT_PRICE']) ? $priceArr['PRINT_PRICE'] : '';
    ?>

    <div class="product-mini-a catalog__products-item" id="<?= $areaId ?>" data-entity="item">
        <div class="product-mini-a__cell product-mini-a__cell_title product-mini-a__cell_1-1">
            <div class="product-mini-a__cell-inner">
                <div class="product-mini-a__picture">
                    <div class="product-mini-a__picture-inner">
                        <img src="<?= $item['PREVIEW_PICTURE']['SRC'] ?>"
                             alt="<?= $item['NAME'] ?>" class="product-mini-a__picture-img"/>
                    </div>
                </div>
                <div class="product-mini-a__title">
                    <?= $item['NAME'] ?>
                    <?php
                    if (isset($item['DISPLAY_PROPERTIES']['NEW']['~VALUE']) && $item['DISPLAY_PROPERTIES']['NEW']['~VALUE'] == 'Y'): ?>
                        <br/><b>Новинка</b>
                    <?php
                    endif ?>
                    <?php
                    if (isset($item['DISPLAY_PROPERTIES']['SPECIAL_OFFER']['~VALUE']) && $item['DISPLAY_PROPERTIES']['SPECIAL_OFFER']['~VALUE'] == 'Y'): ?>
                        <br/><b>Спецпредложение</b>
                    <?php
                    endif ?>
                </div>
            </div>
        </div>
        <div class="product-mini-a__cell product-mini-a__cell_1-2 product-mini-a__cell_color">
            <div class="product-mini-a__cell-inner">
                <div class="product-mini-a__caption">
                    Цвет
                </div>
                <div class="product-mini-a__color"
                     style="background: <?= $item['DISPLAY_PROPERTIES']['COLOR']['COLOR'] ?>;"
                     title="<?= $item['DISPLAY_PROPERTIES']['COLOR']['DISPLAY_VALUE'] ?>"></div>
            </div>
        </div>
        <div class="product-mini-a__cell product-mini-a__cell_1-3">
            <div class="product-mini-a__cell-inner">
                <div class="product-mini-a__caption">
                    Сорт
                </div>
                <div class="product-mini-a__detail">
                    <?= $item['DISPLAY_PROPERTIES']['VARIENTIE']['DISPLAY_VALUE'] ?>
                </div>
            </div>
        </div>
        <div class="product-mini-a__cell product-mini-a__cell_1-4">
            <div class="product-mini-a__cell-inner">
                <div class="product-mini-a__caption">
                    Страна и плантация
                </div>
                <div class="product-mini-a__detail">
                    <?= $item['DISPLAY_PROPERTIES']['COUNTRY']['DISPLAY_VALUE'] ?><?= mb_strlen(
                        $item['DISPLAY_PROPERTIES']['COUNTRY']['DISPLAY_VALUE']
                    ) && mb_strlen(
                        $item['DISPLAY_PROPERTIES']['PLANTATION']['DISPLAY_VALUE']
                    ) ? ', ' : '' ?><?= $item['DISPLAY_PROPERTIES']['PLANTATION']['DISPLAY_VALUE'] ?>
                </div>
            </div>
        </div>
        <?php /*
        <div class="product-mini-a__cell product-mini-a__cell_1-9">
            <div class="product-mini-a__cell-inner">
                <div class="product-mini-a__caption">
                    Длина
                </div>
                <div class="product-mini-a__detail">
                    <?= $item['DISPLAY_PROPERTIES']['LENGTH']['DISPLAY_VALUE'] ?>
                </div>
            </div>
        </div>
        */?>
        <div class="product-mini-a__cell product-mini-a__cell_1-5">
            <div class="product-mini-a__cell-inner">
                <div class="product-mini-a__caption">
                    Доступно
                </div>
                <div class="product-mini-a__details">
                    <div class="product-mini-a__detail">
                        <span data-amount-id="<?= $item['ID'] ?>"><?= $item['PRODUCT']['QUANTITY'] ?></span>
                        <?php
                        if ($item['DISPLAY_PROPERTIES']['QUANTITY_IN_PACK']['DISPLAY_VALUE']): ?>по <?= $item['DISPLAY_PROPERTIES']['QUANTITY_IN_PACK']['DISPLAY_VALUE'] ?><?php
                        endif; ?>
                    </div>
                    <?php
                    if ($item['DISPLAY_PROPERTIES']['AMOUNT_IN_PALLET']['DISPLAY_VALUE']): ?>
                        <div class="product-mini-a__detail product-mini-a__detail_icon">
                            <div class="product-mini-a__detail-icon">
                                <svg class="icon product-mini-a__detail-icon-canvas">
                                    <use xlink:href="#icon-box"></use>
                                </svg>
                            </div>
                            <div class="product-mini-a__detail-text">
                                <?= $item['DISPLAY_PROPERTIES']['AMOUNT_IN_PALLET']['DISPLAY_VALUE'] ?>
                            </div>
                        </div>
                    <?php
                    endif; ?>
                </div>
            </div>
        </div>
        <div class="product-mini-a__cell product-mini-a__cell_1-6">
            <div class="product-mini-a__cell-inner">
                <div class="product-mini-a__caption">
                    Размер
                </div>
                <div class="product-mini-a__detail product-mini-a__detail_date">
                    <?php
                    if ($item['DISPLAY_PROPERTIES']['LENGTH_CODE']['DISPLAY_VALUE']): ?>
                        Д-<?= $item['DISPLAY_PROPERTIES']['LENGTH_CODE']['DISPLAY_VALUE'] ?>
                    <?php
                    endif; ?>
                    <br/>
                    <?php
                    if ($item['DISPLAY_PROPERTIES']['WEIGHT_CODE']['DISPLAY_VALUE']): ?>
                        В-<?= $item['DISPLAY_PROPERTIES']['WEIGHT_CODE']['DISPLAY_VALUE'] ?>
                    <?php
                    endif; ?>
                </div>
            </div>
        </div>
        <div class="product-mini-a__cell product-mini-a__cell_1-7">
            <div class="product-mini-a__cell-inner">
                <div class="product-mini-a__price">
                    <?= $price ?>
                </div>
            </div>
        </div>
        <div class="product-mini-a__cell product-mini-a__cell_1-8">
            <div class="product-mini-a__cell-inner">
                <div class="amount-mini product-mini-a__amount"
                     data-step="<?= $item['DISPLAY_PROPERTIES']['QUANTITY_IN_PACK']['DISPLAY_VALUE'] ?>"
                     data-min="0"
                     data-max="<?= $item['PRODUCT']['QUANTITY'] ?>">
                    <button type="button" class="amount-mini__button amount-mini__button_decrement"></button>
                    <input type="text" value="0" class="amount-mini__input" data-product-id="<?= $item['ID'] ?>"/>
                    <button type="button" class="amount-mini__button amount-mini__button_increment"></button>
                </div>
            </div>
        </div>
    </div>
    <?php
    unset($item, $actualItem, $minOffer, $itemIds, $jsParams);
}
