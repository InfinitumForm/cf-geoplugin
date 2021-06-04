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
		// We must do realy problematic part of the plugin. Rename and remove things		
		$('body.post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-country, body.post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-region, body.post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-city, body.post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-postcode').each(function(){
			var $this = $(this),
				$type = function(){
					if($this.hasClass('taxonomy-cf-geoplugin-country')) {
						return 'country';
					} else if($this.hasClass('taxonomy-cf-geoplugin-region')) {
						return 'region';
					} else if($this.hasClass('taxonomy-cf-geoplugin-city')) {
						return 'city';
					} else if($this.hasClass('taxonomy-cf-geoplugin-postcode')) {
						return 'postcode';
					} else {
						return 'undefined';
					}
				},
				$name = $this.find('.term-name-wrap'),
				$description = $this.find('.term-description-wrap'),
				$description_field = $description.find('textarea[name="description"]'),
				$inline_edit_col = $this.find('.inline-edit-col'),
				$label = {
					country : {
						name :  CFGP.label.taxonomy.country.name,
						name_info :  CFGP.label.taxonomy.country.name_info,
						description :  CFGP.label.taxonomy.country.description,
						description_info :  CFGP.label.taxonomy.country.description_info
					},
					region : {
						name :  CFGP.label.taxonomy.region.name,
						name_info :  CFGP.label.taxonomy.region.name_info,
						description :  CFGP.label.taxonomy.region.description,
						description_info :  CFGP.label.taxonomy.region.description_info
					},
					city : {
						name :  CFGP.label.taxonomy.city.name,
						name_info :  CFGP.label.taxonomy.city.name_info,
					},
					postcode : {
						name :  CFGP.label.taxonomy.postcode.name,
						name_info :  CFGP.label.taxonomy.postcode.name_info,
					}
				};
			
			$label = $label[$type()];
			
			// Remove things
			$this.find('.form-field.term-parent-wrap').remove();
			
			// Fix name
			$name.find('label').text( $label.name );
			$name.find('> p').text( $label.name_info );
			$name.find('p.description').text( $label.name_info );
			
			// Fix description		
			if($type() == 'country' || $type() == 'region')
			{	
				if($this.find('#edittag').length > 0){
					$description.find('label').text( $label.description );
					$description.find('textarea').closest('td').prepend(function(){
						return $('<input/>').attr({
							type : 'text',
							name : $description_field.attr('name'),
							id : $description_field.attr('id'),
							value : $description_field.val()
						})
					});
				} else {
					$description.find('label').text( $label.description ).after(function(){
						return $('<input/>').attr({
							type : 'text',
							name : $description_field.attr('name'),
							id : $description_field.attr('id'),
							value : $description_field.val()
						})
					});
				}
				$description.find('textarea').remove();
				$description.find('> p').text( $label.description_info );
				$description.find('p.description').text( $label.description_info );	
			}
			else
			{
				$description.remove();
			}
			// Fix inline edit
			if($inline_edit_col.length > 0)
			{
				$inline_edit_col.find('label:nth-child(1) > .title').text( $label.name );
			}		 
		});
		
		
		
		
		// Fix more things
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