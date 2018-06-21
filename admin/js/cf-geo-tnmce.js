(function() {
	tinymce.create('tinymce.plugins.cf_geoplugin', {
 
		init : function(ed, url) {
		},
		createControl : function(n, cm) {
 
            if(n=='cf_geoplugin'){
                var mlb = cm.createListBox('cf_geoplugin', {
                     title: 'CF GeoPlugin',
                     onselect : function(v) {
                     	if(tinyMCE.activeEditor.selection.getContent() == ''){
                            tinyMCE.activeEditor.selection.setContent( v )
                        }
                     }
                });
 
                for(var i in cf_geo_shortcodes)
                	mlb.add(cf_geo_shortcodes[i],cf_geo_shortcodes[i]);
 
                return mlb;
            }
            return null;
        }
 
 
	});
	tinymce.PluginManager.add('cf_geoplugin', tinymce.plugins.cf_geoplugin);
	
	setTimeout(function() {
		jQuery('.mce-widget.mce-btn').each(function() {
			var btn = jQuery(this);
			if (btn.attr('aria-label')=="CF GeoPlugin")
				btn.find('span').css({padding:"10px 20px 10px 10px"});
		});
	},1000);
	
})();