<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $cfgp_cache;

?>
<div class="wrap wrap-cfgp" id="<?php echo $_GET['page']; ?>">
	<h1 class="wp-heading-inline"><i class="fa fa-globe"></i> <?php _e('SEO Redirection', CFGP_NAME); ?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff">
        	<div id="post-body" class="metabox-holder columns-2">
            	<div id="post-body-content">
					Underconstruction!
                </div>
                <div id="postbox-container-1" class="postbox-container">
					<?php do_action('cfgp/page/seo_redirection/sidebar'); ?>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
</div>