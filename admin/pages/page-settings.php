<?php
/*if (isset($_POST) && count($_POST)>0) {
	// Do the saving
	$front_page_elements = array();
	$updates=array();
	foreach($_POST as $key=>$val){
		$updates[]=update_option($key, esc_attr($val));
	}
	
	if(in_array('false',$updates)!==false || count($updates)==0)
		echo '<div class="notice notice-error is-dismissible"><p>'.__('An error has occurred! Some settings are not saved.',CFGP_NAME).'</p></div>';
	else
		echo '<div class="notice notice-success is-dismissible"><p>'.__('Settings are saved!',CFGP_NAME).'</p></div>';
}*/
$optionName=array(__('Disabled',CFGP_NAME),__('Enabled',CFGP_NAME));

$defender = new CF_Geoplugin_Defender;
$enable=$defender->enable;

$enableForm = ($enable==false ? ' disabled':'');
?>
<?php
	$data = CF_GEO_D::curl_get("http://cfgeoplugin.com/team.json");
	$data = json_decode($data);
//	var_dump($data);
?>
<div class="wrap">
	<h2><span class="fa fa-cogs"></span> <?php _e('Settings',CFGP_NAME); ?></h2>
    <p class="about-description"><?php _e('Global setup for CF GeoPlugin',CFGP_NAME); ?></strong></p><br>
    
    <h2 class="nav-tab-wrapper">
    <?php
    	foreach(array(
			'global'		=>	'<span class="fa fa-cog"></span> '.__('Global Settings',CFGP_NAME),
			'google-map'	=>	'<span class="fa fa-globe"></span> '.__('Google Map Settings',CFGP_NAME),
		//	'geo-banner'	=>	'<span class="fa fa-tasks"></span> '.__('Geo Banner',CFGP_NAME),
		//	'get-premium'	=>	'<span class="fa fa-star-o"></span> '.__('Register Premium',CFGP_NAME),
			'license'		=>	CFGP_DEFENDER_ACTIVATED ? '' : '<span class="fa fa-certificate"></span> '.__('License',CFGP_NAME),
			'credits'		=>	'<span class="fa fa-info-circle"></span> '.__('Credits & Info',CFGP_NAME),
			'team-members'	=>	'<span class="fa fa-braille"></span> '.__('Development Team',CFGP_NAME),
			'sponsors'		=>	(isset($data->sponsors)?'<span class="fa fa-star-o"></span> '.__('Sponsors',CFGP_NAME):''),
			'donation'		=>	(isset($data->team)?'<span class="fa fa-heart"></span> '.__('Donation',CFGP_NAME):''),
		) as $part=>$tab){
			if(!$enable && $part=='get-premium') {} else
			{
				if(!empty($tab))
				{
					$active	=	((!is_numeric($part) && isset($_GET['part']) && $_GET['part']==$part) || (isset($_GET['part'])===false && is_numeric($part)) ? ' nav-tab-active':'');
					if(isset($_GET['part']) && !empty($_GET['part'])){}else	if($part=='global') $active = ' nav-tab-active';
					
					$url	=	admin_url('admin.php?page=cf-geoplugin-settings'.(is_numeric($part)?'':'&part='.$part));
					echo '<a class="nav-tab'.$active.'" href="'.$url.'">'.$tab.'</a>';
				}
			}
		}
	?>
    </h2>
    <?php
    	switch(isset($_GET['part']) ? strtolower($_GET['part']) : '')
		{
			case 'global': default:
				require_once plugin_dir_path(__FILE__) . 'page-settings/settings-global.php';
			break;
			case 'google-map':
				require_once plugin_dir_path(__FILE__) . 'page-settings/settings-google-map.php';
			break;
			case 'geo-banner':
				require_once plugin_dir_path(__FILE__) . 'page-settings/settings-geo-banner.php';
			break;
			case 'get-premium':
				require_once plugin_dir_path(__FILE__) . 'page-settings/settings-get-premium.php';
			break;
			case 'license':
				require_once plugin_dir_path(__FILE__) . 'page-settings/settings-license.php';
			break;
			case 'credits':
				require_once plugin_dir_path(__FILE__) . 'page-settings/settings-credits.php';
			break;
			case 'donation':
				require_once plugin_dir_path(__FILE__) . 'page-settings/settings-donation.php';
			break;
			case 'sponsors':
				require_once plugin_dir_path(__FILE__) . 'page-settings/settings-sponsors.php';
			break;
			case 'team-members':
				require_once plugin_dir_path(__FILE__) . 'page-settings/settings-team-members.php';
			break;
		}
	?>
</p>