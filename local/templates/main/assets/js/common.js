var DEVICE_MOBILE = isMobile(),
    DEVICE_TOUCH = isTouchDevice();


$(document).ready(function(){
    /* Form Elements */
    /* $('.js-select').selectCustom(); */

    $(document).on('change', '.pages-nav-a__input', function(){
        const catalogBlock = $(this).parents('.js-catalog').parent();
        const containerId = catalogBlock.attr('id');
        const bxAjaxId = containerId.replace('comp_', '');
        const pageNum = 'PAGEN_' + $(this).parents('[data-pagination-num]').data('paginationNum');

        const url = new URL(window.location.href)
        url.searchParams.delete(pageNum);
        url.searchParams.set(pageNum, $(this).val());
        url.searchParams.set('bxajaxid', bxAjaxId);

        let urlAjax = url.pathname.toString() + '?' + url.searchParams.toLocaleString();
        BX.ajax.insertToNode(urlAjax, containerId);
    })

    /* Popup */
    $('.js-popup').each(function () {
        var $popup = $(this),
            popup = {
                id: $(this).attr('id'),
            },
            $buttonClose = $('.popup__button-close', $popup),
            $overlay = $('.popup__overlay', $popup);

        if (window.location.hash && window.location.hash == '#' + popup.id) {
            popupOpen($popup);
        }

        $buttonClose.on('click', function(event){
            popupClose($popup);

            event.preventDefault();
        });

        $overlay.on('click', function(event){
            popupClose($popup);

            event.preventDefault();
        });

        $(window).on('keyup', function(event){
            if (event.keyCode == 27 && $popup.hasClass('shown')) {
                popupClose($popup);
            }
        });
    });

    $(document).on('click', '.js-link-popup-open', function(event){
        var popupId = $(this).attr('href');

        popupOpen(popupId);

        event.preventDefault();
    });

    /* Header contacts */
    $('.js-contacts-header').each(function() {
        var $contacts = $(this);

        $('.contacts-header__icon', $contacts).on('click', function(event) {
            $contacts.toggleClass('expanded');
            event.stopPropagation();
            event.preventDefault();
        });

        $('.contacts-header__value', $contacts).on('click', function(event) {
            event.stopPropagation();
        });

        $(document).on('click', function() {
            $contacts.removeClass('expanded');
        });
    });

    /* Catalog */
    $('.js-catalog').each(function() {
        var $catalog = $(this);

        window.catalog.init($catalog);
    });

    /* Cart mini */
    $('.js-cart-mini').each(function() {
        var $cart = $(this);

        $('.cart-mini__products', $cart).each(function() {
            var $products = $('.cart-mini__products-item', this);

            $products.each(function() {
                var product = {
                    $el: $(this),
                };

                product.$amount = $('.product-mini-b__amount', product.$el);

                amountInit(product.$amount);
            });
        });

        $('.cart-mini__buttons-item_toggle .cart-mini__button', $cart).on('click', function(event) {
            $cart.toggleClass('expanded');
            event.preventDefault();
        });

        $('.cart-mini__button-close', $cart).on('click', function(event) {
            $cart.removeClass('expanded');
            event.preventDefault();
        });
    });

    /* Cart mini */
    $('.js-orders').each(function() {
        var $box = $(this);

        $('.orders__table-item', $box).each(function() {
            var order = {
                $el: $(this),
                $popupDetails: $('.order-mini__popup-details', this),
            };

            $('.order-mini__cell', order.$el).on('click', function(event) {
                popupOpen(order.$popupDetails);
                event.preventDefault();
            });

            $('.order-popup__buttons-item_close .order-popup__button', order.$popupDetails).on('click', function() {
                popupClose(order.$popupDetails);
                event.preventDefault();
            });

            $('.popup__button-close, .popup__overlay', order.$popupDetails).on('click', function() {
                popupClose(order.$popupDetails);
                event.preventDefault();
            });
        });

    });

});

function windowSizeGet() {
    var win = {
        width: $(window).width(),
        height: $(window).height()
    }

    return win;
}

