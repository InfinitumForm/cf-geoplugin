(function ($) {
	var custom_uploader,
		custom_uploader_timeout,
		debounce,
		loader = '<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> ' + CFGP.label.loading,
		/*
		 * Menus control
		 * @since 8.0.6
		*/
		menus_checkbox_control = function menus_checkbox_control(){
			var checkbox = $('.cfgp-menu-item-enable-restriction input[type^="checkbox"]');
			if(checkbox.length) {
				checkbox.on('change', function(){
					if(this.checked) {
						$( '.cfgp-menu-item-restriction-locations', $(this).closest('.cfgp-menu-item-restriction') ).show();
					} else {
						$( '.cfgp-menu-item-restriction-locations', $(this).closest('.cfgp-menu-item-restriction') ).hide();
					}
				}).each(function(){
					if(this.checked) {
						$( '.cfgp-menu-item-restriction-locations', $(this).closest('.cfgp-menu-item-restriction') ).show();
					} else {
						$( '.cfgp-menu-item-restriction-locations', $(this).closest('.cfgp-menu-item-restriction') ).hide();
					}
				});
			}
		},
		/*
		 * Select2 Initialization
		 */
		select2_init = function select2_init(){
			var select2 = $('.cfgp_select2:not(.select2-hidden-accessible)');
			if( select2.length > 0 ) {
				
				// Set country
				$('[data-type^="country"].cfgp_select2:not(.select2-hidden-accessible)').select2({
					allowClear: false,
					language: {
						'inputTooShort': function () {
							return CFGP.label.select2.type_to_search;
						},
						'noResults': function(){
							return CFGP.label.select2.not_found['country'];
						},
						'searching': function() {
							return CFGP.label.select2.searching;
						},
						'removeItem': function() {
							return CFGP.label.select2.removeItem;
						},
						'removeAllItems': function() {
							return CFGP.label.select2.removeAllItems;
						},
						'loadingMore': function() {
							return CFGP.label.select2.loadingMore;
						}
					}
				});
				
				// Set postcode
				$('[data-type^="postcode"].cfgp_select2:not(.select2-hidden-accessible)').select2({
					allowClear: false,
					language: {
						'inputTooShort': function () {
							return CFGP.label.select2.type_to_search;
						},
						'noResults': function(){
							return CFGP.label.select2.not_found['postcode'];
						},
						'searching': function() {
							return CFGP.label.select2.searching;
						},
						'removeItem': function() {
							return CFGP.label.select2.removeItem;
						},
						'removeAllItems': function() {
							return CFGP.label.select2.removeAllItems;
						},
						'loadingMore': function() {
							return CFGP.label.select2.loadingMore;
						}
					}
				});
				
				// Set pharams after selection
				select2.on('select2:select', function (e) {
					var $this = $(this),
						$type = $this.attr('data-type'),
						$container = $this.closest('.cfgp-country-region-city-multiple-form'),
						$value = '';
					
					if( ['country','region','city'].indexOf($type) === -1 ) {
						return this;
					}
					
					if('country' === $type){
						$value = $this.val();
						
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
				
				// Set region and city
				return select2.each(function(){
					var $this = $(this),
						$type = $this.attr('data-type');
						
					if( ['region','city'].indexOf($type) === -1 ) {
						return this;
					}
					
					$this.select2({
						minimumInputLength: 0,
						language: {
							'inputTooShort': function () {
								return CFGP.label.select2.type_to_search;
							},
							'noResults': function(){
								return CFGP.label.select2.not_found[$type];
							},
							'searching': function() {
								return CFGP.label.select2.searching;
							},
							'removeItem': function() {
								return CFGP.label.select2.removeItem;
							},
							'removeAllItems': function() {
								return CFGP.label.select2.removeAllItems;
							},
							'loadingMore': function() {
								return CFGP.label.select2.loadingMore;
							}
						},
						allowClear: false,
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
	
	/**
	 * Fix admin panels
	**/
	(function(nav_tabs){
		if( nav_tabs.length > 0 ) {
			nav_tabs.on({
				'click' : function(e){
					e.preventDefault();
					var $this = $(this),
						$id = $this.attr('data-id'),
						$href = $this.attr('href'),
						$container = $this.closest('.nav-tab-wrapper-chosen');
					
					if(/https?/.test($href)){
						window.open($href);
						return;
					}
					
					$container.find('.cfgp-tab-panel').removeClass('cfgp-tab-panel-active');
					$container.find('.nav-tab-wrapper > a.nav-tab').removeClass('nav-tab-active');
					
					$container.find($id).addClass('cfgp-tab-panel-active').focus();
					$this.addClass('nav-tab-active').blur();
					
					if($container.find($id + ' .nav-tab-wrapper-chosen').length > 0) {
						$container.find($id + ' .nav-tab-wrapper-chosen .nav-tab-wrapper > a.nav-tab:first-child').trigger('click');
					}
					
				}
			});
		}
	}( $('.nav-tab-wrapper-chosen > .nav-tab-wrapper > a.nav-tab') ));
	
	/**
	 * Detect form changing, fix things and prevent lost data
	**/
	(function(f){		
		if(f.length > 0)
		{
			var formChangeFlag = false;
			
			$(document).ready(function(){
				if( $( '.enable-disable-proxy:checked' ).val() == 1 ) {
					$('.proxy-disable').prop('disabled',false).removeClass('disabled');
				} else {
					$('.proxy-disable').prop('disabled',true).addClass('disabled');
				}

				if( $( '.enable-disable-gmap:checked' ).val() == 1 ) {
					$('.nav-tab-wrapper > a[data-id="#google-map"]').show();
				} else {
					$('.nav-tab-wrapper > a[data-id="#google-map"]').hide();
				}
				
				if( $( '.enable-disable-rest:checked' ).val() == 1 ) {
					$('.nav-tab-wrapper > a[data-id="#rest-api"]').show();
				} else {
					$('.nav-tab-wrapper > a[data-id="#rest-api"]').hide();
				}
			});
			
			f.on('input change keyup', 'input, select, textarea', function(e){
				formChangeFlag = true;
			});
			
			f.on( 'change', function( e ) {
				if( $( '.enable-disable-proxy:checked' ).val() == 1 ) {
					$('.proxy-disable').prop('disabled',false).removeClass('disabled');
				} else {
					$('.proxy-disable').prop('disabled',true).addClass('disabled');
				}

				if( $( '.enable-disable-gmap:checked' ).val() == 1 ) {
					$('.nav-tab-wrapper > a[data-id="#google-map"]').show();
				} else {
					$('.nav-tab-wrapper > a[data-id="#google-map"]').hide();
				}
				
				if( $( '.enable-disable-rest:checked' ).val() == 1 ) {
					$('.nav-tab-wrapper > a[data-id="#rest-api"]').show();
				} else {
					$('.nav-tab-wrapper > a[data-id="#rest-api"]').hide();
				}
			});
			
			f.on( 'click', '[name="submit"]', function( e ) {
				formChangeFlag = false;
			});
			
			$( window ).on('unload beforeunload', function() {
				if(formChangeFlag === true){
					return CFGP.label.unload;
				}
			});
		}
	}($('#cf-geoplugin-settings form, #cf-geoplugin-defender form')));
	
	
	/*
	 * Initialization for the menus
	 * @since 8.0.2
	*/
	(function( menu ){
		menus_checkbox_control();
		if(menu.length > 0)
		{
			var html = menu.html();
			setInterval(function(){
				var new_html = $('#menu-to-edit').html();
				if( new_html != html ) {
					select2_init()
					menus_checkbox_control();
					html = new_html;
				}
			}, 500);
		}
		
	}( $('#menu-to-edit') ));
	
	// Generate Secret Key
	(function($$) {
		if( !!$($$) )
		{
			$($$).on( 'click touchstart', function (e) {
				e.preventDefault();
				var $this = $(this), $nonce = $this.attr('data-nonce'), $confirm = $this.attr('data-confirm'), container = $('#cf-geoplugin-secret-key');
				
				if($confirm){
					if(!confirm($confirm)){
						return;
					}
				}
				
				container.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i><span class="sr-only">Loading...</span>');
				$this.prop('disabled', true).addClass('disabled');
				$.ajax({
					url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
					method: 'post',
					dataType: 'text',
					data: {
						action : 'cfgp_rest_generate_secret_key',
						nonce : $nonce
					},
					cache: false
				}).done( function( data ) {
					container.html(data);
					$this.prop('disabled', false).removeClass('disabled');
				}).fail(function(a,b,c){
					container.html(c);
					console.log((typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl), {
						action : 'cfgp_generate_secret_key',
						nonce : $nonce
					})
					$this.prop('disabled', false).removeClass('disabled');
				});
			});
		}
	})('#cf-geoplugin-generate-secret-key');
	
	/* Delete REST Access token */
	$(document).on('click', '.cfgp-button-token-remove', function( e ){
		e.preventDefault();
		
		var $this = $(this),
			$remove = $this.attr('data-remove'),
			$confirm = confirm($this.attr('data-confirm')),
			$nonce = $this.attr('data-nonce');
		
		if($confirm)
		{
			$.ajax({
				url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
				method: 'post',
				dataType: 'text',
				data: {
					action : 'cfgp_rest_delete_access_token',
					token_id : $this.attr('data-id'),
					nonce : $nonce
				},
				cache: false
			}).done( function( data ) {
				$( $remove ).remove();
			}).fail(function(a,b,c){
				console.log(c);
				console.log((typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl), {
					action : 'cfgp_rest_delete_access_token',
					token_id : $this.attr('data-id'),
					nonce : $nonce
				})
			});
		}
	});
	
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
			$target.trigger('change');
		});
	});
	
	/* Prevent double submit */
	$('form').submit(function(){
		 $(this).find('[type="submit"]').prop('disabled',true);
	});
	
	/*
	 * Select CSV file
	 */
	$(document).on('click', '.button-cfgeo-seo-import-csv', function(e) {
		e.preventDefault();
		var $this = $(this),
			$confirm = $this.attr('data-confirm'),
			$label = $this.attr('data-label'),
			$nonce = $this.attr('data-nonce'),
			$callback = $this.attr('data-callback');

		$this.html(loader).prop('disabled', true);
		
		if(!confirm($confirm)) {
			$this.html($label).prop('disabled', false);
			return;
		}

		//If the uploader object has already been created, reopen the dialog
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		//Extend the wp.media object
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: CFGP.label.upload_csv,
			button: {
				text: CFGP.label.upload_csv
			},
			multiple: false,
			library: {
				type : 'text/csv'
			}
		});

		var upload_csv = function(){
			
			$this.html($label).prop('disabled', false);

			if(custom_uploader_timeout) clearTimeout(custom_uploader_timeout);

			attachment = custom_uploader.state().get('selection').first().toJSON();
			
			if(!attachment) return;
			
			$this.val(attachment.url).attr('data-id', attachment.id);
		
			/* TO DO - AJAX UPLOAD */
			$this.html(loader).prop('disabled', true);
			$.ajax({
				url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
				method: 'post',
				accept: 'application/json',
				data: {
					action : 'cfgp_seo_redirection_csv_upload',
					attachment_id : attachment.id,
					attachment_url : attachment.url,
					nonce : $nonce
				},
				cache: false
			}).done( function( data ) {
				$this.html($label).prop('disabled', false);
				if(data.return == true)
				{
					if(!(window.location.href = $callback)){
						window.location.replace($callback);
					}
				}
				else
				{
					alert(data.message);
				}
				return;
			}).fail(function(a,b,c){
				console.log(a,b,c);
				alert(c);
				$this.html($label).prop('disabled', false);
			});
			

			custom_uploader_timeout = setTimeout(function(){
				custom_uploader = null;
			}, 5);
		};
		
		//When a file is selected, grab the URL and set it as the text field's value
		custom_uploader.on({
			'select': upload_csv,
			'close': function(){$this.html($label).prop('disabled', false);}
		});
		//Open the uploader dialog
		custom_uploader.open();
	});
	
	/*
	 * Set RSS Feed
	 */
	(function($feed){
		if( $feed.length > 0 )
		{
			$.ajax({
				url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
				method: 'post',
				accept: 'text/html',
				data: {
					action : 'cfgp_rss_feed'
				},
				cache: true
			}).done( function( data ) {
				$feed.html(data).removeClass('cfgp-load-rss-feed');
			});
		}
	}( $('.cfgp-load-rss-feed') ));	
	
	/*
	 * Select country, region, city
	 */
	(function($form){
		if($form.length > 0)
		{
			$($form).each(function(){
				var $container = $(this),
					$select_countries = $container.find('select.cfgp-select-country'),
					$select_regions = $container.find('select.cfgp-select-region'),
					$select_cities = $container.find('select.cfgp-select-city');
		
				$select_countries.on('change', function(){
					var $country_code = $(this).find('option:selected').attr('value');
					
					$select_regions.html('<option>' + CFGP.label.loading + '</option>').prop('disabled', true).trigger("change");
					$select_cities.html('<option>' + CFGP.label.loading + '</option>').prop('disabled', true).trigger("change");
					
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
						var options = '<option>-</option>';
						for(key in data){
							options+='<option value="' + data[key].key + '">' + data[key].value + '</option>';
						}
						$select_regions.html(options).prop('disabled', false).trigger("change");
					}).fail(function(){
						$select_regions.html('<option>-</option>').prop('disabled', false).trigger("change");
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
						var options = '<option>-</option>', i;
						for(key in data){
							options+='<option value="' + data[key].key + '">' + data[key].value + '</option>';
						}
						$select_cities.html(options).prop('disabled', false).trigger("change");
					}).fail(function(){
						$select_cities.html('<option>-</option>').prop('disabled', false).trigger("change");
					});
					
				});
			});
		}
	})($('.cfgp-country-region-city-form'));
	
	/*
	 * SEO Redirection bulk actions
	 */
	var bulk_action_seo_redirection;
	$('#cf-geoplugin-seo-redirection #bulk-action-selector-top').on('change select', function(){
		bulk_action_seo_redirection = $(this).val();
	});
	$('#cf-geoplugin-seo-redirection #doaction').attr('type', 'button').on('click', function(e){
		console.log(e);
		if( bulk_action_seo_redirection ){
			if(bulk_action_seo_redirection === 'delete') {
				if( $('#cf-geoplugin-seo-redirection #seo-redirection-table-form input[name="seo_redirection[]"]').is(':checked') ) {
					if( confirm(CFGP.label.seo_redirection.bulk_delete) ){
						$('#cf-geoplugin-seo-redirection #seo-redirection-table-form').submit();
					}
				} else {
					alert(CFGP.label.seo_redirection.not_selected);
				}
			} else {
				if( $('#cf-geoplugin-seo-redirection #seo-redirection-table-form input[name="seo_redirection[]"]').is(':checked') ) {
					$('#cf-geoplugin-seo-redirection #seo-redirection-table-form').submit();
				} else {
					alert(CFGP.label.seo_redirection.not_selected);
				}
			}
		}
	});

	/**
	 * Display Thank You footer
	**/
	$('#footer-left').html('<div>'+CFGP.label.footer_menu.thank_you+' <a href="https://cfgeoplugin.com" target="_blank">CF Geo Plugin</a></div><div class="alignleft"><a href="https://cfgeoplugin.com/documentation" target="_blank">'+CFGP.label.footer_menu.documentation+'</a> | <a href="https://cfgeoplugin.com/faq" target="_blank">'+CFGP.label.footer_menu.faq+'</a> | <a href="https://cfgeoplugin.com/contact" target="_blank">'+CFGP.label.footer_menu.contact+'</a> | <a href="https://cfgeoplugin.com/blog" target="_blank">'+CFGP.label.footer_menu.blog+'</a></div>');
	$('#footer-upgrade').remove();	
})(jQuery || window.jQuery || Zepto || window.Zepto);