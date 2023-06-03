<?php
/**
 * Include plugins support if they are available
 *
 * @link              http://infinitumform.com/
 * @since             8.0.0
 * @package           cf-geoplugin
 * @author            Ivijan-Stefan Stipic
 */
 
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Plugins', false)) : class CFGP_Plugins extends CFGP_Global
	{
		// plugin-directory => plugin-file.php
		private $plugins = array(
			'woocommerce' 			=> 'woocommerce',
			'wooplatnica' 			=> 'wooplatnica',
			'contact-form-7'		=> 'wp-contact-form-7',
			'wordpress-seo' 		=> 'wp-seo',
			'elementor' 			=> 'elementor',
		//	'buddyboss-platform'	=> 'bp-loader',
			'gravityforms'			=> 'gravityforms'
		);
		
		function __construct( $options=[], $only_object = false ) {
			$this->include_plugins($options, $only_object);
			$this->add_filter( 'cfgp/settings', 'cfgp_settings' );
			$this->add_filter( 'cfgp/settings/default', 'cfgp_settings_default' );
			$this->add_action( 'wp_ajax_cfgp_dimiss_notice_plugin_support', 'ajax_dimiss_notice' );
			$this->add_action( 'plugins_loaded', 'cfgp_activate_all_plugins_support' );
		}
		
		/* 
		 * Activate all plugins
		 * @verson    1.0.0
		 */
		public function cfgp_activate_all_plugins_support(){
			
			$data = array_map('sanitize_text_field', $_GET);
			
			if( ( $data['cfgp_activate_all_plugins_support'] ?? 0 ) == '1' ){
				if( !wp_verify_nonce( ( $data['cfgp_nonce'] ?? NULL ), 'cfgp-activate-all-plugins-support' ) ) {
					return;
				}
				
				if( $data['plugins'] ?? NULL ) {
					$plugins = explode(',', $data['plugins']);
					$plugins = array_map('trim', $plugins);
					$plugins = array_filter($plugins);
				//	var_dump($plugins); exit;
					foreach($plugins as $plugin) {
						CFGP_Options::set("enable-{$plugin}", 1);
					}
					
					if( wp_safe_redirect( remove_query_arg([
						'cfgp_activate_all_plugins_support',
						'plugins',
						'cfgp_nonce'
					]) ) ) {
						exit;
					}
				}
			}
		}
		
		/* 
		 * Dimiss notice
		 * @verson    1.0.0
		 */
		public function ajax_dimiss_notice ( ) {
			if( !wp_verify_nonce( $_POST['nonce'], 'cfgp-dimiss-notice-plugin-support' ) ) {
				return;
			}
			
			update_option(CFGP_NAME . '_dimiss_notice_plugin_support', true, true);
			set_transient(CFGP_NAME . '_dimiss_notice_plugin_support', true, (MONTH_IN_SECONDS * time()));
			
			echo 1; exit;
		}
		
		/* 
		 * Add plugins settings
		 * @verson    1.0.0
		 */
		private function notify_for_plugins ( $plugins ) {
			// Dimissed notice
			if( get_option(CFGP_NAME . '_dimiss_notice_plugin_support') ) {
				return;
			}
			
			// Add notice script to footer
			add_action('admin_footer', function(){ ?>
<script id="cfgp-dimiss-notice-plugin-support-js">
/* <![CDATA[ */
(function($){
	$(document).on( 'click', '#cf-geoplugin-notice-plugin-support .notice-dismiss', function(){
		$.post(
			'<?php echo esc_url( admin_url('admin-ajax.php') ); ?>',
			{
				action : 'cfgp_dimiss_notice_plugin_support',
				nonce : '<?php echo esc_attr(wp_create_nonce('cfgp-dimiss-notice-plugin-support')); ?>'
			}
		);
	} );
}(jQuery || window.jQuery));
/* ]]> */
</script>
			<?php }, 9999);
			
			// Notice script
			add_action( 'admin_notices', function () use ( $plugins ) {
				// Collect slugs
				$slugs = [];
				foreach($plugins as $path => $plugin) {
					$path = explode('/', $path);
					$slugs[]= $path[0];
				}
				
				// Collect plugin names
				$names = [];
				$last = NULL;
				if( count($plugins) > 1 ) {
					$last = '<strong>' . (end($plugins))->name . '</strong>';
					unset($plugins[array_key_last($plugins)]);
				}
				foreach($plugins as $path => $plugin) {
					$names[]= '<strong>' . $plugin->name . '</strong>';
				}
				if($last) {
					$names = sprintf(
						__('%s and %s', 'cf-geoplugin'),
						join(', ', $names),
						$last
					);
				} else {
					$names = join(', ', $names);
				}
				
				//Fire notice
?><div class="notice notice-info is-dismissible" id="cf-geoplugin-notice-plugin-support">
	<h3><?php echo wp_kses_post( sprintf( __('Attention %s enthusiasts!', 'cf-geoplugin'), $names ) ); ?></h3>
	<p><?php echo wp_kses_post( sprintf( __('Get ready to unleash the full potential of your website with <b>Geo Controller</b> integration. The plugins are all installed, active, and waiting for you to activate the magic. You need to go to the %s and activate the integration for the mentioned plugins.', 'cf-geoplugin'), '<a href="' . esc_url(admin_url('admin.php?page=cf-geoplugin-settings').'#plugin_support') . '">' . __('Geo Controller settings', 'cf-geoplugin') . '</a>' ) ); ?></p>
	<p><a href="<?php echo esc_url( add_query_arg([
		'cfgp_activate_all_plugins_support' => true,
		'plugins' => join(',', $slugs),
		'cfgp_nonce' => wp_create_nonce('cfgp-activate-all-plugins-support')
	]) ); ?>" class="button button-primary"><?php esc_html_e('Activate All Geo Power in One Click!', 'cf-geoplugin'); ?></a></p>
	<p><strong><?php esc_html_e('Don\'t miss out on this opportunity to enhance your online presence!', 'cf-geoplugin'); ?></strong></p>
</div><?php } );
		}
		
		/* 
		 * Add plugins settings
		 * @verson    1.0.0
		 */
		public function cfgp_settings ($options =[]){
			$plugin_options = [];
			
			$this->plugins = apply_filters('cfgp/plugins', $this->plugins);				
			foreach($this->plugins as $dir_name=>$file_name){
				$addon = CFGP_PLUGINS . "/{$dir_name}/{$dir_name}.php";
				if( file_exists($addon) && CFGP_U::is_plugin_active("{$dir_name}/{$file_name}.php") )
				{
					if($plugin_info = CFGP_U::plugin_info([], $dir_name))
					{
						if( is_wp_error($plugin_info) ) {
							continue;
						}
						
						$plugin_options[]= array(
							'name' => 'enable-' . $dir_name,
							'label' => $plugin_info->name,
							'desc' => sprintf(__('Enable %s integration.', 'cf-geoplugin'), $plugin_info->name),
							'type' => 'radio',
							'options' => array(
								1 => __('Yes', 'cf-geoplugin'),
								0 => __('No', 'cf-geoplugin')
							),
							'default' => 0
						);
						$plugin_info = NULL;
					}
				}
			}
			
			if(empty($plugin_options)){
				return $options;
			}
			
			return array_merge($options, array(
				// Tab
				array(
					'id' => 'plugin_support',
					'title' => __('Plugin Support', 'cf-geoplugin'),
					// Section
					'sections' => array(
						array(
							'id' => 'enable-plugins',
							'title' => __('Enable Plugins', 'cf-geoplugin'),
							'desc' => __('Allow Geo Controller to integrate with existing installed plugins.', 'cf-geoplugin'),
							'inputs' => $plugin_options
						)
					)
				)
			));
		}
		
		/* 
		 * Add default plugin security settings
		 * @verson    1.0.0
		 */
		public function cfgp_settings_default ($options =[]){
			$plugin_options = [];
			
			$this->plugins = apply_filters('cfgp/plugins', $this->plugins);				
			foreach($this->plugins as $dir_name=>$file_name){
				$addon = CFGP_PLUGINS . "/{$dir_name}/{$dir_name}.php";
				if( file_exists($addon) && CFGP_U::is_plugin_active("{$dir_name}/{$file_name}.php") )
				{
					$plugin_options["enable-{$dir_name}"]=0;
				}
			}
			
			return array_merge($options, $plugin_options);
		}
		
		/* 
		 * Include WordPress plugins support
		 * @verson    1.0.0
		 */
		private function include_plugins( $options=[], $only_object = false ){
			if($only_object === false)
			{							
				$this->plugins = apply_filters('cfgp/plugins', $this->plugins);
				
				$notify = [];
				
				foreach($this->plugins as $dir_name=>$file_name)
				{
					$addon = CFGP_PLUGINS . "/{$dir_name}/{$dir_name}.php";
					if( file_exists($addon) && CFGP_U::is_plugin_active("{$dir_name}/{$file_name}.php") )
					{
						$class_name = str_replace(['-','.'], '_', $dir_name);
						$plugin_class = "CFGP__Plugin__{$class_name}";
						
						if(CFGP_Options::get("enable-{$dir_name}", 0))
						{
							if(class_exists($plugin_class, false) && method_exists($plugin_class, 'instance')) {
								$plugin_class::instance();
							} else {
								CFGP_U::include_once($addon);
								if(class_exists($plugin_class, false) && method_exists($plugin_class, 'instance')) {
									$plugin_class::instance();
								}
							}
						} else if( $plugin_info = CFGP_U::plugin_info([], $dir_name) ) {
							$notify["{$dir_name}/{$dir_name}.php"] = $plugin_info;
						}
					}
				}
				
				if( !empty( $notify ) ) {
					$this->notify_for_plugins($notify);
				}
			}
		}
		
		/* 
		 * Instance
		 * @verson    1.0.0
		 */
		public static function instance($options = [], $only_object = false ) {
			$class = self::class;
			$instance = CFGP_Cache::get($class);
			if ( !$instance ) {
				$instance = CFGP_Cache::set($class, new self($options, $only_object));
			}
			return $instance;
		}
	}
endif;