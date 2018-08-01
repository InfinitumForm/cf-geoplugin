(function() {
	tinymce.create('tinymce.plugins.cf_geoplugin_banner', {
 
		init : function(ed, url) {
		},
		createControl : function(n, cm) {
 
            if(n=='cf_geoplugin_banner'){
                var mlb = cm.createListBox('cf_geoplugin_banner', {
                     title: 'Geo Banner',
                     onselect : function(v) {
                     	if(tinyMCE.activeEditor.selection.getContent() == ''){
                            tinyMCE.activeEditor.selection.setContent( v )
                        }
                     }
                });
 
                for(var i in shortcodes)
                	mlb.add(cfgeo_banner_shortcode_names[i],cfgeo_banner_shortcode[i]);
 
                return mlb;
            }
            return null;
        }
 
 
	});
	tinymce.PluginManager.add('cf_geoplugin_banner', tinymce.plugins.cf_geoplugin_banner);
	
	setTimeout(function() {
		jQuery('.mce-widget.mce-btn').each(function() {
			var btn = jQuery(this);
			if (btn.attr('aria-label')=="Geo Banner")
				btn.find('span').css({padding:"10px 20px 10px 10px"});
		});
	},1000);
	
})();