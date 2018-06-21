(function() {
"use strict";   
	
	var rs_val = [],i;
	
	for(i in cf_geo_shortcodes){
		rs_val[i] = {text: cf_geo_shortcodes[i], onclick : function() {
			// tinymce.execCommand('mceInsertContent', false, cf_geo_shortcodes[i]);
		}};
	}
	
	tinymce.PluginManager.add( 'cf_geoplugin', function( editor, url ) {

		editor.addButton( 'cf_geoplugin', {
			type: 'listbox',
			title: 'CF GeoPlugin',			
			text: 'CF GeoPlugin',
			icon: false,
			onselect: function(e) {
				tinymce.execCommand('mceInsertContent', false, (typeof e.control['_text']=='undefined'?e.control.settings['text']:e.control['_text']));
			}, 
			values: rs_val
 
		});
	});
	
	setTimeout(function() {
		jQuery('.mce-widget.mce-btn').each(function() {
			var btn = jQuery(this);
			if (btn.attr('aria-label')=="CF GeoPlugin")
				btn.find('span').css({padding:"10px 20px 10px 10px"});
		});
	},1000);
 
})();