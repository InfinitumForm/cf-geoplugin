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
	//	CFGP_DB_Cache::delete('cfgp-rss');
		$RSS = CFGP_DB_Cache::get('cfgp-rss');
	?>
<div class="postbox">
	<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('Live News & info', 'cf-geoplugin'); ?></span></h3><hr>
	<div class="inside<?php echo (empty($RSS) ? ' cfgp-load-rss-feed' : ''); ?>">
		<?php echo ($RSS ? $RSS : __('Loading...', 'cf-geoplugin')); ?>
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
	<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('Statistic', 'cf-geoplugin'); ?></span></h3><hr>
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
			<h3><span class="cfa cfa-close"></span> <?php _e('ERROR!', 'cf-geoplugin'); ?></h3>
			<p><?php _e('API no longer supports this version of CF Geo Plugin', 'cf-geoplugin'); ?></p>
		<?php elseif(CFGP_U::api('status') == 417) : ?>
			<h3><span class="cfa cfa-close"></span> <?php _e('NOT VALID!', 'cf-geoplugin'); ?></h3>
			<p><?php _e('Your IP address is not valid or is in the private range.', 'cf-geoplugin'); ?></p>
		<?php elseif(CFGP_U::api('status') == 403) : ?>
			<h3><span class="cfa cfa-ban"></span> <?php _e('BANNED!', 'cf-geoplugin'); ?></h3>
			<p><?php _e('Your domain is banned!', 'cf-geoplugin'); ?></p>
		<?php elseif(CFGP_U::api('status') == 402) : ?>
			<h3><span class="cfa cfa-ban"></span> <?php _e('API is limited', 'cf-geoplugin'); ?></h3>
			<p><?php _e('No Information', 'cf-geoplugin'); ?></p>
		<?php elseif(CFGP_U::api('status') == 401) : ?>
			<h3><span class="cfa cfa-ban"></span> <?php _e('DISABLED!', 'cf-geoplugin'); ?></h3>
			<p><?php _e('The API key is disabled because of unauthorized use!', 'cf-geoplugin'); ?></p>
		<?php elseif(CFGP_U::api('status') == 200) : ?>
			<h3>
			<?php
				if($flag = CFGP_U::admin_country_flag(CFGP_U::api('country_code'))) {
					echo wp_kses_post($flag);
				} else {
					echo '<span class="cfa cfa-globe"></span>';
				}
			?> <?php echo esc_html(CFGP_U::api('ip')); ?> (IPv<?php echo esc_html(CFGP_U::api('ip_version')); ?>)</h3>
			<p><?php echo esc_html(CFGP_U::api('address')); ?></p>
		<?php else : ?>
			<h3><span class="cfa cfa-close"></span> <?php _e('ERROR!', 'cf-geoplugin'); ?></h3>
			<p><?php _e('There was an error communicating with the server.', 'cf-geoplugin'); ?></p>
		<?php endif; ?>
	</li>
	<li class="cfgp-statistic-limit">
		<?php if(in_array(CFGP_U::api('status'), array(200,402))) : ?>
			<h3><?php $this->cfgp_lookup_status_icon(CFGP_U::api('available_lookup')); ?> <?php _e('Lookup', 'cf-geoplugin'); ?></h3>
			<?php if(CFGP_U::api('available_lookup') === 'lifetime') : ?>
				<p><?php _e('Congratulations, your license has provided you with a lifetime lookup.', 'cf-geoplugin'); ?></p>
			<?php elseif(CFGP_U::api('available_lookup') === 'unlimited') : ?>
				<?php if($license_expire = CFGP_License::expire_date()) : ?>
					<p><?php _e('You have an unlimited lookup that you can use until:', 'cf-geoplugin'); ?> <strong><?php echo esc_html($license_expire); ?></strong></p>
				<?php else: ?>
					<p><?php _e('You have an unlimited lookup.', 'cf-geoplugin'); ?></p>
				<?php endif; ?>
			<?php else : ?>
				<?php if(is_numeric(CFGP_U::api('available_lookup')) && CFGP_U::api('available_lookup') > 0) : ?>
					<p><?php printf(__('You currently spent %1$d lookups of the %3$d lookups available. This means you have %2$d lookups left today.', 'cf-geoplugin'), (CFGP_LIMIT-CFGP_U::api('available_lookup')), CFGP_U::api('available_lookup'), CFGP_LIMIT); ?></p>
					<?php if(CFGP_U::api('available_lookup') <= (CFGP_LIMIT/3)) : ?>
						<p style="color:#900"><?php _e('Your lookup expires soon, the site may be left without important functionality.', 'cf-geoplugin'); ?></p>
					<?php endif; ?>
				<?php elseif(CFGP_U::api('available_lookup') == 0) : ?>
					<p style="color:#900"><?php _e('You spent the entire lookup. It will be available again the next day.', 'cf-geoplugin'); ?></p>
				<?php endif; ?>
				<p><?php printf(
					__('If you want to have an %1$s, you need to %2$s.', 'cf-geoplugin'),
					'<a href="<?php echo esc_url(CFGP_STORE); ?>/documentation/quick-start/what-do-i-get-from-unlimited-license" target="_blank">'.__('unlimited lookup', 'cf-geoplugin').'</a>',
					'<a href="'.CFGP_U::admin_url('admin.php?page=cf-geoplugin-activate').'" target="_blank"><strong>'.__('activate the license', 'cf-geoplugin').'</strong></a>'
				); ?></p>
			<?php endif; ?>
		
		<?php else : ?>
			<h3><?php $this->cfgp_lookup_status_icon(0); ?> <?php _e('Lookup', 'cf-geoplugin'); ?></h3>
			<p style="color:#900"><?php _e('Lookup not available.', 'cf-geoplugin'); ?></p>
		<?php endif; ?>
	</li>
	<li class="cfgp-statistic-quality">
		<h4><?php _e('Quality', 'cf-geoplugin'); ?> <?php $this->cfgp_runtime_status_icon(CFGP_U::api('runtime')); ?> (<?php echo number_format((float)CFGP_U::api('runtime'), 2, '.', ''); ?>s)</h4>
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
	<h4 style="text-align:center;"><?php _e('Affiliates discounts up to 60%:', 'cf-geoplugin'); ?></h4>
