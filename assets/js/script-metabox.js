(function ($) {
	var debounce,
		loader = '<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> ' + CFGP.label.loading;
		
	/* Select all */
	$(document).on('click', '.cfgp-select-all', function( e ){
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
			$target.trigger('chosen:updated');
		});
	});
	
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
	
	/*
	 * Select country, region, city (multiple)
	 */
	(function($form){
		if($form.length > 0)
		{
			$($form).each(function(){
				var $container = $(this),
					$select_countries = $container.find('select.cfgp-select-country'),
					$select_regions = $container.find('select.cfgp-select-region'),
					$select_cities = $container.find('select.cfgp-select-city');
				
				$select_countries.on('change select', function(){
					var $country_code = $(this).find('option:selected').map(function(_, e){return e.value}).get();
					
					$select_regions.next('.chosen-container').find('.chosen-search-input').prop('disabled', true);
					$select_cities.next('.chosen-container').find('.chosen-search-input').prop('disabled', true);
					
					$.ajax({
						url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
						method: 'post',
						accept: 'application/json',
						data: {
							action : 'cfgp_load_regions',
							country_code : $country_code
						},
						cache: true
					}).done( function( data ) {
						var options = '',
							$regions_code = $select_regions.find('option:selected').map(function(_, e){return e.value}).get();
						
						for(key in data){
							options+='<option value="' + data[key].key + '">' + data[key].value + '</option>';
						}
						
						$select_regions.html(options);
						
						for (i in $regions_code) {
							$select_regions.find('option[value="'+$regions_code[i]+'"]').prop('selected', true);
						}
						
						$select_regions.next('.chosen-container').find('.chosen-search-input').prop('disabled', false);
						$select_regions.trigger("chosen:updated");
					}).fail(function(){
						$select_regions.next('.chosen-container').find('.chosen-search-input').prop('disabled', false);
					});
					
					$.ajax({
						url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
						method: 'post',
						accept: 'application/json',
						data: {
							action : 'cfgp_load_cities',
							country_code : $country_code
						},
						cache: true
					}).done( function( data ) {
						var options = '',
							$cities_code = $select_cities.find('option:selected').map(function(_, e){return e.value}).get();
							
						for(key in data){
							options+='<option value="' + data[key].key + '">' + data[key].value + '</option>';
						}
						
						$select_cities.html(options);
						
						for (i in $cities_code) {
							$select_cities.find('option[value="'+$cities_code[i]+'"]').prop('selected', true);
						}
						
						$select_cities.next('.chosen-container').find('.chosen-search-input').prop('disabled', false);
						$select_cities.trigger("chosen:updated");
					}).fail(function(){
						$select_cities.next('.chosen-container').find('.chosen-search-input').prop('disabled', false);
					});
					
				});
			});
		}
	})($('.cfgp-country-region-city-multiple-form'));
	
	
	$(document)
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
		
		console.log($index);
		
		var $html = $('<div/>').addClass('cfgp-row cfgp-repeater-item').html($template);
		$html.find('select option:selected').removeAttr('selected').prop('selected', false);
		$html.find('input[type="url"], input[type="text"]').val('');
		
		/* TODO - Reset chosen */
			
		$repeater.append($html);
	})
	// Remove SEO redirection
	.on('click', '.cfgp-remove-seo-redirection', function(e){
		e.preventDefault();
		var $this = $(this),
			$item = $this.closest('.cfgp-repeater-item');
		$item.remove();
	});
		
})(jQuery || window.jQuery || Zepto || window.Zepto);