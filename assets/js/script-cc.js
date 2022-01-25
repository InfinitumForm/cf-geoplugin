(function (jCFGP) {jCFGP(document).ready(function($){
	
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
					url : (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP_CC.ajaxurl) + '?action=cfgeo_full_currency_converter',
					beforeSend: function()
					{
						jCFGP( $this )
							.find( 'p.cfgp-currency-converted' )
								.html( '<div class="cfgp-card"><div class="cfgp-card-body"><img src="' + CFGP_CC.loading_gif + '" class="cfgp-loader" /></div></div>' );
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
	
});})(jQuery || window.jQuery || Zepto || window.Zepto);