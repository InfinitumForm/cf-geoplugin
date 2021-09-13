(function ($) {
	var debounce,
		loader = '<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> ' + CFGP.label.loading,
		/*
		 * Select country, region, city (multiple)
		 */		
		country_region_city_multiple_form = function country_region_city_multiple_form (dry){
			var $form = $('.cfgp-country-region-city-multiple-form');
			if($form.length > 0)
			{
				$form.each(function(){
					var $container = $(this),
						$select_countries = $container.find('select.cfgp-select-country'),
						$select_regions = $container.find('select.cfgp-select-region'),
						$select_cities = $container.find('select.cfgp-select-city'),
						$required_field = $container.find('.required-field');
						
					if(dry === true){
						var $country_codes = $select_countries.find('option:selected').map(function(_, e){return e.value}).get();							
						
						if($country_codes.length === 0) {
							$select_regions.attr('data-placeholder', CFGP.label.chosen.choose_countries).prop('disabled', true).trigger("chosen:updated");
							$select_cities.attr('data-placeholder', CFGP.label.chosen.choose_countries).prop('disabled', true).trigger("chosen:updated");
						} else {
							$select_regions.attr('data-placeholder', CFGP.label.chosen.choose_regions).prop('disabled', false).trigger("chosen:updated");
							$select_cities.attr('data-placeholder', CFGP.label.chosen.choose_cities).prop('disabled', false).trigger("chosen:updated");
						}
						
						if($required_field.length > 0){
							if($country_codes.length > 0 && $required_field.val().length === 0) {
								$required_field.removeClass('required').prop('required', true);
							} else {
								$required_field.addClass('required').prop('required', false);
							}
						}
					}
					
					$select_countries.on('change', function(){
						var $country_codes = $(this).find('option:selected').map(function(_, e){return e.value}).get(),
							$regions_code = $select_regions.find('option:selected').map(function(_, e){return e.value}).get(),
							$cities_code = $select_cities.find('option:selected').map(function(_, e){return e.value}).get(),
							$regions = [], $cities = [], r=0, c=0;

							if($country_codes.length === 0) {
								$select_regions.attr('data-placeholder', CFGP.label.chosen.choose_countries).prop('disabled', true).trigger("chosen:updated");
								$select_cities.attr('data-placeholder', CFGP.label.chosen.choose_countries).prop('disabled', true).trigger("chosen:updated");
							} else {
								$select_regions.attr('data-placeholder', CFGP.label.chosen.choose_regions).prop('disabled', false).trigger("chosen:updated");
								$select_cities.attr('data-placeholder', CFGP.label.chosen.choose_cities).prop('disabled', false).trigger("chosen:updated");
							}
							
							if($required_field.length > 0){
								if($country_codes.length > 0 && $required_field.val().length === 0) {
									$required_field.removeClass('required').prop('required', true);
								} else {
									$required_field.addClass('required').prop('required', false);
								}
							}
							
						for(var i in $country_codes){
							var cc = CFGP_GEODATA[$country_codes[i]];
							
							for(region in cc.region){
								$regions[r]= '<option value="' + region + '">' + cc.region[region] + '</option>';
								r++;
							}
							
							for(city in cc.city){
								$cities[c]= '<option value="' + city + '">' + cc.city[city] + '</option>';
								c++;
							}
						}
						
						$select_regions.html( $regions.join('') );
						for (var i in $regions_code) {
							$select_regions.find('option[value="' + $regions_code[i] + '"]').prop('selected', true);
						}
						$select_regions.trigger("chosen:updated");
						
						$select_cities.html( $cities.join('') );	
						for (var i in $cities_code) {
							$select_cities.find('option[value="' + $cities_code[i] + '"]').prop('selected', true);
						}
						$select_cities.trigger("chosen:updated");
						
						return;
					});
				});
			}
		};
		
	country_region_city_multiple_form(true);

	
	/*
	 * Chosen initialization
	 * @since 7.0.0
	*/
	(function($$){
		if( $($$) )
		{
			$($$).each(function(index, element) {
				$(this).chosen({
					no_results_text: CFGP.label.chosen.not_found,
					width: "100%",
					search_contains:true
				});
			});
		}
	}('.chosen-select'));
	
	
	$(document)
	
	// Select all
	.on('click', '.cfgp-select-all', function( e ){
		e.preventDefault();
		var $this = $(this),
			$target = $( '#' + $this.attr('data-target') );
		$target.find('option').each(function(){
			var $option = $(this);
			if($option.is(':selected')) {
				$(this).prop('selected',false);
			} else {
				$(this).prop('selected',true);
			}
		}).promise().done(function(){
			$target.trigger('change').trigger('chosen:updated');
		});
	})
	
	// Add SEO redirection
	.on('click', '.cfgp-add-seo-redirection', function(e){
		e.preventDefault();
		var $this = $(this),
			$item = $this.closest('.cfgp-repeater-item'),
			$repeater  = $item.closest('.cfgp-repeater'),
			$template = $repeater.find('.cfgp-repeater-item:first-child').html(),
			$index = $repeater.find('.cfgp-repeater-item').length;
			
		$template = $template.replace(/(\[0\])/gi, '[' + $index + ']');
		$template = $template.replace(/(-0-)/gi, '-' + $index + '-');
		$template = $template.replace(/(_0_)/gi, '_' + $index + '_');
		
		var $html = $('<div/>').addClass('cfgp-row cfgp-repeater-item cfgp-country-region-city-multiple-form').html($template);
		$html.find('select option:selected').removeAttr('selected').prop('selected', false);
		$html.find('input[type="url"], input[type="text"]').val('');
		
		$html.find('input[type="checkbox"]').prop('checked', false);
		
		$html.find('select#cfgp-seo-redirection-' + $index + '-http_code').val(302);
		
		$html.find('select#cfgp-seo-redirection-' + $index + '-region').html('');
		$html.find('select#cfgp-seo-redirection-' + $index + '-city').html('');
		
		/* TODO - Reset chosen */
		$html.find('.chosen-container').remove();
		$html.find('.chosen-select').each(function(){
			$(this).chosen({
				no_results_text: CFGP.label.chosen.not_found,
				width: "100%",
				search_contains:true
			});
		}).promise().done(function(){
			$repeater.append($html);
			country_region_city_multiple_form(true);
		});
	})
	
	// Remove SEO redirection
	.on('click', '.cfgp-remove-seo-redirection', function(e){
		e.preventDefault();
		var $this = $(this),
			$item = $this.closest('.cfgp-repeater-item');
		$item.remove();
	});
		
})(jQuery || window.jQuery || Zepto || window.Zepto);