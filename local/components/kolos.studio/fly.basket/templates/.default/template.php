<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */

\Bitrix\Main\UI\Extension::load('ui.vue3');
?>

<div class="cart-mini js-cart-mini" id="js-cart-mini">
    <template v-if="!isBasketEmpty">
        <div class="cart-mini__data">
            <div class="cart-mini__data-inner">
                <div class="cart-mini__products">
                    <div class="product-mini-b cart-mini__products-item" v-for="basketItem in basketItems">
                        <div class="product-mini-b__cell product-mini-b__cell_1-1">
                            <div class="product-mini-b__title" v-html="basketItem.NAME">
                            </div>
                            <div class="product-mini-b__detail" v-html="basketItem.QUANTITY + 'шт'">
                            </div>
                        </div>
                        <div class="product-mini-b__cell product-mini-b__cell_1-2">
                            <div class="amount-mini product-mini-b__amount" data-step="{{ basketItem.STEP }}"
                                 data-min="0" data-max="{{ basketItem.MAX }}">
                                <button type="button"
                                        class="amount-mini__button amount-mini__button_decrement"
                                        @click="decrement"></button>
                                <input type="text" :value="basketItem.QUANTITY" class="amount-mini__input"
                                       :data-product-id="basketItem.PRODUCT_ID" @change="changeQuantity"/>
                                <button type="button"
                                        class="amount-mini__button amount-mini__button_increment"
                                        @click="increment"></button>
                            </div>
                        </div>
                        <div class="product-mini-b__cell product-mini-b__cell_1-3">
                            <div class="product-mini-b__price">
                                {{ basketItem.PRICE }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cart-mini__bar">
            <div class="cart-mini__info">
                <svg class="icon cart-mini__info-icon">
                    <use xlink:href="#icon-info"></use>
                </svg>
                Внимание! Товары в корзине не резервируются, и могут быть выкуплены другими пользователями. Для гарантии
                закупки оформите заказ:
            </div>
            <div class="cart-mini__buttons">
                <div class="cart-mini__buttons-item cart-mini__buttons-item_toggle">
                    <a href="javascript:void(0)" class="button-a button-a_bg-3 cart-mini__button" @click="toggle">
                        <div class="button-a__inner">
                            <span class="button-a__text-item">
                                Подробнее
                            </span>
                            <span class="button-a__text-item">
                                Свернуть
                            </span>
                        </div>
                    </a>
                </div>
                <div class="cart-mini__buttons-item cart-mini__buttons-item_order">
                    <a href="javascript:void(0)" class="button-a button-a_bg-2 cart-mini__button" @click="createOrder">
                        <div class="button-a__inner">
                            Оформить заказ
                            <div class="cart-mini__button-price" v-html="basketTotalPrice">

                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </template>

    <template v-if="orderId > 0 && isBasketEmpty">
        <div class="cart-mini__data">
            <div class="cart-mini__data-inner">
                <div class="cart-mini__head">
                    <div class="title cart-mini__title">
                        Заказ оформлен!
                    </div>
                </div>
                <div class="cart-mini__text">
                    <p>
                        Заказ №{{ orderId }} на сумму {{ orderPrice }} успешно создан. Менеджер Гринвилль свяжется с
                        вами в рабочем порядке для дальнейшей обработки заказа.
                    </p>
                </div>

                <div class="cart-mini__message cart-mini__message_products-unavailability" v-if="clearItems.length > 0">
                    <div class="cart-mini__message-head">
                        <div class="title cart-mini__message-title color-red">
                            Отсутствующие товары
                        </div>
                    </div>

                    <p>
                        Часть товаров из вашей корзины на сумму <b>{{ clearPrice }}</b> была выкуплена другими
                        пользователями и
                        не попала в ваш заказ. Эти товары:
                    </p>
                    <div class="cart-mini__message-products" v-for="basketItem in clearItems">
                        <div class="cart-mini__message-product">
                            “{{ basketItem.NAME }}” × {{ basketItem.QUANTITY }}
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="cart-mini__button-close" @click="toggle">
                <svg class="icon-svg cart-mini__button-close-icon">
                    <use xlink:href="#icon-close"></use>
                </svg>
            </button>
        </div>
        <div class="cart-mini__bar">
            <div class="cart-mini__buttons">
                <div class="cart-mini__buttons-item cart-mini__buttons-item_toggle">
                    <a href="javascript:void(0)" class="button-a button-a_bg-3 cart-mini__button" @click="toggle">
                        <div class="button-a__inner">
									<span class="button-a__text-item">
										Показать
									</span>
                            <span class="button-a__text-item">
										Закрыть
									</span>
                        </div>
                    </a>
                </div>
                <div class="cart-mini__buttons-item cart-mini__buttons-item_orders">
                    <a href="#" class="button-a button-a_bg-2 cart-mini__button">
                        <div class="button-a__inner">
                            К заказам
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    const appFlyBasket = BX.Vue3.BitrixVue.createApp({
        data() {
            return {
                componentName: '<?= $this->getComponent()->getName() ?>',
                basket: {},
                loading: true,
                blockAjax: false,
                orderId: 0,
                clearItems: {},
                orderPrice: 0,
            }
        },
        computed: {
            proxyBasket() {
                return this.basket || {}
            },
            isBasketEmpty() {
                return this.basketItems === null
            },
            basketItems() {
                return this.proxyBasket.basket || null
            },
            basketTotalPrice() {
                return this.proxyBasket.totalPrice
            },
        },
        watch: {
            basket(value) {

            },
        },
        mounted() {
            this.getBasket();
        },
        methods: {
            createOrder() {
                const _this = this

                this.send('createOrder', {})
                    .then(function (response) {
                        if (response.status === 'success' && response.data.orderId) {
                            _this.orderId = response.data.orderId || 0
                            _this.orderPrice = response.data.orderPrice || 0
                            _this.clearItems = response.data.clearList || {}
                            _this.clearPrice = response.data.clearPrice || 0
                            _this.basket = {}
                            document.getElementById('js-cart-mini').classList.toggle('expanded')

                            document.querySelectorAll('.amount-mini__input').forEach(product => {
                                product.value = 0;
                            })
                        } else if (response.data.reload) {
                            location.reload()
                        }
                        _this.blockAjax = false
                    }, function (error) {
                        console.log(error)
                    })
            },
            toggle() {
                document.getElementById('js-cart-mini').classList.toggle('expanded')
            },
            getBasketItemsByProductId(id) {
                let filteredItems = this.basketItems.filter(el => +el.PRODUCT_ID === +id)
                return filteredItems.length > 0 ? filteredItems : null
            },
            deleteItem(id, qty) {
                const _this = this

                _this.send('deleteItem', {
                    'id': id,
                    'quantity': qty,
                })
                    .then(function (response) {
                        if (response.status === 'success' && response.data.basket) {
                            _this.basket = response.data.basket || {}
                            _this.updateItems();
                        }
                        _this.blockAjax = false
                    }, function (error) {
                        console.log(error)
                    })
            },
            changeQuantity(productBox) {
                const _this = this;

                const product = productBox.target;
                const parent = product.parentElement;

                let value = +Number(product.value);

                const min = +parent.dataset.min || 0;
                const max = +parent.dataset.max || null;

                if (value < min && value != 0) {
                    value = min;
                } else if (max && value > max) {
                    value = max;
                }

                _this.updateItem(product);
            },
            updateItem(product, updateItems = true) {
                const _this = this;

                const dataSend = {
                    id: product.dataset.productId,
                    quantity: product.value
                }

                _this.send('updateItem', dataSend).then(function (response) {
                    if (response.status === 'success' && response.data.basket) {
                        _this.basket = response.data.basket || {}
                        if(updateItems) {
                            _this.updateItems();
                        }
                        _this.blockAjax = false
                    }
                }, function (error) {
                    console.log(error)
                })
            },
            increment(button) {
                const _this = this

                const parent = button.target.parentElement;

                const product = parent.querySelector('.amount-mini__input')

                let value = +Number(product.value);
                const step = +parent.dataset.step || 1;
                const min = +parent.dataset.min || 0;
                const max = +parent.dataset.max || null;

                if (value < min) {
                    value = min;
                } else if (max && value > max) {
                    value = max;
                }

                value = value + step;

                if (max && value > max) {
                    value = max;
                }

                product.value = value;
                _this.updateItem(product);
            },
            decrement(button) {
                const _this = this
                const parent = button.target.parentElement;
                const product = parent.querySelector('.amount-mini__input')

                let value = +Number(product.value);
                const step = +parent.dataset.step || 1;
                const min = +parent.dataset.min || 0;
                const max = +parent.dataset.max || null;

                if (value < min) {
                    value = min;
                } else if (max && value > max) {
                    value = max;
                }

                value = value - step;

                if (value < min && value != 0) {
                    value = min;
                }

                product.value = value;
                _this.updateItem(product);
            },
            clearBasket() {
                const _this = this

                _this.send('clearBasket')
                    .then(function (response) {
                        if (response.status === 'success' && response.data.result) {
                            _this.basket = response.data.basket || {}
                            _this.updateItems();
                            _this.blockAjax = false
                        }
                    }, function (error) {
                        console.log(error)
                    })
            },
            updateItems() {
                const _this = this
                if (_this.basketItems) {
                    _this.basketItems.forEach(item => {
                        const products = document.querySelectorAll('[data-product-id="' + item.PRODUCT_ID + '"]')

                        products.forEach(product => {
                            product.value = item.QUANTITY;
                        })
                    })
                }
            },
            getBasket() {
                const _this = this

                _this.send('getBasket')
                    .then(function (response) {
                        if (response.status === 'success') {
                            _this.basket = response.data.basket || {}
                            _this.updateItems();
                            _this.blockAjax = false
                        }
                    }, function (error) {
                        console.log(error)
                    })
            },
            send(method, fields, asFormData) {
                const _this = this
                _this.blockAjax = true;

                _this.loading = true

                let params = {
                    mode: 'class',
                }

                if (fields && asFormData) {
                    let formData = new FormData()

                    for (const field in fields) {
                        let value = fields[field]

                        formData.append(field, value)
                    }

                    params.data = formData
                } else {
                    if (fields) {
                        params.data = {fields: fields}
                    }
                }

                return new Promise(function (resolve, reject) {
                    BX.ajax.runComponentAction(
                        _this.componentName,
                        method,
                        params
                    )
                        .then(function (response) {
                            resolve(response)
                            _this.loading = false

                        }, function (response) {
                            reject(response.errors[0])
                            _this.loading = false
                        })
                        .catch(err => {
                            console.log(err)
                            _this.loading = false
                        })

                })

            },
            listener() {
                console.log('listener')
                const _this = this
                _this.loading = false
                document.getElementById('js-cart-mini').classList.remove('basket_hidden')
                document.addEventListener('fly_basket_update_basket', _this.getBasket)

                const products = document.querySelectorAll('.amount-mini__input')

                if (products) {
                    products.forEach(item => {
                        item.addEventListener('change', _this.changeQuantity);
                    })
                }
            },
        },

        created() {
            this.listener();
        },

    }).mount('#js-cart-mini');

    BX.addCustomEvent('onAjaxSuccess', function ($param1, $param2) {
        if ($param2 == null || $param2.url.indexOf('kolos.studio%3Afly.basket') < 1) {
            appFlyBasket.listener();
            appFlyBasket.getBasket();
        }
    });
</script>