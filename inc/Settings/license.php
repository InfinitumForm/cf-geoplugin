<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

do_action('cfgp/page/license/save');

?>
<div class="wrap cfgp-wrap" id="<?php echo $_GET['page']; ?>">
	<h1 class="wp-heading-inline"><i class="cfa cfa-trophy"></i> <?php
		if( CFGP_U::api('available_lookup') == 'lifetime' ){
			_e('Congratulations, you have a lifetime lookup!', CFGP_NAME);
		} else if(CFGP_License::activated()) {
			printf(__('Your license is successfully active until %s', CFGP_NAME), CFGP_License::expire_date());
		} else {
			_e('Select the desired license and activate the plugin', CFGP_NAME);
		}
	?></h1>
    <hr class="wp-header-end">
    <div id="post">
    	<div id="poststuff" class="metabox-holder has-right-sidebar">
        	<div id="post-body">
            	<div id="post-body-content">
					<?php do_action('cfgp/page/license/content'); ?>
                </div>
            </div>
			
			<div class="inner-sidebar" id="<?php echo CFGP_NAME; ?>-license-sidebar">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php do_action('cfgp/page/license/sidebar'); ?>
				</div>
			</div>
			
            <br class="clear">
        </div>
    </div>
</div>
