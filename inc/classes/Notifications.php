<?php
/**
 * Notifications control
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       2.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Notifications')) :
class CFGP_Notifications extends CFGP_Global{

	function __construct(){
		
		$this->add_action( 'admin_init', 'check_installation_time' );
		$this->add_action( 'admin_init', 'cfgp_dimiss_review', 5 );
		
		if( defined( 'CFGP_DISABLE_NOTIFICATION' ) && CFGP_DISABLE_NOTIFICATION ) {
			return;
		} else {
			if( !CFGP_License::activated() ){
				$this->add_filter('cf_geoplugin_notification_emails', 'remove_spams', 1);
				$this->add_filter('init', 'lookup_expire_soon');
			}
		}
	}
	
	// remove the notice for the user if review already done or if the user does not want to
	public function cfgp_dimiss_review(){    
		if( isset( $_GET['cfgp_dimiss_review'] ) && !empty( $_GET['cfgp_dimiss_review'] ) ){
			$cfgp_dimiss_review = $_GET['cfgp_dimiss_review'];
			if( $cfgp_dimiss_review == 1 ){
				add_option( CFGP_NAME . '-reviewed' , true );
				
				$parse_url = CFGP_U::parse_url();
				if(wp_safe_redirect(remove_query_arg('cfgp_dimiss_review', $parse_url['url']))) {
					exit;
				}
			}
		}
	}
	
	// check if review notice should be shown or not
	public function check_installation_time() {
		
		if(get_option(CFGP_NAME . '-reviewed')){
			return;
		}
		
		$get_dates = get_option(CFGP_NAME . '-activation');
		if(is_array($get_dates)){
			$install_date = strtotime(end($get_dates));
		} else {
			$install_date = strtotime($get_dates);
		}
		
		$past_date = strtotime( '-7 days' );
	 
		if ( $past_date >= $install_date) {
			$this->add_action( 'admin_notices', 'display_admin_notice' );
		}
	}
	
	/**
	 * Display Admin Notice, asking for a review
	**/
	public function display_admin_notice() {
		$parse_url = CFGP_U::parse_url();
		$dont_disturb = esc_url( add_query_arg('cfgp_dimiss_review', '1', $parse_url['url']) );
		$plugin_info = get_plugin_data( CFGP_FILE , true, true );       
		$reviewurl = esc_url( 'https://wordpress.org/support/plugin/cf-geoplugin/reviews/?filter=5#new-post' );
	 
		printf(
			'<div class="notice notice-info"><h3>'.__('You have been using <b> %1$s </b> plugin for a while. We hope you liked it!', CFGP_NAME).'</h3><p>'.__('Please give us a quick rating, it works as a boost for us to keep working on the plugin!', CFGP_NAME).'</p><p class="void-review-btn"><a href="%2$s" class="button button-primary" target="_blank">'.__('Rate Now!', CFGP_NAME).'</a> &nbsp;&nbsp;<a href="%3$s" class="void-grid-review-done">'.__('I\'ve already done that!', CFGP_NAME).'</a></p></div>',
			$plugin_info['Name'],
			$reviewurl,
			$dont_disturb
		);
	}
	
	/*
	 * Lookup Expire Soon Message
	 */
	public function lookup_expire_soon() {
		if( defined( 'CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRE_SOON' ) && CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRE_SOON ) return;
		
		$transient = 'cfgp-notification-lookup-expire-soon';

		if( get_transient($transient) ) return;
		
		if( CFGP_U::api('lookup') != 'unlimited' && CFGP_U::api('lookup') != 'lifetime' && CFGP_U::api('lookup') <= 100 && ($emails = $this->get_admins()))
		{		
			$message = array();
			$message[]= '<p>' . __('Hi there,', CFGP_NAME) . '</p>';
			$message[]= '<p>' . __('Your lookup will expire soon and geo plugin services will be unavailable for the next 24 hours.', CFGP_NAME) . '</p>';
			$message[]= '<p>' . sprintf(
				__('If your site has a large traffic and you need the full functionality of the plugin, you need to get the appropriate license and activate the %1$s.', CFGP_NAME),
				'<a href="' . CFGP_STORE . '/pricing/" target="_blank">' . __('UNLIMITED LOOKUP.', CFGP_NAME) . '</a>'
			) . '</p>';
			$message[]= '<p>' . sprintf(
				__('You currently have %1$d lookups available and need to %2$s so that all services work smoothly.', CFGP_NAME),
				CFGP_U::api('lookup'),
				'<a href="' . CFGP_STORE . '/pricing/" target="_blank">' . __('extend your license', CFGP_NAME) . '</a>'
			) . '</p>';
			
			$message = apply_filters('cf_geoplugin_notification_lookup_expire_soon_message', $message);

			$this->send($emails, __('CF GEO PLUGIN NOTIFICATION - Lookup expires soon', CFGP_NAME), $message);
			set_transient($transient, CFGP_TIME, DAY_IN_SECONDS); // 24 hours
		}
	}
	
	/*
	 * Send message
	 */
	public function send($email, $subject, $message, $headers = array(), $attachments = array()){
		$this->add_filter( 'wp_mail_content_type', '_content_type');
		
		if(is_array($message)) $message = join(PHP_EOL,$message);
		
		$headers = apply_filters('cf_geoplugin_notification_mail_headers', array_merge($headers, array('Content-Type: text/html; charset=UTF-8')), $headers);
		
		$return = wp_mail( $email, $subject, $this->template($subject, $message), $headers, $attachments );
		$this->remove_filter( 'wp_mail_content_type', '_content_type' );
		return $return;
	}
	
	/*
	 * Set Content Type
	 */
	public function _content_type(){
		return 'text/html';
	}
	
	/*
	 * Let's filter spammers.
	 * Everyone is responsible for their own license.
	 */
	public function remove_spams( $emails ) {
		if(strpos($_SERVER['HTTP_HOST'], 'cfgeoplugin') === false)
		{
			foreach($emails as $e=>$email)
			{
				if(strpos($email, 'cfgeoplugin') !== false){
					unset($emails[$e]);
				}
			}
		}
		return $emails;
	}
	
	/*
	 * Get admins email
	 */
	private function get_admins(){		
		$emails = array();
		
		if(CFGP_Options::get('notification_recipient_type') == 'manual')
		{
			$explode_emails = explode(',', CFGP_Options::get('notification_recipient_emails'));
			$explode_emails = array_map('trim', $explode_emails);
			$explode_emails = array_map('sanitize_email', $explode_emails);
			$emails = array_filter($explode_emails);
		}
		else if(CFGP_Options::get('notification_recipient_type') == 'all')
		{
			$admins = get_users(
				apply_filters(
					'cf_geoplugin_notification_users_setup',
					array(
						'role__in' => apply_filters(
							'cf_geoplugin_notification_user_roles',
							array( 'administrator' )
						) 
					)
				)
			);

			if($admins && is_array($admins))
			{
				foreach ( $admins as $admin ) {
					$emails[$admin->ID]= $admin->user_email;
				}
			}
		}
		
		
		$emails = apply_filters('cf_geoplugin_notification_emails', $emails);
		
		if(!empty($emails))
		{
			return $emails;
		}
		
		return false;
	}
	
	
	/*
	 * Email Template
	 */
	private function template($subject, $content){
		ob_start( 'trim', 0, PHP_OUTPUT_HANDLER_REMOVABLE ); ?>
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $subject; ?></title>
    <style>
      /* -------------------------------------
          GLOBAL RESETS
      ------------------------------------- */
      
      /*All the styling goes here*/
      
      img {
        border: none;
        -ms-interpolation-mode: bicubic;
        max-width: 100%; 
      }

      body {
        background-color: #f6f6f6;
        font-family: sans-serif;
        -webkit-font-smoothing: antialiased;
        font-size: 14px;
        line-height: 1.4;
        margin: 0;
        padding: 0;
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%; 
      }

      table {
        border-collapse: separate;
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
        width: 100%; }
        table td {
          font-family: sans-serif;
          font-size: 14px;
          vertical-align: top; 
      }

      /* -------------------------------------
          BODY & CONTAINER
      ------------------------------------- */

      .body {
        background-color: #f6f6f6;
        width: 100%; 
      }

      /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
      .container {
        display: block;
        margin: 0 auto !important;
        /* makes it centered */
        max-width: 580px;
        padding: 10px;
        width: 580px; 
      }

      /* This should also be a block element, so that it will fill 100% of the .container */
      .content {
        box-sizing: border-box;
        display: block;
        margin: 0 auto;
        max-width: 580px;
        padding: 10px; 
      }

      /* -------------------------------------
          HEADER, FOOTER, MAIN
      ------------------------------------- */
      .main {
        background: #ffffff;
        border-radius: 3px;
        width: 100%; 
      }

      .wrapper {
        box-sizing: border-box;
        padding: 20px; 
      }

      .content-block {
        padding-bottom: 10px;
        padding-top: 10px;
      }

      .footer {
        clear: both;
        margin-top: 10px;
        text-align: center;
        width: 100%; 
      }
        .footer td,
        .footer p,
        .footer span{
          color: #787878;
          font-size: 12px;
          text-align: center; 
      }
	  
	  .footer a {
          color: #fd4624;
      }

      /* -------------------------------------
          TYPOGRAPHY
      ------------------------------------- */
      h1,
      h2,
      h3,
      h4 {
        color: #000000;
        font-family: sans-serif;
        font-weight: 400;
        line-height: 1.4;
        margin: 0;
        margin-bottom: 30px; 
      }

      h1 {
        font-size: 35px;
        font-weight: 300;
        text-align: center;
        text-transform: capitalize; 
      }

      p,
      ul,
      ol {
        font-family: sans-serif;
        font-size: 14px;
        font-weight: normal;
        margin: 0;
        margin-bottom: 15px; 
      }
        p li,
        ul li,
        ol li {
          list-style-position: inside;
          margin-left: 5px; 
      }

      a {
        color: #fd4624;
        text-decoration: underline; 
      }

      /* -------------------------------------
          BUTTONS
      ------------------------------------- */
      .btn {
        box-sizing: border-box;
        width: 100%; }
        .btn > tbody > tr > td {
          padding-bottom: 15px; }
        .btn table {
          width: auto; 
      }
        .btn table td {
          background-color: #ffffff;
          border-radius: 5px;
          text-align: center; 
      }
        .btn a {
          background-color: #ffffff;
          border: solid 1px #fd4624;
          border-radius: 5px;
          box-sizing: border-box;
          color: #fd4624;
          cursor: pointer;
          display: inline-block;
          font-size: 14px;
          font-weight: bold;
          margin: 0;
          padding: 12px 25px;
          text-decoration: none;
          text-transform: capitalize; 
      }

      .btn-primary table td {
        background-color: #fd4624; 
      }

      .btn-primary a {
        background-color: #fd4624;
        border-color: #fd4624;
        color: #ffffff; 
      }

      /* -------------------------------------
          OTHER STYLES THAT MIGHT BE USEFUL
      ------------------------------------- */
      .last {
        margin-bottom: 0; 
      }

      .first {
        margin-top: 0; 
      }

      .align-center {
        text-align: center; 
      }

      .align-right {
        text-align: right; 
      }

      .align-left {
        text-align: left; 
      }

      .clear {
        clear: both; 
      }

      .mt0 {
        margin-top: 0; 
      }

      .mb0 {
        margin-bottom: 0; 
      }

      .preheader {
        color: transparent;
        display: none;
        height: 0;
        max-height: 0;
        max-width: 0;
        opacity: 0;
        overflow: hidden;
        mso-hide: all;
        visibility: hidden;
        width: 0; 
      }

      .powered-by a {
        text-decoration: none; 
      }

      hr {
        border: 0;
        border-bottom: 1px solid #f6f6f6;
        margin: 20px 0; 
      }

      /* -------------------------------------
          RESPONSIVE AND MOBILE FRIENDLY STYLES
      ------------------------------------- */
      @media only screen and (max-width: 620px) {
        table[class=body] h1 {
          font-size: 28px !important;
          margin-bottom: 10px !important; 
        }
        table[class=body] p,
        table[class=body] ul,
        table[class=body] ol,
        table[class=body] td,
        table[class=body] span,
        table[class=body] a {
          font-size: 16px !important; 
        }
        table[class=body] .wrapper,
        table[class=body] .article {
          padding: 10px !important; 
        }
        table[class=body] .content {
          padding: 0 !important; 
        }
        table[class=body] .container {
          padding: 0 !important;
          width: 100% !important; 
        }
        table[class=body] .main {
          border-left-width: 0 !important;
          border-radius: 0 !important;
          border-right-width: 0 !important; 
        }
        table[class=body] .btn table {
          width: 100% !important; 
        }
        table[class=body] .btn a {
          width: 100% !important; 
        }
        table[class=body] .img-responsive {
          height: auto !important;
          max-width: 100% !important;
          width: auto !important; 
        }
      }

      /* -------------------------------------
          PRESERVE THESE STYLES IN THE HEAD
      ------------------------------------- */
      @media all {
        .ExternalClass {
          width: 100%; 
        }
        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
          line-height: 100%; 
        }
        .apple-link a {
          color: inherit !important;
          font-family: inherit !important;
          font-size: inherit !important;
          font-weight: inherit !important;
          line-height: inherit !important;
          text-decoration: none !important; 
        }
        #MessageViewBody a {
          color: inherit;
          text-decoration: none;
          font-size: inherit;
          font-family: inherit;
          font-weight: inherit;
          line-height: inherit;
        }
        .btn-primary table td:hover {
          background-color: #34495e !important; 
        }
        .btn-primary a:hover {
          background-color: #34495e !important;
          border-color: #34495e !important; 
        } 
      }

    </style>
  </head>
  <body class="">
	<span class="preheader"><?php printf(__('If you no longer wish to receive these notifications, please read how to %1$s.', CFGP_NAME), '<a href="' . CFGP_STORE . '/documentation/advanced-usage/php-integration/constants/cfgp_disable_notification/" target="_blank">' . __('disable this notifications', CFGP_NAME) . '</a>'); ?></span>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
      <tr>
        <td>&nbsp;</td>
        <td class="container">
          <div class="content">

            <!-- START CENTERED WHITE CONTAINER -->
            <table role="presentation" class="main">

              <!-- START MAIN CONTENT AREA -->
              <tr>
                <td class="wrapper">
                  <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>
						<?php echo $content;?>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

            <!-- END MAIN CONTENT AREA -->
            </table>
            <!-- END CENTERED WHITE CONTAINER -->

            <!-- START FOOTER -->
            <div class="footer">
              <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td class="content-block">
                    <span class="apple-link"><?php printf(__('This email is automatically sent by CF Geo Plugin via site %1$s.', CFGP_NAME), '<a href="' . get_bloginfo('url') . '" target="_blank">' . get_bloginfo('name') . '</a>'); ?><br>
					<a href="<?php echo CFGP_STORE ?>/privacy-policy/" target="_blank"><?php _e('Privacy Policy', CFGP_NAME); ?></a> | <a href="<?php echo CFGP_STORE ?>/terms-and-conditions/" target="_blank"><?php _e('Terms And Conditions', CFGP_NAME); ?></a> | <a href="<?php echo CFGP_STORE ?>/documentation/" target="_blank"><?php _e('Documentation', CFGP_NAME); ?></a> | <a href="<?php echo CFGP_STORE ?>/contact-and-support/" target="_blank"><?php _e('Contact & Support', CFGP_NAME); ?></a></span>
                  </td>
                </tr>
                <tr>
                  <td class="content-block powered-by">
                    <?php printf(__('Powered by  %1$s.', CFGP_NAME), '<a href="' . CFGP_STORE . '" target="_blank">CF Geo Plugin</a>'); ?>
                  </td>
                </tr>
				<tr>
                  <td class="content-block powered-by"><br><br>
                    <?php printf(__('If you no longer wish to receive these notifications, please read how to %1$s.', CFGP_NAME), '<a href="' . CFGP_STORE . '/documentation/advanced-usage/php-integration/constants/cfgp_disable_notification/" target="_blank">' . __('disable this notifications', CFGP_NAME) . '</a>'); ?>
                  </td>
                </tr>
              </table>
            </div>
            <!-- END FOOTER -->

          </div>
        </td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </body>
</html><?php return ob_get_clean();
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
}
endif;