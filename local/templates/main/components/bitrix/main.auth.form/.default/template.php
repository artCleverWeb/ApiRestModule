<?php

/** @var $arResult */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arResult['AUTHORIZED']) {
    return;
}
?>

<form name="<?= $arResult['FORM_ID']; ?>" method="post" target="_top" action="<?= POST_FORM_ACTION_URI; ?>"
      class="form auth-popup__form">

    <div class="popup__head auth-popup__head">
        <div class="title popup__title">
            Чтобы видеть цены и совершать покупки необходимо войти
        </div>
    </div>
    <div class="form__intro">
        Пожалуйста, авторизуйтесь:
    </div>
    <?php if ($arResult['ERRORS']): ?>
        <div class="alert alert-danger">
            <?php
            foreach ($arResult['ERRORS'] as $error) {
                echo $error;
            }
            ?>
        </div>
    <?php
    endif; ?>
    <div class="form__item">
        <div class="form__item-grid form__item-grid_1">
            <div class="form__item-grid-cell form__item-grid-cell_1">
                <div class="form__caption">
                    Логин:
                </div>
            </div>
            <div class="form__item-grid-cell form__item-grid-cell_2">
                <div class="field-form form__field">
                    <input type="text" class="input-text field-form__input-text" placeholder=""
                           name="<?= $arResult['FIELDS']['login']; ?>" maxlength="255"
                           value="<?= \htmlspecialcharsbx($arResult['LAST_LOGIN']); ?>"/>
                </div>
            </div>
        </div>
    </div>
    <div class="form__item">
        <div class="form__item-grid form__item-grid_1">
            <div class="form__item-grid-cell form__item-grid-cell_1">
                <div class="form__caption">
                    Пароль:
                </div>
            </div>
            <div class="form__item-grid-cell form__item-grid-cell_2">
                <div class="field-form form__field">
                    <input type="password" class="input-text field-form__input-text" placeholder=""
                           name="<?= $arResult['FIELDS']['password']; ?>" maxlength="255"
                           autocomplete="off"/>
                </div>
            </div>
        </div>
    </div>
    <div class="form__item">
        <div class="form__item-grid form__item-grid_1">
            <div class="form__item-grid-cell form__item-grid-cell_1">

            </div>
            <div class="form__item-grid-cell form__item-grid-cell_2">
                <div class="auth-popup__remember-computer">
                    <label class="label-button">
                        <input type="checkbox" name="<?= $arResult['FIELDS']['remember']; ?>" value="Y"/>
                        <span class="label-button__text">
										Запомнить меня на этом компьютере
									</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="form__item form__item_button-submit">
        <div class="form__item-grid form__item-grid_1">
            <div class="form__item-grid-cell form__item-grid-cell_1">

            </div>
            <div class="form__item-grid-cell form__item-grid-cell_2">
                <button type="submit" class="button-a button-a_bg-2" name="<?= $arResult['FIELDS']['action'];?>" value="Войти">
                    Войти
                </button>
            </div>
        </div>
    </div>

    <div class="auth-popup__text">
        Для использования раздела Биржа Гринвилль вам потребуется особый уровень доступа предоставляемый менеджером Гринвилль. <a href="javascript::void(0)" onclick="popupClose('#auth-popup');">Подробнее</a>
    </div>
</form>
</div>
