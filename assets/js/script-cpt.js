(function ($) {
    'use strict';
		
	var cfMenu_main =  $( "#toplevel_page_cf-geoplugin" );

	if( !!cfMenu_main )
	{
		if( cfMenu_main.hasClass('wp-not-current-submenu') )
		{
			cfMenu_main.removeClass('wp-not-current-submenu').addClass('wp-menu-open wp-has-current-submenu');
		}
		if( cfMenu_main.children('a').hasClass('wp-not-current-submenu') )
		{
			cfMenu_main.children('a').removeClass('wp-not-current-submenu').addClass('wp-menu-open wp-has-current-submenu');
		}
	}

    var $cfMenu = $( "#toplevel_page_cf-geoplugin ul.wp-submenu li a[href*='"+ CFGP.current_url +"']");

	if( !!$cfMenu )
	{
		if( !$cfMenu.parent('li').hasClass('current') )
		{
			$cfMenu.parent( 'li' ).addClass( 'current' );
		}
		if( !$cfMenu.hasClass('current') )
		{
			$cfMenu.addClass( 'current' ).attr( 'aria-selected', 'page' );
		}
	}

    $(document).ready( function() {
		// Remove unusefull functions
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-country .form-field.term-parent-wrap").remove();
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-region .form-field.term-parent-wrap").remove();
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-city .form-field.term-parent-wrap").remove();
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-postcode .form-field.term-parent-wrap").remove();
		
        var $categoryDivs = $('.categorydiv');
		$categoryDivs.each(function(){
			var $categoryID = $(this).parent().parent().attr('id');
			var $categoryTitle = $(this).parent().parent().find("h2 > span").text();
	
			$(this).prepend('<input type="search" class="'+$categoryID+'-search-field" placeholder="' + CFGP.label.placeholder + ' '+$categoryTitle+'" style="width: 100%" />');
	
			$(this).on('keyup search', '.'+$categoryID+'-search-field', function (event) {
	
				var searchTerm = event.target.value,
					$listItems = $(this).parent().find('.categorychecklist li');
	
				if ($.trim(searchTerm)) {
	
					$listItems.hide().filter(function () {
						return $(this).text().toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1;
					}).show();
	
				} else {
	
					$listItems.show();
	
				}
	
			});
		});
    });
})(jQuery || window.jQuery || Zepto || window.Zepto);