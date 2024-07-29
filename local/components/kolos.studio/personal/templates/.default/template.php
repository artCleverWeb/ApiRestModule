<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */

\Bitrix\Main\UI\Extension::load('ui.vue3');

?>

<div class="cabinet" id="cabinet">
    <div class="cnt cabinet__cnt" v-if="user.isAuth">
        <div class="cabinet__grid cabinet__grid_1">
            <div class="cabinet__grid-item cabinet__grid-item_0">
                <div class="headline cabinet__head">
                    <h1 class="title headline__title cabinet__title">
                        Заказы на Бирже Гринвилль
                    </h1>
                </div>
            </div>
            <div class="cabinet__grid-item cabinet__grid-item_1">
                <div class="orders cabinet__orders js-orders">
                    <template v-for="(ordersList, state) in orders">
                        <div class="orders__table" v-if="ordersList.length > 0">
                            <div class="headline cabinet__head">
                                <h1 class="title headline__title cabinet__title" v-if="state=='active'">
                                    Активные заказы
                                </h1>
                                <h1 class="title headline__title cabinet__title" v-else>
                                    Архивные заказы
                                </h1>
                            </div>
                            <div class="orders__table-head">
                                <div class="orders__table-cell orders__table-cell_1-1">
                                    Поставка
                                </div>
                                <div class="orders__table-cell orders__table-cell_1-2">
                                    Номер
                                </div>
                                <div class="orders__table-cell orders__table-cell_1-3">
                                    Состав заказа
                                </div>
                                <div class="orders__table-cell orders__table-cell_1-4">
                                    Сумма, руб.
                                </div>
                            </div>


                            <template v-for="order in ordersList">
                                <div class="order-mini orders__table-item">
                                    <div class="order-mini__cell order-mini__cell_1-1">
                                        <div class="order-mini__cell-inner">
                                            <div class="order-mini__number" v-if="order.IS_PREORDER">Предзаказ</div>
                                            <div class="order-mini__number" v-else>Допзаказ</div>
                                            <div class="order-mini__date-order"
                                                 v-html="order.SUPPLIES_NAME"></div>
                                        </div>
                                    </div>
                                    <div class="order-mini__cell order-mini__cell_1-2">
                                        <div class="order-mini__cell-inner">
                                            <div class="order-mini__number" v-html="showOrderId(order.ID)"></div>
                                            <div class="order-mini__date-order"
                                                 v-html="showOrderDate(order.DATE)"></div>
                                        </div>
                                    </div>
                                    <div class="order-mini__cell order-mini__cell_1-3">
                                        <div class="order-mini__cell-inner">
                                            <div class="order-mini__title">
                                                <div class="order-mini__title-text" v-html="order.PRODUCT_TITLE"></div>
                                                <div class="order-mini__title-more" v-if="order.COUNT_ITEMS > 0">
                                                    и еще
                                                    <span class="order-mini__title-more-value"
                                                          v-html="order.COUNT_ITEMS_TEXT"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="order-mini__cell order-mini__cell_1-4">
                                        <div class="order-mini__cell-inner">
                                            <div class="order-mini__price" v-html="order.PRICE"></div>
                                        </div>
                                    </div>


                                    <!-- Попап с деталями заказа -->
                                    <div class="popup order-popup order-mini__popup-details">
                                        <div class="popup__overlay order-popup__overlay"></div>
                                        <div class="popup__data order-popup__data">
                                            <div class="order-popup__head">
                                                <div class="title order-popup__title">
                                                    Подробности заказа №{{order.ID}}
                                                </div>
                                            </div>

                                            <div class="order-popup__info">
                                                <!--                                                <div class="order-popup__info-item" v-html="getStatus(order.STATUS_ID)">-->
                                                <!--                                                </div>-->
                                                <div class="order-popup__info-item">
                                                    Клиент: {{user.fullName}}, {{user.email}}
                                                </div>
                                            </div>

                                            <div class="order-popup__products">
                                                <div class="order-popup__products-table">

                                                    <div class="order-popup__products-head">
                                                        <div class="order-popup__products-cell order-popup__products-cell_1-1">
                                                            Наименование
                                                        </div>
                                                        <div class="order-popup__products-cell order-popup__products-cell_1-2">
                                                            Цена, руб.
                                                        </div>
                                                        <div class="order-popup__products-cell order-popup__products-cell_1-3">
                                                            Количество, шт.
                                                        </div>
                                                        <div class="order-popup__products-cell order-popup__products-cell_1-4">
                                                            Стоимость, руб.
                                                        </div>
                                                    </div>
                                                    <template v-for="basketItem in order.basket">
                                                        <div class="order-popup__products-item">
                                                            <div class="order-popup__products-cell order-popup__products-cell_1-1">
                                                                <div class="order-popup__products-cell-inner">
                                                                    <div class="order-popup__products-title"
                                                                         v-html="basketItem.name"></div>
                                                                </div>
                                                            </div>
                                                            <div class="order-popup__products-cell order-popup__products-cell_1-2">
                                                                <div class="order-popup__products-cell-inner">
                                                                    <div class="order-popup__products-caption">
                                                                        Цена
                                                                    </div>
                                                                    <div class="order-popup__products-price"
                                                                         v-html="showItemPrice(basketItem.price)">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="order-popup__products-cell order-popup__products-cell_1-3">
                                                                <div class="order-popup__products-cell-inner">
                                                                    <div class="order-popup__products-caption">
                                                                        Количество
                                                                    </div>
                                                                    <div class="order-popup__products-amount"
                                                                         v-html="basketItem.quantity"></div>
                                                                </div>
                                                            </div>
                                                            <div class="order-popup__products-cell order-popup__products-cell_1-4">
                                                                <div class="order-popup__products-cell-inner">
                                                                    <div class="order-popup__products-caption">
                                                                        Стоимость
                                                                    </div>
                                                                    <div class="order-popup__products-cost"
                                                                         v-html="showItemPrice(basketItem.amount)"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <div class="order-popup__products-prices">
                                                    <div class="order-popup__products-prices-item order-popup__products-prices-item_total">
                                                        <div class="order-popup__products-prices-item-caption">
                                                            Итого
                                                        </div>
                                                        <div class="order-popup__products-prices-item-value">
                                                            {{ order.PRICE}}
                                                            <span class="order-popup__products-prices-item-value-currency">
                                                            руб.
                                                        </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="order-popup__total">
                                            <span class="order-popup__total-caption"
                                                  v-html="showFullPrice(order.COUNT_ITEMS_FULL_TEXT)"></span>
                                                <span class="order-popup__total-value">
                                                {{order.PRICE_FORMAT}} руб.
                                            </span>
                                            </div>

                                            <div class="order-popup__buttons">
                                                <div class="order-popup__buttons-item order-popup__buttons-item_close">
                                                    <a href="/?logout=yes&<?= bitrix_sessid_get() ?>"
                                                       class="button-a button-a_bg-4 order-popup__button">
                                                        <span class="button-a__text">
                                                            Выйти
                                                        </span>
                                                    </a>
                                                </div>
                                                <!--                                            <div class="order-popup__buttons-item order-popup__buttons-item_cancell">-->
                                                <!--                                                <a href="#"-->
                                                <!--                                                   class="button-a button-a_bg-4 button-a_icon order-popup__button">-->
                                                <!--															<span class="button-a__icon">-->
                                                <!--																<svg class="icon-svg popup__button-close-icon"><use-->
                                                <!--                                                                            xlink:href="#icon-cancellation"></use></svg>-->
                                                <!--															</span>-->
                                                <!--                                                    <span class="button-a__text">-->
                                                <!--																Отменить-->
                                                <!--															</span>-->
                                                <!--                                                </a>-->
                                                <!--                                            </div>-->
                                                <!--                                            <div class="order-popup__buttons-item order-popup__buttons-item_edit">-->
                                                <!--                                                <a href="#"-->
                                                <!--                                                   class="button-a button-a_bg-4 button-a_icon order-popup__button">-->
                                                <!--															<span class="button-a__icon">-->
                                                <!--																<svg class="icon-svg popup__button-close-icon"><use-->
                                                <!--                                                                            xlink:href="#icon-edit"></use></svg>-->
                                                <!--															</span>-->
                                                <!--                                                    <span class="button-a__text">-->
                                                <!--																Редактировать-->
                                                <!--															</span>-->
                                                <!--                                                </a>-->
                                                <!--                                            </div>-->
                                            </div>

                                            <button type="button" class="popup__button-close">
                                                <svg class="icon-svg popup__button-close-icon">
                                                    <use xlink:href="#icon-close"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            <div class="cabinet__grid-item cabinet__grid-item_2">
                <div class="user-mini cabinet__user">
                    <div class="user-mini__head">
                        <div class="user-mini__name">
                            {{ user.fullName}}
                        </div>
                        <div class="user-mini__email">
                            <svg class="icon-svg user-mini__email-icon">
                                <use xlink:href="#icon-email"></use>
                            </svg>
                            <div class="user-mini__email-text">
                                {{ user.email}}
                            </div>
                        </div>
                    </div>
                    <!--                    <div class="user-mini__button-edit">-->
                    <!--                        <a href="#" class="user-mini__button-edit-link">-->
                    <!--                            Редактировать профиль-->
                    <!--                        </a>-->
                    <!--                    </div>-->
                    <div class="user-mini__button-logout">
                        <a href="/?logout=yes&<?= bitrix_sessid_get() ?>"
                           class="button-a button-a_bg-4 user-mini__button-logout-link">
                            Выход
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const appCabinet = BX.Vue3.BitrixVue.createApp({
        data() {
            return {
                componentName: '<?= $this->getComponent()->getName() ?>',
                orders: <?= $arResult['orders'] ? CUtil::PhpToJSObject($arResult['orders']) : '{}'?>,
                user: <?= $arResult['user'] ? CUtil::PhpToJSObject($arResult['user']) : '{}'?>,
                statuses: <?= $arResult['statuses'] ? CUtil::PhpToJSObject($arResult['statuses']) : '{}'?>,
            }
        },
        computed: {},
        watch: {},
        mounted() {
        },
        methods: {
            getStatus(value) {
                if (this.statuses[value] !== undefined) {
                    return `Статус: ` + this.statuses[value];
                }

                return '';
            },
            showItemPrice(value) {
                return value + `<span class="order-popup__products-price-currency">
                                    руб.
                                </span>`
            },
            showOrderId(value) {
                return `№` + value;
            },
            showOrderDate(value) {
                return `от ` + value;
            },
            showFullPrice(value) {
                return `Итого ` + value + ` на общую сумму `;
            },
        },

        created() {

        },

    }).mount('#cabinet');

</script>