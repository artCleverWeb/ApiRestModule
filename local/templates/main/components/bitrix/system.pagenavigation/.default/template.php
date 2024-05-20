<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"] . "&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?" . $arResult["NavQueryString"] : "");
?>
<div class="pages-nav-a catalog__pages-nav">
    <?php
    if ($arResult["NavPageNomer"] > 1):?>
        <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] - 1) ?>"
           class="pages-nav-a__button-next">
            ←
        </a>
    <?php
    endif; ?>
    <input type="number" class="pages-nav-a__input" value="<?= $arResult["NavPageNomer"]?>"
           max="<?= $arResult["NavPageCount"] ?>" min="1">
    <div class="pages-nav-a__amount">
        из
        <span class="pages-nav-a__amount-value">
													<?= $arResult["NavPageCount"] ?>
												</span>
    </div>
    <?php
    if ($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
        <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] + 1) ?>"
           class="pages-nav-a__button-next">
            →
        </a>
    <?php
    endif; ?>
</div>
