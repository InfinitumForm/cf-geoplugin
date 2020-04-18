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
			if(class_exists('CF_Geoplugin_Global'))
			{
				global $wpdb;
				
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
			}
		}
	}, 1, 1);
}