(function ($) {
	var debounce,
		loader = '<i class="cfa cfa-circle-o-notch cfa-spin cfa-fw"></i> ' + CFGP.label.loading,
		/*
		 * Select2 Initialization
		 */
		select2_init = function select2_init(){
			var select2 = $('.cfgp_select2:not(.select2-hidden-accessible)');
			if( select2.length > 0 ) {
				
				var CFGP_LABEL = CFGP.label.select2;
				
				// Set country
				$('[data-type^="country"].cfgp_select2:not(.select2-hidden-accessible)').select2({
					allowClear: false,
					language: {
						'inputTooShort': function () {
							return CFGP_LABEL.type_to_search['country'];
						},
						'noResults': function(){
							return CFGP_LABEL.not_found['country'];
						},
						'searching': function() {
							return CFGP_LABEL.searching;
						},
						'removeItem': function() {
							return CFGP_LABEL.removeItem;
						},
						'removeAllItems': function() {
							return CFGP_LABEL.removeAllItems;
						},
						'loadingMore': function() {
							return CFGP_LABEL.loadingMore;
						}
					}
				});
				
				// Set pharams after selection
				select2.on('select2:select', function (e) {
					var $this = $(this),
						$type = $this.attr('data-type'),
						$container = $this.closest('.cfgp-country-region-city-multiple-form'),
						$value = '';
					
					if( ['country','region','city','postcode'].indexOf($type) === -1 ) {
						return this;
					}
					
					if('country' === $type){
						$value = $this.val();
						
						if( Array.isArray($value) ) {
							$value = $value.join(',');
						}
						
						$container
							.find('[data-type^="region"].cfgp_select2,[data-type^="city"].cfgp_select2,[data-type^="postcode"].cfgp_select2')
								.attr('data-country_codes', $value)
									.each(function(){
										$(this).find('option:selected').removeAttr('selected').prop('selected', false);
									}).trigger('change');
					}
				});
				
				// Set region and city
				return select2.each(function(){
					var $this = $(this),
						$type = $this.attr('data-type');
						
					if( ['region','city','postcode'].indexOf($type) === -1 ) {
						return this;
					}
					
					$this.select2({
						minimumInputLength: 1,
						language: {
							'inputTooShort': function () {
								return CFGP_LABEL.type_to_search[$type];
							},
							'noResults': function(){
								return CFGP_LABEL.not_found[$type];
							},
							'searching': function() {
								return CFGP_LABEL.searching;
							},
							'removeItem': function() {
								return CFGP_LABEL.removeItem;
							},
							'removeAllItems': function() {
								return CFGP_LABEL.removeAllItems;
							},
							'loadingMore': function() {
								return CFGP_LABEL.loadingMore;
							}
						},
						allowClear: false,
						ajax : {
							url : (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
							dataType: 'json',
							delay: 250,
							cache: true,
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
						},
						escapeMarkup: function(markup) {
							return markup;
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
			$target = $( '#' + $this.attr('data-target') ),
			$type = $target.attr('data-type'),
			$container = $target.closest('.cfgp-country-region-city-multiple-form');
			
		$target.find('option').each(function(){
			var $option = $(this);
			if($option.is(':selected')) {
				$(this).prop('selected',false);
			} else {
				$(this).prop('selected',true);
			}
		}).promise().done(function(){
			$target.trigger('change');
			
			if( $type === 'country' ) {
				$value = $target.val();
						
				if( Array.isArray($value) ) {
					$value = $value.join(',');
				}
				
				$container
					.find('[data-type^="region"].cfgp_select2,[data-type^="city"].cfgp_select2')
						.attr('data-country_codes', $value)
							.each(function(){
								$(this).find('option:selected').removeAttr('selected').prop('selected', false);
							}).trigger('change');
				$container
					.find('[data-type^="postcode"].cfgp_select2')
						.each(function(){
							$(this).find('option:selected').removeAttr('selected').prop('selected', false);
						}).trigger('change');
			}
		});
	})
	
	// Add SEO redirection
	.on('click', '.cfgp-add-seo-redirection', function(e){
		e.preventDefault();
		var $this = $(this),
			$current_item = $this.closest('.cfgp-repeater-item'),
			$repeater  = $current_item.closest('.cfgp-repeater'),
			$items = $('.cfgp-repeater-item', $repeater)
			$template = $($items[0]).html(),
			$index = $items.length;
		
		// Set new indexes
		$template = $template.replace(/(\[0\])/gi, '[' + $index + ']');
		$template = $template.replace(/(-0-)/gi, '-' + $index + '-');
		$template = $template.replace(/(_0_)/gi, '_' + $index + '_');
		
		// Generate new HTML
		var $html = $('<div/>').addClass('cfgp-row cfgp-repeater-item cfgp-country-region-city-multiple-form').attr('data-id', $index).html($template);
		
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
			$items = $this.closest('.cfgp-repeater').find('.cfgp-repeater-item'),
			$current_item = $this.closest('.cfgp-repeater-item'),
			$index = $current_item.attr('data-id');
		
		if($items.length > 1) {
			$current_item.remove();
		} else {
			// Clean data
			$current_item.find('select').each(function(){
				$(this).find('option:selected').removeAttr('selected').prop('selected', false);
			});
			$current_item.find('input[type="url"], input[type="text"]').val('');
			$current_item.find('input[type="checkbox"]').prop('checked', false);
			
			// Remove select2
			$current_item.find('.cfgp_select2').select2().select2('destroy');
			$current_item.find('.select2').remove();
			
			// Assign new values
			$current_item.find('select#cfgp-seo-redirection-' + $index + '-http_code').val(302);
			$current_item.find('select#cfgp-seo-redirection-' + $index + '-country').removeClass('select2-hidden-accessible').attr('data-country_codes','');
			$current_item.find('select#cfgp-seo-redirection-' + $index + '-region').removeClass('select2-hidden-accessible').attr('data-country_codes','').html('');
			$current_item.find('select#cfgp-seo-redirection-' + $index + '-city').removeClass('select2-hidden-accessible').attr('data-country_codes','').html('');
			
			// Append and load select2
			select2_init();
		}
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