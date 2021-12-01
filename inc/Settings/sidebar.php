<?php
/**
 * Sidebars
 *
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Sidebar')) :
class CFGP_Sidebar extends CFGP_Global {
	
	function __construct(){
		$this->add_action('cfgp/page/main_page/sidebar', 'statistic', 30);
		$this->add_action('cfgp/page/defender/sidebar', 'statistic', 30);
		$this->add_action('cfgp/page/google_map/sidebar', 'statistic', 30);
		$this->add_action('cfgp/page/seo_redirection/sidebar', 'statistic', 30);
		$this->add_action('cfgp/page/debug/sidebar', 'statistic', 30);
		$this->add_action('cfgp/page/settings/sidebar', 'statistic', 30);
		$this->add_action('cfgp/page/license/sidebar', 'statistic', 30);
	}	
	
	/**
	 * Statistic sidebar
	 *
	 * @since    8.0.0
	 **/
	public function statistic(){
	?>
<div class="postbox">
	<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('Statistic', CFGP_NAME); ?></span></h3><hr>
	<div class="inside">
		<ul id="cfgp-statistic">
        	<li class="cfgp-statistic-address">
            	<?php if(CFGP_U::api('status') == 505) : ?>
                	<h3><span class="fa fa-close"></span> <?php _e('ERROR!',CFGP_NAME); ?></h3>
                	<p><?php _e('API no longer supports this version of CF Geo Plugin',CFGP_NAME); ?></p>
				<?php elseif(CFGP_U::api('status') == 417) : ?>
                	<h3><span class="fa fa-close"></span> <?php _e('NOT VALID!',CFGP_NAME); ?></h3>
                	<p><?php _e('Your IP address is not valid or is in the private range.',CFGP_NAME); ?></p>
				<?php elseif(CFGP_U::api('status') == 403) : ?>
                	<h3><span class="fa fa-ban"></span> <?php _e('BANNED!',CFGP_NAME); ?></h3>
                	<p><?php _e('Your domain is banned!',CFGP_NAME); ?></p>
				<?php elseif(CFGP_U::api('status') == 402) : ?>
                	<h3><span class="fa fa-ban"></span> <?php _e('API is limited',CFGP_NAME); ?></h3>
                	<p><?php _e('No Information',CFGP_NAME); ?></p>
                <?php elseif(CFGP_U::api('status') == 401) : ?>
                	<h3><span class="fa fa-ban"></span> <?php _e('DISABLED!',CFGP_NAME); ?></h3>
                	<p><?php _e('The API key is disabled because of unauthorized use!',CFGP_NAME); ?></p>
                <?php elseif(CFGP_U::api('status') == 200) : ?>
                    <h3>
                    <?php
						if($flag = CFGP_U::admin_country_flag(CFGP_U::api('country_code'))) {
							echo $flag;
						} else {
							echo '<span class="fa fa-globe"></span>';
						}
					?> <?php echo CFGP_U::api('ip'); ?> (IPv<?php echo CFGP_U::api('ip_version'); ?>)</h3>
                    <p><?php echo CFGP_U::api('address'); ?></p>
				<?php else : ?>
                	<h3><span class="fa fa-close"></span> <?php _e('ERROR!',CFGP_NAME); ?></h3>
                	<p><?php _e('There was an error communicating with the server.',CFGP_NAME); ?></p>
				<?php endif; ?>
            </li>
            <li class="cfgp-statistic-limit">
                <?php if(in_array(CFGP_U::api('status'), array(200,402))) : ?>
                	<h3><?php $this->cfgp_lookup_status_icon(CFGP_U::api('lookup')); ?> <?php _e('Lookup', CFGP_NAME); ?></h3>
					<?php if(CFGP_U::api('lookup') === 'unlimited' && $license_expire = CFGP_License::expire_date()) : ?>
                        <p><?php _e('Congratulations, you have an unlimited lookup that you can use until:', CFGP_NAME); ?> <strong><?php echo $license_expire; ?></strong></p>
                    <?php elseif(CFGP_U::api('lookup') === 'unlimited') : ?>
                        <p><?php _e('Congratulations, your license has provided you with a lifetime lookup.', CFGP_NAME); ?></p>
                    <?php else : ?>
                        <?php if(CFGP_U::api('lookup') > 0) : ?>
                            <p><?php printf(__('You currently spent %1$d lookup of the %2$d lookup available.', CFGP_NAME), (CFGP_LIMIT-CFGP_U::api('lookup')), CFGP_U::api('lookup')); ?></p>
                            <?php if(CFGP_U::api('lookup') <= (CFGP_LIMIT/3)) : ?>
                                <p style="color:#900"><?php _e('Your lookup expires soon, the site may be left without important functionality.', CFGP_NAME); ?></p>
                            <?php endif; ?>
                        <?php elseif(CFGP_U::api('lookup') == 0) : ?>
                            <p style="color:#900"><?php _e('You spent the entire lookup. It will be available again the next day.', CFGP_NAME); ?></p>
                        <?php endif; ?>
                        <p><?php printf(
                            __('If you want to have an %1$s, you need to %2$s.', CFGP_NAME),
                            '<a href="https://cfgeoplugin.com/documentation/quick-start/what-do-i-get-from-unlimited-license" target="_blank">'.__('unlimited lookup', CFGP_NAME).'</a>',
                            '<a href="'.admin_url('admin.php?page=cf-geoplugin-activate').'" target="_blank"><strong>'.__('activate the license', CFGP_NAME).'</strong></a>'
                        ); ?></p>
                    <?php endif; ?>
                
                <?php else : ?>
                	<h3><?php $this->cfgp_lookup_status_icon(0); ?> <?php _e('Lookup', CFGP_NAME); ?></h3>
                	<p style="color:#900"><?php _e('Lookup not available.', CFGP_NAME); ?></p>
				<?php endif; ?>
            </li>
            <li class="cfgp-statistic-quality">
            	<h4><?php _e('Quality', CFGP_NAME); ?> <?php $this->cfgp_runtime_status_icon(CFGP_U::api('runtime')); ?> (<?php echo number_format((float)CFGP_U::api('runtime'), 2, '.', ''); ?>s)</h4>
            </li>
        </ul>
	</div>
</div>
<a href="https://www.digitalocean.com/?refcode=a4160dafc356&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge" title="<?php esc_attr_e('CF Geo Plugin uses an API hosted on Digital Ocean servers. Get yours now!', CFGP_NAME); ?>" target="_blank"><img src="https://web-platforms.sfo2.digitaloceanspaces.com/WWW/Badge%202.svg" alt="<?php esc_attr_e('CF Geo Plugin Uses an API Hosted on Digital Ocean Cloud Servers. Get yours now!', CFGP_NAME); ?>" style="margin:0 auto 0 auto; display:block; width:100%; max-width:100%; height:auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%);" /></a>
	<?php }
	
	/**
	 * Show status icon for the runtime
	 *
	 * @since    7.0.0
	 **/
	public function cfgp_runtime_status_icon($runtime, $class='')
	{
		if(round($runtime)<=1){
			echo '<span class="fa fa-battery-full '.$class.'" title="'.__('Exellent',CFGP_NAME).'"></span> <span class="cfgp-statistic-label">'.__('Exellent',CFGP_NAME).'</span>';
		}
		else if(round($runtime) == 2){
			echo '<span class="fa fa-battery-three-quarters '.$class.'" title="'.__('Perfect',CFGP_NAME).'"></span> <span class="cfgp-statistic-label">'.__('Perfect',CFGP_NAME).'</span>';
		}
		else if(round($runtime) == 3){
			echo '<span class="fa fa-battery-half '.$class.'" title="'.__('Good',CFGP_NAME).'"></span> <span class="cfgp-statistic-label">'.__('Good',CFGP_NAME).'</span>';
		}
		else if(round($runtime) == 4){
			echo '<span class="fa fa-battery-quarter '.$class.'" title="'.__('Week',CFGP_NAME).'"></span> <span class="cfgp-statistic-label">'.__('Week',CFGP_NAME).'</span>';
		}
		else if(round($runtime) >= 5){
			echo '<span class="fa fa-battery-empty '.$class.'" title="'.__('Bad',CFGP_NAME).'"></span> <span class="cfgp-statistic-label">'.__('Bad',CFGP_NAME).'</span>';
		}
	}
	
	/**
	 * Lookup status icon for the runtime
	 *
	 * @since    7.0.0
	 **/
	public function cfgp_lookup_status_icon($lookup, $class='')
	{
		if($lookup === 'unlimited'){
			echo '<span class="fa fa-check '.$class.'" title="'.__('UNLIMITED',CFGP_NAME).'"></span>';
		}
		else if($lookup == 0){
			echo '<span class="fa fa-ban '.$class.'" title="'.__('EXPIRED',CFGP_NAME).'"></span>';
		}
		else if($lookup <= CFGP_LIMIT && $lookup > (CFGP_LIMIT/2)){
			echo '<span class="fa fa-hourglass-start '.$class.'" title="'.__('Available',CFGP_NAME).' '.$lookup.'"></span>';
		}
		else if($lookup <= (CFGP_LIMIT/2) && $lookup > (CFGP_LIMIT/3)){
			echo '<span class="fa fa-hourglass-halp '.$class.'" title="'.__('Available',CFGP_NAME).' '.$lookup.'"></span>';
		}
		else if($lookup <= (CFGP_LIMIT/3)){
			echo '<span class="fa fa-hourglass-end '.$class.'" title="'.__('Available',CFGP_NAME).' '.$lookup.'"></span>';
		}
	}
	
	public static function instance() {
		
		if(!is_admin()) {
			return;
		}
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}
}
endif;