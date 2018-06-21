(function() {
	tinymce.create('tinymce.plugins.cf_geo_banner', {
 
		init : function(ed, url) {
		},
		createControl : function(n, cm) {
 
            if(n=='cf_geo_banner'){
                var mlb = cm.createListBox('cf_geo_banner', {
                     title: 'CF Geo Banner',
                     onselect : function(v) {
                     	if(tinyMCE.activeEditor.selection.getContent() == ''){
                            tinyMCE.activeEditor.selection.setContent( v )
                        }
                     }
                });
 
                for(var i in cf_geo_banner_shortcodes){
                	mlb.add(cf_geo_banner_shortcodes[i],cf_geo_banner_shortcodes[i]);
 					console.log(cf_geo_banner_shortcodes[i]);
				}
                return mlb;
            }
            return null;
        }
 
 
	});
	tinymce.PluginManager.add('cf_geo_banner', tinymce.plugins.cf_geo_banner);
	
	setTimeout(function() {
		jQuery('.mce-widget.mce-btn').each(function() {
			var btn = jQuery(this);
			if (btn.attr('aria-label')=="CF Geo Banner")
				btn.find('span').css({padding:"10px 20px 10px 10px"});
		});
	},1000);
	
})();