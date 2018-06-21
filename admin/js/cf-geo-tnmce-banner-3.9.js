(function() {
"use strict";   
	
	var rs_val = [];
	
	for(var i in cf_geo_banner_shortcodes){
		rs_val[i] = {text: cf_geo_banner_shortcodes[i], onclick : function() {
		//	tinymce.execCommand('mceInsertContent', false, cf_geo_banner_shortcodes[i]);
			
		}};
	}
	
	tinymce.PluginManager.add( 'cf_geo_banner', function( editor, url ) {

		editor.addButton( 'cf_geo_banner', {
			type: 'listbox',
			title: 'CF Geo Banner',			
			text: 'CF Geo Banner',
			icon: false,
			onselect: function(e) {
				console.log(e.control['_text']);
				tinymce.execCommand('mceInsertContent', false, (typeof e.control['_text']=='undefined'?e.control.settings['text']:e.control['_text']));
			}, 
			values: rs_val
 
		});
	});
	
	setTimeout(function() {
		jQuery('.mce-widget.mce-btn').each(function() {
			var btn = jQuery(this);
			if (btn.attr('aria-label')=="CF Geo Banner")
				btn.find('span').css({padding:"10px 20px 10px 10px"});
		});
	},1000);
 
})();