(function ($) {
	var debounce,
		loader = '<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> ' + CFGP.label.loading,
		/*
		 * Select2 Initialization
		 */
		select2_init = function select2_init(){
			var select2 = $('.cfgp_select2:not(.select2-hidden-accessible)');
			if( select2.length > 0 ) {
				
				// Set country
				$('[data-type^="country"].cfgp_select2:not(.select2-hidden-accessible)').select2({
					allowClear: true,
					language: {
						'inputTooShort': function () {
							return CFGP.label.select2.type_to_search;
						},
						'noResults': function(){
							return CFGP.label.select2.not_found['country'];
						},
						'searching': function() {
							return CFGP.label.select2.searching;
						}
					}
				});
				
				// Set postcode
				$('[data-type^="postcode"].cfgp_select2:not(.select2-hidden-accessible)').select2({
					allowClear: true,
					language: {
						'inputTooShort': function () {
							return CFGP.label.select2.type_to_search;
						},
						'noResults': function(){
							return CFGP.label.select2.not_found['postcode'];
						},
						'searching': function() {
							return CFGP.label.select2.searching;
						}
					}
				});
				
				// Set pharams after selection
				select2.on('select2:select', function (e) {
					var $this = $(this),
						$type = $this.attr('data-type'),
						$container = $this.closest('.cfgp-country-region-city-multiple-form');
					
					if( ['country','region','city'].indexOf($type) === -1 ) {
						return this;
					}
					
					if('country' === $type){
						if(Array.isArray($this.val())) {
							$container
								.find('[data-type^="region"].cfgp_select2,[data-type^="city"].cfgp_select2')
									.attr('data-country_codes', $this.val().join(','));
						} else {
							$container
								.find('[data-type^="region"].cfgp_select2,[data-type^="city"].cfgp_select2')
									.attr('data-country_codes', $this.val());
						}
					}
				});
				
				// Set region and city
				return select2.each(function(){
					var $this = $(this),
						$type = $this.attr('data-type');
						
					if( ['region','city'].indexOf($type) === -1 ) {
						return this;
					}
					
					$this.select2({
						minimumInputLength: 1,
						language: {
							'inputTooShort': function () {
								return CFGP.label.select2.type_to_search;
							},
							'noResults': function(){
								return CFGP.label.select2.not_found[$type];
							},
							'searching': function() {
								return CFGP.label.select2.searching;
							}
						},
						allowClear: true,
						ajax : {
							url : (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
							dataType: 'json',
							data : function (params) {
								var $select_this = $(this),
									$type = $select_this.attr('data-type'),
									$container = $select_this.closest('.cfgp-country-region-city-multiple-form'),
									$search = (params.term || ''),
									$country_codes = $select_this.attr('data-country_codes');
								
								if($country_codes) {
									$country_codes = $country_codes.split(',');
								}
								
								return {
									search : $search,
									type : $type,
									country_codes : $country_codes,
									exclude : [].filter((item) => item),
									action : 'cfgp_select2_locations',
									page: params.page || 1
								}
							}
						}
					});
				});
			}
		};
		
	select2_init();
	
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
			$target.trigger('change');
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
		
		// Set new indexes
		$template = $template.replace(/(\[0\])/gi, '[' + $index + ']');
		$template = $template.replace(/(-0-)/gi, '-' + $index + '-');
		$template = $template.replace(/(_0_)/gi, '_' + $index + '_');
		
		// Generate new HTML
		var $html = $('<div/>').addClass('cfgp-row cfgp-repeater-item cfgp-country-region-city-multiple-form').html($template);
		
		// Clean data
		$html.find('select').each(function(){
			$(this).find('option:selected').removeAttr('selected').prop('selected', false);
		});
		$html.find('input[type="url"], input[type="text"]').val('');
		$html.find('input[type="checkbox"]').prop('checked', false);
		
		// Remove select2
		$html.find('.cfgp_select2').select2().select2('destroy');
		$html.find('.select2').remove();
		
		// Assign new values
		$html.find('select#cfgp-seo-redirection-' + $index + '-http_code').val(302);
		$html.find('select#cfgp-seo-redirection-' + $index + '-country').removeClass('select2-hidden-accessible').attr('data-country_codes','');
		$html.find('select#cfgp-seo-redirection-' + $index + '-region').removeClass('select2-hidden-accessible').attr('data-country_codes','').html('');
		$html.find('select#cfgp-seo-redirection-' + $index + '-city').removeClass('select2-hidden-accessible').attr('data-country_codes','').html('');
		
		// Append and load select2
		$repeater.append($html).promise().done(function(){
			select2_init();
		});
	})
	
	// Remove SEO redirection
	.on('click', '.cfgp-remove-seo-redirection', function(e){
		e.preventDefault();
		var $this = $(this),
			$item = $this.closest('.cfgp-repeater-item');
		$item.remove();
	})
	
	// Enable GEO Tags
	.on('change', '#cfgp-geo-tag-container input[name^="cfgp-geotag-enable"]', function(e){
		if( $(this).is(':checked') ) {
			$('#cfgp-geo-tag-map-container').show(1, function(){
				$(this).find('input').prop('disabled', false);
			});
		} else  {
			$('#cfgp-geo-tag-map-container').hide(1, function(){
				$(this).find('input').prop('disabled', true);
			});
		}
	});
		
})(jQuery || window.jQuery || Zepto || window.Zepto);