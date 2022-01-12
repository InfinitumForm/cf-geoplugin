(function (jCFGP) {
jCFGP(document).ready(function($){
	
	/*
	 * Fix banner shortcode cache
	 */
	(function(banner){
		if(banner.length > 0)
		{
			banner.each(function(){
				var $this = jCFGP(this);
				jCFGP.ajax({
					type: "POST",
					dataType: 'html',
					url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
					data: {
						action : 'cf_geoplugin_banner_cache',
						id : $this.attr('data-id'),
						posts_per_page : $this.attr('data-posts_per_page'),
						class : $this.attr('data-class'),
						exact : $this.attr('data-exact'),
						default : $this.attr('data-default')
					},
					cache : true
				}).done(function(data){
					$this.html(data);
					$this.removeClass('cache').addClass('cached')
						.removeAttr('data-default')
							.removeAttr('data-posts_per_page')
								.removeAttr('data-class')
									.removeAttr('data-exact')
										.removeAttr('data-id');
				});
				
			});
		}
	}( jCFGP('.cf-geoplugin-banner.cache') ));
	
	
	/*
	 * Fix plugin shortcode cache
	 */
	(function(sc){
		if(sc.length > 0)
		{
			sc.each(function(){
				var $this = jCFGP(this);
				jCFGP.ajax({
					type: "POST",
					dataType: 'html',
					url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
					data: {
						action : 'cf_geoplugin_shortcode_cache',
						options : $this.attr('data-options'),
						shortcode : $this.attr('data-shortcode'),
						default : $this.attr('data-default')
					},
					cache : true
				}).done(function(data){
					if(data == 'false'){
						return;
					}
					$this.html(data);
					$this.removeClass('cache').addClass('cached')
						.removeAttr('data-default')
							.removeAttr('data-shortcode')
								.removeAttr('data-options');
				});
				
			});
		}
	}( jCFGP('.cf-geoplugin-shortcode.cache') ));
	
	/**
     * Exchange currencies
     */
	(function(sc){
		if(sc.length > 0)
		{
			sc.on( 'click', function( e ) {
				e.preventDefault();
				var $this = jCFGP( this );

				var fromVal = jCFGP( $this ).closest( 'form' ).find( 'select.cfgp-currency-from option:selected' ).val();
				var fromText = jCFGP( $this ).closest( 'form' ).find( 'select.cfgp-currency-from option:selected' ).text();

				var toVal = jCFGP( $this ).closest( 'form' ).find( 'select.cfgp-currency-to option:selected' ).val();
				var toText = jCFGP( $this ).closest( 'form' ).find( 'select.cfgp-currency-to option:selected' ).text();

				jCFGP( 'select.cfgp-currency-from option:selected' ).val( toVal ).text( toText );
				jCFGP( 'select.cfgp-currency-to option:selected' ).val( fromVal ).text( fromText );
			});
		}
	}( jCFGP( '.cfgp-exchange-currency' ) ));

    /**
     * Ajax for conversion
     */
	(function(sc){
		if(sc.length > 0)
		{
			sc.on( 'submit', function( e ) {
				e.preventDefault();
				var $this = jCFGP( this );

				var formData = $this.serialize();

				jCFGP.ajax({
					method : 'POST',
					dataType: 'html',
					data : formData,
					cache  : false,
					url : (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl) + '?action=cfgeo_full_currency_converter',
					beforeSend: function()
					{
						jCFGP( $this )
							.find( 'p.cfgp-currency-converted' )
								.html( '<div class="cfgp-card"><div class="cfgp-card-body"><img src="' + CFGP.loading_gif + '" class="cfgp-loader" /></div></div>' );
					} 
				}).done( function( d ) {
					jCFGP( $this ).find( 'p.cfgp-currency-converted' ).html( d );
				}).fail( function( jqXHR, error, textStatus ) {
					console.log( jqXHR );
					console.log( error );
					console.log( textStatus );
				});
			});
		}
	}( jCFGP( 'form.cfgp-currency-form' ) ));
	
});
})(jQuery || window.jQuery || Zepto || window.Zepto);