/**
 * Gravity Forms integrations
 *
 * @since      8.4.2
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */
(function($){
	
	(function(cfgp_gfield){
		if(cfgp_gfield.length > 0) {
			var debounce;
			cfgp_gfield.find('.gfield_select_country').on('change paste select', function(e){
				
				var $country = $(this);
				
				if( debounce ) {
					clearTimeout(debounce);
				}
				
				debounce = setTimeout(function(){
					var $value = $country.val(),
						$container = $country.closest('.cfgp_gfield_group__autocomplete_location'),
						$region = $container.find('.gfield_select_region'),
						$city = $container.find('.gfield_select_city'),
						$transition = {
							country : $country.html(),
							region : $region.html(),
							city : $city.html()
						};
					
					$country.prop('disabled', true);
					$region.prop('disabled', true).html('<option value="" disabled selected>' + CFGP_GFORM.label.please_wait + '</option>');
					$city.prop('disabled', true).html('<option value="" disabled selected>' + CFGP_GFORM.label.please_wait + '</option>');
					
					$.post(CFGP_GFORM.ajaxurl, {
						action : 'cfgp_gfield_autocomplete_location',
						country_code : $value,
						nonce : CFGP_GFORM.nonce.cfgp_gfield_autocomplete_location
					}).done(function(response){
						$country.prop('disabled', false);
						
						if( response.success ) {
							
							let append_regions = [];
							for (key in response.data.regions){
								append_regions[key] = '<option value="' + response.data.regions[key] + '">' + response.data.regions[key] + '</option>';
							}
							$region.html(append_regions.join("\r\n")).prop('disabled', false);
							
							let append_cities = [];
							for (key in response.data.cities){
								append_cities[key] = '<option value="' + response.data.cities[key] + '">' + response.data.cities[key] + '</option>';
							}
							$city.html(append_cities.join("\r\n")).prop('disabled', false);
							
							append_regions = null;
							append_cities = null;
							$transition = null;
						} else {
							$country.html($transition.country);
							$region.html($transition.region).prop('disabled', false);
							$city.html($transition.city).prop('disabled', false);
							$transition = null;
						}
					}).fail(function(response){
						console.log(response);
						$country.html($transition.country).prop('disabled', false);
						$region.html($transition.region).prop('disabled', false);
						$city.html($transition.city).prop('disabled', false);
						$transition = null;
					});
					
				}, 100);
			});
		}
	}( $('.cfgp_gfield_group__autocomplete_location') ));
	
})(jQuery || window.jQuery || Zepto || window.Zepto);