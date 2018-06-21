<?php
if(isset($_GET['test']) && ((bool) $_GET['test']) === true) :
	$blockMessage='<div class="wrap">';
	$blockMessage='<h1>'. __("CF Geo Defender - DEBUG",CFGP_NAME).'</h1><hr>';
	$blockMessage.=get_option("cf_geo_block_country_messages");
	$blockMessage.='</div>';
	die(html_entity_decode(stripslashes($blockMessage)));
	exit;
endif;
if (isset($_POST) && count($_POST)>0) {
	// Do the saving
	$front_page_elements = array();
	$updates=array();
	foreach($_POST as $key=>$val){
		if($key=='cf_geo_block_country' || $key=='cf_geo_block_state' || $key=='cf_geo_block_city')
		{
			$val=join("]|[",$val);
			$updates[]=(string) update_option($key, esc_attr($val));
		}
		else
			$updates[]=(string) update_option($key, esc_attr($val));
	}
	
	if(in_array('false',$updates)!==false || count($updates)==0)
		echo '<div class="notice notice-error is-dismissible"><p>'.__('There is some error!',CFGP_NAME).'</p></div>';
	else
		echo '<div class="notice notice-success is-dismissible"><p>'.__('Settings are saved!',CFGP_NAME).'</p></div>';
}

$defender = new CF_Geoplugin_Defender;
$enable=$defender->enable;

$enableForm = ($enable==false ? ' disabled':'');
?>
<div class="wrap">
    	<h2><span class="fa fa-lock"></span> <?php _e("CF Geo Defender",CFGP_NAME); ?></h2>
    	<p>
	<?php echo sprintf(__("With %s you can block the access from the specific IP, country, state and city to your site. Names of countries, states, regions or cities are not case sensitive, but the name must be entered correctly (in English) to get this feature work correctly. This feature is very safe and does not affect to SEO.",CFGP_NAME),'<strong>'.__("CF Geo Defender",CFGP_NAME).'</strong>'); ?><br><br>
	<?php _e("Please, don't use this like antispam or antivirus, this option is only to prevent access to vebsite from specific locations. This option will remove all your content, template, design and display custom message to your visitors.", CFGP_NAME); ?><br><br>
<?php if($enable==false): ?>
	<?php require_once plugin_dir_path(__FILE__) . '/page-settings/settings-get-premium.php'; ?>
<?php endif; ?>
</p>
<form method="post" enctype="multipart/form-data" action="<?php echo  get_admin_url(); ?>admin.php?page=<?php echo $_GET['page']?>&settings-updated=true" target="_self" id="template-options-tab">
<?php if($enable==false) : ?>
	<table class="form-table manage-menus">
    	<tbody>
        	<tr>
            	<th scope="row" style="text-align:right">
                	<label for="cf_geo_defender_api_key"><?php _e('Activation KEY',CFGP_NAME); ?>:</label>
                </th>
                <td>
                	<input type="text" autocomplete="off" value="" name="cf_geo_defender_api_key" id="cf_geo_defender_api_key"><input type="submit" value="<?php _e('Save',CFGP_NAME); ?>" class="button action">
                </td>
            </tr>
        </tbody>
    </table><br>
