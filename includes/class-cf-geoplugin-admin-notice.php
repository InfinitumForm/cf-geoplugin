<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Metaboxes
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic

$notice = CF_Geoplugin_Notice::instance();
$notice->register_notice( 'my_notice', 'warning', 'This is the message' ) );
$notice->register_notice( 'my_notice', 'warning', 'This is the message', array( 'scope' => 'user' ) ) );

Available parameters
------------------------+-------------------+-------------------------------+-----------------------+---------------------------------------------
Parameter				| Type				| Options						| Defaults				| Description
------------------------+-------------------+-------------------------------+-----------------------+---------------------------------------------
id						| string			| 								| 						| Required ID to identify the notice
------------------------+-------------------+-------------------------------+-----------------------+---------------------------------------------
type					| string			| success, warning,				| 						| Determine the type of notice
											| error, info					| 						| 
------------------------+-------------------+-------------------------------+-----------------------+---------------------------------------------
message					| string			| 								| 						| The message you wish to display within WordPress
------------------------+-------------------+-------------------------------+-----------------------+---------------------------------------------
args					| array				| scope (global, user),			| scope = global		| Additional settings available for the notice
						| 					| dismissible (true/false), 	| dismissible = true	| 
						| 					| cap, class					| 						| 
------------------------+-------------------+-------------------------------+-----------------------+---------------------------------------------
 */

if( !class_exists( 'CF_Geoplugin_Notice' ) ) :
class CF_Geoplugin_Notice {

	/**
	 * The class instance.
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * The current version of the library.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * List of registered notices.
	 *
	 * @var array
	 */
	private $notices;

	/**
	 * Get things started.
	 *
	 * @return void
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CF_Geoplugin_Notice ) ) {
			self::$instance = new CF_Geoplugin_Notice;
			self::$instance->init();
		}

		return self::$instance;

	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	private function init() {
		add_action( 'admin_notices', array( self::$instance, 'display' ) );
		add_action( 'admin_print_scripts', array( self::$instance, 'load_script' ) );
		add_action( 'wp_ajax_cfgeo_dismiss_notice', array( self::$instance, 'dismiss_notice_ajax' ) );
	}

	/**
	 * Load script to dismiss notices.
	 *
	 * @return void
	 */
	public function load_script() {
		wp_register_script( CFGP_NAME . '-notices', CFGP_ASSETS . '/js/notices.js', array( 'jquery' ), self::$instance->version, true );
		wp_enqueue_script( CFGP_NAME . '-notices' );
	}

	/**
	 * Display all the notices.
	 *
	 * @return void
	 */
	public function display() {

		if ( is_null( self::$instance->notices ) || empty( self::$instance->notices ) ) {
			return;
		}

		foreach ( self::$instance->notices as $id => $notice ) {

			$id = self::$instance->get_id( $id );

			// Check if the notice was dismissed.
			if ( self::$instance->is_dismissed( $id ) ) {
				continue;
			}

			if ( ! empty( $notice['cap'] ) && ! current_user_can( $notice['cap'] ) ) {
					continue;
			}

			$class = array(
				'notice',
				'notice-' . $notice['type'],
				$notice['dismissible']===true ? 'is-dismissible' : false,
				$notice['class'],
			);

			printf( '<div id="%3$s" class="%1$s"><p>%2$s</p></div>', trim( implode( ' ', $class ) ), $notice['content'], "cfgeo-$id" );

		}

	}

	/**
	 * Get the id of the notice.
	 *
	 * @param string $id
	 * @return void
	 */
	public function get_id( $id ) {
		return sanitize_key( $id );
	}

	/**
	 * List of currently available notice types.
	 *
	 * @return void
	 */
	public function get_types() {
		$types = array(
			'error',
			'warning',
			'success',
			'info',
		);
		return apply_filters( 'cfgeo_notice_types', $types );
	}

	/**
	 * Default settings for each notice.
	 *
	 * @return void
	 */
	private function default_args() {
		$args = array(
			'scope'       => 'global',
			'dismissible' => true,
			'cap'         => 'manage_options',
			'class'       => '',
		);
		return apply_filters( 'cfgeo_default_args', $args );
	}

	/**
	 * Register a notice.
	 *
	 * @param string $id
	 * @param string $type
	 * @param string $content
	 * @param array $args
	 * @return void
	 */
	public function register_notice( $id, $type, $content, $args = array() ) {
		if ( is_null( self::$instance->notices ) ) {
			self::$instance->notices = array();
		}

		$id      = self::$instance->get_id( $id );
		$type    = in_array( $t = sanitize_text_field( $type ), self::$instance->get_types() ) ? $t : 'updated';
		$content = wp_kses_post( $content );
		$args    = wp_parse_args( $args, self::$instance->default_args() );

		$notice = array(
			'type'    => $type,
			'content' => $content,
		);

		$notice                         = array_merge( $notice, $args );
		self::$instance->notices[ $id ] = $notice;

		return true;
	}