function isMobile() {
    var ua = navigator.userAgent;
    if (ua.match(/Android/i) || ua.match(/webOS/i) || ua.match(/iPhone/i) || ua.match(/iPad/i) || ua.match(/iPod/i) || ua.match(/BlackBerry/i) || ua.match(/Windows Phone/i)){
        return true;
    } else {
        return false;
    }
}

function isTouchDevice() {
  return (('ontouchstart' in window) ||
    (navigator.maxTouchPoints > 0) ||
    (navigator.msMaxTouchPoints > 0));
}

function popupOpen(inputData) {
    var $popup = null;

    if (typeof inputData == 'object') {
        $popup = inputData;
    } else {
        $popup = $(inputData);
    }

    // $('body').addClass('u-scroll-lock');
    $('.js-popup.shown').removeClass('shown');
    $popup.addClass('shown');
}

function popupClose(inputData) {
    var $popup = null;

    if (typeof inputData == 'object') {
        $popup = inputData;
    } else {
        $popup = $(inputData);
    }

    // $('body').removeClass('u-scroll-lock');

    if ($popup.length) {
        $popup.removeClass('shown');
    } else {
        $('.js-popup.shown').removeClass('shown');
    }
}

function amountInit($amount) {
    var amount = {
        $buttonDecrement: $('.amount-mini__button_decrement', $amount),
        $buttonIncrement: $('.amount-mini__button_increment', $amount),
        $input: $('.amount-mini__input', $amount),
    };

    amount.value = Number(getClearValue(amount.$input.val()));
    amount.step = $amount.data('step') || 1;
    amount.min = $amount.data('min') || 0;
    amount.max = $amount.data('max') || null;

    if (amount.value < amount.min) {
        amount.value = amount.min;
    } else if (amount.max && amount.value > amount.max) {
        amount.value = amount.max;
    }

    amount.$input.val(amount.value);

    amount.$buttonDecrement.on('click', function() {
        amount.value = Number(getClearValue(amount.$input.val()));
        amount.value = amount.value - amount.step;

        if (amount.value < amount.min) {
            amount.value = amount.min;
        }

        amount.$input.val(amount.value);

        if(appFlyBasket){
            appFlyBasket.updateItem(amount.$input.get(0), false);
        }
    });

    amount.$buttonIncrement.on('click', function() {
        amount.value = Number(getClearValue(amount.$input.val()));
        amount.value = amount.value + amount.step;

        if (amount.max && amount.value > amount.max) {
            amount.value = amount.max;
        }

        amount.$input.val(amount.value);

        if(appFlyBasket){
            appFlyBasket.updateItem(amount.$input.get(0), false);
        }
    });

    amount.$input.on('input', function(dataTarget) {
        amount.value = Number(getClearValue(amount.$input.val()));
        amount.$input.val(amount.value);
    });

    amount.$input.on('change', function(dataTarget) {
        amount.value = Number(getClearValue(amount.$input.val()));

        if (amount.value < amount.min) {
            amount.value = amount.min;
        } else if (amount.max && amount.value > amount.max) {
            amount.value = amount.max;
        }

        amount.$input.val(amount.value);

    });

    function getClearValue(value) {
        if (value) {
            return value.replace(/[^0-9]/g, '')
        }

        return null;
    }
}

function timerInit($timer) {
    var timer = {};

    timer.$value = $('.timer__value', $timer);
    timer.dateTargetString = $timer.data('date-target');

    if (!timer.dateTargetString) {
        return false;
    }

    timer.dateTarget = new Date(timer.dateTargetString);
    timer.dateToday = new Date();
    timer.timeLeft = (timer.dateTarget - timer.dateToday) / 1000;

    if (timer.timeLeft > 0) {
        timer.$value.timer({
            format: '%D д %H ч %M м %S с',
            countdown: true,
            duration: Math.ceil(timer.timeLeft) + 's',
            callback: function() {
                // После завршения
            }
        });
    }
}

