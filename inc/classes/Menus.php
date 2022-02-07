<?php
/**
 * Add controls of the navigation menu
 *
 * @link          http://infinitumform.com/
 * @since         8.0.1
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Menus')) :
class CFGP_Menus extends CFGP_Global {
	// Save cached object data
	private $menu_options = array();
	
	public function __construct(){
		$this->add_action( 'wp_nav_menu_item_custom_fields', 'field__enble', 10, 2 );
		$this->add_action( 'wp_update_nav_menu_item', 'update__nav_menu_item', 10, 3 );
		
		$this->add_filter( 'wp_get_nav_menu_items', 'restrict_menu_items', 1, 3);
		$this->add_filter( 'template_redirect', 'restrict_page_access', 1, 0);
	}
	
	/*
	 * New select field for the enabling geo restriction
	 */
	public function field__enble( $item_id, $item ) {
		
		if( !in_array($item->type, array('post_type', 'taxonomy', 'custom')) ){
			return;
		}
?>
<div class="cfgp-menu-item-restriction cfgp-menu-item-restriction-<?php echo $item_id; ?>" data-id="<?php echo $item_id; ?>" style="clear: both;">
	<p class="cfgp-menu-item cfgp-menu-item-enable-restriction">
		<label for="edit-menu-item-enable-restriction-<?php echo $item_id; ?>">
			<input type="checkbox" id="edit-menu-item-enable-restriction-<?php echo $item_id; ?>" name="cfgp_menu_enable_restriction[<?php echo $item_id; ?>]" value="1" data-id="<?php echo $item_id; ?>" <?php checked(1, $this->get_values( $item->object_id, 'enable', NULL, $item->type )); ?>> <?php _e('Enable geographic location control', CFGP_NAME); ?>
		</label>
	</p>
	<div class="cfgp-menu-item-restriction-locations cfgp-menu-item-restriction-locations-<?php echo $item_id; ?> cfgp-country-region-city-multiple-form-no-ajax">
		<p class="cfgp-menu-item cfgp-menu-item-countrues" style="clear: both;">
			<label for="edit-menu-item-countrues-<?php echo $item_id; ?>"><?php _e('Hide in Countries', CFGP_NAME); ?></label><br>
			<?php CFGP_Form::select_countries(array(
				'name'=>"cfgp_menu_countries[{$item_id}]",
				'id' => "edit-menu-item-countrues-{$item_id}"
			), $this->get_values( $item->object_id, 'countries', array(), $item->type ), true, true); ?>
		</p>
		<p class="cfgp-menu-item cfgp-menu-item-regions">
			<label for="edit-menu-item-regions-<?php echo $item_id; ?>"><?php _e('Hide in Regions', CFGP_NAME); ?></label><br>
			<?php CFGP_Form::select_regions(array(
				'name'=>"cfgp_menu_regions[{$item_id}]",
				'id' => "edit-menu-item-regions-{$item_id}",
				'country_code' => $this->get_values( $item->object_id, 'countries', array(), $item->type )
			), $this->get_values( $item->object_id, 'regions', array(), $item->type ), true, true); ?>
		</p>
		<p class="cfgp-menu-item cfgp-menu-item-cities">
			<label for="edit-menu-item-cities-<?php echo $item_id; ?>"><?php _e('Hide in Cities', CFGP_NAME); ?></label><br>
			<?php CFGP_Form::select_cities(array(
				'name'=>"cfgp_menu_cities[{$item_id}]",
				'id' => "edit-menu-item-cities-{$item_id}",
				'country_code' => $this->get_values( $item->object_id, 'countries', array(), $item->type )
			), $this->get_values( $item->object_id, 'cities', array(), $item->type ), true, true); ?>
		</p>
	</div>
	<p class="cfgp-menu-item-description"><?php _e('If you enable this option, in selected locations navigation will be hidden from the public as well as direct access to the URL.', CFGP_NAME); ?></p>
</div>
		<?php
	}
	
	/*
	 * Update menu items settings
	 */
	public function update__nav_menu_item( $menu_id, $menu_item_db_id, $item ) {
		// Clear cache
		$this->menu_options = array();
		// Set controls
		$control = CFGP_Options::sanitize( array(
			'enable' => ($_POST['cfgp_menu_enable_restriction'][$menu_item_db_id] ?? NULL),
			'countries' => ($_POST['cfgp_menu_countries'][$menu_item_db_id] ?? array()),
			'regions' => ($_POST['cfgp_menu_regions'][$menu_item_db_id] ?? array()),
			'cities' => ($_POST['cfgp_menu_cities'][$menu_item_db_id] ?? array()),
		) );
		
		// Custom links need to be saved as post meta with menu ID
		if( in_array($item['menu-item-type'], array('custom')) )
		{
			if ( $control['enable'] ) {
				update_post_meta( $menu_item_db_id, 'cfgp_menu_item_control', $control );
			} else {
				if( empty($control['countries']) && empty($control['regions']) && empty($control['cities']) ) {
					delete_post_meta( $menu_item_db_id, 'cfgp_menu_item_control' );
				} else {
					update_post_meta( $menu_item_db_id, 'cfgp_menu_item_control', $control );
				}
			}
		}
		// Post types need to be saved as post meta with page ID
		else if( in_array($item['menu-item-type'], array('post_type')) )
		{
			if ( $control['enable'] ) {
				update_post_meta( absint($item['menu-item-object-id']), 'cfgp_menu_item_control', $control );
			} else {
				if( empty($control['countries']) && empty($control['regions']) && empty($control['cities']) ) {
					delete_post_meta( absint($item['menu-item-object-id']), 'cfgp_menu_item_control' );
				} else {
					update_post_meta( absint($item['menu-item-object-id']), 'cfgp_menu_item_control', $control );
				}
			}
		}
		// Taxonomy need to be saved as term meta with term ID
		else if( in_array($item['menu-item-type'], array('taxonomy')) )
		{
			if ( $control['enable'] ) {
				update_term_meta( absint($item['menu-item-object-id']), 'cfgp_menu_item_control', $control );
			} else {
				if( empty($control['countries']) && empty($control['regions']) && empty($control['cities']) ) {
					delete_term_meta( absint($item['menu-item-object-id']), 'cfgp_menu_item_control' );
				} else {
					update_term_meta( absint($item['menu-item-object-id']), 'cfgp_menu_item_control', $control );
				}
			}
		}
	}
	
	/*
	 * New select field for the City
	 */
	public function restrict_menu_items( $items, $menu, $args ) {
		// In admin menu we need this visible
		if( is_admin() ) {
			return $items;
		}
		
		// Iterate over the items to search and destroy
		foreach ( $items as $key => $item ) {
			if( $control = $this->get_values( (in_array($item->type, array('custom')) ? $item->ID : $item->object_id), NULL, array(
				'enable' => NULL,
				'countries' => array(),
				'regions' => array(),
				'cities' => array(),
			), $item->type ) ) {
				
				if($control['enable'] == 1) {
					$protect = false;
		
					if( CFGP_U::check_user_by_city($control['cities']) && CFGP_U::check_user_by_region($control['regions']) ) {
						$protect = true;
					} else if( CFGP_U::check_user_by_region($control['regions']) ) {
						$protect = true;
					} else if( CFGP_U::check_user_by_country($control['countries']) ) {
						$protect = true;
					}
				
					if( $protect ){
						unset($items[$key]);
					}
				}
			}
		}
		
		// Return
		return $items;
	}
	
	public function restrict_page_access(){
		global $wp_query;
		$page_id = get_the_ID();
		if( $control = $this->get_values( $page_id, NULL, array(
			'enable' => NULL,
			'countries' => array(),
			'regions' => array(),
			'cities' => array(),
		) ) ) {
			if($control['enable'] == 1) {
				$protect = false;
			
				if( CFGP_U::check_user_by_city($control['cities']) && CFGP_U::check_user_by_region($control['regions']) ) {
					$protect = true;
				} else if( CFGP_U::check_user_by_region($control['regions']) ) {
					$protect = true;
				} else if( CFGP_U::check_user_by_country($control['countries']) ) {
					$protect = true;
				}
			
				if( $protect ){
					$wp_query->set_404();
					status_header(503);
					get_template_part( 404 );
					exit;
				}
			}
		}
	}
	
	/*
	 * Get values for input fields
	 */
	private function get_values($item_id, $option, $default = NULL, $type = 'post_type') {
		
		// Post types and custom links are inside post meta
		$function = 'get_post_meta';
		
		// Taxonomy need to be look at term meta
		if($type == 'taxonomy') {
			$function = 'get_term_meta';
		}
		
		$key = "{$function}_{$item_id}";
		
		if( $menu_options[$key] ?? NULL ) {
			if(NULL === $option) {
				return $menu_options[$key];
			} else {
				return $menu_options[$key][$option];
			}
		} else if($control = $function( $item_id, 'cfgp_menu_item_control', true )) {
			$menu_options[$key] = CFGP_Options::sanitize($control);
			
			if(NULL === $option) {
				return $menu_options[$key];
			} else {
				return $menu_options[$key][$option];
			}
		}
		
		return $default;
	}

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


} endif;