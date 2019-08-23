(function($)
{
    /**
     * Exchange currencies
     */
    $( '.cfgp-exchange-currency' ).on( 'click', function( e ) {
        e.preventDefault();
        var $this = $( this );

        var fromVal = $( $this ).closest( 'form' ).find( 'select.cfgp-currency-from option:selected' ).val();
        var fromText = $( $this ).closest( 'form' ).find( 'select.cfgp-currency-from option:selected' ).text();

        var toVal = $( $this ).closest( 'form' ).find( 'select.cfgp-currency-to option:selected' ).val();
        var toText = $( $this ).closest( 'form' ).find( 'select.cfgp-currency-to option:selected' ).text();

        $( 'select.cfgp-currency-from option:selected' ).val( toVal );
        $( 'select.cfgp-currency-from option:selected' ).text( toText );

        $( 'select.cfgp-currency-to option:selected' ).val( fromVal );
        $( 'select.cfgp-currency-to option:selected' ).text( fromText );
    });

    /**
     * Ajax for conversion
     */
    $( 'form.cfgp-currency-form' ).on( 'submit', function( e ) {
        e.preventDefault();
        var $this = $( this );

        var formData = $this.serialize();

        $.ajax({
            'method': 'POST',
            'data': formData,
            'cache' : false,
            'url': CFGP_PUBLIC.ajax_url + '?action=cfgeo_full_currency_converter',
            beforeSend: function()
            {
                $( $this ).find( 'p.cfgp-currency-converted' ).html( '<div class="card w-100 text-white bg-secondary"><div class="card-body text-center"><img src="'+ CFGP_PUBLIC.loading_gif +'" /></div></div>' );
            } 
        }).done( function( d ) {
            $( $this ).find( 'p.cfgp-currency-converted' ).html( d );
        }).fail( function( jqXHR, error, textStatus ) {
            console.log( jqXHR );
            console.log( textStatus );
        });
    });
	
	/* Cache Handle */
	(function(element){
		var replace = $(element);
		if(replace.length)
		{
			$.ajax({
				'method': 'POST',
				'data': {
					'cfgeo_nonce' : CFGP_PUBLIC.cfgeo_nonce
				},
				'cache' : false,
				'url': CFGP_PUBLIC.ajax_url + '?action=cfgeo_cache', 
			}).done( function( d ) {
				if(d)
				{
					var data = JSON.parse(d);
					replace.each(function(){
						var $this = $(this),
							key = $this.attr('data-key');
							
						if(key)
						{
							$this.text( data[key] ).removeAttr('data-key');
							var parent = $this.parent(),
								parent_html = parent.html();
							if(parent_html)
							{
								parent.html(parent_html.replace(/<span class="cfgeo-replace">(.*?)<\/span>/gi,'$1'));
							}
						}
							
					});
				}
			}).fail( function( jqXHR, error, textStatus ) {
				console.log( jqXHR );
				console.log( textStatus );
			});
		}
	}('.cfgeo-replace'));

})(jQuery || window.jQuery || Zepto || window.Zepto);