function sliderInit($slider) {
    var slider = {
            $inputs: $('.filter__slider-input', $slider),
            $captions: $('.filter__slider-caption', $slider),
            $el: $('.filter__slider-scale', $slider),
            noUiSlider: null,
        };
        
    slider.min = $slider.data('min') || 0;
    slider.max = $slider.data('max') || 1000;
    slider.startFrom = $slider.data('start-from') || slider.min;
    slider.startTo = $slider.data('start-to') || slider.max;

    slider.$captions.eq(0).text(slider.min);
    slider.$captions.eq(1).text(slider.max);

    slider.noUiSlider = noUiSlider.create(slider.$el[0], {
        start: [slider.startFrom, slider.startTo],
        range: {
            min: slider.min,
            max: slider.max,
        },
        step: 1,
        connect: true,
    });

    slider.noUiSlider.on('update', function(values) {
        slider.$inputs.eq(0).val(Math.round(values[0]));
        slider.$inputs.eq(1).val(Math.round(values[1]));
    });

    slider.noUiSlider.on('change', function(values) {
        slider.$inputs.eq(0).trigger('keyup');
    });

    slider.$inputs.on('input', function() {
        var value = this.value.replace(/[^0-9]/g, '') * 1;
        var index = $(this).index();

        slider.$inputs.eq(index).val(value);
    });

    slider.$inputs.on('change', function() {
        var value = this.value * 1;
        var index = $(this).index();

        if (value < slider.min) {
            value = slider.min;
        } else if (value > slider.max) {
            value = slider.max;
        }

        slider.$inputs.eq(index).val(value);
        slider.noUiSlider.set([slider.$inputs.eq(0).val(), slider.$inputs.eq(1).val()]);
    });
}

window.catalog = {
    init: function($catalog) {
        if ($catalog.hasClass('catalog_initialized')) {
            return false;
        }

        $('.catalog__bar', $catalog).each(function() {
            var $select = $('select', this);

            $select.selectCustom();
        });

        $('.catalog__menu_dates', $catalog).each(function() {
            var carousel = {
                $el: $('.owl-carousel', this),
            };

            carousel.options = {
                nav: true,
                dots: false,
                navText: ['<svg class="icon-svg"><use xlink:href="#icon-arrow-1"></use></svg>', '<svg class="icon-svg"><use xlink:href="#icon-arrow-1"></use></svg>'],
                margin: 0,
                loop: false,
                autoWidth: true,
                items: 1,
            };

            carousel.$el.on('translated.owl.carousel', function(event) {
                $('.owl-item.active', carousel.$el).first().addClass('active-first').siblings().removeClass('active-first');
            });

            carousel.$el.owlCarousel(carousel.options);
        });

        $('.catalog__menu_categories', $catalog).each(function() {
            var $timers = $('.timer', this);

            $timers.each(function() {
                var $timer = $(this);

                timerInit($timer);
            });
        });

        $('.catalog__head-sub', $catalog).each(function() {
            var $timer = $('.timer', this);

            timerInit($timer);
        });

        $('.catalog__menu_categories', $catalog).each(function() {
            var $timers = $('.timer', this);

            $timers.each(function() {
                var $timer = $(this);

                timerInit($timer);
            });
        });

        $('.catalog__filter', $catalog).each(function() {
            var $filter = $(this),
                $items = $('.filter__item', this);

            $items.each(function() {
                var item = {
                    $el: $(this),
                };

                item.$slider = $('.filter__slider', item.$el);
                item.$title = $('.filter__item-title', item.$el);

                if (item.$slider.length) {
                    sliderInit(item.$slider);
                }

                item.$title.on('click', function(event) {
                    item.$el.toggleClass('expanded');
                    event.preventDefault();
                });
            });

            $('.filter__button-toggle', $filter).on('click', function(event) {
                $filter.toggleClass('expanded');
                event.preventDefault();
            });
        });

        $('.catalog__products', $catalog).each(function() {
            var $products = $('.catalog__products-item', this);

            $products.each(function() {
                var product = {
                    $el: $(this),
                };

                product.$amount = $('.product-mini-a__amount', product.$el);

                $('.button-tooltip', product.$el).tooltipCustom();

                amountInit(product.$amount);
            });
        });

        $catalog.addClass('catalog_initialized');
    },
};