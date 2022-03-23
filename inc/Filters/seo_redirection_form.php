<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }


add_action('cfgp/page/seo_redirection/response', function(){
	CFGP_SEO::response_error();
	if($_SERVER['REQUEST_METHOD'] !== 'POST') {
		CFGP_SEO::response_success();
	}
	
	if(CFGP_U::request_string('action') == 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		CFGP_SEO::response_success();
	}
});

add_action('cfgp/page/seo_redirection/form/content', function(){

	$ID = CFGP_U::request_int('id', 0);
	$get = CFGP_SEO::get($ID);
	if(!$get) {
		$get = (object)array(
			'url' => NULL,
			'country' => NULL,
			'region' => NULL,
			'city' => NULL,
			'postcode' => NULL,
			'http_code' => 302,
			'only_once' => 0,
			'active' => 1
		);
	}
	$select_country = CFGP_U::request_string('country',$get->country);
	$select_region = CFGP_U::request_string('region',$get->region);
	$select_city = CFGP_U::request_string('city',$get->city);
	$select_postcode = CFGP_U::request_string('postcode',$get->postcode);
	$http_code = CFGP_U::request_string('http_code', $get->http_code);
	$only_once = CFGP_U::request_int('only_once', $get->only_once);
	$redirect_enable = CFGP_U::request_int('redirect_enable', $get->active);
	$redirection_url = CFGP_U::request_string('url',$get->url);

?>
<div class="postbox">
	<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('SEO Redirection Global Params', CFGP_NAME); ?></span></h3><hr>
	<div class="inside">
    	<?php CFGP_Form::input('hidden', array('name'=>'id','value'=>$ID)); ?>
    	<table class="form-table cfgp-form-table cfgp-country-region-city-multiple-form" role="presentation" id="cfgp-new-seo-redirection">
        	<tbody>
                <tr>
                    <th scope="row" valign="top" class="cfgp-label"><label><?php _e('Enable this redirection', CFGP_NAME); ?></label></th>
                    <td valign="top"><?php
						CFGP_Form::radio(
							array(
								1 => __('Enable', CFGP_NAME),
								0 => __('Disable', CFGP_NAME)
							),
							array('name'=>'redirect_enable'),
							$redirect_enable
						);
					?></td>
                </tr>
                <tr>
                    <th scope="row" valign="top" class="cfgp-label"><label for="country"><?php _e('Select Country', CFGP_NAME); ?></label></th>
                    <td valign="top"><?php 
						CFGP_Form::select_countries(array('name'=>'country', 'class'=>'cfgp_select2'), $select_country);
					?></td>
                </tr>
                <tr>
                    <th scope="row" valign="top" class="cfgp-label"><label for="region"><?php _e('Select Region', CFGP_NAME); ?></label></th>
                    <td valign="top"><?php
						CFGP_Form::select_regions(array('name'=>'region', 'country_code' => $select_country, 'class'=>'cfgp_select2'), $select_region);
					?></td>
                </tr>
                <tr>
                    <th scope="row" valign="top" class="cfgp-label"><label for="city"><?php _e('Select City', CFGP_NAME); ?></label></th>
                    <td valign="top"><?php
						CFGP_Form::select_cities(array('name'=>'city', 'country_code' => $select_country, 'class'=>'cfgp_select2'), $select_city);
					?></td>
                </tr>
                <tr>
                    <th scope="row" valign="top" class="cfgp-label"><label for="postcode"><?php _e('Select Postcode', CFGP_NAME); ?></label></th>
                    <td valign="top"><?php
						CFGP_Form::select_postcodes(array('name'=>'postcode', 'class'=>'cfgp_select2'), $select_postcode);
					?></td>
                </tr>
                <tr>
                    <th scope="row" valign="top" class="cfgp-label"><label for="url"><?php _e('Redirect URL', CFGP_NAME); ?></label></th>
                    <td valign="top"><?php CFGP_Form::input('url', array('name'=>'url','value'=>$redirection_url, 'style'=>'width:100%;max-width:50%;')); ?></td>
                </tr>
                <tr>
                    <th scope="row" valign="top" class="cfgp-label"><label for="http_code"><?php _e('HTTP Code', CFGP_NAME); ?></label></th>
                    <td valign="top"><?php CFGP_Form::select_http_code(array('name'=>'http_code'), $http_code); ?></td>
                </tr>
                <tr>
                    <th scope="row" valign="top" class="cfgp-label"><label><?php _e('Redirection', CFGP_NAME); ?></label></th>
                    <td valign="top"><?php
						CFGP_Form::radio(
							array(
								1 => __('only once', CFGP_NAME),
								0 => __('always', CFGP_NAME)
							),
							array('name'=>'only_once'),
							$only_once
						);
					?></td>
                </tr>
            </tbody>
        </table>
        <p class="submit"><button type="submit" class="button button-primary"><?php _e('Save Redirection', CFGP_NAME); ?></button><?php if($ID): ?> <a href="<?php echo CFGP_U::admin_url('admin.php?page='.CFGP_NAME.'-seo-redirection'); ?>" class="button" style="float:right"><?php _e('Go back to list', CFGP_NAME); ?></a><?php endif; ?></p>
    </div>
</div>
<?php });

add_action('cfgp/page/seo_redirection/form', function(){
	$action = CFGP_U::request_string('action');	
?>
<div class="wrap wrap-cfgp" id="<?php echo sanitize_title($_GET['page']); ?>">
	<h1 class="wp-heading-inline"><i class="fa fa-location-arrow"></i> <?php
		if($action == 'edit') {
			_e('Edit SEO redirection', CFGP_NAME);
		} else {
			_e('Add new SEO redirection', CFGP_NAME);
		}
	?></h1>
	<?php 
		if($action == 'edit') {
			printf(
				'<a href="%s" class="page-title-action button-cfgeo-seo-new"><i class="fa fa-plus"></i> %s</a> ',
				CFGP_U::admin_url('admin.php?page=cf-geoplugin-seo-redirection&action=new&nonce='.wp_create_nonce(CFGP_NAME.'-seo-new')),
				__('New SEO redirection', CFGP_NAME)
			);
		}
	?>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">
            <form method="post">
                <div class="inner-sidebar" id="<?php echo CFGP_NAME; ?>-license-sidebar">
                    <div id="side-sortables" class="meta-box-sortables ui-sortable">
                        <?php
							do_action('cfgp/page/seo_redirection/form/sidebar');
							do_action('cfgp/page/seo_redirection/sidebar');
						?>
                    </div>
                </div>
    
                <div id="post-body">
                    <div id="post-body-content">
                        <?php do_action('cfgp/page/seo_redirection/form/content'); ?>
                    </div>
                </div>
            </form>
            <br class="clear">
        </div>
    </div>
</div>
<?php });