<a href="https://www.digitalocean.com/?refcode=a4160dafc356&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge" title="<?php esc_attr_e('CF Geo Plugin uses an API hosted on Digital Ocean servers. Get yours now!', 'cf-geoplugin'); ?>" target="_blank"><img src="<?php echo esc_url(CFGP_ASSETS); ?>/images/Logo-DigitalOcean.jpg" alt="<?php esc_attr_e('CF Geo Plugin Uses an API Hosted on Digital Ocean Cloud Servers. Get yours now!', 'cf-geoplugin'); ?>" style="margin:0 auto 0 auto; display:block; width:100%; max-width:100%; height:auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%);" /></a>

<a href="https://portal.draxhost.com/?affid=1" title="<?php esc_attr_e('The CF Geo Plugin official site is hosted on Drax Host servers. Interested in affordable and secure hosting?', 'cf-geoplugin'); ?>" target="_blank"><img src="<?php echo esc_url(CFGP_ASSETS); ?>/images/Logo-Drax-Host.jpg" alt="<?php esc_attr_e('The CF Geo Plugin official site is hosted on Drax Host servers. Interested in affordable and secure hosting?', 'cf-geoplugin'); ?>" style="margin:15px auto 0 auto; display:block; width:100%; max-width:100%; height:auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%);" /></a>

