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

use Bitrix\Iblock\SectionPropertyTable;

$this->setFrameMode(true);
$isCheked = false;
?>
<div class="filter catalog__filter">
    <button type="button" class="filter__button-toggle">
            <span class="filter__button-toggle-icon">
                <svg class="icon filter__button-toggle-icon-canvas"><use
                            xlink:href="#icon-filter"></use></svg>
                <svg class="icon filter__button-toggle-icon-canvas"><use
                            xlink:href="#icon-close"></use></svg>
            </span>
        Фильтр
    </button>
    <div class="filter__data">
        <form name="<?= $arResult["FILTER_NAME"] . "_form" ?>" action="<?= $arResult["FORM_ACTION"] ?>" method="get">
            <?php
            foreach ($arResult["HIDDEN"] as $arItem): ?>
                <input type="hidden" name="<?= $arItem["CONTROL_NAME"] ?>" id="<?= $arItem["CONTROL_ID"] ?>"
                       value="<?= $arItem["HTML_VALUE"] ?>"/>
            <?php
            endforeach; ?>
            <?php
            foreach ($arResult["ITEMS"] as $key => $arItem)//prices
            {
                $key = $arItem["ENCODED_ID"];
                if (isset($arItem["PRICE"])):
                    if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0) {
                        continue;
                    }

                    $precision = 0;
                    $step_num = 4;
                    $step = ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"]) / $step_num;
                    $prices = array();
                    if (Bitrix\Main\Loader::includeModule("currency")) {
                        for ($i = 0; $i < $step_num; $i++) {
                            $prices[$i] = CCurrencyLang::CurrencyFormat(
                                $arItem["VALUES"]["MIN"]["VALUE"] + $step * $i,
                                $arItem["VALUES"]["MIN"]["CURRENCY"],
                                false
                            );
                        }
                        $prices[$step_num] = CCurrencyLang::CurrencyFormat(
                            $arItem["VALUES"]["MAX"]["VALUE"],
                            $arItem["VALUES"]["MAX"]["CURRENCY"],
                            false
                        );
                    } else {
                        $precision = $arItem["DECIMALS"] ? $arItem["DECIMALS"] : 0;
                        for ($i = 0; $i < $step_num; $i++) {
                            $prices[$i] = number_format(
                                $arItem["VALUES"]["MIN"]["VALUE"] + $step * $i,
                                $precision,
                                ".",
                                ""
                            );
                        }
                        $prices[$step_num] = number_format($arItem["VALUES"]["MAX"]["VALUE"], $precision, ".", "");
                    }
                    ?>
                    <div class="bx-filter-parameters-box filter__item <?php
                    if (isset($arItem["DISPLAY_EXPANDED"]) && $arItem["DISPLAY_EXPANDED"] == "Y"): ?>expanded<?php
                    endif ?>">
                        <span class="bx-filter-container-modef"></span>
                        <div class="bx-filter-parameters-box-title filter__item-title">
                                Цена
                            <svg class="icon filter__item-title-arrow">
                                <use xlink:href="#icon-arrow-1"></use>
                            </svg>
                        </div>
                        <div class="filter__item-data" data-role="bx_filter_block">
                            <div class="filter__slider" data-min="<?= $arItem["VALUES"]["MIN"]["VALUE"] ?>"
                                 data-max="<?= $arItem["VALUES"]["MAX"]["VALUE"] ?>"
                                 data-start-from="<?= $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                 data-start-to="<?= $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>">
                                <div class="filter__slider-inputs">
                                    <input type="text" class="filter__slider-input filter__slider-input_min"
                                           placeholder="От"
                                           name="<?= $arItem["VALUES"]["MIN"]["CONTROL_NAME"] ?>"
                                           id="<?= $arItem["VALUES"]["MIN"]["CONTROL_ID"] ?>"
                                           value="<?= $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                           size="5"
                                           onkeyup="smartFilter.keyup(this)"
                                    />
                                    <input type="text" class="filter__slider-input filter__slider-input_max"
                                           placeholder="До"
                                           name="<?= $arItem["VALUES"]["MAX"]["CONTROL_NAME"] ?>"
                                           id="<?= $arItem["VALUES"]["MAX"]["CONTROL_ID"] ?>"
                                           value="<?= $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
                                           size="5"
                                           onkeyup="smartFilter.keyup(this)"
                                    />
                                </div>
                                <div class="filter__slider-captions">
                                    <div class="filter__slider-caption filter__slider-caption_min"></div>
                                    <div class="filter__slider-caption filter__slider-caption_max"></div>
                                </div>
                                <div class="filter__slider-scale"></div>
                            </div>
                        </div>
                    </div>
                <?php
                endif;
            }

            //not prices
            foreach ($arResult["ITEMS"] as $key => $arItem) {
                if (
                    empty($arItem["VALUES"])
                    || isset($arItem["PRICE"])
                ) {
                    continue;
                }

                if (
                    $arItem["DISPLAY_TYPE"] === SectionPropertyTable::NUMBERS_WITH_SLIDER
                    && ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
                ) {
                    continue;
                }
                ?>


                <div class="bx-filter-parameters-box bx-active filter__item <?php
                if (isset($arItem["DISPLAY_EXPANDED"]) && $arItem["DISPLAY_EXPANDED"] == "Y"): ?>expanded<?php
                endif ?>">
                    <span class="bx-filter-container-modef"></span>
                    <div class="bx-filter-parameters-box-title filter__item-title">
                        <?= $arItem["NAME"] ?>
                        <svg class="icon filter__item-title-arrow">
                            <use xlink:href="#icon-arrow-1"></use>
                        </svg>
                    </div>
                    <div class="filter__item-data" data-role="bx_filter_block">
                        <?php
                        $arCur = current($arItem["VALUES"]);
                        switch ($arItem["DISPLAY_TYPE"]) {
                            case SectionPropertyTable::NUMBERS_WITH_SLIDER:
                                ?>
                                <div class="filter__slider" data-min="<?= $arItem["VALUES"]["MIN"]["VALUE"] ?>"
                                     data-max="<?= $arItem["VALUES"]["MAX"]["VALUE"] ?>"
                                     data-start-from="<?= $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                     data-start-to="<?= $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>">
                                    <div class="filter__slider-inputs">
                                        <input type="text" class="filter__slider-input filter__slider-input_min"
                                               placeholder="От"
                                               name="<?= $arItem["VALUES"]["MIN"]["CONTROL_NAME"] ?>"
                                               id="<?= $arItem["VALUES"]["MIN"]["CONTROL_ID"] ?>"
                                               value="<?= $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                               size="5"
                                               onkeyup="smartFilter.keyup(this)"
                                        />
                                        <input type="text" class="filter__slider-input filter__slider-input_max"
                                               placeholder="До"
                                               name="<?= $arItem["VALUES"]["MAX"]["CONTROL_NAME"] ?>"
                                               id="<?= $arItem["VALUES"]["MAX"]["CONTROL_ID"] ?>"
                                               value="<?= $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
                                               size="5"
                                               onkeyup="smartFilter.keyup(this)"
                                        />
                                    </div>
                                    <div class="filter__slider-captions">
                                        <div class="filter__slider-caption filter__slider-caption_min"></div>
                                        <div class="filter__slider-caption filter__slider-caption_max"></div>
                                    </div>
                                    <div class="filter__slider-scale"></div>
                                </div>
                                <?php
                                break;
                            case SectionPropertyTable::NUMBERS://NUMBERS
                                ?>
                                <div class="filter__slider" data-min="<?= $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                     data-max="<?= $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
                                     data-start-from="<?= $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                     data-start-to="<?= $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>">
                                    <div class="filter__slider-inputs">
                                        <input type="text" class="filter__slider-input filter__slider-input_min"
                                               placeholder="От"
                                               name="<?= $arItem["VALUES"]["MIN"]["CONTROL_NAME"] ?>"
                                               id="<?= $arItem["VALUES"]["MIN"]["CONTROL_ID"] ?>"
                                               value="<?= $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                               size="5"
                                               onkeyup="smartFilter.keyup(this)"
                                        />
                                        <input type="text" class="filter__slider-input filter__slider-input_max"
                                               placeholder="До"
                                               name="<?= $arItem["VALUES"]["MAX"]["CONTROL_NAME"] ?>"
                                               id="<?= $arItem["VALUES"]["MAX"]["CONTROL_ID"] ?>"
                                               value="<?= $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
                                               size="5"
                                               onkeyup="smartFilter.keyup(this)"
                                        />
                                    </div>
                                    <div class="filter__slider-captions">
                                        <div class="filter__slider-caption filter__slider-caption_min"></div>
                                        <div class="filter__slider-caption filter__slider-caption_max"></div>
                                    </div>
                                    <div class="filter__slider-scale"></div>
                                </div>
                                <?php
                                break;
                            default:
                                ?>
                                <div class="filter__input-buttons">
                                    <?php
                                    foreach ($arItem["VALUES"] as $val => $ar): ?>
                                        <div class="filter__input-buttons-item">
                                            <label class="label-button <?= $ar["DISABLED"] ? 'disabled' : '' ?>" data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                                <?= $ar["DISABLED"] ? 'disabled' : '' ?>
                                                   for="<?= $ar["CONTROL_ID"] ?>">
                                                <input type="checkbox"
                                                       value="<?= $ar["HTML_VALUE"] ?>"
                                                       name="<?= $ar["CONTROL_NAME"] ?>"
                                                       id="<?= $ar["CONTROL_ID"] ?>"
                                                    <?= $ar["DISABLED"] ? 'disabled' : '' ?>
                                                    <?= $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                                       onclick="smartFilter.click(this)"
                                                />
                                                <span class="label-button__text">
                                                <?= $ar["VALUE"]; ?>
                                            </span>
                                            </label>
                                        </div>
                                    <?php
                                    endforeach; ?>
                                </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="filter__buttons">
                <div class="filter__buttons-item filter__buttons-item_show">
                    <input
                            class="button-a filter__button"
                            type="submit"
                            id="set_filter"
                            name="set_filter"
                            style="display:none;"
                            value="<?= GetMessage("CT_BCSF_SET_FILTER") ?>"
                    />
                </div>
                <div class="filter__buttons-item filter__buttons-item_reset">
                    <input
                            class="button-a button-a_bg-1 filter__button"
                            type="submit"
                            id="del_filter"
                            name="del_filter"
                            value="<?= GetMessage("CT_BCSF_DEL_FILTER") ?>"
                    />
                </div>
            </div>
            <div class="bx-filter-popup-result" id="modef"
            <?php
            if (!isset($arResult["ELEMENT_COUNT"])) {
                echo 'style="display:none"';
            } ?> style="display: inline-block;">
            <?php
            echo GetMessage(
                "CT_BCSF_FILTER_COUNT",
                array("#ELEMENT_COUNT#" => '<span id="modef_num">' . (int)($arResult["ELEMENT_COUNT"] ?? 0) . '</span>')
            ); ?>
            <span class="arrow"></span>
            <br/>
            <a href="<?php
            echo $arResult["FILTER_URL"] ?>" target=""><?
                echo GetMessage("CT_BCSF_FILTER_SHOW") ?></a>
    </div>
    </form>
    </form>
</div>
</div>

<script type="text/javascript">
    var smartFilter = new JCSmartFilter('<?php echo CUtil::JSEscape($arResult["FORM_ACTION"])?>', '<?=CUtil::JSEscape(
        $arParams["FILTER_VIEW_MODE"]
    )?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);
</script>