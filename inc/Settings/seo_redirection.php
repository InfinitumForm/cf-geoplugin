<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $cfgp_cache;

?>
<div class="wrap wrap-cfgp" id="<?php echo $_GET['page']; ?>">
	<h1 class="wp-heading-inline"><i class="fa fa-location-arrow"></i> <?php _e('SEO Redirection', CFGP_NAME); ?></h1>
    <?php printf('<button type="button" class="page-title-action button-cfgeo-seo-new"><i class="fa fa-plus"></i> %s</button> ', __('New SEO redirection', CFGP_NAME)); ?>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff">
            <?php CFGP_SEO_Table::print();	?>
            <br class="clear">
        </div>
    </div>
</div>