<a href="https://affiliates.nordvpn.com/publisher/#!/offer/15" title="<?php esc_attr_e('The CF Gep plugin recommends using Nord VPN for testing.', 'cf-geoplugin'); ?>" class="affiliate-nordvpn" target="_blank"><img src="<?php echo esc_url(CFGP_ASSETS); ?>/images/Logo-NordVPN.jpg" alt="<?php esc_attr_e('The CF Gep plugin recommends using Nord VPN for testing.', 'cf-geoplugin'); ?>" style="margin:15px auto 0 auto; display:block; width:100%; max-width:100%; height:auto; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgb(0 0 0 / 4%);" /></a>
<hr style="margin:32px auto 32px auto;">
<a href="https://infinitumform.com/" title="<?php esc_attr_e('We have created many good projects, do you want to we create something for you?', 'cf-geoplugin'); ?>" target="_blank"><img src="<?php echo esc_url(CFGP_ASSETS); ?>/images/developed-by.png" alt="<?php esc_attr_e('We have created many good projects, do you want to we create something for you?', 'cf-geoplugin'); ?>" style="margin:0 auto; display:block; width:100%; max-width:200px; height:auto;" /></a>
<hr style="margin:32px auto 32px auto;">
	<?php }
	
	
	/**
	 * Dashboard news feed
	 *
	 * @since    8.0.0
	 **/
	public function dashboard_feed(){ $RSS = CFGP_DB_Cache::get('cfgp-dashboard-rss'); ?>
	<div class="wordpress-news hide-if-no-js<?php echo (empty($RSS) ? ' cfgp-load-dashboard-rss-feed' : ''); ?>">
	<?php if($RSS) : ?>
		<?php echo wp_kses_post($RSS); ?>
	<?php else : ?>
		<ul class="rss-widget">
			<li style="background-color:transparent;"><?php _e('Loading...', 'cf-geoplugin'); ?></li>
		</ul>
	<?php endif; ?>
	</div>
	<div class="community-events-footer">
		<a href="<?php echo esc_url(CFGP_STORE); ?>/category/announcements" target="_blank"><?php _e( 'Announcements', 'cf-geoplugin'); ?> <span class="screen-reader-text"><?php _e('(opens in a new tab)', 'cf-geoplugin'); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
		| <a href="<?php echo esc_url(CFGP_STORE); ?>/category/information" target="_blank"><?php _e( 'Information', 'cf-geoplugin'); ?> <span class="screen-reader-text">(opens in a new tab)</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
		| <a href="<?php echo esc_url(CFGP_STORE); ?>/category/tutorial" target="_blank"><?php _e( 'Tutorial', 'cf-geoplugin'); ?> <span class="screen-reader-text"><?php _e('(opens in a new tab)', 'cf-geoplugin'); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
	</div>
	<?php }
	
	
	/**
	 * Dashboard footer in sidebar
	 *
	 * @since    8.0.0
	 **/
	public function dashboard_footer(){ ?>
	<p class="community-events-footer" id="cf-geoplugin-dashboard-footer" style="text-align:center;">
		<a href="<?php echo esc_url(CFGP_STORE); ?>/documentation/" target="_blank"><?php _e( 'Documentation', 'cf-geoplugin'); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', 'cf-geoplugin'); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> 
		| <a href="<?php echo esc_url(CFGP_STORE); ?>/pricing/" target="_blank"><?php _e( 'Pricing', 'cf-geoplugin'); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', 'cf-geoplugin'); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> 
		| <a href="<?php echo esc_url(CFGP_STORE); ?>/blog/" target="_blank"><?php _e( 'Blog', 'cf-geoplugin'); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', 'cf-geoplugin'); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
	</p>
	<p class="community-events-footer" id="cf-geoplugin-dashboard-footer" style="text-align:center;">
		<a href="<?php echo esc_url(CFGP_STORE); ?>/terms-and-conditions/" target="_blank"><?php _e( 'Terms & Conditions', 'cf-geoplugin'); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', 'cf-geoplugin'); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
		| <a href="<?php echo esc_url(CFGP_STORE); ?>/privacy-policy/" target="_blank"><?php _e( 'Privacy Policy', 'cf-geoplugin'); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', 'cf-geoplugin'); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
		| <a href="<?php echo esc_url(CFGP_STORE); ?>/cookie-policy/" target="_blank"><?php _e( 'Cookie Policy', 'cf-geoplugin'); ?><span class="screen-reader-text"><?php _e('(opens in a new tab)', 'cf-geoplugin'); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>
	</p>
	<p class="community-events-footer" id="cf-geoplugin-copyright" style="font-size:0.85em; text-align:center;">
		<?php printf(__('Copyright © %d-%d CF Geo Plugin. All rights reserved.', 'cf-geoplugin'), 2015, date('Y')); ?>
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
	?>
<li class="cfgp-statistic-separator"></li>
<li class="cfgp-statistic-plugin-details">
	<h3><i class="cfa cfa-plug" aria-hidden="true"></i> <?php _e( 'CF Geo Plugin details', 'cf-geoplugin'); ?></h3>
	<ul>
		<li><strong><?php _e( 'Last Update', 'cf-geoplugin'); ?>:</strong> <span><?php echo date(CFGP_DATE_TIME_FORMAT, strtotime($plugin->last_updated)); ?></span></li>
		<li><strong><?php _e( 'Homepage', 'cf-geoplugin'); ?>:</strong> <span><a href="<?php echo esc_url($plugin->homepage); ?>" target="_blank"><?php echo esc_url($plugin->homepage) ?></a></span></li>
		<li><strong><?php _e( 'WP Support', 'cf-geoplugin'); ?>:</strong> <span><?php
			if(version_compare(get_bloginfo('version'), $plugin->requires, '>='))
			{
				printf('<span class="text-success">' . __( 'Supported on WP version %s', 'cf-geoplugin') . '</span>', get_bloginfo('version'));
			}
			else
			{
				printf('<span class="text-danger">' . __( 'Plugin require WordPress version %s or above!', 'cf-geoplugin') . '</span>', $plugin->requires);
			}
		?></span></li>
		<li><strong><?php _e( 'PHP Support', 'cf-geoplugin'); ?>:</strong> <?php
			preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $match);
			if(version_compare(PHP_VERSION, $plugin->requires_php, '>='))
			{
				printf('<span class="text-success">' . __( 'Supported on PHP version %s', 'cf-geoplugin') . '</span>', esc_html($match[0]));
			}
			else
			{
				printf('<span class="text-danger">' . __( 'Plugin not support PHP version %1$s. Please use PHP vesion %2$s or above.', 'cf-geoplugin') . '</span>', PHP_VERSION, esc_html($plugin->requires_php));
			}
		?></li>
	</ul>
