(function( $ ) {
 
    $.fn.tooltipCustom = function() {
    	return this.each(function() {
            var $button = $(this),
                tooltip = {
                    $el: $('.tooltip-mini', $button).clone(),
                    width: 0,
                    top: 0,
                    left: 0,
                    position: $button.data('tooltip-position'),
                    timer: null,
                    arrow: {},
                },
                win = {};

            if (!tooltip.$el.length) {
                return false;
            }

            tooltip.$el.addClass('tooltip-mini_temprorary');

            $button.on('mouseenter mouseleave', function(event){
                if (event.type == 'mouseenter') {
                    clearInterval(tooltip.timer);

                    $('.tooltip-mini_temprorary').remove();
                    $('body').append(tooltip.$el);

                    win.width = $(window).width();

                    tooltip.$el = $('.tooltip-mini_temprorary').last();
                    tooltip.$el.append('<div class="tooltip-mini__arrow"></div>');

                    tooltip.width = tooltip.$el.outerWidth();
                    tooltip.height = tooltip.$el.outerHeight();
                    tooltip.top = $button.offset().top - tooltip.height - 15;
                    tooltip.left = $button.offset().left + $button.outerWidth()/2 - tooltip.width/2;
                    tooltip.arrow.$el = $('.tooltip-mini__arrow', tooltip.$el);
                    tooltip.arrow.left = -1;

                    if (tooltip.position === 'bottom') {
                        tooltip.top = $button.offset().top + $button.outerHeight() + 12;
                    }

                    if (tooltip.left < 0) {
                        tooltip.left = 0;
                        tooltip.arrow.left = $button.offset().left + ($button.width() / 2);
                    } else if (tooltip.left + tooltip.width > win.width) {
                        tooltip.left = win.width - tooltip.width;
                        tooltip.arrow.left = $button.offset().left - tooltip.left + ($button.width() / 2);
                    }

                    tooltip.$el.css({top: tooltip.top, left: tooltip.left}).addClass('tooltip-mini_shown');

                    if (tooltip.arrow.left > -1) {
                        tooltip.arrow.$el.css({left: tooltip.arrow.left});
                    }

                    tooltip.$el.on('mouseenter mouseleave', function(event){
                        if (event.type == 'mouseenter') {
                            clearInterval(tooltip.timer);
                        } else {
                            tooltip.timer = setTimeout(function(){
                                tooltip.$el.remove();
                            }, 100);
                        }
                    });

                } else {
                    if (tooltip.$el) {
                        tooltip.timer = setTimeout(function(){
                            tooltip.$el.remove();
                        }, 100);
                    }
                }
            });
    	});
    };
 
}( jQuery ));