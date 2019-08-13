<?php
/**
 * ADDITIONAL ACTIVATION CONTROL
 *
 * Force plugin to be loaded first if is possible to can others use it properly.
 * Update database with missing column
 *
 * @since         7.7.1
 * @version       7.7.2
 *
 */
if(is_admin())
{
	add_action( 'activated_plugin', function($plugin){
		$path = plugin_basename(__FILE__);
		if($plugin == $path)
		{
			global $wpdb;
			
			add_option('cf_geoplugin_do_activation_redirect', true);
			
			$table_name = $wpdb->prefix . CF_Geoplugin_Global::TABLE['seo_redirection'];
			if($wpdb->query("
				   SELECT 1 FROM information_schema.tables 
				   WHERE table_schema = '{$wpdb->dbname}' 
				   AND table_name = '{$table_name}'
			;"))
			{
				$list_columns = $wpdb->get_col("SHOW COLUMNS FROM {$table_name}");
				if(!in_array('only_once', $list_columns))
				{
					$wpdb->query( "ALTER TABLE {$table_name} ADD only_once TINYINT(1) NOT NULL DEFAULT 0" );
				}
			}
			
			if ( $plugins = get_option( 'active_plugins' ) ) {
				if ( $key = array_search( $path, $plugins ) ) {
					array_splice( $plugins, $key, 1 );
					array_unshift( $plugins, $path );
					update_option( 'active_plugins', $plugins );
				}
			}
		}
	}, 1, 1);
}

/**
 * REDIRECTIONS 
 *
 * Redirections after activation
 *
 * @since         7.7.1
 * @version       7.7.2
 *
 */
add_action('admin_init', function () {
	if (get_option('cf_geoplugin_do_activation_redirect', false)) {
		$CF_GEOPLUGIN_OPTIONS = (isset($GLOBALS['CF_GEOPLUGIN_OPTIONS']) ? $GLOBALS['CF_GEOPLUGIN_OPTIONS'] : array());
		
		delete_option('cf_geoplugin_do_activation_redirect');
		
		if(isset($CF_GEOPLUGIN_OPTIONS['first_plugin_activation']) && $CF_GEOPLUGIN_OPTIONS['first_plugin_activation']) {
			exit( wp_redirect("admin.php?page=cf-geoplugin-settings") );
		} else {
			if(!CF_Geoplugin_Global::validate()) {
				exit( wp_redirect("admin.php?page=cf-geoplugin-activate") );
			} else {
				exit( wp_redirect("admin.php?page=cf-geoplugin") );
			}
		}
	}
}, 1, 0);