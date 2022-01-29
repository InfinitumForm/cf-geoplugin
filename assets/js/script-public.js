(function (jCFGP) {jCFGP(document).ready(function($){
	
	/*
	 * Fix banner shortcode cache
	 */
	(function(banner){
		if(banner.length > 0 && CFGP.cache == 1)
		{
			banner.each(function(){
				var $this = jCFGP(this),
					$id = ($this.attr('data-id') || ''),
					$posts_per_page = ($this.attr('data-posts_per_page') || ''),
					$class = ($this.attr('data-class') || ''),
					$exact = ($this.attr('data-exact') || ''),
					$default = ($this.attr('data-default') || ''),
					$send_ajax = function(){
						
						if( CFGP.rest_enabled == 1 ) {
							return jCFGP.ajax({
								type: "POST",
								accept: 'application/json',
								url: CFGP.cache_banner_url,
								data: {
									action : 'cf_geoplugin_banner_cache',
									id : $id,
									posts_per_page : $posts_per_page,
									class : $class,
									exact : $exact,
									default : $default
								},
							//	cache : true,
								async : true
							}).done(function(data){
								if(data.error !== false) {
									$this.html(data.response);
									$this.removeClass('cache').addClass('cached')
										.removeAttr('data-default')
											.removeAttr('data-posts_per_page')
												.removeAttr('data-class')
													.removeAttr('data-exact')
														.removeAttr('data-id');
								}
							});
						} else {
							return jCFGP.ajax({
								type: "POST",
								dataType: 'html',
								url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
								data: {
									action : 'cf_geoplugin_banner_cache',
									id : $id,
									posts_per_page : $posts_per_page,
									class : $class,
									exact : $exact,
									default : $default
								},
							//	cache : true,
								async : true
							}).done(function(data){
								$this.html(data);
								$this.removeClass('cache').addClass('cached')
									.removeAttr('data-default')
										.removeAttr('data-posts_per_page')
											.removeAttr('data-class')
												.removeAttr('data-exact')
													.removeAttr('data-id');
							});
						}
					};
				
				// Make 3 attempts
				$send_ajax().fail(function(){
					$send_ajax().fail(function(){
						$send_ajax();
					});
				});
				
			});
		}
	}( jCFGP('.cf-geoplugin-banner.cache') ));
	

	/*
	 * Fix plugin shortcode cache
	 */
	(function(sc){
		if(sc.length > 0 && CFGP.cache == 1)
		{
			sc.each(function(){
				var $this = jCFGP(this),
					$options = ($this.attr('data-options') || ''),
					$shortcode = ($this.attr('data-shortcode') || ''),
					$default = ($this.attr('data-default') || ''),
					$send_ajax = function(){
						if( CFGP.rest_enabled == 1 ) {
							return jCFGP.ajax({
								type: "POST",
								accept: 'application/json',
								url: CFGP.cache_shortcode_url,
								data: {
									action : 'cf_geoplugin_shortcode_cache',
									options : $options,
									shortcode : $shortcode,
									default : $default
								},
							//	cache : true,
								async : true
							}).done(function(data){
								if(data.response == 'false' || data.error === true){
									return;
								}
								$this.html(data.response);
								$this.removeClass('cache').addClass('cached')
									.removeAttr('data-default')
										.removeAttr('data-shortcode')
											.removeAttr('data-options');
							});
						} else {
							return jCFGP.ajax({
								type: "POST",
								dataType: 'html',
								url: (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
								data: {
									action : 'cf_geoplugin_shortcode_cache',
									options : $options,
									shortcode : $shortcode,
									default : $default
								},
							//	cache : true,
								async : true
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
						}
					};
				
				// Make 3 attempts
				$send_ajax().fail(function(){
					$send_ajax().fail(function(){
						$send_ajax();
					});
				});
			});
		}
	}( jCFGP('.cf-geoplugin-shortcode.cache') ));
	
});})(jQuery || window.jQuery || Zepto || window.Zepto);