/*
 * Geo Controller
 * @package           cf-geoplugin
 * @link              https://wordpress.org/plugins/cf-geoplugin/
 * @author            Ivijan-Stefan Stipic <ivijan.stefan@gmail.com>
 * @copyright         2014-2024 Ivijan-Stefan Stipic
 * @license           GPL v2 or later
 */
(function (jCFGP) {jCFGP(document).ready(function($){
	
	/*
	 * Fix banner shortcode cache
	 */
	(function(banner) {
		if (banner.length > 0 && CFGP.cache == 1) {
			banner.each(function() {
				var $this = jCFGP(this),
					$id = $this.attr('data-id') || '',
					$nonce = $this.attr('data-nonce') || '',
					ajaxOptions = {
						type: "POST",
						dataType: 'json',
						accept: 'application/json',
						url: (CFGP.rest_enabled == 1) ? CFGP.cache_banner_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
						data: {
							action: 'cf_geoplugin_banner_cache',
							id: $id,
							nonce: $nonce
						},
						async: true
					},
					handleResponse = function(data) {
						var isRestEnabled = CFGP.rest_enabled == 1;
						var success = isRestEnabled ? data.response !== 'false' && !data.error : data.success && data.data.response !== 'false';

						if (success) {
							$this.html(isRestEnabled ? data.response : data.data.response);
							$this.removeClass('cache').addClass('cached').removeAttr('data-nonce data-id');
						} else {
							$this.removeClass('cache').addClass('cache-fail').removeAttr('data-nonce data-id');
						}
					},
					handleError = function() {
						$this.removeClass('cache').addClass('cache-fail').removeAttr('data-nonce data-id');
					},
					sendAjaxRequest = function() {
						return jCFGP.ajax(ajaxOptions).done(handleResponse).fail(handleError);
					};

				// Make 3 attempts
				sendAjaxRequest().fail(function() {
					sendAjaxRequest().fail(function() {
						sendAjaxRequest();
					});
				});
			});
		}
	}(jCFGP('.cf-geoplugin-banner.cache')));
	

	/*
	 * Fix plugin shortcode cache
	 */
	(function(sc) {
		if (sc.length > 0 && CFGP.cache == 1) {
			sc.each(function() {
				var $this = jCFGP(this),
					$type = $this.attr('data-type') || '',
					$nonce = $this.attr('data-nonce') || '',
					ajaxOptions = {
						type: "POST",
						dataType: 'json',
						accept: 'application/json',
						url: (CFGP.rest_enabled == 1) ? CFGP.cache_shortcode_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : CFGP.ajaxurl),
						data: {
							action: 'cf_geoplugin_shortcode_cache',
							nonce: $nonce,
							type: $type
						},
						async: true
					},
					handleResponse = function(data) {
						var isRestEnabled = CFGP.rest_enabled == 1;
						var success = isRestEnabled ? data.response !== 'false' && !data.error : data.success && data.data !== 'false';

						if (success) {
							$this.html(isRestEnabled ? data.response : data.data);
							$this.removeClass('cache').addClass('cached').removeAttr('data-type data-nonce');
						} else {
							$this.removeClass('cache').addClass('cache-fail').removeAttr('data-type data-nonce');
						}
					},
					handleError = function() {
						$this.removeClass('cache').addClass('cache-fail').removeAttr('data-type data-nonce');
					},
					sendAjaxRequest = function() {
						return jCFGP.ajax(ajaxOptions).done(handleResponse).fail(handleError);
					};

				// Make 3 attempts in case of failure
				sendAjaxRequest().fail(function() {
					sendAjaxRequest().fail(function() {
						sendAjaxRequest();
					});
				});
			});
		}
	}(jCFGP('.cf-geoplugin-shortcode.cache')));
	
});})(jQuery || window.jQuery || Zepto || window.Zepto);