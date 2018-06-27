/**
* jQuery Admin Functionality
*
* @author     Ivijan-Stefan Stipic <creativform@gmail.com>
* @version    1.0.0
*/
(function( $ ) {
	'use strict';
	/* Initialize Advanced Choose boxes */
	$(document).ready(function(){
		
		if($("input[name^='cf_geo_enable_proxy']").val()=='true')
		{
			$("input[name^='cf_geo_enable_proxy_ip']").prop("disabled",false);
			$("input[name^='cf_geo_enable_proxy_port']").prop("disabled",false);
			$("input[name^='cf_geo_enable_proxy_username']").prop("disabled",false);
			$("input[name^='cf_geo_enable_proxy_password']").prop("disabled",false);
		}
		else
		{
			$("input[name^='cf_geo_enable_proxy_ip']").prop("disabled",true);
			$("input[name^='cf_geo_enable_proxy_port']").prop("disabled",true);
			$("input[name^='cf_geo_enable_proxy_username']").prop("disabled",true);
			$("input[name^='cf_geo_enable_proxy_password']").prop("disabled",true);
		}
		
		$(".chosen-select").each(function(index, element) {
			$(this).chosen({
				no_results_text: CF_GEOPLUGIN.select_nothing_found,
				width: "100%",
				search_contains:true
			});
		});
		
		// Remove unusefull functions
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-country .form-field.term-parent-wrap").remove();
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-region .form-field.term-parent-wrap").remove();
		$(".post-type-cf-geoplugin-banner.taxonomy-cf-geoplugin-city .form-field.term-parent-wrap").remove();
		
		// CF Geoplugin SEO Settup
		if($("#cf_geoplugin_seo_redirection").length > 0 ){
			
			if(CF_GEOPLUGIN.premium === false)
			{
				$("#cf_geoplugin_seo_redirection #cf_geo_metabox_country, #cf_geoplugin_seo_redirection #cf_geo_metabox_url, #cf_geoplugin_seo_redirection #cf_geo_metabox_status_code, #cf_geoplugin_seo_redirection .cfgeo_option").prop("disabled",true);
			}
			
			$("#cf_geoplugin_seo_redirection #cf_geo_metabox_country").chosen({
				no_results_text: CF_GEOPLUGIN.select_nothing_found,
				width: "100%",
			});
			
			$("#cf_geoplugin_seo_redirection #cf_geo_metabox_url").on("keyup paste", $.debounce( 300, function(){
				var This = $(this),
					val = This.val().trim(),
					countryBox = $("#cf_geoplugin_seo_redirection #cf_geo_metabox_country"),
					countryBoxVal = countryBox.val().trim();

				// countryBox.removeAttr("style");
				This.removeAttr("style");
				/*
				if(val.length>0)
				{						
					if(countryBoxVal.length === 0)
					{
						countryBox.css({
							'border':'1px solid #cc0000'
						})
					}
				}
				*/
			}));
			
			$("#cf_geoplugin_seo_redirection #cf_geo_metabox_country").on("change", $.debounce( 250, function(){
				var This = $(this),
					val = This.val().trim(),
					urlBox = $("#cf_geoplugin_seo_redirection #cf_geo_metabox_url"),
					urlBoxVal = urlBox.val().trim();
				
				if(val.length > 0)
				{						
				//	This.removeAttr("style");
					if(urlBoxVal.length===0)
					{
						urlBox.css({
							'border':'1px solid #cc0000'
						});
					}
				}
				else if(urlBoxVal.length>0)
				{
					urlBox.removeAttr("style");
				/*	This.css({
						'border':'1px solid #cc0000'
					});
				*/
				}
				else
				{
				//	This.removeAttr("style");
					urlBox.removeAttr("style");
				}
			}));
			
			$("#cf_geoplugin_seo_redirection #cf_geo_metabox_enable_seo1, #cf_geoplugin_seo_redirection #cf_geo_metabox_enable_seo2").on("change", $.debounce( 200, function(){
				var This = $(this),
					val = This.val().trim(),
					urlBox = $("#cf_geoplugin_seo_redirection #cf_geo_metabox_url"),
					urlBoxVal = urlBox.val().trim();
				
				urlBox.removeAttr("style");
				
				if(val == 'true' && urlBoxVal.length===0){
					urlBox.css({
						'border':'1px solid #cc0000'
					})
				}
			}));
			
			if($("#cf_geoplugin_seo_redirection.postbox.closed").length === 0 && $("#cf_geoplugin_seo_redirection #cf_geo_metabox_enable_seo2:checked").length > 0)
				$("#cf_geoplugin_seo_redirection.postbox").addClass("closed");
		}
		// Initialize AJAX call for settings
		if(CF_GEOPLUGIN.url.indexOf('cf-geoplugin-settings') > -1)
		{
			var ajaxStop = false,
				selectFocus = true,
			settingsAJAX = function(){
				var This = $(this),
					name = This.attr("name"),
					value = This.val(),
					data = {};
				selectFocus = false;
				// reset admin notice
				if(name == 'cf_geo_enable_banner' || name == 'cf_geo_enable_gmap' || name == 'cf_geo_enable_defender' || name == 'cf_geo_auto_update' )	
					$("#notice-ajax-saved").fadeOut(100,function(){$(this).remove();});
				
				$("#notice-ajax-saved-error").fadeOut(100,function(){$(this).remove();});
				
				data['action'] = 'cfgeo_settings';
				data[name] = value;
				
				
				if($('#info_' +name).length>0){
					$('#info_' +name).remove();
				}
				
				This.after(' <span class="info fa fa-spinner fa-pulse" id="info_' +name+ '" title="Loading..."></span>');
				if(ajaxStop === false)
				{
					ajaxStop = true;
					$.post(ajaxurl, data).done(function(returns){
						if(returns=='true')
						{
							
							// enable/disable proxy
							if(name == 'cf_geo_enable_proxy')
							{
								if(value == 'true')
								{
									$("input[name^='cf_geo_enable_proxy_ip']").prop("disabled",false);
									$("input[name^='cf_geo_enable_proxy_port']").prop("disabled",false);
									$("input[name^='cf_geo_enable_proxy_username']").prop("disabled",false);
									$("input[name^='cf_geo_enable_proxy_password']").prop("disabled",false);
								}
								else
								{
									$("input[name^='cf_geo_enable_proxy_ip']").prop("disabled",true);
									$("input[name^='cf_geo_enable_proxy_port']").prop("disabled",true);
									$("input[name^='cf_geo_enable_proxy_username']").prop("disabled",true);
									$("input[name^='cf_geo_enable_proxy_password']").prop("disabled",true);
								}
							}
							
							// enable  disable geo banner
							if(name == 'cf_geo_enable_banner')
							{
								if(value == 'true')
								{
									$("#menu-posts-cf-geoplugin-banner").show();
								}
								else
								{
									$("#menu-posts-cf-geoplugin-banner").hide();
								}
							}
							
							$('#info_' +name).remove();
							This.after(' <span class="info fa fa-check" id="info_' +name+ '" title="Updated!"></span>');
							
							if(name == 'cf_geo_enable_banner' || name == 'cf_geo_enable_gmap' || name == 'cf_geo_enable_defender' )
							{
								$("#settings-form").prepend('<div class="notice notice-success is-dismissible" id="notice-ajax-saved"><p>Settings saved. Please <a href="' + CF_GEOPLUGIN.url + '">refresh page</a> to see all changes properly.</p></div>');
							}/*
							else
							{
								$("#settings-form").prepend('<div class="notice notice-success is-dismissible" id="notice-ajax-saved"><p>Settings saved.</p></div>');
							}
							*/
						}
						else
						{
							$('#info_' +name).remove();
							This.after(' <span class="info fa fa-minus-circle" id="info_' +name+ '" title="ERROR: Not Updated!"></span>');
							
							if(name == 'cf_geo_defender_api_key')
								$("#settings-form").prepend('<div class="notice notice-warning is-dismissible" id="notice-ajax-saved-error"><p>WARNING: Wrong API Key.</p></div>');
							else
								$("#settings-form").prepend('<div class="notice notice-error is-dismissible" id="notice-ajax-saved-error"><p>ERROR: Not Updated! Try again.</p></div>');
						}
						ajaxStop = false;
						selectFocus = true;
						return false;
					}).fail(function(a,b,c){
						console.log(a);
						console.log(b);
						console.log(c);
						$('#info_' +name).remove();
						This.after(' <span class="info fa fa-minus-circle" id="info_' +name+ '" title="FATAL ERROR: Check Console Log for more info."></span>');
						$("#settings-form").prepend('<div class="notice notice-error is-dismissible" id="notice-ajax-saved-error"><p>FATAL ERROR: Check Console Log for more info.</p></div>');
						ajaxStop = false;
						selectFocus = true;
						return false;
					});
				}
				return false;
			};
			
			$("input[name^='cf_geo_'], select[name^='cf_geo_'], textarea[name^='cf_geo_']").on("change keyup paste", $.debounce( 300, settingsAJAX));
			
			$("select[name^='cf_geo_'] > option").on("focus", function(){
				if(selectFocus) $(this).prop("selected",false);
			});
		}
	});
	
	/* Initialize Settings */
	/*if($('#settings-form').length > 0)
	{
		var somethingChanged=false;
		$('#settings-form input, #settings-form select, #settings-form textarea').change(function() { 
			somethingChanged = true; 
	   	}); 
		$(window).bind('beforeunload', function(e){
			if(somethingChanged)
				return "NOTE: Please save all your changes before you go on the next tab or leave page.";
			else 
				e=null; // i.e; if form state change show warning box, else don't show it.
		});
	}*/
	
	/* Initialize Tabs */
	if($(".nav-tab-body").length>0)
	{
		var $tabButton = $(".nav-tab"),
			$tabContent = $(".nav-tab-item"),
			$activeClass =	['nav-tab-active','nav-tab-item-active'];
		
		$tabButton.on("click",function(e){
			e.preventDefault();
			
			var button = $(this),
				href = button.attr("href").trim(),
				classMax = $activeClass.length;
				
			for(var i=0; i<classMax; i++){
				$("."+$activeClass[i]).removeClass($activeClass[i]);
			};
				
			button.addClass($activeClass[0]);
			$(href).addClass($activeClass[1]);
		});
		
	}	
})( window.jQuery || window.Zepto );


/**
* Javascript Popup Plugin
*
* @author     Ivijan-Stefan Stipic <creativform@gmail.com>
* @version    1.0.8
*/
function cf_geoplugin_popup(url, title, w, h) {
	// Fixes dual-screen position Most browsers Firefox
	var dualScreenLeft = (window.screenLeft != undefined ? window.screenLeft : screen.left),
		dualScreenTop = (window.screenTop != undefined ? window.screenTop : screen.top);
	
	width = (window.innerWidth ? window.innerWidth : (document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width));
	height = (window.innerHeight ? window.innerHeight : (document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height));
	
	var left = ((width / 2) - (w / 2)) + dualScreenLeft,
		top = ((height / 2) - (h / 2)) + dualScreenTop,
		newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
	
	// Puts focus on the newWindow
	if (window.focus) {
		newWindow.focus();
	}
};