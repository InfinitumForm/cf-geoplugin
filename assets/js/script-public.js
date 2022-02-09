/*
 * CF Geo Plugin
 * @package           cf-geoplugin
 * @link              https://wordpress.org/plugins/cf-geoplugin/
 * @author            Ivijan-Stefan Stipic <ivijan.stefan@gmail.com>
 * @copyright         2014-2022 Ivijan-Stefan Stipic
 * @license           GPL v2 or later
 */
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
						
						return jCFGP.ajax({
							type: "POST",
							dataType: ((CFGP.rest_enabled == 1) ? 'json' : 'html'),
							accept: ((CFGP.rest_enabled == 1) ? 'application/json' : 'text/html'),
							url: ((CFGP.rest_enabled == 1) ? CFGP.cache_banner_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl)),
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
							if(CFGP.rest_enabled == 1) {
								if(data.error === false) {									
									$this.html(data.response);
								}
							} else {
								$this.html(data);
							}
							$this.removeClass('cache').addClass('cached')
								.removeAttr('data-default')
									.removeAttr('data-posts_per_page')
										.removeAttr('data-class')
											.removeAttr('data-exact')
												.removeAttr('data-nonce')
													.removeAttr('data-id');
						});
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
					$type = ($this.attr('data-type') || ''),
					$default = ($this.attr('data-default') || ''),
					$send_ajax = function(){
						
						return jCFGP.ajax({
							type: "POST",
							dataType: ((CFGP.rest_enabled == 1) ? 'json' : 'html'),
							accept: ((CFGP.rest_enabled == 1) ? 'application/json' : 'text/html'),
							url: ((CFGP.rest_enabled == 1) ? CFGP.cache_shortcode_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl)),
							data: {
								action : 'cf_geoplugin_shortcode_cache',
								options : $options,
								shortcode : $type,
								default : $default
							},
						//	cache : true,
							async : true
						}).done(function(data){
							if(CFGP.rest_enabled == 1) {
								if(data.response == 'false' || data.error === true){
									return;
								}
								$this.html(data.response);
							} else {
								if(data == 'false'){
									return;
								}
								$this.html(data);
							}
							$this.removeClass('cache').addClass('cached')
								.removeAttr('data-default')
									.removeAttr('data-type')
										.removeAttr('data-nonce')
											.removeAttr('data-options');
						});
						
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