(function() {
"use strict";   
	
	var rs_val = [],i;
	
	for(i in cfgeo_shortcodes){
		rs_val[i] = {text: cfgeo_shortcode_names[i], value: cfgeo_shortcodes[i]};
	}
	
	tinymce.PluginManager.add( 'cf_geoplugin', function( editor, url ) {
		editor.addButton( 'cf_geoplugin', {
			type: 'listbox',
			title: 'CF GeoPlugin',			
			text: 'CF GeoPlugin',
			icon: false,
			onselect: function(e) {
				tinymce.execCommand('mceInsertContent', false, (typeof e.control['_value']=='undefined' ? e.control.settings['value'] : e.control['_value']));
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