<?php endif; ?>
    <table class="form-table">
    	<tbody>
        	<tr>
            	<th scope="row">
                </th>
                <td>
                	<input type="submit" value="<?php _e('Save / Update',CFGP_NAME); ?>" class="button action"> <a href="<?php echo admin_url();?>admin.php?page=cf-geoplugin-defender&test=true" target="_blank" class="button action"><?php _e('Click here for safe test (first save your changes)',CFGP_NAME); ?></a>
                </td>
            </tr>
            <tr>
            	<th scope="row">
                	<label for="cf_geo_block_ip"><?php _e('IP address separated by comma',CFGP_NAME); ?>:</label>
                </th>
                <td>
                	<textarea style="width:100%; height:50px;" name="cf_geo_block_ip" id="cf_geo_block_ip"<?php echo $enableForm; ?> placeholder="<?php echo (!$enable?' [ '.__('PRO Version Only',CFGP_NAME).' ] ':''); ?>"><?php echo get_option("cf_geo_block_ip"); ?></textarea>
                </td>
            </tr>
        	<tr>
            	<th scope="row">
                	<label for="cf_geo_block_country"><?php _e('Choose Countries',CFGP_NAME); ?>:</label>
                </th>
                <td>
                	<?php
                    $all_countries = cf_geo_get_terms(array(
						'taxonomy'		=> 'cf-geoplugin-country',
						'hide_empty'	=> false
					));
					echo '<select name="cf_geo_block_country[]" class="chosen-select" id="cf-geo-block-country" data-placeholder="'
						.__('Choose a country...',CFGP_NAME)
						.'" multiple>';
					if(is_array( $all_countries ) && count($all_countries)>0)
					{
						$cf_geo_block_country = get_option("cf_geo_block_country");
						$find = array_map("trim",explode("]|[",$cf_geo_block_country));
						foreach($all_countries as $i=>$country)
						{
							echo '<option id="'
							.$country->slug
							.'" value="'
							.$country->slug
							.'"'
							.(in_array($country->slug, $find)!==false?' selected':'')
							.'>'
							.$country->name
							.' - '.$country->description.'</option>';
						}
					}
					echo '</select>';
					?>
					<small><?php _e('To setup list of countries, you need to go in CF GeoBanner -> Countries',CFGP_NAME); ?></small>
                </td>
            </tr>
            <tr>
            	<th scope="row">
                	<label for="cf_geo_block_state"><?php _e('Choose Regions',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <?php
                    $all_regions = cf_geo_get_terms(array(
						'taxonomy'		=> 'cf-geoplugin-region',
						'hide_empty'	=> false
					));
					echo '<select name="cf_geo_block_state[]" class="chosen-select" id="cf-geo-block-state" data-placeholder="'
						.(!$enable?' [ '.__('PRO Version Only',CFGP_NAME).' ] ':'').__('Choose a region...',CFGP_NAME)
						.'" multiple'
						.$enableForm
						.'>';
					if(is_array( $all_regions ) && count($all_regions)>0)
					{
						$cf_geo_block_state = get_option("cf_geo_block_state");
						$find = array_map("trim",explode("]|[",$cf_geo_block_state));
						foreach($all_regions as $i=>$region)
						{
							echo '<option id="'
							.$region->slug
							.'" value="'
							.$region->slug
							.'"'
							.(in_array($region->slug, $find)!==false?' selected':'')
							.'>'
							.$region->name
							.'</option>';
						}
					}
					echo '</select>';
					?>
					<small><?php _e('To setup list of regions, you need to go in CF GeoBanner -> Regions',CFGP_NAME); ?></small>
                </td>
            </tr>
            <tr>
            	<th scope="row">
                	<label for="cf_geo_block_city"><?php _e('Choose Cities',CFGP_NAME); ?>:</label>
                </th>
                <td>
                	<?php
                    $all_city = cf_geo_get_terms(array(
						'taxonomy'		=> 'cf-geoplugin-city',
						'hide_empty'	=> false
					));
					echo '<select name="cf_geo_block_city[]" class="chosen-select" id="cf-geo-block-city" data-placeholder="'
						.(!$enable?' [ '.__('PRO Version Only',CFGP_NAME).' ] ':'').__('Choose a city...',CFGP_NAME)
						.'" multiple'
						.$enableForm
						.'>';
					if(is_array( $all_city ) && count($all_city)>0)
					{
						$cf_geo_block_city = get_option("cf_geo_block_city");
						$find = array_map("trim",explode("]|[",$cf_geo_block_city));
						foreach($all_city as $i=>$city)
						{
							echo '<option id="'
							.$city->slug
							.'" value="'
							.$city->slug
							.'"'
							.(in_array($city->slug, $find)!==false?' selected':'')
							.'>'
							.$city->name
							.'</option>';
						}
					}
					echo '</select>';
					?>
					<small><?php _e('To setup list of cities, you need to go in CF GeoBanner -> Cities',CFGP_NAME); ?></small>
                </td>
            </tr>
            <tr>
            	<th scope="row">
                	<label for="cf_geo_block_country_messages"><?php _e('Message that is displayed to a blocked visitor (HTML allowed)',CFGP_NAME); ?>:</label>
                </th>
                <td>
                	<?php
						$messages=trim(html_entity_decode(get_option("cf_geo_block_country_messages")));
						if($enable==false && empty($messages)){
							update_option('cf_geo_block_country_messages', __('Sorry, you are not allowed access to the content of this website in your country.',CFGP_NAME));
						}
					?>
                 <?php if($enable==false) : ?>
                	<textarea style="width:100%; height:100px;" name="cf_geo_block_country_messages" id="cf_geo_block_country_messages"><?php echo (empty($messages)?'
<h1>Error</h1>
<h3>404 - Page not found</h3>
<p>We could not find the above page on our servers.</p>
':$messages); ?></textarea>
<?php else : ?>

<?php wp_editor( (empty($messages)?'
<h1>Error</h1>
<h3>404 - Page not found</h3>
<p>We could not find the above page on our servers.</p>
':$messages) , 'cf_geo_block_country_messages', $settings = array('textarea_name'=>'cf_geo_block_country_messages') ); ?>

<?php endif; ?>
                </td>
            </tr>
            <tr>
            	<th scope="row">
                </th>
                <td>
                	<input type="submit" value="<?php _e('Save / Update',CFGP_NAME); ?>" class="button action"> <a href="<?php echo admin_url();?>admin.php?page=cf-geoplugin-defender&test=true" target="_blank" class="button action"><?php _e('Click here for safe test (first save your changes)',CFGP_NAME); ?></a>
                </td>
            </tr>
        </tbody>
    </table>
 </form>
 </div>