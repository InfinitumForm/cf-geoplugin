<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( CFGP_U::request_bool('preview'))
{
	die( wpautop( html_entity_decode( stripslashes( CFGP_Options::get('block_country_messages') ) ) ) );
	exit;
}

global $cfgp_cache;

$all_countries = get_terms(array(
	'taxonomy'		=> 'cf-geoplugin-country',
	'hide_empty'	=> false
));

$all_regions = get_terms(array(
	'taxonomy'		=> 'cf-geoplugin-region',
	'hide_empty'	=> false
));

$all_cities = get_terms(array(
	'taxonomy'		=> 'cf-geoplugin-city',
	'hide_empty'	=> false
));

if(CFGP_U::request_bool('save_defender') && wp_verify_nonce(sanitize_text_field($_REQUEST['nonce']), CFGP_NAME.'-save-defender') !== false)
{
	if( !isset( $_POST['block_country'] ) )
	{
		CFGP_Options::set( 'block_country', '' );
	}
	if( !isset( $_POST['block_region'] ) )
	{
		CFGP_Options::set( 'block_region', '' );
	}
	if( !isset( $_POST['block_city'] ) )
	{
		CFGP_Options::set( 'block_city', '' );
	}

	$updates = array();
	foreach( $_POST as $key => $value )
	{
		if($key == 'submit') continue;
		
		if( $key == 'block_country' || $key=='block_region' || $key=='block_city' )
		{
			$value = implode( ']|[', $value );
			$updated = CFGP_Options::set( $key, esc_attr( $value ) );
			$updates[] = (string) $updated[$key];
		}
		else
		{
			$updated = CFGP_Options::set( $key, $value );
			$updates[] = (string) $updated[$key];
		}
	}
	if( in_array( 'false', $updates ) !== false || count( $updates ) == 0 )
	{
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			__('There is an error. Settings not saved.', CFGP_NAME)
		);
	}
	else
	{
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			__('Settings saved.', CFGP_NAME)
		);
	}
}

