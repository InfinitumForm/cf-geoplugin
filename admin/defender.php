<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Page CF Defender
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Goran Zivkovic
 *
**/

$CFGEO = $GLOBALS['CFGEO']; $CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
global $wp_version;
?>
<div class="clearfix"></div>
<div class="container-fluid" id="cf-geoplugin-page">
    <!-- HEADER -->
    <div class="row mt-3">
        <div class="col-12">
            <h1 class="h5 mt-3">
                <i class="fa fa-lock text-left"></i>
                <?php _e( 'Anti Spam Protection & Site Restriction', CFGP_NAME ); ?>
            </h1>
            <hr>
        </div>
    </div>
    <?php
    if( isset( $_POST ) && !empty( $_POST ) )
    {
        ?>
        <div class="row mt-3">
            <div class="col-12">
                <?php
                    // Chosen select for empty select does not return anything so I thought this should work
                    if( isset( $_GET['setting'] ) && $_GET['setting'] == 'general' )
                    {
                        if( !isset( $_POST['block_country'] ) )
                        {
                            $this->update_option( 'block_country', '' );
                        }
                        if( !isset( $_POST['block_region'] ) )
                        {
                            $this->update_option( 'block_region', '' );
                        }
                        if( !isset( $_POST['block_city'] ) )
                        {
                            $this->update_option( 'block_city', '' );
                        }
                    }

                    $updates = array();
                    foreach( $_POST as $key => $value )
                    {
                        if( $key == 'block_country' || $key=='block_region' || $key=='block_city' )
                        {
                            $value = implode( ']|[', $value );
                            $updated = $this->update_option( $key, esc_attr( $value ) );
                            $updates[] = (string) $updated[$key];
                        }
                        else
                        {
                            $updated = $this->update_option( $key, esc_attr( $value ) );
                            $updates[] = (string) $updated[$key];
                        }
                    }
                    if( in_array( "false", $updates ) !== false || count( $updates ) == 0 )
                    {
                        ?>
                            <div class="alert alert-danger" role="alert">
                                <?php _e('There is some error!',CFGP_NAME); ?>
                            </div>
                        <?php
                    }
                    else
                    {
                        ?>
                            <div class="alert alert-success" role="alert">
                                <?php _e('Settings are saved!',CFGP_NAME); ?>
                            </div>
                        <?php
                    }
                ?>
            </div>
        </div>
        <?php
    } 
    $active_general = 'active show';
    $active_page = '';
    if( $this->get( 'setting' ) === 'page' )
    {
        $active_general = '';
        $active_page = 'active show';
    }
    ?>
    <div class="row mt-3">
    	<div class="col-sm-9">
            <?php 
                do_action('page-cf-geoplugin-defender-before-tab'); 
                if( $this->get( 'test', 'bool' ) === true )
                {
                    die( wpautop( html_entity_decode( stripslashes( $this->get_option('block_country_messages') ) ) ) );
                    exit;
                }
            ?>
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link text-dark <?php echo $active_general; ?>" href="#general-defender" role="tab" data-toggle="tab"><span class="fa fa-wrench"></span> <?php _e( 'General Defender Settings', CFGP_NAME ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark <?php echo $active_page; ?>" href="#defender-page" role="tab" data-toggle="tab"><span class="fa fa-file"></span> <?php _e( 'Defender page', CFGP_NAME ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark" href="<?php echo CF_Geoplugin_Global::add_admin_url( 'admin.php?page='. $_GET['page'] .'&test=true' ); ?>" target="_blank"><span class="fa fa-desktop"></span> <?php _e( 'Defender test', CFGP_NAME ); ?></a>
                </li>
                <?php do_action('page-cf-geoplugin-defender-tab'); ?>
            </ul>
            <!-- Tap panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade in <?php echo $active_general; ?>" id="general-defender">
                	<div class="row">
                        <div class="col-12">
                            <?php
                                $blacklist_text = '<strong>'
									.sprintf(__( 'Automatic IP address blacklist check is NOT ENABLED. If you want additional protection %1$s', CFGP_NAME ),
									'<a href="'.CF_Geoplugin_Global::add_admin_url('admin.php?page=cf-geoplugin-settings#Spam_Protection').'">'
										.__('enable it in settings', CFGP_NAME)
									.'</a>')
								.'</strong>';
                                
								if( isset( $CF_GEOPLUGIN_OPTIONS['enable_spam_ip'] ) && $CF_GEOPLUGIN_OPTIONS['enable_spam_ip'] ) $blacklist_text = __( '<strong>Automatic IP Address Blacklist Check</strong> is enabled. All of these IPs are from safe source and most of them are bots and crawlers. Blackliested IPs will be automatically recognized and blocked. If you don\'t want this kind of protection disable it in plugin settings', CFGP_NAME );
                            ?>
                            <p><?php printf(__("With %s you can block the access from the specific IP, country, state and city to your site. Names of countries, states, regions or cities are not case sensitive, but the name must be entered correctly (in English) to get this feature work correctly. This feature is very safe and does not affect to SEO.",CFGP_NAME),'<strong>'.__("Anti Spam Protection",CFGP_NAME).'</strong>'); ?></p>
                        </div>
                        <div class="col-12">
                            <form method="post" enctype="multipart/form-data" action="<?php echo CF_Geoplugin_Global::add_admin_url( 'admin.php?page='. $_GET['page'] .'&settings-updated=true&setting=general'); ?>" target="_self" id="template-options-tab">
							
								<ul class="nav nav-tabs" id="general-defender-table" role="tablist">
									<li class="nav-item" role="presentation">
										<a class="nav-link text-dark active" id="general-defender-block-ip-tab" data-toggle="tab" href="#general-defender-block-ip" role="tab" aria-controls="general-defender-block-ip" aria-selected="true">
											<i class="fa fa-shield" aria-hidden="true"></i> <?php _e('IP Restriction',CFGP_NAME); ?>
										</a>
									</li>
									<li class="nav-item" role="presentation">
										<a class="nav-link text-dark" id="general-defender-block-country-tab" data-toggle="tab" href="#general-defender-block-country" role="tab" aria-controls="general-defender-block-country" aria-selected="false">
											<i class="fa fa-globe" aria-hidden="true"></i> <?php _e('Country Restriction',CFGP_NAME); ?>
										</a>
									</li>
									<li class="nav-item" role="presentation">
										<a class="nav-link text-dark" id="general-defender-block-regions-tab" data-toggle="tab" href="#general-defender-block-regions" role="tab" aria-controls="general-defender-block-regions" aria-selected="false">
											<i class="fa fa-map-marker" aria-hidden="true"></i> <?php _e('Region Restriction',CFGP_NAME); ?>
										</a>
									</li>
									<li class="nav-item" role="presentation">
										<a class="nav-link text-dark" id="general-defender-block-city-tab" data-toggle="tab" href="#general-defender-block-city" role="tab" aria-controls="general-defender-block-city" aria-selected="false">
											<i class="fa fa-building-o" aria-hidden="true"></i> <?php _e('City Restriction',CFGP_NAME); ?>
										</a>
									</li>
								</ul>
								<div class="tab-content" id="general-defender-table-content" style="margin-bottom:15px; border-bottom: 1px solid #dee2e6;">
									<div class="tab-pane fade show active" id="general-defender-block-ip" role="tabpanel" aria-labelledby="general-defender-block-ip-tab">
										<div class="row">
											<div class="col-12">
												<div class="form-group">
													<label for="block_ip"><?php _e('IP address separated by comma',CFGP_NAME); ?>:</label>
													<textarea class="form-control" id="block_ip" name="block_ip" rows="1" cols="5" style="min-height:115px"><?php echo $this->get_option('block_ip'); ?></textarea>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane fade" id="general-defender-block-country" role="tabpanel" aria-labelledby="general-defender-block-country-tab">
										<div class="row">
											<div class="col-12">
												<div class="form-group">
													<label for="block_country"><?php _e('Choose Countries',CFGP_NAME); ?>:</label>
													<?php
														if ( version_compare( $wp_version, '4.6', '>=' ) )
														{
															$all_countries = get_terms(array(
																'taxonomy'		=> 'cf-geoplugin-country',
																'hide_empty'	=> false
															));
														}
														else
														{
															$all_countries = $this->cf_geo_get_terms(array(
																'taxonomy'		=> 'cf-geoplugin-country',
																'hide_empty'	=> false
															));
														}
													?>
													<select class="chosen-select w-100" data-placeholder="<?php _e( 'Choose countries...', CFGP_NAME ); ?>" id="block_country" aria-describedby="countryHelp" id="block_country" name="block_country[]" multiple >
													<?php
														if( is_array( $all_countries ) && !empty( $all_countries ) )
														{
															$find = array_map( "trim", explode( "]|[", $this->get_option('block_country') ) );
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
													<br>
													<small id="countryHelp" class="form-text text-muted"><?php printf(__('To setup list of countries, you need to go in Geo Plugin -> %s',CFGP_NAME), '<a href="' . admin_url('edit-tags.php?taxonomy=cf-geoplugin-country&post_type=cf-geoplugin-banner') . '" target="_blank">' . __('Countries',CFGP_NAME) . '</a>'); ?></small>
													<br>
													<button style="font-size: 14px;" type="button" class="btn btn-light btn-small cfgp-select-all" data-target="block_country"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane fade" id="general-defender-block-regions" role="tabpanel" aria-labelledby="general-defender-block-regions-tab">
										<div class="row">
											<div class="col-12">
												<div class="form-group">
													<label for="block_region"><?php _e('Choose Regions',CFGP_NAME); ?>:</label>
													<?php
														if ( version_compare( $wp_version, '4.6', '>=' ) )
														{
															$all_regions = get_terms(array(
																'taxonomy'		=> 'cf-geoplugin-region',
																'hide_empty'	=> false
															));
														}
														else
														{
															$all_regions = $this->cf_geo_get_terms(array(
																'taxonomy'		=> 'cf-geoplugin-region',
																'hide_empty'	=> false
															));
														}
													?>
													<select class="chosen-select w-100" name="block_region[]" id="block_region" data-placeholder="<?php _e( 'Choose regions...', CFGP_NAME ); ?>" aria-describedby="regionHelp" multiple> 
													<?php
														if( is_array( $all_regions ) && !empty( $all_regions ) )
														{
															$find = array_map( "trim", explode( "]|[", $this->get_option('block_region') ) );

															foreach( $all_regions as $key => $region )
															{
																echo '<option id="'
																.$region->slug
																.'" value="'
																.$region->slug
																.'"'
																.(in_array($region->slug, $find)!==false?' selected':'')
																.'>'
																.$region->name . '</option>';
															}
														}
													?>
													</select>
													<br>
													<small id="countryHelp" class="form-text text-muted"><?php printf(__('To setup list of regions, you need to go in Geo Plugin -> %s',CFGP_NAME), '<a href="' . admin_url('edit-tags.php?taxonomy=cf-geoplugin-region&post_type=cf-geoplugin-banner') . '" target="_blank">' . __('Regions',CFGP_NAME) . '</a>'); ?></small>
													<br>
													<button style="font-size: 14px;" type="button" class="btn btn-light btn-small cfgp-select-all" data-target="block_region"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>
												</div>
											</div>
										</div>
									</div>
									<div class="tab-pane fade" id="general-defender-block-city" role="tabpanel" aria-labelledby="general-defender-block-city-tab">
										<div class="row">
											<div class="col-12">
												<div class="form-group">
													<label for="block_city"><?php _e('Choose Cities',CFGP_NAME); ?>:</label>
													<?php
														if ( version_compare( $wp_version, '4.6', '>=' ) )
														{
															$all_cities = get_terms(array(
																'taxonomy'		=> 'cf-geoplugin-city',
																'hide_empty'	=> false
															));
														}
														else
														{
															$all_cities = $this->cf_geo_get_terms(array(
																'taxonomy'		=> 'cf-geoplugin-city',
																'hide_empty'	=> false
															));
														}
													?>
													<select class="chosen-select w-100" name="block_city[]" id="block_city" data-placeholder="<?php _e( 'Choose cities...', CFGP_NAME ); ?>" aria-describedby="cityHelp"  multiple > 
													<?php
													if( is_array( $all_cities ) && !empty( $all_cities ) )
													{
														$find = array_map( "trim", explode( "]|[", $this->get_option('block_city') ) );

														foreach( $all_cities as $key => $city )
														{
															echo '<option id="'
															.$city->slug
															.'" value="'
															.$city->slug
															.'"'
															.(in_array($city->slug, $find)!==false?' selected':'')
															.'>'
															.$city->name . '</option>';
														}
													}
													?>
													</select>
													<br>
													<small id="countryHelp" class="form-text text-muted"><?php printf(__('To setup list of cities, you need to go in Geo Plugin -> %s',CFGP_NAME), '<a href="' . admin_url('edit-tags.php?taxonomy=cf-geoplugin-city&post_type=cf-geoplugin-banner') . '" target="_blank">' . __('Cities',CFGP_NAME) . '</a>'); ?></small>
													<br>
													<button style="font-size: 14px;" type="button" class="btn btn-light btn-small cfgp-select-all" data-target="block_city"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<p><?php echo $blacklist_text; ?></p>
								<p><?php _e( 'This options will remove all your content, template, design and display custom message to your visitors.', CFGP_NAME ); ?></p>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-info"><?php _e( 'Save / Update', CFGP_NAME ); ?></button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane fade <?php echo $active_page; ?>" id="defender-page">
                    <div class="row">
                        <div class="col-12">
                            <form method="post" enctype="multipart/form-data" action="<?php echo CF_Geoplugin_Global::add_admin_url( 'admin.php?page='. $_GET['page'] .'&settings-updated=true&setting=page' ); ?>" target="_self" id="template-options-tab">
                                <div class="form-group">
                                    <label for="block_country_message" ><?php _e('Message that is displayed to a blocked visitor (HTML allowed)',CFGP_NAME); ?>:</label>
                                    <?php
                                        $settings = array( 'textarea_name'  => 'block_country_messages', 'editor_height' => 450, 'textarea_rows' => 30 );
                                        $block_country_messages = html_entity_decode( trim( $this->get_option('block_country_messages') ) );
                                        if( empty( $block_country_messages ) )
                                        {
                                            $messages="
                                                <h1>Error</h1>
                                                <h3>404 - Page not found</h3>
                                                <p>We could not find the above page on our servers.</p>
                                                <p>NOTE: This option is not saved!</p>
                                            ";
                                            wp_editor( $messages, 'block_country_messages', $settings );
                                        }
                                        else
                                        {
                                            wp_editor( $block_country_messages, 'block_country_messages', $settings );
                                        }
                                    ?>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-info"><?php _e( 'Save / Update', CFGP_NAME ); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php do_action('page-cf-geoplugin-defender-after-tab'); ?>
        <div class="col-sm-3">
            <?php do_action('page-cf-geoplugin-defender-sidebar'); ?>
        </div>
    </div>
</div>