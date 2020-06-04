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
            method : 'POST',
            data : formData,
            cache  : false,
            url : CFGP_PUBLIC.ajax_url + '?action=cfgeo_full_currency_converter',
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
	
	/* Cache Support for the geo tags */
	var xhrs = [];
	(function(element){
		var replace = $(element),
			timeout,
			clean_this = function(){
				var e = $(element);
				if(e.length > 0)
				{
					var $this = e,
						parent = $this.parent(),
						parent_html = parent.html();
					if(parent_html)
					{
						var regex = /<span class="cfgeo-replace">([0-9a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF\,\;\s\t\n\r\"\'\<\>\:\/\\\#\$\%\&\(\)\€\@\ł\|]+)<\/span>/gi;
						parent.html(parent_html.replace(regex,'$1')).promise().done(clean_this);
						//timeout = setTimeout(clean_this,10);
					}
				} else {
					//if(timeout) clearTimeout(timeout);
				}
			};
		if(replace.length > 0)
		{
			var nonce = $(replace.get(0)).attr('data-nonce');
			
			var xhr = $.ajax({
				method : 'POST',
				data: {
					'cfgeo_nonce' : nonce
				},
				cache : false,
				async : false,
				url: CFGP_PUBLIC.ajax_url + '?action=cfgeo_cache', 
			});
			
			xhrs.push(xhr);
			
			xhr.done( function( d ) {
				if(d)
				{
					var data = JSON.parse(d);
					replace.each(function(i, e){
						var $this = $(e),
							key = $this.attr('data-key');

						if(key)
						{
							$this.html( data[key] ).removeAttr('data-key').removeAttr('data-nonce');
						}
					}).promise().done(function(){
						
					});
				}
			}).fail( function( jqXHR, error, textStatus ) {
				console.log( jqXHR );
				console.log( textStatus );
			});
		}
	}('.cfgeo-replace'));
	
	
	/* Cache support for the Geo Banner */
	(function(element){
		var replace = $(element),
			timeout;
		if(replace.length > 0)
		{
			var nonce = $(replace.get(0)).attr('data-nonce');
			
			$.each(replace, function (i, e)  {
				var $this = $(e),
					xhr = $.ajax({
						method : 'POST',
						data: {
							'cfgeo_nonce' : nonce,
							'post_id' : $this.attr('data-id'),
							'post_posts_per_page' : $this.attr('data-posts_per_page'),
							'post_class' : $this.attr('data-class'),
							'post_html'	: $this.html()
						},
						cache : false,
						async : true,
						url: CFGP_PUBLIC.ajax_url + '?action=cfgeo_banner_cache', 
					});
					
				xhrs.push(xhr);
				
				xhr.done(function(data){
					if(data)
					{
						var el = $(data);
						
						if(el.is('#cf-geoplugin-banner-' + $this.attr('data-id')))
						{			
							$this.html( el.html() );
						}
						else
						{
							$this.html( data );
						}
					}
					else
					{
						$this.html( '' );
					}
					$this.addClass('cached');
				});
			});
		}
	}('.cf-geoplugin-banner-cached'));

	/* Detect cache done */
	$.when.apply($, xhrs).done(function(){
		console.info('Fragment cache cleaned for the WordPress Geo Plugin');
	});

})(jQuery || window.jQuery || Zepto || window.Zepto);