<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action('cfgp/page/seo_redirection/table', function(){ ?>
<div class="wrap wrap-cfgp" id="<?php echo $_GET['page']; ?>">
	<h1 class="wp-heading-inline"><i class="fa fa-location-arrow"></i> <?php _e('SEO Redirection', CFGP_NAME); ?></h1>
    <?php printf(
		'<a href="%s" class="page-title-action button-cfgeo-seo-new"><i class="fa fa-plus"></i> %s</a> ',
		admin_url('admin.php?page='.CFGP_U::request_string('page').'&action=new&nonce='.wp_create_nonce(CFGP_NAME.'-seo-new')),
		__('New SEO redirection', CFGP_NAME)
	); ?>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff">
            <form method="get">
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php _e('Search Redirections', CFGP_NAME); ?>:</label>
                    <input type="search" id="post-search-input" name="s" value="<?php echo CFGP_U::request_string('s'); ?>">
                    <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Redirections', CFGP_NAME); ?>">
                    <input type="hidden" value="<?php echo CFGP_U::request_string('page'); ?>" name="page">
                    <input type="hidden" value="<?php echo wp_create_nonce(CFGP_NAME.'-seo-search') ?>" name="nonce">
                </p>
            </form>
            <form method="post">
            	<?php CFGP_SEO_Table::print();	?>
            </form>
            <br class="clear">
        </div>
    </div>
</div>
<?php });