<?php
/**
 * Metaboxes
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Metabox')) :
class CFGP_Metabox extends CFGP_Global {
	public $metabox = 'cfgp-seo-redirection';
	
	public function __construct(){
		$this->add_action('add_meta_boxes', 'add_seo_redirection', 1);
		$this->add_action('admin_enqueue_scripts', 'register_style');
		$this->add_action('save_post', 'save_post');
	}
	
	/**
	 * Hook for the post save/update
	 */
	public function save_post($post_id){
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		$post_type = get_post_type($post_id);
		
		if(!in_array($post_type, CFGP_Options::get('enable_seo_posts', array()))) {
			return;
		}
		
		$save = array(); $i=0;
		if($prepared_data = CFGP_U::request($this->metabox, array()))
		{
			if(is_array($prepared_data))
			{
				$prepared_data = array_filter($prepared_data);
				foreach($prepared_data as $data){
					if(isset($data['url']) && !empty($data['url'])) {
						$save[$i]=CFGP_Options::sanitize($data);
						++$i;
					}
				}
			}
		}
		
		update_post_meta( $post_id, "{$this->metabox}-enabled", !empty($save));
		update_post_meta( $post_id, $this->metabox, $save);
		
		delete_post_meta($post_id, CFGP_METABOX . 'redirection');
	}
	
	public function register_style(){
		$screen = get_current_screen();
		if(isset( $screen->post_type ) && in_array($screen->post_type, CFGP_Options::get('enable_seo_posts', array())) || $screen->post_type === 'cf-geoplugin-banner'){
			$url = CFGP_U::get_url();
			
			
			wp_enqueue_style( CFGP_NAME . '-fontawesome', CFGP_ASSETS . '/css/font-awesome.min.css', array(), (string)CFGP_VERSION );
			wp_enqueue_style( CFGP_NAME . '-metabox', CFGP_ASSETS . '/css/style-metabox.css', array(CFGP_NAME . '-fontawesome'), (string)CFGP_VERSION, false );
			wp_enqueue_style( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.min.css', 1,  '1.8.7' );
			
			wp_enqueue_script( CFGP_NAME . '-choosen', CFGP_ASSETS . '/js/chosen_v1.8.7/chosen.jquery.min.js', array('jquery'), '1.8.7', true );
			wp_enqueue_script( CFGP_NAME . '-metabox', CFGP_ASSETS . '/js/script-metabox.js', array('jquery', CFGP_NAME . '-choosen'), (string)CFGP_VERSION, true );
			wp_localize_script(CFGP_NAME . '-metabox', 'CFGP', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'adminurl' => self_admin_url('/'),
				'label' => array(
					'unload' => esc_attr__('Data will lost , Do you wish to continue?',CFGP_NAME),
					'loading' => esc_attr__('Loading...',CFGP_NAME),
					'not_found' => esc_attr__('Not Found!',CFGP_NAME),
					'chosen' => array(
						'not_found' 		=> esc_attr__('Nothing found!',CFGP_NAME),
						'choose' 			=> esc_attr__('Choose...',CFGP_NAME),
						'choose_first' 		=> esc_attr__('Choose countries first!',CFGP_NAME),
						'choose_countries' 	=> esc_attr__('Choose countries...',CFGP_NAME),
						'choose_regions' 	=> esc_attr__('Choose regions...',CFGP_NAME),
						'choose_cities' 	=> esc_attr__('Choose cities...',CFGP_NAME),
						'choose_postcodes' 	=> esc_attr__('Choose postcodes...',CFGP_NAME),
					)
				)
			));
			
			// Load geodata
			if(strpos($url, 'post-new.php') !== false || (strpos($url, 'action=edit') !== false && strpos($url, 'post=') !== false)){
				wp_localize_script(CFGP_NAME . '-metabox', 'CFGP_GEODATA', CFGP_Library::all_geodata());
			}
			
			
		}
	}
	
	/**
     * Add SEO redirection
     */
	public function add_seo_redirection(){
		$screen = get_current_screen();
		if(isset( $screen->post_type ) && in_array($screen->post_type, CFGP_Options::get('enable_seo_posts', array()))){
			$this->add_meta_box(
				CFGP_NAME . '-page-seo-redirection',			// Unique ID
				__( 'SEO Redirection', CFGP_NAME ),				// Box title
				'add_seo_redirection__callback',				// Content callback, must be of type callable
				$screen->post_type,								// Post type
				'advanced',
				'high'
			);
		}
		
		return;
	}
	
	public function add_seo_redirection__callback( $post ){
		
		$this->metabox = 'cfgp-seo-redirection';
		
		$seo_redirection = get_post_meta($post->ID, $this->metabox, true);
		if(empty($seo_redirection)){
			$seo_redirection = get_post_meta($post->ID, CFGP_METABOX . 'redirection', true); // Depricated (it will be removed in the future)
		}
		
		if(empty($seo_redirection)){
			$seo_redirection = array(
				array(
					'country' => NULL,
					'region' => NULL,
					'city' => NULL,
					'postcode' => NULL,
					'url' => NULL,
					'http_code' => 302,
					'only_once' => 0,
					'active' => 1,
					'search_type' => 'exact'
				)
			);
		}
	?>
<div class="cfgp-container cfgp-repeater">
	<?php foreach($seo_redirection as $i=>$data):
		$country 	= (isset($data['country']) ? $data['country'] : '');
		$region 	= (isset($data['region']) ? $data['region'] : '');
		$city 		= (isset($data['city']) ? $data['city'] : '');
		$postcode 	= (isset($data['postcode']) ? $data['postcode'] : '');
		$url 		= (isset($data['url']) ? $data['url'] : '');
		$http_code 	= (isset($data['http_code']) ? $data['http_code'] : 302);
		$only_once 	= (isset($data['only_once']) ? $data['only_once'] : 0);
		$active 	= (isset($data['active']) ? $data['active'] : 1);
		$search_type = (isset($data['search_type']) ? $data['search_type'] : 'exact');
		
		$exclude_country = (isset($data['exclude_country']) ? $data['exclude_country'] : NULL);
		$exclude_region = (isset($data['exclude_region']) ? $data['exclude_region'] : NULL);
		$exclude_city = (isset($data['exclude_city']) ? $data['exclude_city'] : NULL);
		$exclude_postcode = (isset($data['exclude_postcode']) ? $data['exclude_postcode'] : NULL);
	?>
    <div class="cfgp-row cfgp-repeater-item cfgp-country-region-city-multiple-form">
        <div class="cfgp-col cfgp-col-4">
            <label for="country"><?php _e('Choose Countries', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_countries(array('name'=>"{$this->metabox}[{$i}][country]", 'id'=>"{$this->metabox}-{$i}-country"), $country, true);?>
            <span class="description"><?php _e( 'Select the countries you want to redirect.', CFGP_NAME ); ?></span>
            <button type="button" class="cfgp-select-all" data-target="<?php echo "{$this->metabox}-{$i}-country"; ?>"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
				CFGP_Form::checkbox(
					array(
						"{$this->metabox}[{$i}][exclude_country]" => array(
							'label' => __('Exclude from redirection', CFGP_NAME),
							'value' => 1,
							'checked' => $exclude_country,
							'id' => "{$this->metabox}-{$i}-exclude_country",
						)
					),
					true
				);
			?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="region"><?php _e('Choose Regions', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_regions(array('name'=>"{$this->metabox}[{$i}][region]", 'id'=>"{$this->metabox}-{$i}-region", 'country_code' => $country), $region, true); ?>
            <span class="description"><?php _e( 'Select the regions you want to redirect.', CFGP_NAME ); ?></span>
            <button type="button" class="cfgp-select-all" data-target="<?php echo "{$this->metabox}-{$i}-region"; ?>"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
				CFGP_Form::checkbox(
					array(
						"{$this->metabox}[{$i}][exclude_region]" => array(
							'label' => __('Exclude from redirection', CFGP_NAME),
							'value' => 1,
							'checked' => $exclude_region,
							'id' => "{$this->metabox}-{$i}-exclude_region",
						)
					),
					true
				);
			?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="city"><?php _e('Choose Cities', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_cities(array('name'=>"{$this->metabox}[{$i}][city]", 'id'=>"{$this->metabox}-{$i}-city", 'country_code' => $country), $city, true); ?>
            <span class="description"><?php _e( 'Select the cities you want to redirect.', CFGP_NAME ); ?></span>
            <button type="button" class="cfgp-select-all" data-target="<?php echo "{$this->metabox}-{$i}-city"; ?>"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
				CFGP_Form::checkbox(
					array(
						"{$this->metabox}[{$i}][exclude_city]" => array(
							'label' => __('Exclude from redirection', CFGP_NAME),
							'value' => 1,
							'checked' => $exclude_city,
							'id' => "{$this->metabox}-{$i}-exclude_city",
						)
					),
					true
				);
			?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="postcode"><?php _e('Choose Postcodes', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_postcodes(array('name'=>"{$this->metabox}[{$i}][postcode]", 'id'=>"{$this->metabox}-{$i}-postcode"), $postcode, true); ?>
            <span class="description"><?php _e( 'Select the postcodes you want to redirect.', CFGP_NAME ); ?></span>
            <button type="button" class="cfgp-select-all" data-target="<?php echo "{$this->metabox}-{$i}-postcode"; ?>"><object data="<?php echo CFGP_ASSETS . '/images/select.svg'; ?>" width="15" height="15"></object> <?php esc_attr_e( 'Select all', CFGP_NAME ); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;
			<?php
				CFGP_Form::checkbox(
					array(
						"{$this->metabox}[{$i}][exclude_postcode]" => array(
							'label' => __('Exclude from redirection', CFGP_NAME),
							'value' => 1,
							'checked' => $exclude_postcode,
							'id' => "{$this->metabox}-{$i}-exclude_postcode",
						)
					),
					true
				);
			?>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="url"><?php _e('Define Redirect URL', CFGP_NAME); ?></label>
            <?php CFGP_Form::input('url', array('name'=>"{$this->metabox}[{$i}][url]",'value'=>$url, 'id'=>"{$this->metabox}-{$i}-url", 'class'=>'required-field')); ?>
            <span class="description"><?php _e( 'URL where you want to redirect.', CFGP_NAME ); ?></span>
        </div>
        <div class="cfgp-col cfgp-col-4">
            <label for="http_code"><?php _e('HTTP Code', CFGP_NAME); ?></label>
            <?php CFGP_Form::select_http_code(array('name'=>"{$this->metabox}[{$i}][http_code]", 'id'=>"{$this->metabox}-{$i}-http_code"), $http_code); ?>
            <span class="description"><?php _e( 'Select the desired HTTP redirection.', CFGP_NAME ); ?></span>
        </div>
        <div class="cfgp-col cfgp-col-3 input-radio">
            <label><?php _e('Enable this redirection', CFGP_NAME); ?></label>
            <?php
                CFGP_Form::radio(
                    array(
                        1 => __('Enable', CFGP_NAME),
                        0 => __('Disable', CFGP_NAME)
                    ),
                    array('name'=>"{$this->metabox}[{$i}][active]", 'id'=>"{$this->metabox}-{$i}-active"),
                    $active
                );
            ?>
        </div>
        <div class="cfgp-col cfgp-col-sm-6 cfgp-col-3 input-radio">
            <label><?php _e('Redirect only once', CFGP_NAME); ?></label>
            <?php
                CFGP_Form::radio(
                    array(
                        1 => __('Enable', CFGP_NAME),
                        0 => __('Disable', CFGP_NAME)
                    ),
                    array('name'=>"{$this->metabox}[{$i}][only_once]"),
                    $only_once
                );
            ?>
        </div>
        <div class="cfgp-col cfgp-col-sm-6 cfgp-col-3 input-radio"></div>
        <div class="cfgp-col cfgp-col-sm-6 cfgp-col-3 cfgp-col-content-right cfgp-repeater-actions">
        	<button type="button" class="button button-link cfgp-remove-seo-redirection"><i class="fa fa-times"></i> <?php _e( 'Remove', CFGP_NAME ); ?></button>
        	<button type="button" class="button button-primary cfgp-add-seo-redirection"><i class="fa fa-plus"></i> <?php _e( 'Add New Redirection', CFGP_NAME ); ?></button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
	<?php }
	
	
	/* 
	 * Instance
	 * @verson    1.0.0
	 */
	public static function instance() {
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}
}
endif;