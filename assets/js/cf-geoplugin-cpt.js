// CPT and taxonomies customatization and actions
(function ($) {
    'use strict';

    var $currentUrl = document.URL || window.location.href;
    $currentUrl = $currentUrl.split( '/' );
	$currentUrl = $currentUrl[ ($currentUrl.length-1) ];
	if( $currentUrl.indexOf('cf-geoplugin-region') > 0 )
	{
		$currentUrl = 'cf-geoplugin-region';
	}
	else if( $currentUrl.indexOf('cf-geoplugin-country') > 0 )
	{
		$currentUrl = 'cf-geoplugin-country';
	}
	else if( $currentUrl.indexOf('cf-geoplugin-city') > 0 )
	{
		$currentUrl = 'cf-geoplugin-city';
	}

	if( $currentUrl == 'post-new.php?post_type=cf-geoplugin-banner' )
	{
		$currentUrl = 'edit.php?post_type=cf-geoplugin-banner';
	}
	
    var $cfMenu = $( "#toplevel_page_cf-geoplugin ul.wp-submenu li a[href*='"+ $currentUrl +"']");

    if( !$cfMenu.parent('li').hasClass('current') )
    {
        $cfMenu.parent( 'li' ).addClass( 'current' );
    }
    if( !$cfMenu.hasClass('current') )
    {
        $cfMenu.addClass( 'current' ).attr( 'aria-selected', 'page' );
    }

    $(document).ready( function() {
		// Remove unusefull functions
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-country .form-field.term-parent-wrap").remove();
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-region .form-field.term-parent-wrap").remove();
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-city .form-field.term-parent-wrap").remove();
		
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