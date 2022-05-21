<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action('cfgp/page/seo_redirection/table', function(){ global $wpdb; ?>
<?php if( !CFGP_SEO_Table::table_exists() ) : ?>
<div class="notice notice-error"> 
	<p><?php printf(__('The database table "%s" not exists! You can try to reactivate the WordPress Geo Plugin to correct this error.', CFGP_NAME), "<strong>{$wpdb->cfgp_seo_redirection}</strong>"); ?></p>
</div>
<?php endif; ?>
<div class="wrap wrap-cfgp" id="<?php echo sanitize_title($_GET['page']); ?>">
	<h1 class="wp-heading-inline"><i class="cfa cfa-location-arrow"></i> <?php _e('SEO Redirection', CFGP_NAME); ?></h1>
    <?php printf(
		'<a href="%s" class="page-title-action button-cfgeo-seo-new"><i class="cfa cfa-plus"></i> %s</a> ',
		CFGP_U::admin_url('/admin.php?page=cf-geoplugin-seo-redirection&action=new&nonce='.wp_create_nonce(CFGP_NAME.'-seo-new')),
		__('New SEO redirection', CFGP_NAME)
	); ?>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff">
			<?php CFGP_SEO_Table::get_filter_links(); ?>
            <form method="get" id="seo-redirection-table-search">
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php _e('Search Redirections', CFGP_NAME); ?>:</label>
                    <input type="search" id="post-search-input" name="s" value="<?php echo CFGP_U::request_string('s'); ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Redirections', CFGP_NAME); ?>">
                    <input type="hidden" value="<?php echo CFGP_U::request_string('page'); ?>" name="page">
                    <input type="hidden" value="<?php echo CFGP_U::request_string('filter'); ?>" name="filter">
					<input type="hidden" value="<?php echo wp_create_nonce(CFGP_NAME.'-seo-search') ?>" name="_wpnonce">
                </p>
            </form>
            <form method="post" id="seo-redirection-table-form">
            	<?php CFGP_SEO_Table::print();	?>
            </form>
            <br class="clear">
        </div>
    </div>
</div>
<script>;(function(jQ){jQ('#toplevel_page_cf-geoplugin-seo-redirection .wp-submenu').find('.wp-first-item').addClass('current');}(jQuery || window.jQuery));</script>
<?php });