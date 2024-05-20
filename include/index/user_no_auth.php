<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<div class="post-card">
    <div class="cnt post-card__cnt">
        <div class="post-card__head">
            <h1 class="title post-card__title">
                Нет доступа на Биржу Гринвилль
            </h1>
        </div>

        <div class="message message_no-access">
            <div class="message__logo">
                <svg class="icon message__logo-img"><use xlink:href="#icon-logo"></use></svg>
            </div>
            <div class="message__text message__text_width-1">
                <p style="color: #8A8B8C;">
                    К сожалению, у вас нет доступа на Биржу Гринвилль.
                </p>

                <p>
                    Для получения доступа обратитесь к своему менеджеру Гринвилль,
                    сообщив email-адрес, на который зарегистрирован ваш аккаунт.
                </p>
            </div>

            <div class="message__button">
                <a href="https://www.sibflowers.ru/" class="button-a message__button-link" target="_blank">
                    В интернет-магазин
                </a>
            </div>

            <div class="contacts-mini message__contacts">
                <svg class="icon contacts-mini__icon"><use xlink:href="#icon-phone"></use></svg>
                <div class="contacts-mini__value">
                    <?php
                    include_edit_file_text('/include/header/phone.php', true); ?>
                </div>
            </div>

            <div class="social-links message__social-links">
                <?php
                include_edit_file_text('/include/social_link.php', true); ?>
            </div>

        </div>
    </div>
</div>
