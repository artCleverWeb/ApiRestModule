<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<?php
if (!empty($arResult)): ?>
    <nav class="menu-main js-menu-main">
        <button type="button" class="menu-main__button-toggle">
            <span class="menu-main__button-toggle-icon"></span>
        </button>
        <div class="menu-main__inner">
            <div class="menu-main__data">
                <div class="menu-main__list">
                    <?php
                    foreach ($arResult as $arItem):
                        if ($arItem["DEPTH_LEVEL"] > 1) {
                            continue;
                        }
                        ?>
                        <div class="menu-main__item <?php if($arItem['SELECTED']):?>active<?php endif?>">
                            <?php
                            if (is_absolute_link($arItem["LINK"])): ?>
                                <a href="<?= $arItem["LINK"] ?>" class="menu-main__link" target="_blank">
										<span class="menu-main__link-text">
											<?= $arItem["TEXT"] ?>
										</span>
                                    <span class="menu-main__link-icon">
											<svg class="icon menu-main__link-icon-canvas">
                                                <use xlink:href="#icon-link-outer"></use></svg>
                                    </span>
                                </a>
                            <?php
                            else: ?>
                                <a href="<?= $arItem["LINK"] ?>" class="menu-main__link">
										<span class="menu-main__link-text">
											<?= $arItem["TEXT"] ?>
										</span>
                                </a>
                            <?php
                            endif; ?>
                        </div>
                    <?php
                    endforeach ?>

                </div>
            </div>
        </div>
    </nav>
<?php
endif ?>