(function ($) {
	var custom_uploader,
		custom_uploader_timeout,
		debounce,
		loader = '<i class="cfa cfa-circle-o-notch cfa-spin cfa-fw"></i> ' + CFGP.label.loading,
		/*
		 * Menus control
		 * @since 8.0.6
		*/
		menus_checkbox_control = function menus_checkbox_control() {
			var checkboxes = $('.cfgp-menu-item-enable-restriction input[type^="checkbox"]');

			checkboxes.each(function() {
				var relatedLocation = $(this).closest('.cfgp-menu-item-restriction').find('.cfgp-menu-item-restriction-locations');
				relatedLocation.toggle(this.checked);
			});

			checkboxes.on('change', function() {
				var relatedLocation = $(this).closest('.cfgp-menu-item-restriction').find('.cfgp-menu-item-restriction-locations');
				relatedLocation.toggle(this.checked);
			});
		},
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
	
	
	$(document).ready(function(){
		$('#cf-geoplugin-settings #cfgp-base_currency').select2({
			allowClear: false
		});
	});
	
	/**
	 * Detect form changing, fix things and prevent lost data
	**/
	(function(f){		
		if(f.length > 0)
		{
			var formChangeFlag = false;
			
			$(document).ready(function(){
				let disable_proxy = $('.enable-disable-proxy:checked').val() == 1;
				$('.proxy-disable').prop('disabled', !disable_proxy).toggleClass('disabled', !disable_proxy);

				$('.nav-tab-wrapper > a[data-id="#google-map"]').toggle( $( '.enable-disable-gmap:checked' ).val() == 1 );
				
				$('.nav-tab-wrapper > a[data-id="#rest-api"]').toggle( $( '.enable-disable-rest:checked' ).val() == 1 );
			});
			
			f.on('input change keyup', 'input, select, textarea', function(e){
				formChangeFlag = true;
			});
			
			f.on( 'change', function( e ) {
				let disable_proxy = $('.enable-disable-proxy:checked').val() == 1;
				$('.proxy-disable').prop('disabled', !disable_proxy).toggleClass('disabled', !disable_proxy);

				$('.nav-tab-wrapper > a[data-id="#google-map"]').toggle( $( '.enable-disable-gmap:checked' ).val() == 1 );
				
				$('.nav-tab-wrapper > a[data-id="#rest-api"]').toggle( $( '.enable-disable-rest:checked' ).val() == 1 );
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
				
				container.html('<i class="cfa cfa-circle-o-notch cfa-spin cfa-fw"></i><span class="sr-only">Loading...</span>');
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
			$target = $( '#' + $this.attr('data-target') ),
			$type = $target.attr('data-type'),
			$container = $target.closest('.cfgp-country-region-city-multiple-form');
			
		$target.find('option').each(function(){
			var $option = $(this);
			$(this).prop('selected',!$option.is(':selected'));
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
	 * Add Geolocate Menus 
	 */
	$(document).on('click', '#cfgp-menu-add-location', function(e){
		e.preventDefault();
		var $this = $(this),
			$table = $this.closest('table'),
			$location = $table.find('#cfgp-menu-locations-select'),
			$location_val = $location.val(),
			$country = $table.find('#cfgp-menu-country-select'),
			$country_val = $country.val(),
			$continue = true;

		$location.css({
			border : ''
		});

		$country.next('.select2.select2-container').css({
			border : ''
		});

		if($location_val == '') {
			$location.css({
				border : '1px solid #cc0000'
			});
			$continue = false;
		}

		if($country_val == '') {
			$country.next('.select2.select2-container').css({
				border : '1px solid #cc0000'
			});
			$continue = false;
		}

		if( $('#cfgp-menu-location-item-' + $country_val + '-' + $location_val, $table).length > 0 ) {
			$country.next('.select2.select2-container').css({
				border : '1px solid #cc0000'
			});
			$continue = false;
		}

		if($continue) {
			$.ajax({
				url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
				method: 'post',
				accept: 'text/html',
				data: {
					country : $country_val,
					location : $location_val,
					action : 'cfgp_geolocate_menu'
				}
			}).done( function( data ) {
				$table.find('#cfgp-menu-locations').html(data);
				$location.val('').trigger('change');
				$country.val('').trigger('change');
			});
		}
	});
	
	/*
	 * Remove Geolocate Menus 
	 */
	$(document).on('click', '.cfgp-menu-remove-location', function(e){
		e.preventDefault();
		var $this = $(this),
			$table = $this.closest('table'),
			$id = $this.attr('data-id'),
			$continue = confirm($this.attr('data-confirm'));
		if( $continue ) {
			$.ajax({
				url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
				method: 'post',
				accept: 'text/html',
				data: {
					term_id : $id,
					action : 'cfgp_geolocate_remove_menu'
				}
			}).done( function( data ) {
				$table.find('#cfgp-menu-locations').html(data);
			});
		}
	});
	
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
	
	// Let's do Affiliate
	var cfgp_affilliates = null;
	(function(affiliate){
		if(affiliate.length > 0){
			
			if(cfgp_affilliates) {
				if(cfgp_affilliates) {
					if(typeof cfgp_affilliates.nord_vpn != 'undefined' && cfgp_affilliates.nord_vpn.length > 0) {
						affiliate.attr('href', cfgp_affilliates.nord_vpn[ Math.floor(Math.random()*cfgp_affilliates.nord_vpn.length) ]);
					}
				}
			} else {			
				$.getJSON('https://wpgeocontroller.com/affiliate.json', function(json){
					cfgp_affilliates = json;
					if(cfgp_affilliates) {
						if(typeof cfgp_affilliates.nord_vpn != 'undefined' && cfgp_affilliates.nord_vpn.length > 0) {
							affiliate.attr('href', cfgp_affilliates.nord_vpn[ Math.floor(Math.random()*cfgp_affilliates.nord_vpn.length) ]);
						}
					}
				});
			}
		}
	}( $('.affiliate-nordvpn') ));

	/**
	 * Display Thank You footer
	**/
	$('#footer-left').html('<div>'+CFGP.label.footer_menu.thank_you+' <a href="https://wpgeocontroller.com" target="_blank">Geo Controller</a></div><div class="alignleft"><a href="https://wpgeocontroller.com/documentation" target="_blank">'+CFGP.label.footer_menu.documentation+'</a> | <a href="https://wpgeocontroller.com/faq" target="_blank">'+CFGP.label.footer_menu.faq+'</a> | <a href="https://wpgeocontroller.com/contact" target="_blank">'+CFGP.label.footer_menu.contact+'</a> | <a href="https://wpgeocontroller.com/blog" target="_blank">'+CFGP.label.footer_menu.blog+'</a></div>');
	$('#footer-upgrade').remove();	
})(jQuery || window.jQuery || Zepto || window.Zepto);