	/**
	 * Dismiss a notice via ajax.
	 *
	 * @return void
	 */
	public function dismiss_notice_ajax() {
		if ( ! isset( $_POST['id'] ) ) {
			echo 0;
			exit;
		}
		if ( empty( $_POST['id'] ) || false === strpos( $_POST['id'], 'cfgeo-' ) ) {
			echo 0;
			exit;
		}

		$id = self::$instance->get_id( str_replace( 'cfgeo-', '', $_POST['id'] ) );
		echo self::$instance->dismiss_notice( $id );
		exit;

	}

	/**
	 * Dismiss notice into the database.
	 *
	 * @param string $id
	 * @return void
	 */
	public function dismiss_notice( $id ) {
		$notice = self::$instance->get_notice( self::$instance->get_id( $id ) );
		if ( self::$instance->is_dismissed( $id ) ) {
			return false;
		}
		return 'user' === $notice['scope'] ? self::$instance->dismiss_user( $id ) : self::$instance->dismiss_global( $id );
	}

	/**
	 * Dismiss notice for an user.
	 *
	 * @param string $id the notice id.
	 * @return void
	 */
	private function dismiss_user( $id ) {
		$dismissed = self::$instance->dismissed_user();
		if ( in_array( $id, $dismissed ) ) {
			return false;
		}
		array_push( $dismissed, $id );
		return update_user_meta( get_current_user_id(), 'cf_geoplugin_dismissed_notices', $dismissed );
	}

	/**
	 * Dismiss a global notice.
	 *
	 * @param string $id the id of the notice.
	 * @return void
	 */
	private function dismiss_global( $id ) {
		$dismissed = self::$instance->dismissed_global();
		if ( in_array( $id, $dismissed ) ) {
			return false;
		}
		array_push( $dismissed, $id );
		return update_option( 'cf_geoplugin_dismissed_notices', $dismissed );
	}

	/**
	 * Restore a notice previously dismissed.
	 *
	 * @param string $id the id of the notice.
	 * @return void
	 */
	public function restore_notice( $id ) {
		$id     = self::$instance->get_id( $id );
		$notice = self::$instance->get_notice( $id );
		if ( false === $notice ) {
			return false;
		}
		return 'user' === $notice['scope'] ? self::$instance->restore_user( $id ) : self::$instance->restore_global( $id );
	}

	/**
	 * Restore a notice for the current user.
	 *
	 * @param string $id the id of the notice.
	 * @return void
	 */
	private function restore_user( $id ) {
		$id     = self::$instance->get_id( $id );
		$notice = self::$instance->get_notice( $id );

		if ( false === $notice ) {
			return false;
		}

		$dismissed = self::$instance->dismissed_user();
		if ( ! in_array( $id, $dismissed ) ) {
			return false;
		}

		$flip = array_flip( $dismissed );
		$key  = $flip[ $id ];
		unset( $dismissed[ $key ] );

		return update_user_meta( get_current_user_id(), 'cf_geoplugin_dismissed_notices', $dismissed );
	}

	/**
	 * Restore a globally dismissed notice.
	 *
	 * @param string $id
	 * @return void
	 */
	private function restore_global( $id ) {
		$id     = self::$instance->get_id( $id );
		$notice = self::$instance->get_notice( $id );

		if ( false === $notice ) {
			return false;
		}

		$dismissed = self::$instance->dismissed_global();

		if ( ! in_array( $id, $dismissed ) ) {
			return false;
		}

		$flip = array_flip( $dismissed );
		$key  = $flip[ $id ];

		unset( $dismissed[ $key ] );
		return update_option( 'cf_geoplugin_dismissed_notices', $dismissed );
	}

	/**
	 * Get list of dismissed notices.
	 *
	 * @return array
	 */
	public function dismissed_notices() {
		$user   = self::$instance->dismissed_user();
		$global = self::$instance->dismissed_global();

		return array_merge( $user, $global );
	}

	/**
	 * Get a list of dismissed notices for the current user.
	 *
	 * @return void
	 */
	private function dismissed_user() {
		$dismissed = get_user_meta( get_current_user_id(), 'cf_geoplugin_dismissed_notices', true );
		if ( '' === $dismissed ) {
			$dismissed = array();
		}
		return $dismissed;
	}

	/**
	 * Return a list of globally dismissed notices.
	 *
	 * @return array
	 */
	private function dismissed_global() {
		return get_option( 'cf_geoplugin_dismissed_notices', array() );
	}

	/**
	 * Check if a notice is dismissed.
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function is_dismissed( $id ) {
		$dismissed = self::$instance->dismissed_notices();
		if ( ! in_array( self::$instance->get_id( $id ), $dismissed ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Retrieve currently registered notices.
	 *
	 * @return mixed
	 */
	public function get_notices() {
		return self::$instance->notices;
	}

	/**
	 * Get a registered notice.
	 *
	 * @param string $id the id of the notice.
	 * @return void
	 */
	public function get_notice( $id ) {
		$id = self::$instance->get_id( $id );
		if ( ! is_array( self::$instance->notices ) || ! array_key_exists( $id, self::$instance->notices ) ) {
			return false;
		}
		return self::$instance->notices[ $id ];
	}

}
endif;