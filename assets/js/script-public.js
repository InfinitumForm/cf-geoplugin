(function (jCFGP) {jCFGP(document).ready(function($){
	
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
	
});})(jQuery || window.jQuery || Zepto || window.Zepto);