?>
<div class="wrap wrap-cfgp" id="<?php echo $_GET['page']; ?>">
	<h1 class="wp-heading-inline"><i class="fa fa-lock"></i> <?php _e('Anti Spam Protection & Site Restriction', CFGP_NAME); ?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">

				<div class="inner-sidebar" id="<?php echo CFGP_NAME; ?>-defender-sidebar">
					<div id="side-sortables" class="meta-box-sortables ui-sortable">
						<?php do_action('cfgp/page/defender/sidebar'); ?>
					</div>
				</div>

        	<div id="post-body">
            	<div id="post-body-content">
					<form method="post" action="<?php echo admin_url('/admin.php?page=cf-geoplugin-defender&save_defender=true&nonce='.wp_create_nonce(CFGP_NAME.'-save-defender')); ?>">
                    	<div class="nav-tab-wrapper-chosen">
                        	<nav class="nav-tab-wrapper">
                            	<a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#defender-settings"><i class="fa fa-wrench"></i><span class="label"> <?php _e('General Defender Settings', CFGP_NAME); ?></span></a>
                                <a href="javascript:void(0);" class="nav-tab" data-id="#defender-settings-page"><i class="fa fa-file"></i><span class="label"> <?php _e('Defender page', CFGP_NAME); ?></span></a>
                                <a href="<?php echo admin_url('/admin.php?page=cf-geoplugin-defender&preview=true'); ?>" class="nav-tab" target="_blank"><i class="fa fa-desktop"></i><span class="label"> <?php _e('Preview', CFGP_NAME); ?></span></a>
                            </nav>
                            
                            <div class="cfgp-tab-panel cfgp-tab-panel-active" id="defender-settings">
                            	<p><?php _e('With Anti Spam Protection you can block the access from the specific IP, country, state and city to your site. Names of countries, states, regions or cities are not case sensitive, but the name must be entered correctly (in English) to get this feature work correctly. This feature is very safe and does not affect SEO.', CFGP_NAME); ?></p>
                                
                                <div class="nav-tab-wrapper-chosen">
                                    <nav class="nav-tab-wrapper">
                                        <a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#ip-restriction"><i class="fa fa-shield"></i><span class="label"> <?php _e('IP Restriction', CFGP_NAME); ?></span></a>
                                        <a href="javascript:void(0);" class="nav-tab" data-id="#country-restriction"><i class="fa fa-globe"></i><span class="label"> <?php _e('Country Restriction', CFGP_NAME); ?></span></a>
                                        <a href="javascript:void(0);" class="nav-tab" data-id="#region-restriction"><i class="fa fa-map-marker"></i><span class="label"> <?php _e('Region Restriction', CFGP_NAME); ?></span></a>
                                        <a href="javascript:void(0);" class="nav-tab" data-id="#city-restriction"><i class="fa fa-building-o"></i><span class="label"> <?php _e('City Restriction', CFGP_NAME); ?></span></a>
                                    </nav>
                                    <div class="cfgp-tab-panel cfgp-tab-panel-active" id="ip-restriction">
                                    	<div class="cfgp-form-group">
                                            <label for="block_ip"><?php _e('IP address separated by comma',CFGP_NAME); ?>:</label>
                                            <textarea class="form-control" id="block_ip" name="block_ip" rows="5" style="min-height:115px"><?php echo CFGP_Options::get('block_ip'); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="cfgp-tab-panel" id="country-restriction">
                                    	<div class="cfgp-form-group">
                                            <label for="block_country"><?php _e('Choose Countries',CFGP_NAME); ?>:</label>
                                            <select class="chosen-select" data-placeholder="<?php _e( 'Choose countries...', CFGP_NAME ); ?>" id="block_country" aria-describedby="countryHelp" name="block_country[]" multiple >
                                            <?php
                                                if( is_array( $all_countries ) && !empty( $all_countries ) )
                                                {
                                                    $find = array_map( "trim", explode( "]|[", CFGP_Options::get('block_country') ) );
                                                    foreach( $all_countries as $key => $country )
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
                                            ?>
                                            </select>
                                            <p><?php printf(__('To set up a list of countries, you need to go to the Geo Plugin -> %s',CFGP_NAME), '<a href="' . admin_url('edit-tags.php?taxonomy=cf-geoplugin-country&post_type=cf-geoplugin-banner') . '" target="_blank">' . __('Countries',CFGP_NAME) . '</a>'); ?></p>
                                            <button type="button" class="button cfgp-select-all" data-target="block_country"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select/Deselect all', CFGP_NAME ); ?></button>
                                        </div>
                                    </div>
                                    <div class="cfgp-tab-panel" id="region-restriction">
                                    	<div class="cfgp-form-group">
                                            <label for="block_region"><?php _e('Choose Region',CFGP_NAME); ?>:</label>
                                            <select class="chosen-select" data-placeholder="<?php _e( 'Choose regions...', CFGP_NAME ); ?>" id="block_region" aria-describedby="regionHelp" name="block_region[]" multiple >
                                            <?php
                                                if( is_array( $all_regions ) && !empty( $all_regions ) )
                                                {
                                                    $find = array_map( "trim", explode( "]|[", CFGP_Options::get('block_region') ) );
                                                    foreach( $all_regions as $key => $country )
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
                                            ?>
                                            </select>
                                            <p><?php printf(__('To set up a list of regions, you need to go to the Geo Plugin -> %s',CFGP_NAME), '<a href="' . admin_url('edit-tags.php?taxonomy=cf-geoplugin-region&post_type=cf-geoplugin-banner') . '" target="_blank">' . __('Regions',CFGP_NAME) . '</a>'); ?></p>
                                            <button type="button" class="button cfgp-select-all" data-target="block_region"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select/Deselect all', CFGP_NAME ); ?></button>
                                        </div>
                                    </div>
                                    <div class="cfgp-tab-panel" id="city-restriction">
                                    	<div class="cfgp-form-group">
                                            <label for="block_city"><?php _e('Choose Cities',CFGP_NAME); ?>:</label>
                                            <select class="chosen-select" data-placeholder="<?php _e( 'Choose cities...', CFGP_NAME ); ?>" id="block_city" aria-describedby="cityHelp" name="block_city[]" multiple >
                                            <?php
                                                if( is_array( $all_regions ) && !empty( $all_regions ) )
                                                {
                                                    $find = array_map( "trim", explode( "]|[", CFGP_Options::get('block_city') ) );
                                                    foreach( $all_regions as $key => $country )
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
                                            ?>
                                            </select>
                                            <p><?php printf(__('To set up a list of cities, you need to go to the Geo Plugin -> %s',CFGP_NAME), '<a href="' . admin_url('edit-tags.php?taxonomy=cf-geoplugin-city&post_type=cf-geoplugin-banner') . '" target="_blank">' . __('City',CFGP_NAME) . '</a>'); ?></p>
                                            <button type="button" class="button cfgp-select-all" data-target="block_city"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select/Deselect all', CFGP_NAME ); ?></button>
                                        </div>
                                    </div>
                                 </div>
                                 
                                 <?php if(CFGP_Options::get('enable_spam_ip')): ?>
                                     <p><strong><?php printf(__( 'Automatic IP Address Blacklist Check is enabled. All of these IPs are from a safe source and most of them are bots and crawlers. Blacklisted IPs will be automatically recognized and blocked. If you don\'t want this kind of protection %s.', CFGP_NAME ),
                                        '<a href="'.admin_url('admin.php?page=cf-geoplugin-settings').'#spam-protection">'
                                            .__('disable it in plugin settings', CFGP_NAME)
                                        .'</a>'); ?></strong></p>
                                 <?php else: ?>
                                     <p><strong><?php printf(__( 'Automatic IP address blacklist check is NOT ENABLED. If you want additional protection %s.', CFGP_NAME ),
                                        '<a href="'.admin_url('admin.php?page=cf-geoplugin-settings').'#spam-protection">'
                                            .__('enable it in settings', CFGP_NAME)
                                        .'</a>'); ?></strong></p>
                                 <?php endif; ?>
                                 
                                 <p style="color:#cc0000;"><?php _e( 'These options will remove all your content, template, design and display custom messages to your visitors.', CFGP_NAME ); ?></p>
                                 <?php submit_button(); ?>
                            </div>
                            
                            <div class="cfgp-tab-panel" id="defender-settings-page">
                            	<p><?php _e('Message that is displayed to a blocked visitor (HTML allowed)',CFGP_NAME); ?>:</p>
                                <div class="cfgp-form-group">
                                    <?php
                                        $settings = array( 'textarea_name'  => 'block_country_messages', 'editor_height' => 450, 'textarea_rows' => 30 );
                                        $block_country_messages = html_entity_decode( trim( CFGP_Options::get('block_country_messages') ) );
                                        if( empty( $block_country_messages ) )
                                        {
                                            $messages="<h1>Error</h1>
<h3>404 - Page not found</h3>
<p>We could not find the above page on our servers.</p>
<p>NOTE: This option is not saved!</p>";
                                            wp_editor( $messages, 'block_country_messages', $settings );
                                        }
                                        else
                                        {
                                            wp_editor( $block_country_messages, 'block_country_messages', $settings );
                                        }
                                    ?>
                                </div>
                                <?php submit_button(); ?>                           
                            </div>
                            
                    	</div>
                    </form>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
</div>