</li>
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
			echo '<span class="cfa cfa-battery-full incomparable'.esc_attr($class).'" aria-hidden="true" title="'.esc_attr__('Incomparable', 'cf-geoplugin').'"></span> <span class="cfgp-statistic-label incomparable">'.__('Incomparable', 'cf-geoplugin').'</span>';
		}
		else if(floatval($runtime) <= 0.5){
			echo '<span class="cfa cfa-battery-full exellent'.esc_attr($class).'" aria-hidden="true" title="'.esc_attr__('Exellent', 'cf-geoplugin').'"></span> <span class="cfgp-statistic-label exellent">'.__('Exellent', 'cf-geoplugin').'</span>';
		}
		else if(floatval($runtime) <= 0.8){
			echo '<span class="cfa cfa-battery-three-quarters perfect'.esc_attr($class).'" aria-hidden="true" title="'.esc_attr__('Perfect', 'cf-geoplugin').'"></span> <span class="cfgp-statistic-label perfect">'.__('Perfect', 'cf-geoplugin').'</span>';
		}
		else if(floatval($runtime) <= 1.2){
			echo '<span class="cfa cfa-battery-half good'.esc_attr($class).'" aria-hidden="true" title="'.esc_attr__('Good', 'cf-geoplugin').'"></span> <span class="cfgp-statistic-label good">'.__('Good', 'cf-geoplugin').'</span>';
		}
		else if(floatval($runtime) <= 1.5){
			echo '<span class="cfa cfa-battery-quarter week'.esc_attr($class).'" aria-hidden="true" title="'.esc_attr__('Week', 'cf-geoplugin').'"></span> <span class="cfgp-statistic-label week">'.__('Week', 'cf-geoplugin').'</span>';
		}
		else {
			echo '<span class="cfa cfa-battery-empty bad'.esc_attr($class).'" aria-hidden="true" title="'.esc_attr__('Bad', 'cf-geoplugin').'"></span> <span class="cfgp-statistic-label bad">'.__('Bad', 'cf-geoplugin').'</span>';
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
			echo '<span class="cfa cfa-check '.esc_attr($class).'" title="'.esc_attr__('UNLIMITED', 'cf-geoplugin').'"></span>';
		}
		else if($lookup == 0){
			echo '<span class="cfa cfa-ban '.esc_attr($class).'" title="'.esc_attr__('EXPIRED', 'cf-geoplugin').'"></span>';
		}
		else if($lookup <= CFGP_LIMIT && $lookup > (CFGP_LIMIT/2)){
			echo '<span class="cfa cfa-hourglass-start '.esc_attr($class).'" title="'.esc_attr__('Available', 'cf-geoplugin').' '.esc_attr($lookup).'"></span>';
		}
		else if($lookup <= (CFGP_LIMIT/2) && $lookup > (CFGP_LIMIT/3)){
			echo '<span class="cfa cfa-hourglass-halp '.esc_attr($class).'" title="'.esc_attr__('Available', 'cf-geoplugin').' '.esc_attr($lookup).'"></span>';
		}
		else if($lookup <= (CFGP_LIMIT/3)){
			echo '<span class="cfa cfa-hourglass-end '.esc_attr($class).'" title="'.esc_attr__('Available', 'cf-geoplugin').' '.esc_attr($lookup).'"></span>';
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