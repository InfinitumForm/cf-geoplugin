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
		$this->add_action('cfgp/page/main_page/sidebar', 'statistic', 10);
		$this->add_action('cfgp/page/defender/sidebar', 'statistic', 10);
		$this->add_action('cfgp/page/google_map/sidebar', 'statistic', 10);
		$this->add_action('cfgp/page/seo_redirection/sidebar', 'statistic', 10);
		$this->add_action('cfgp/page/debug/sidebar', 'statistic', 10);
		$this->add_action('cfgp/page/settings/sidebar', 'statistic', 10);
	//	$this->add_action('cfgp/page/license/sidebar', 'statistic', 10);
		
		$this->add_action('cfgp/page/main_page/sidebar', 'rss_feed', 10);
	//	$this->add_action('cfgp/page/defender/sidebar', 'rss_feed', 10);
		$this->add_action('cfgp/page/google_map/sidebar', 'rss_feed', 10);
		$this->add_action('cfgp/page/seo_redirection/sidebar', 'rss_feed', 10);
		$this->add_action('cfgp/page/debug/sidebar', 'rss_feed', 10);
		$this->add_action('cfgp/page/settings/sidebar', 'rss_feed', 10);
	//	$this->add_action('cfgp/page/license/sidebar', 'rss_feed', 10);
		
		$this->add_action('cfgp/page/main_page/sidebar', 'sidebar_affiliate', 40);
		$this->add_action('cfgp/page/defender/sidebar', 'sidebar_affiliate', 40);
		$this->add_action('cfgp/page/google_map/sidebar', 'sidebar_affiliate', 40);
		$this->add_action('cfgp/page/seo_redirection/sidebar', 'sidebar_affiliate', 40);
		$this->add_action('cfgp/page/debug/sidebar', 'sidebar_affiliate', 40);
		$this->add_action('cfgp/page/settings/sidebar', 'sidebar_affiliate', 40);
		$this->add_action('cfgp/page/license/sidebar', 'sidebar_affiliate', 40);
		
		$this->add_action('cfgp/dashboard/widget/statistic', 'sidebar_statistic', 10);
		$this->add_action('cfgp/dashboard/widget/statistic', 'dashboard_footer', 10);
		
		$this->add_action('cfgp/dashboard/widget/feed', 'dashboard_feed', 10);
		
		$this->add_action('cfgp/sidebar_statistic/list/after/dashboard', 'sidebar_statistic_plugin_info', 10);
	}	
	
	/**
	 * RSS Feed sidebar
	 *
	 * @since    8.0.0
	 **/
	public function rss_feed(){
	//	delete_transient('cfgp-rss');
		$RSS = get_transient('cfgp-rss');
	?>
<div class="postbox">
	<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('Live News & info', CFGP_NAME); ?></span></h3><hr>
	<div class="inside<?php echo (empty($RSS) ? ' cfgp-load-rss-feed' : ''); ?>">
		<?php echo ($RSS ? $RSS : __('Loading...', CFGP_NAME)); ?>
	</div>
</div>
	<?php }


	/**
	 * Statistic sidebar container
	 *
	 * @since    8.0.0
	 **/
	public function statistic(){
	?>
<div class="postbox">
	<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('Statistic', CFGP_NAME); ?></span></h3><hr>
	<div class="inside">
		<?php $this->sidebar_statistic(); ?>
	</div>
</div>
	<?php }
	
	/**
	 * Statistic sidebar
	 *
	 * @since    8.0.0
	 **/
	public function sidebar_statistic(){
		$current_screen_base = get_current_screen()->base;
	?>
<ul id="cfgp-statistic">
	<?php do_action('cfgp/sidebar_statistic/list/before', $this); ?>
	<?php do_action("cfgp/sidebar_statistic/list/before/{$current_screen_base}", $this); ?>
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
			<?php if(CFGP_U::api('lookup') === 'lifetime') : ?>
				<p><?php _e('Congratulations, your license has provided you with a lifetime lookup.', CFGP_NAME); ?></p>
			<?php elseif(CFGP_U::api('lookup') === 'unlimited') : ?>
				<?php if($license_expire = CFGP_License::expire_date()) : ?>
					<p><?php _e('You have an unlimited lookup that you can use until:', CFGP_NAME); ?> <strong><?php echo $license_expire; ?></strong></p>
				<?php else: ?>
					<p><?php _e('You have an unlimited lookup.', CFGP_NAME); ?></p>
				<?php endif; ?>
			<?php else : ?>
				<?php if(is_numeric(CFGP_U::api('lookup')) && CFGP_U::api('lookup') > 0) : ?>
					<p><?php printf(__('You currently spent %1$d lookups of the %3$d lookups available. This means you have %2$d lookups left today.', CFGP_NAME), (CFGP_LIMIT-CFGP_U::api('lookup')), CFGP_U::api('lookup'), CFGP_LIMIT); ?></p>
					<?php if(CFGP_U::api('lookup') <= (CFGP_LIMIT/3)) : ?>
						<p style="color:#900"><?php _e('Your lookup expires soon, the site may be left without important functionality.', CFGP_NAME); ?></p>
					<?php endif; ?>
				<?php elseif(CFGP_U::api('lookup') == 0) : ?>
					<p style="color:#900"><?php _e('You spent the entire lookup. It will be available again the next day.', CFGP_NAME); ?></p>
				<?php endif; ?>
				<p><?php printf(
					__('If you want to have an %1$s, you need to %2$s.', CFGP_NAME),
					'<a href="<?php echo CFGP_STORE; ?>/documentation/quick-start/what-do-i-get-from-unlimited-license" target="_blank">'.__('unlimited lookup', CFGP_NAME).'</a>',
					'<a href="'.CFGP_U::admin_url('admin.php?page=cf-geoplugin-activate').'" target="_blank"><strong>'.__('activate the license', CFGP_NAME).'</strong></a>'
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
	<?php do_action('cfgp/sidebar_statistic/list/after', $this); ?>
	<?php do_action("cfgp/sidebar_statistic/list/after/{$current_screen_base}", $this); ?>
</ul>
	<?php }
	
	/**
	 * Digital Ocean sidebar
	 *
	 * @since    8.0.0
	 **/
	public function sidebar_affiliate(){ ?>
	<h4 style="text-align:center;"><?php _e('Special thanks to:', CFGP_NAME); ?></h4>
<a href="https://www.digitalocean.com/?refcode=a4160dafc356&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge" title="<?php esc_attr_e('CF Geo Plugin uses an API hosted on Digital Ocean servers. Get yours now!', CFGP_NAME); ?>" target="_blank"><img src="<?php echo CFGP_ASSETS; ?>/images/Logo-DigitalOcean.jpg" alt="<?php esc_attr_e('CF Geo Plugin Uses an API Hosted on Digital Ocean Cloud Servers. Get yours now!', CFGP_NAME); ?>" style="margin:0 auto 0 auto; display:block; width:100%; max-width:100%; height:auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%);" /></a>

<a href="https://portal.draxhost.com/?affid=1" title="<?php esc_attr_e('The CF Geo Plugin official site is hosted on Drax Host servers. Interested in affordable and secure hosting?', CFGP_NAME); ?>" target="_blank"><img src="<?php echo CFGP_ASSETS; ?>/images/Logo-Drax-Host.jpg" alt="<?php esc_attr_e('The CF Geo Plugin official site is hosted on Drax Host servers. Interested in affordable and secure hosting?', CFGP_NAME); ?>" style="margin:15px auto 0 auto; display:block; width:100%; max-width:100%; height:auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%);" /></a>

<a href="https://affiliates.nordvpn.com/publisher/#!/offer/15" title="<?php esc_attr_e('The CF Gep plugin recommends using Nord VPN for testing.', CFGP_NAME); ?>" class="affiliate-nordvpn" target="_blank"><img src="<?php echo CFGP_ASSETS; ?>/images/Logo-NordVPN.jpg" alt="<?php esc_attr_e('The CF Gep plugin recommends using Nord VPN for testing.', CFGP_NAME); ?>" style="margin:15px auto 0 auto; display:block; width:100%; max-width:100%; height:auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%);" /></a>
<?php /* ?>
<h4 style="text-align:center;"><?php _e('Powered by:', CFGP_NAME); ?></h4>
<a href="https://infinitumform.com/" title="<?php esc_attr_e('We have created many good projects, do you want to we create something for you?', CFGP_NAME); ?>" target="_blank"><img src="<?php echo CFGP_ASSETS; ?>/images/Logo-Infinitum-Form.jpg" alt="<?php esc_attr_e('We have created many good projects, do you want to we create something for you?', CFGP_NAME); ?>" style="margin:0 auto 0 auto; display:block; width:100%; max-width:100%; height:auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%);" /></a>
<?php */ ?>
	<?php }
	
	
	/**
	 * Dashboard news feed
	 *
	 * @since    8.0.0
	 **/
	public function dashboard_feed(){ $RSS = get_transient('cfgp-dashboard-rss'); ?>
	<div class="wordpress-news hide-if-no-js<?php echo (empty($RSS) ? ' cfgp-load-dashboard-rss-feed' : ''); ?>">
	<?php if($RSS) : ?>
		<?php echo $RSS; ?>
	<?php else : ?>
		<ul class="rss-widget">
			<li style="background-color:transparent;"><?php _e('Loading...', CFGP_NAME); ?></li>
		</ul>
	<?php endif; ?>
	</div>
	<div class="community-events-footer">
		<a href="<?php echo CFGP_STORE; ?>/category/announcements" target="_blank"><?php _e( 'Announcements', CFGP_NAME ); ?> <span class="screen-reader-text"><?php _e('(opens in a new tab)', CFGP_NAME); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
		| <a href="<?php echo CFGP_STORE; ?>/category/information" target="_blank"><?php _e( 'Information', CFGP_NAME ); ?> <span class="screen-reader-text">(opens in a new tab)</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
		| <a href="<?php echo CFGP_STORE; ?>/category/tutorial" target="_blank"><?php _e( 'Tutorial', CFGP_NAME ); ?> <span class="screen-reader-text"><?php _e('(opens in a new tab)', CFGP_NAME); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
	</div>
	<?php }
	
	
	/**
	 * Dashboard footer in sidebar
	 *
	 * @since    8.0.0
	 **/
	public function dashboard_footer(){ ?>
	<p class="community-events-footer" id="cf-geoplugin-dashboard-footer" style="text-align:center;">
		<a href="<?php echo CFGP_STORE; ?>/documentation/" target="_blank"><?php _e( 'Documentation', CFGP_NAME ); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', CFGP_NAME); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> 
		| <a href="<?php echo CFGP_STORE; ?>/pricing/" target="_blank"><?php _e( 'Pricing', CFGP_NAME ); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', CFGP_NAME); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> 
		| <a href="<?php echo CFGP_STORE; ?>/blog/" target="_blank"><?php _e( 'Blog', CFGP_NAME ); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', CFGP_NAME); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
	</p>
	<p class="community-events-footer" id="cf-geoplugin-dashboard-footer" style="text-align:center;">
		<a href="<?php echo CFGP_STORE; ?>/terms-and-conditions/" target="_blank"><?php _e( 'Terms & Conditions', CFGP_NAME ); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', CFGP_NAME); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
		| <a href="<?php echo CFGP_STORE; ?>/privacy-policy/" target="_blank"><?php _e( 'Privacy Policy', CFGP_NAME ); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', CFGP_NAME); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
		| <a href="<?php echo CFGP_STORE; ?>/cookie-policy/" target="_blank"><?php _e( 'Cookie Policy', CFGP_NAME ); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', CFGP_NAME); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
	</p>
	<p class="community-events-footer" id="cf-geoplugin-copyright" style="font-size:0.85em; text-align:center;">
		<?php printf(__('Copyright © %d-%d CF Geo Plugin. All rights reserved.', CFGP_NAME), 2015, date('Y')); ?>
	</p>
	<?php }
	
	/**
	 * Get plugin informations
	 *
	 * @since    8.0.0
	 **/
	public function sidebar_statistic_plugin_info(){
		$plugin = CFGP_U::plugin_info( array(
			'version' => true,
			'tested' => true,
			'sections' => true,
			'donate_link' => true,
			'downloadlink' => true,
			'downloaded' => true,
			'requires_php' => true,
			'requires' => true,
			'last_updated' => true,
			'homepage' => true
		), false, false );
		if( !$plugin || is_wp_error( $plugin ) ) return;
		
		$plugin_updated = version_compare(CFGP_VERSION, $plugin->version, '<');
	?>
<li class="cfgp-statistic-separator"></li>
<li class="cfgp-statistic-plugin-details">
	<h3><i class="fa fa-plug" aria-hidden="true"></i> <?php _e( 'CF Geo Plugin details', CFGP_NAME ); ?></h3>
	<ul>
		<li><strong><?php _e( 'Last Update', CFGP_NAME ); ?>:</strong> <span><?php echo date((get_option('date_format').' '.get_option('time_format')),strtotime($plugin->last_updated)); ?></span></li>
		<li><strong><?php _e( 'Homepage', CFGP_NAME ); ?>:</strong> <span><a href="<?php echo $plugin->homepage ?>" target="_blank"><?php echo $plugin->homepage ?></a></span></li>
		<li><strong><?php _e( 'WP Support', CFGP_NAME ); ?>:</strong> <span><?php
			if(version_compare(get_bloginfo('version'), $plugin->requires, '>='))
			{
				printf('<span class="text-success">' . __( 'Supported on WP version %s', CFGP_NAME ) . '</span>', get_bloginfo('version'));
			}
			else
			{
				printf('<span class="text-danger">' . __( 'Plugin require WordPress version %s or above!', CFGP_NAME ) . '</span>', $plugin->requires);
			}
		?></span></li>
		<li><strong><?php _e( 'PHP Support', CFGP_NAME ); ?>:</strong> <?php
			preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $match);
			if(version_compare(PHP_VERSION, $plugin->requires_php, '>='))
			{
				printf('<span class="text-success">' . __( 'Supported on PHP version %s', CFGP_NAME ) . '</span>', $match[0]);
			}
			else
			{
				printf('<span class="text-danger">' . __( 'Plugin not support PHP version %1$s. Please use PHP vesion %2$s or above.', CFGP_NAME ) . '</span>', PHP_VERSION, $plugin->requires_php);
			}
		?></li>
	</ul>
</li>
<?php if($plugin_updated) : ?>
<li class="cfgp-statistic-separator"></li>
<li class="cfgp-statistic-plugin-details changelog has-update">
	<h3><i class="fa fa-code-fork" aria-hidden="true"></i> <?php printf(__( 'NEW version is available - CF Geo Plugin ver.%s', CFGP_NAME ), $plugin->version); ?></h3>
	<?php
	preg_match('@<h4>' . str_replace('.','\.',$plugin->version) . '</h4>.*?(<ul>(.*?)</ul>)@si', $plugin->sections['changelog'], $version_details, PREG_OFFSET_CAPTURE);
	if(isset($version_details[1]) && isset($version_details[1][0])) {
		echo str_replace('<ul>', '<ul class="cfgp-statistic-plugin-changelog">', $version_details[1][0]);
	} else {
		_e( 'There was error in fetching plugin data.', CFGP_NAME );
	}
	if(!is_multisite()) : ?>
		<br><a href="<?php echo self_admin_url('plugin-install.php?tab=plugin-information&plugin=cf-geoplugin&TB_iframe=true&width=600&height=550'); ?>"  class="open-plugin-details-modal button button-primary "><?php _e( 'Download new version NOW', CFGP_NAME ); ?></a>
	<?php endif; ?>
</li>
<?php endif; ?>
	<?php }
	
	/**
	 * Show status icon for the runtime
	 *
	 * @since    7.0.0
	 **/
	public function cfgp_runtime_status_icon($runtime, $class='')
	{
		
		if(!empty($class)) {
			$class = ' ' . $class;
		}
		
		if(floatval($runtime) <= 0.1) {
			echo '<span class="fa fa-battery-full incomparable'.$class.'" title="'.esc_attr__('Incomparable',CFGP_NAME).'"></span> <span class="cfgp-statistic-label incomparable">'.__('Incomparable',CFGP_NAME).'</span>';
		}
		else if(floatval($runtime) <= 0.5){
			echo '<span class="fa fa-battery-full exellent'.$class.'" title="'.esc_attr__('Exellent',CFGP_NAME).'"></span> <span class="cfgp-statistic-label exellent">'.__('Exellent',CFGP_NAME).'</span>';
		}
		else if(floatval($runtime) <= 0.8){
			echo '<span class="fa fa-battery-three-quarters perfect'.$class.'" title="'.esc_attr__('Perfect',CFGP_NAME).'"></span> <span class="cfgp-statistic-label perfect">'.__('Perfect',CFGP_NAME).'</span>';
		}
		else if(floatval($runtime) <= 1.2){
			echo '<span class="fa fa-battery-half good'.$class.'" title="'.esc_attr__('Good',CFGP_NAME).'"></span> <span class="cfgp-statistic-label good">'.__('Good',CFGP_NAME).'</span>';
		}
		else if(floatval($runtime) <= 1.5){
			echo '<span class="fa fa-battery-quarter week'.$class.'" title="'.esc_attr__('Week',CFGP_NAME).'"></span> <span class="cfgp-statistic-label week">'.__('Week',CFGP_NAME).'</span>';
		}
		else {
			echo '<span class="fa fa-battery-empty bad'.$class.'" title="'.esc_attr__('Bad',CFGP_NAME).'"></span> <span class="cfgp-statistic-label bad">'.__('Bad',CFGP_NAME).'</span>';
		}
	}
	
	/**
	 * Lookup status icon for the runtime
	 *
	 * @since    7.0.0
	 **/
	public function cfgp_lookup_status_icon($lookup, $class='')
	{
		if($lookup === 'unlimited' || $lookup === 'lifetime'){
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