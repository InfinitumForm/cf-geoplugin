<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action('cfgp/page/seo_redirection/form/import', function(){ global $wpdb; ?>

<?php if( !CFGP_SEO_Table::table_exists() ) : ?>
<div class="notice notice-error"> 
	<p><?php printf(__('The database table "%s" not exists! You can try to reactivate the CF Geo Plugin to correct this error.', 'cf-geoplugin'), "<strong>{$wpdb->cfgp_seo_redirection}</strong>"); ?></p>
</div>
<?php endif; ?>

<div class="postbox">
	<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('SEO Redirection CSV Upload', 'cf-geoplugin'); ?></span></h3><hr>
	<div class="inside">
    	<p><?php _e('If you want to make large amounts of redirects easier, we give you this option. Here you can easily enter a thousand redirects by the rules you define in your CSV file with just a few clicks. Before proceeding with this, you need to be informed about the structure of the CSV file that we expect.', 'cf-geoplugin'); ?></p>
        <p><strong><?php _e('Please carefully follow this manual to avoid unnecessary problems and waste of time.', 'cf-geoplugin'); ?></strong></p>
        <p><?php _e('The file must be a standard comma separated CSV with exactly 8 columns. The order of the column is extremely important and its content is strict. If you do not follow the format and column order, CSV will be rejected.', 'cf-geoplugin'); ?></p>

        <dl>
            <dt>country</dt>
            <dd><?php _e('Country Code - standard 2 letter country code (example: RS)', 'cf-geoplugin'); ?></dd>
            <dt>region</dt>
            <dd><?php _e('Region Name (example: Belgrade)', 'cf-geoplugin'); ?></dd>
            <dt>city</dt>
            <dd><?php _e('City Name (example: Belgrade)', 'cf-geoplugin'); ?></dd>
            <dt>postcode</dt>
            <dd><?php _e('Postcode Name (example: 1210)', 'cf-geoplugin'); ?></dd>
            <dt>url</dt>
            <dd><?php _e('Redirect URL - valid URL format', 'cf-geoplugin'); ?></dd>
            <dt>http_code</dt>
            <dd><?php _e('HTTP Status Code - Accept 301, 302, 303 and 404', 'cf-geoplugin'); ?></dd>
            <dt>active</dt>
            <dd><?php _e('Active - Optional, accept integer (1-Enable, 0-Disable)', 'cf-geoplugin'); ?></dd>
            <dt>only_once</dt>
            <dd><?php _e('Redirect only once - Optional, accept integer (1-Enable, 0-Disable)', 'cf-geoplugin'); ?></dd>
        </dl>
        <?php if( CFGP_SEO_Table::table_exists() ) : ?>
        <p class="submit"><button type="button" class="button button-primary button-cfgeo-seo-import-csv" data-label="<i class='cfa cfa-upload'></i> <?php esc_attr_e('Click Here to Upload CSV', 'cf-geoplugin'); ?>" data-confirm="<?php esc_attr_e('Are you sure? Once you start the import you will not be able to stop it. You must know that this operation deletes all existing data and replaces it with new one. We strongly recommend that you export the existing data first and then continue with this operation.', 'cf-geoplugin'); ?>" data-nonce="<?php echo CFGP_U::request_string('nonce'); ?>" data-callback="<?php echo CFGP_U::admin_url('admin.php?page=cf-geoplugin-seo-redirection'); ?>"><i class="cfa cfa-upload"></i> <?php _e('Click Here to Upload CSV', 'cf-geoplugin'); ?></button> <?php echo (CFGP_U::has_seo_redirection() ? sprintf('<a aria="button" href="%s" class="button" style="float:right"><i class="cfa cfa-table"></i> %s</a> ', CFGP_U::admin_url('/admin.php?page=cf-geoplugin-seo-redirection&action=export&nonce='.wp_create_nonce(CFGP_NAME.'-seo-export-csv')), __('Export CSV', 'cf-geoplugin')) : ''); ?></p>
		<?php endif; ?>
    </div>
</div>
<?php });


add_action('cfgp/page/seo_redirection/import', function(){ ?>
<div class="wrap wrap-cfgp" id="<?php echo sanitize_title($_GET['page']); ?>">
	<h1 class="wp-heading-inline"><i class="cfa cfa-location-arrow"></i> <?php _e('SEO redirection - Upload CSV file', 'cf-geoplugin');?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">
            <form method="post">
                <div class="inner-sidebar" id="<?php echo esc_attr(CFGP_NAME); ?>-license-sidebar">
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
<script>;(function(jQ){jQ('#toplevel_page_cf-geoplugin-seo-redirection .wp-submenu').find('li:nth-child(4)').addClass('current');}(jQuery || window.jQuery));</script>
<?php });