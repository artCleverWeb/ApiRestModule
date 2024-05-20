
/*!
	Select Custom
*/
(function($){

	var methods = {
		init : function(options) {
			return this.each(function(){
				var settings = $.extend({}, options),
					$select = $(this),
					select = {
						$custom: $select.parents(),
						$list: null,
						$listInner: null,
						$listInnerI: null,
						$items: null,
						$value: null,
						$valueText: null,
						$options: null,
						$popupData: null, /* $select.parents('.popup__data') */
						$buttonClose: null,

						classExtra: $select.data('class-extra') || '',
						classSuffix: $select.data('class-suffix') || '',
						isListOuter: $select.data('list-outer'),
						listHead: $select.data('list-head') || 'Список',
						idRandom: new Date().getTime(),

						isMultiple: $select.prop('multiple'),
						buttonAdd: $select.data('button-add') || 'Добавить',
						$buttonSave: null,

						isSearch: $select.data('search'),
						$search: null,
						search: {
							$input: null,
							placeholder: $select.data('search-placeholder') || 'Найти',
							notFound: $select.data('search-not-found') || 'Не найдено',
							timer: 0,
						},

						list: {
							style: {
								top: 0,
								left: 0,
								width: 0,
								height: 0,
							},
						}
					};

				$select.selectCustom('destroy');

				$select.data('id-random', select.idRandom);
				
				$select.wrap('<div class="select-a '+ select.classExtra +'"></div>');
				select.$custom = $select.parent(),
				select.$options = $('option, optgroup', select.$custom);
				select.$custom.removeClass('select');

				if ($select.prop('disabled')) {
					select.$custom.addClass('disabled');
				}

				select.$custom
						.append('<div class="select-a__value"><div class="select-a__value-inner"><div class="select-a__value-text"></div></div><div class="select-a__mark"><svg class="icon-svg select-a__mark-arrow"><use xlink:href="#icon-arrow-1"></use></svg></div></div>');
				
				select.$value = $('.select-a__value', select.$custom);
				select.$valueText = $('.select-a__value-text', select.$custom);

				select.$value.append('<div class="select-a__overlay"></div>')
						.append('<div class="select-a__list"><div class="select-a__list-inner"><div class="select-a__list-inner-i"></div></div></div>');
				
				select.$list = $('.select-a__list', select.$custom);

				if (select.classSuffix) {
					select.$custom.addClass('select-a_' + select.classSuffix);
					select.$list.addClass('select-a__list_' + select.classSuffix);
				}

				select.$listInner = $('.select-a__list-inner', select.$custom);
				select.$listInnerI = $('.select-a__list-inner-i', select.$custom);

				if (select.isSearch) {
					select.$listInner.prepend('<div class="select-a__search"><input type="text" class="select-a__search-input" placeholder="'+ select.search.placeholder +'"><svg class="icon-svg select-a__search-icon"><use xlink:href="#icon-search"></use></svg></div>');
					select.search.$input = $('.select-a__search', select.$custom);
					select.$search = $('.select-a__search', select.$custom);
					select.search.$input = $('.select-a__search-input', select.$custom);
					select.$custom.addClass('select-a_search');
				}

				select.$listInner.prepend('<div class="select-a__head"><div class="title select-a__title">' + select.listHead + '</div><button type="button" class="select-a__button-close"><svg class="icon-svg filter__button-close-icon"><use xlink:href="#icon-close"></use></svg></button></div>');
				select.$buttonClose = $('.select-a__button-close', select.$custom);

				if (select.isMultiple) {
					select.$custom.addClass('multiple');
					select.$valueText.html(select.buttonAdd);
					select.$listInner.append('<div class="select-a__button-save"><button type="button" class="button-a select-a__button-save-link">Сохранить</button></div>');
					select.$buttonSave = $('.select-a__button-save-link', select.$custom);
				}

				$('option', $select).each(function(index){
					var $option = $(this);

					$option.data('id', index + 1);
				});

				if ($('optgroup', $select).length) {
					select.$custom.addClass('nesting');
				}

				$('> *', $select).each(function(index){
					var $option = $(this);

					listItemCreate($option, select.$listInnerI);
				});

				if (select.isSearch) {
					select.$listInnerI.append('<div class="select-a__search-not-found">'+ select.search.notFound +'</div>');
				}

				select.$items = $('.select-a__item', select.$custom);
				select.$items.filter('.select-a__item_special').last().addClass('select-a__item_special-last');

				/*if (!DEVICE_MOBILE || !DEVICE_TOUCH) {
					select.$listInnerI.mCustomScrollbar({
						theme: "dark",
						mouseWheel: { preventDefault: false },
						scrollInertia: 300,
					});
				}*/

				$select.on('change.' + select.idRandom, function(event){
					if ($select.prop('disabled')) {
						select.$custom.addClass('disabled');
					} else {
						select.$custom.removeClass('disabled');
					}
				});

				select.$items.each(function(){
					var $item = $(this),
						item = {
							id: $item.data('id'),
							text: $item.text(),
							$inner: $('> .select-a__item-inner', $item),
						};

					item.$inner.on('click', function(event){
						if ($item.hasClass('select-a__item_parent')) {
							$item.toggleClass('expanded');
						} else {
							if (!select.isMultiple) {
								if (!$item.hasClass('select-a__item_checked')) {
									$item.addClass('select-a__item_checked').siblings().removeClass('select-a__item_checked');
									select.$valueText.html(item.text);
									select.$value.removeClass('select-a__value_placeholder');
									select.$options.prop('selected', false).filter(function() {
										return $(this).data('id') == item.id;
									}).prop('selected', true);
									$select.trigger('change');
									select.$list.addClass('changed');
								}

								listHide();
							} else {
								$item.toggleClass('select-a__item_checked');

								select.$options.each(function() {
									var $option = $(this);

									if ($option.data('id') == item.id) {
										if ($option.prop('selected')) {
											$option.prop('selected', false);
											$('.select-a__tag', select.$custom).filter(function() {
												return $(this).data('id') == item.id;
											}).remove();
										} else {
											$option.prop('selected', true);
											$('<div class="select-a__tag" data-id="'+ item.id +'">'+ item.text +'<button type="button" class="select-a__tag-button-remove"><svg class="icon-svg select-a__tag-button-remove-icon"><use xlink:href="#icon-close"></use></svg></button></div>').insertBefore(select.$value);
										}
									}
								});
								$select.trigger('change');
								select.$list.addClass('changed');
							}
						}
					});

				});

				select.$custom.on('click', '.select-a__tag-button-remove', function() {
					var $tag = $(this).closest('.select-a__tag'),
						tag = {
							id: $tag.data('id'),
						};

					$tag.remove();

					select.$items.each(function(){
						var $item = $(this),
							item = {
								id: $item.data('id'),
							};

						if (item.id == tag.id) {
							$item.removeClass('select-a__item_checked');
						}
					});

					select.$options.each(function() {
						var $option = $(this);

						if ($option.data('id') == tag.id) {
							$option.prop('selected', false);
						}
					});
				});

				select.$value.on('click', function(event){
					var $selectVal = $(this);

					if (!$selectVal.prop('disabled')) {
						if (!select.$custom.hasClass('expanded')) {
							listShow();
						}
						else {
							listHide();
						}
					}

					event.stopPropagation();
				});

				select.$list.on('click', function(event){

					event.stopPropagation();
				});

				select.$buttonClose.on('click', function(event){
					listHide();
				});

				if (select.isMultiple) {
					select.$buttonSave.on('click', function(event){
						listHide();
					});
				}

				$(document).on('click', function(){
					listHide();
				});

				$(window).on('resize.' + select.idRandom, function(){
					if (select.isListOuter) {
						listReposition();
					}

					listResize();
				});

				$(window).on('scroll.' + select.idRandom, function(){
					if (select.isListOuter) {
						listReposition();
					}
				});

				if (select.$popupData && select.$popupData.length) {
					select.$popupData.on('scroll.' + select.idRandom, function(){
						if (select.isListOuter) {
							listReposition();
						}
					});
				}

				if (select.isSearch) {
					select.search.$input.on('input', function(event){
						var term = $(this).val();
						
						clearTimeout(select.search.timer);

						select.search.timer = setTimeout(function(){
							if (term.length > 1) {
								searchStart(term);
							} else {
								searchReset();
							}
						}, 300);
					});
				}

				function searchStart(term) {
					var found = false;

					select.$items.each(function(){
						var $item = $(this),
							item = {
								text: $('.select-a__item-inner', this).text().toLowerCase()
							};

						if (item.text.indexOf(term.toLowerCase()) > -1) {
							$item.addClass('found');
							$item.closest('.select-a__item_parent').addClass('expanded');
							found = true;
						} else {
							$item.removeClass('found');
						}
					});

					if (found) {
						select.$list.removeClass('select-a__list_not-found');
					} else {
						select.$list.addClass('select-a__list_not-found');
					}

					select.$list.addClass('select-a__list_searching');

					/*if (!DEVICE_MOBILE || !DEVICE_TOUCH) {
						select.$listInnerI.mCustomScrollbar('update');
					}*/

				}

				function searchReset() {
					select.$list.removeClass('select-a__list_not-found select-a__list_searching');
				}


				function listItemCreate($option, $container) {
					var option = {},
						$item = null;

					if ($option[0].tagName === 'OPTION') {
						option = {
							html: $option.html(),
							href: $option.data('href'),
							id: $option.data('id'),
							placeholder: $option.data('placeholder'),
						};
						$item = $('<div class="select-a__item" data-id="'+ option.id +'"><div class="select-a__item-inner">'+ option.html +'<svg class="icon-svg select-a__item-check"><use xlink:href="#icon-check"></use></svg></div></div>');
						
						if (option.href) {
							$item = $('<a href="'+ option.href +'" class="select-a__item"><div class="select-a__item-inner">'+ option.html +'<svg class="icon-svg select-a__item-check"><use xlink:href="#icon-check"></use></svg></div></a>');
						}

						if ($option.prop('selected')) {
							if (select.isMultiple) {
								$('<div class="select-a__tag" data-id="'+ option.id +'">'+ option.html +'<button type="button" class="select-a__tag-button-remove"><svg class="icon-svg select-a__tag-button-remove-icon"><use xlink:href="#icon-close"></use></svg></button></div>').insertBefore(select.$value);
							} else {
								select.$valueText.html(option.html);
								
								if (option.placeholder) {
									select.$value.addClass('select-a__value_placeholder');
								}
							}

							$item.addClass('select-a__item_checked');
						}

						if ($option.data('special')) {
							$item.addClass('select-a__item_special');
						}

						if (!$option.data('placeholder')) {
							$container.append($item);
						}
					}

					if ($option[0].tagName === 'OPTGROUP') {
						option = { label: $option.prop('label') };
						$item = $('<div class="select-a__item select-a__item_parent"><div class="select-a__item-inner">'+ option.label +'<svg class="icon-svg select-a__item-arrow"><use xlink:href="#icon-arrow-1"></use></svg></div><div class="select-a__sub-list"></div>');

						$container.append($item);

						$('> *', $option).each(function(){
							var $optionSub = $(this);

							listItemCreate($optionSub, $('.select-a__item_parent:last .select-a__sub-list', $container));
						});
					}
				}

				function listReposition(){
					if (select.$custom.hasClass('expanded')) {
						select.list.style.top = select.$custom.offset().top;
						select.list.style.left = select.$custom.offset().left;
						select.list.style.width = select.$custom.outerWidth();
						select.list.style.height = select.$custom.outerHeight();

						select.$list.css({top: 0, left: -1000, width: select.list.style.width});

						if (select.list.style.left + select.$list.width() > $(window).width()) {
							select.list.style.left = select.list.style.left + select.$custom.outerWidth() - select.$list.width();
						}

						select.$list.css({top: select.list.style.top + select.list.style.height, left: select.list.style.left, width: select.list.style.width});
					}
				}
				
				function listShow() {
					$('.select-a').removeClass('expanded');
					select.$custom.addClass('expanded');

					if (select.isListOuter) {
						$('body').append(select.$list);

						listReposition();

						select.$list.addClass('select-a__list_outer '+ select.classExtra);
					}

					if (!DEVICE_MOBILE || !DEVICE_TOUCH) {
						/*$('.select-a__item_checked', select.$list).each(function() {
							var $item = $(this);

							select.$listInnerI.mCustomScrollbar('scrollTo', $item, {scrollInertia: 0});
						});*/
						/*setTimeout(function(){
							select.$list.css('opacity', 1);
						}, 100);*/
					}

					listResize();
				}

				function listResize() {
					var win = {
							height: (window.innerHeight || $(window).height()),
						},
						listHeight = 0;

					if (window.matchMedia("(max-width: 767px)").matches) {
						listHeight = win.height - 55;

						if (select.isSearch) {
							listHeight -= 59;
						}
						if (select.isMultiple) {
							listHeight -= 60;
						}

						select.$listInnerI.css({maxHeight: listHeight});
					}
				}

				function listHide() {
					$('.select-a').removeClass('expanded');
					select.$custom.removeClass('expanded');

					if (select.isListOuter) {
						select.$list.removeClass('select-a__list_outer').css({top: 0, left: 0, width: ''});
						select.$custom.append(select.$list);
					}

					select.$list.removeClass('changed');
				}

			});
		},
		destroy : function() {
			return this.each(function(){
				var $select = $(this),
					select = {
						$custom: $(this).parent(),
						idRandom: $(this).data('id-random'),
						$popupData: $(this).parents('.popup__data'),
					};
					

				if ($select.data('id-random')) {
					$select.off('change.' + select.idRandom);
					$(window).off('resize.' + select.idRandom);
					$(window).off('scroll.' + select.idRandom);

					if (select.$popupData.length) {
						select.$popupData.off('scroll.' + select.idRandom);
					}

					$('.select-a__list', select.$custom).remove();
					$('.select-a__value', select.$custom).remove();
					$select.unwrap();
					$select.data('id-random', '');
				}

			})
		},
		update : function() {
			return this.each(function(){
				var $select = $(this),
					select = {
						$custom: $select.parent(),
						$value: null,
						$valueText: null,
						$items: null,
						$options: null,
						isMultiple: $select.prop('multiple'),
						$tags: null,
					}

				select.$items = $('.select-a__item', select.$custom);
				select.$tags = $('.select-a__tag', select.$custom);
				select.$options = $('option, optgroup', select.$custom);
				select.$value = $('.select-a__value', select.$custom);
				select.$valueText = $('.select-a__value-text', select.$custom);

				select.$options.each(function() {
					var $option = $(this);

					listItemUpdate($option);
				});

				function listItemUpdate($option) {
					var option = {},
						$item = null;


					if ($option[0].tagName === 'OPTION') {
						option = {
							html: $option.html(),
							href: $option.data('href'),
							id: $option.data('id'),
							placeholder: $option.data('placeholder'),
						};
						$item = select.$items.filter(function() {
							return $(this).data('id') == option.id; 
						});
						var $tag = select.$tags.filter(function() {
							return $(this).data('id') == option.id; 
						});


						if ($option.prop('selected')) {
							$item.addClass('select-a__item_checked');

							if (option.placeholder) {
								select.$value.addClass('select-a__value_placeholder');
							} else {
								select.$value.removeClass('select-a__value_placeholder');
							}

							if (select.isMultiple) {
								select.$custom.append('<div class="select-a__tag" data-id="'+ option.id +'">'+ option.html +'<button type="button" class="select-a__tag-button-remove"><svg class="icon-svg select-a__tag-button-remove-icon"><use xlink:href="#icon-close"></use></svg></button></div>');
							} else {
								select.$valueText.html(option.html);
							}
						} else {
							$item.removeClass('select-a__item_checked');
							$tag.remove();
						}
					}
				}
			});
		}
	};

	$.fn.selectCustom = function(method) {
		if (methods[method]){
			return methods[method].apply( this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || ! method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Метод с именем ' +  method + ' не существует для jQuery.selectCustom');
		}
	};
})(jQuery);
