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

})(jQuery || window.jQuery || Zepto || window.Zepto);