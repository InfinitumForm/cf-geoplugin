(function( $ ) {
	'use strict';

	$(document).ready(function () {

        var $categoryDivs = $('.categorydiv');
		$categoryDivs.each(function(){
			var $categoryID = $(this).parent().parent().attr('id');
			var $categoryTitle = $(this).parent().parent().find("h2 > span").text();
	
			$(this).prepend('<input type="search" class="'+$categoryID+'-search-field" placeholder="' + cf_geoplugin_category_filter.placeholder + ' '+$categoryTitle+'" style="width: 100%" />');
	
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

})( jQuery );
