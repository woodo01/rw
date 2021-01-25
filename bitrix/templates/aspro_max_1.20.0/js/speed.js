CheckTopMenuDotted = function(){
	var menu = $('nav.mega-menu.sliced');

	if(window.matchMedia('(max-width:991px)').matches)
		return;

	if(menu.length)
	{
		menu.each(function(){
			if($(this).hasClass('initied'))
				return false;

			var menuMoreItem = $(this).find('td.js-dropdown');
			if($(this).parents('.collapse').css('display') == 'none'){
				return false;
			}

			var block_w = $(this).closest('div')[0].getBoundingClientRect().width;
			var	menu_w = $(this).find('table')[0].getBoundingClientRect().width;
			var afterHide = false;

			while(menu_w > block_w) {
				menuItemOldSave = $(this).find('td').not('.nosave').last();
				if(menuItemOldSave.length){
					menuMoreItem.show();
					var oldClass = menuItemOldSave.attr('class');
					menuItemNewSave = '<li class="menu-item ' + (menuItemOldSave.hasClass('dropdown') ? 'dropdown-submenu ' : '') + (menuItemOldSave.hasClass('active') ? 'active ' : '') + '" data-hidewidth="' + menu_w + '" ' + (oldClass ? 'data-class="' + oldClass + '"' : '') + '>' + menuItemOldSave.find('.wrap').html() + '</li>';
					menuItemOldSave.remove();
					menuMoreItem.find('> .wrap > .dropdown-menu').prepend(menuItemNewSave);
					menu_w = $(this).find('table')[0].getBoundingClientRect().width;
					afterHide = true;
				}
				//menu.find('.nosave').css('display', 'table-cell');
				else{
					break;
				}
			}

			if(!afterHide) {
				do {
					var menuItemOldSaveCnt = menuMoreItem.find('.dropdown-menu').find('li').length;
					menuItemOldSave = menuMoreItem.find('.dropdown-menu').find('li').first();
					if(!menuItemOldSave.length) {
						menuMoreItem.hide();
						break;
					}
					else {
						var hideWidth = menuItemOldSave.attr('data-hidewidth');
						if(hideWidth > block_w) {
							break
						}
						else {
							var oldClass = menuItemOldSave.attr('data-class');
							menuItemNewSave = '<td class="' + (oldClass ? oldClass + ' ' : '') + '" data-hidewidth="' + block_w + '"><div class="wrap">' + menuItemOldSave.html() + '</div></td>';
							menuItemOldSave.remove();
							$(menuItemNewSave).insertBefore($(this).find('td.js-dropdown'));
							if(!menuItemOldSaveCnt) {
								menuMoreItem.hide();
								break;
							}
						}
					}
					menu_w = $(this).find('table')[0].getBoundingClientRect().width;
				}
				while(menu_w <= block_w);
			}
			$(this).find('td').css('visibility', 'visible');
			$(this).find('td').removeClass('unvisible');
			$(this).addClass('ovisible');
			$(this).addClass('initied');
		})
	}
	return false;
}

CheckTopMenuPadding = function(){
	if($('.logo_and_menu-row .right-icons .wrap_icon').length && $('.logo_and_menu-row .menu-row').length && !$('.subbottom.menu-row').length){
		var menuPosition = $('.menu-row .menu-only').position().left,
			leftPadding = 0,
			rightPadding = 0;
		$('.logo_and_menu-row .menu-row>div').each(function(indx){
			if(!$(this).hasClass('menu-only')){
				var elementPosition = $(this).position().left,
					elementWidth = $(this).outerWidth()+1;

				if(elementPosition > menuPosition){
					rightPadding += elementWidth;
				}else{
					leftPadding += elementWidth;
				}
			}
		}).promise().done(function(){
			$('.logo_and_menu-row .menu-only').css({'padding-left': leftPadding, 'padding-right': rightPadding});
		});
	}
}

CheckTopMenuOncePadding = function(){
	if($('.menu-row.sliced .right-icons .wrap_icon').length)
	{
		var menuPosition = $('.menu-row .menu-only').position().left,
			leftPadding = 0,
			rightPadding = 0;
		$('.menu-row.sliced .maxwidth-theme>div>div>div').each(function(indx){
			if(!$(this).hasClass('menu-only')){
				var elementPosition = $(this).position().left,
					elementWidth = $(this).outerWidth()+1;

				if(elementPosition > menuPosition){
					rightPadding += elementWidth;
				}else{
					leftPadding += elementWidth;
				}
			}
		}).promise().done(function(){
			$('.menu-row.sliced .menu-only').css({'padding-left': leftPadding, 'padding-right': rightPadding});
		});
	}
	else if($('.logo_and_menu-row .mega-menu.sliced').length && !$('.subbottom.menu-row').length)
	{
		var leftPadding = 0;
		$('.logo_and_menu-row .maxwidth-theme>div>div>div').each(function(indx){
			if(!$(this).hasClass('menu-row')){
				var elementPosition = $(this).position().left,
					elementWidth = $(this).outerWidth()+1;
				if(!$(this).is(':visible') || $(this).hasClass('pull-right') || !$(this).height())
					elementWidth = 0;
				leftPadding += elementWidth;
			}
		}).promise().done(function(){
			$('.logo_and_menu-row .logo-row .menu-row').css({'padding-left': leftPadding});
		});
	}
}