(function ($) {
	/**
	 * Fix admin panels
	**/
	(function(tab_container){
		if(tab_container.length > 0)
		{
			tab_container.each(function(){
				var container = $(this);				
				$('.nav-tab-wrapper > a.nav-tab', container).on({
					'click' : function(e){
						e.preventDefault();
						var $this = $(this),
							$id = $this.attr('data-id');
						
						$('.cfgp-tab-panel', container).removeClass('cfgp-tab-panel-active');
						$('.nav-tab-wrapper > a.nav-tab', container).removeClass('nav-tab-active');
						
						$($id).addClass('cfgp-tab-panel-active');
						$this.addClass('nav-tab-active').blur();
					}
				});
			});
		}
	}($('.nav-tab-wrapper-chosen')));
	
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
				//	$('#toplevel_page_cf-geoplugin a[href^="admin.php?page=cf-geoplugin-google-map"]').parent().show();
				}
				else
				{
					$('.nav-tab-wrapper > a[data-id="#google-map"]').hide();
				//	$('#toplevel_page_cf-geoplugin a[href^="admin.php?page=cf-geoplugin-google-map"]').parent().hide();
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
	}($('#cf-geoplugin-settings form')));

	/**
	 * Display Thank You footer
	**/
	$('#footer-left').html('<div>'+CFGP.label.footer_menu.thank_you+' <a href="https://cfgeoplugin.com" target="_blank">CF Geo Plugin</a></div><div class="alignleft"><a href="https://cfgeoplugin.com/documentation" target="_blank">'+CFGP.label.footer_menu.documentation+'</a> | <a href="https://cfgeoplugin.com/faq" target="_blank">'+CFGP.label.footer_menu.faq+'</a> | <a href="https://cfgeoplugin.com/contact" target="_blank">'+CFGP.label.footer_menu.contact+'</a> | <a href="https://cfgeoplugin.com/blog" target="_blank">'+CFGP.label.footer_menu.blog+'</a></div>');
	$('#footer-upgrade').remove();
})(jQuery || window.jQuery || Zepto || window.Zepto);