<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $cfgp_cache;

?>
<div class="wrap wrap-cfgp" id="<?php echo $_GET['page']; ?>">
	<h1 class="wp-heading-inline"><i class="fa fa-globe"></i> <?php _e('Site protection', CFGP_NAME); ?></h1>
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
					Underconstruction!
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
</div>
