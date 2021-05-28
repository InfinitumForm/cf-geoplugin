(function ($) {
	/**
	 * Fix admin panels
	**/
	$('.nav-tab-wrapper-chosen > .nav-tab-wrapper > a.nav-tab').on({
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
	
	/**
	 * Detect form changing, fix things and prevent lost data
	**/
	(function(f){
		if(f.length > 0)
		{
			var formChangeFlag = false;
			f.on('input change keyup', 'input, select, textarea', function(e){ 
				formChangeFlag = true;
			});
			
			f.on( 'change', function( e ) {
				if( $( '.enable-disable-proxy:checked' ).val() == 1 )
				{
					$('.proxy-disable').prop('disabled',false).removeClass('disabled');
				}
				else
				{
					$('.proxy-disable').prop('disabled',true).addClass('disabled');
				}

				if( $( '.enable-disable-gmap:checked' ).val() == 1 )
				{
					
					$('.nav-tab-wrapper > a[data-id="#google-map"]').show();
				}
				else
				{
					$('.nav-tab-wrapper > a[data-id="#google-map"]').hide();
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

	/**
	 * Display Thank You footer
	**/
	$('#footer-left').html('<div>'+CFGP.label.footer_menu.thank_you+' <a href="https://cfgeoplugin.com" target="_blank">CF Geo Plugin</a></div><div class="alignleft"><a href="https://cfgeoplugin.com/documentation" target="_blank">'+CFGP.label.footer_menu.documentation+'</a> | <a href="https://cfgeoplugin.com/faq" target="_blank">'+CFGP.label.footer_menu.faq+'</a> | <a href="https://cfgeoplugin.com/contact" target="_blank">'+CFGP.label.footer_menu.contact+'</a> | <a href="https://cfgeoplugin.com/blog" target="_blank">'+CFGP.label.footer_menu.blog+'</a></div>');
	$('#footer-upgrade').remove();
})(jQuery || window.jQuery || Zepto || window.Zepto);