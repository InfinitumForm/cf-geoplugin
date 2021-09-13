<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }


add_action('cfgp/page/seo_redirection/form/import', function(){ ?>
<div class="postbox">
	<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('SEO Redirection CSV Upload', CFGP_NAME); ?></span></h3><hr>
	<div class="inside">
    	<p><?php _e('If you want to make large amounts of redirects easier, we give you this option. Here you can easily enter a thousand redirects by the rules you define in your CSV file with just a few clicks. Before proceeding with this, you need to be informed about the structure of the CSV file that we expect.', CFGP_NAME); ?></p>
        <p><strong><?php _e('Please carefully follow this manual to avoid unnecessary problems and waste of time.', CFGP_NAME); ?></strong></p>
        <p><?php _e('The file must be a standard comma separated CSV with exactly 8 columns. The order of the column is extremely important and its content is strict. If you do not follow the format and column order, CSV will be rejected.', CFGP_NAME); ?></p>

        <dl>
            <dt>country</dt>
            <dd><?php _e('Country Code - standard 2 letter country code (example: RS)', CFGP_NAME); ?></dd>
            <dt>region</dt>
            <dd><?php _e('Region Name (example: Belgrade)', CFGP_NAME); ?></dd>
            <dt>city</dt>
            <dd><?php _e('City Name (example: Belgrade)', CFGP_NAME); ?></dd>
            <dt>postcode</dt>
            <dd><?php _e('Postcode Name (example: 1210)', CFGP_NAME); ?></dd>
            <dt>url</dt>
            <dd><?php _e('Redirect URL - valid URL format', CFGP_NAME); ?></dd>
            <dt>http_code</dt>
            <dd><?php _e('HTTP Status Code - Accept 301, 302, 303 and 404', CFGP_NAME); ?></dd>
            <dt>active</dt>
            <dd><?php _e('Active - Optional, accept integer (1-Enable, 0-Disable)', CFGP_NAME); ?></dd>
            <dt>only_once</dt>
            <dd><?php _e('Redirect only once - Optional, accept integer (1-Enable, 0-Disable)', CFGP_NAME); ?></dd>
        </dl>
        
        <p class="submit"><button type="button" class="button button-primary button-cfgeo-seo-import-csv" data-label="<i class='fa fa-upload'></i> <?php esc_attr_e('Click Here to Upload CSV', CFGP_NAME); ?>" data-confirm="<?php esc_attr_e('Are you sure? Once you start the import you will not be able to stop it. You must know that this operation deletes all existing data and replaces it with new one. We strongly recommend that you export the existing data first and then continue with this operation.', CFGP_NAME); ?>" data-nonce="<?php echo CFGP_U::request_string('nonce'); ?>" data-callback="<?php echo admin_url('admin.php?page=cf-geoplugin-seo-redirection'); ?>"><i class="fa fa-upload"></i> <?php _e('Click Here to Upload CSV', CFGP_NAME); ?></button> <?php printf('<a aria="button" href="%s" class="button" style="float:right"><i class="fa fa-table"></i> %s</a> ', admin_url('admin.php?page='.CFGP_U::request_string('page').'&action=export&nonce='.wp_create_nonce(CFGP_NAME.'-seo-export-csv')), __('Export CSV', CFGP_NAME)); ?></p>
    </div>
</div>
<?php });


add_action('cfgp/page/seo_redirection/import', function(){ ?>
<div class="wrap wrap-cfgp" id="<?php echo $_GET['page']; ?>">
	<h1 class="wp-heading-inline"><i class="fa fa-location-arrow"></i> <?php _e('SEO redirection - Upload CSV file', CFGP_NAME);?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">
            <form method="post">
                <div class="inner-sidebar" id="<?php echo CFGP_NAME; ?>-license-sidebar">
                    <div id="side-sortables" class="meta-box-sortables ui-sortable">
                        <?php
							do_action('cfgp/page/seo_redirection/form/sidebar/import');
							do_action('cfgp/page/seo_redirection/sidebar');
						?>
                    </div>
                </div>
    
                <div id="post-body">
                    <div id="post-body-content">
                        <?php do_action('cfgp/page/seo_redirection/form/import'); ?>
                    </div>
                </div>
            </form>
            <br class="clear">
        </div>
    </div>
</div>
<?php });