<?php if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
/**
 * Hooks, actions and other helpers
 *
 * @since      7.0.0
 * @package    CF_Geoplugin
 * @author     Ivijan-Stefan Stipic
 */

if(!class_exists('CF_Geoplugin_Notification')) :
class CF_Geoplugin_Notification extends CF_Geoplugin_Global
{
	
	function __construct()
	{
		if( defined( 'CFGP_DISABLE_NOTIFICATION' ) && CFGP_DISABLE_NOTIFICATION ) return;
		
		if(!$this->check_activation() && !$this->check_defender_activation())
		{
			$this->lookup_expire_soon();
			$this->lookup_expired();
		}
		
		if($this->check_activation() && !$this->check_defender_activation())
		{
			$this->license_expire_soon();
		}
	}
	
	public function license_expire_soon()
	{
		if( defined( 'CFGP_DISABLE_NOTIFICATION_EXPIRE_SOON' ) && CFGP_DISABLE_NOTIFICATION_EXPIRE_SOON ) return;
		
		$transient = 'cf-geoplugin-notification-license-expire-soon';
		if(!get_transient($transient) )
		{
			// Let's first validate license
			if(isset($CF_GEOPLUGIN_OPTIONS['license_expire']) && !empty($CF_GEOPLUGIN_OPTIONS['license_expire']) && strtotime('-1 month', (int)$CF_GEOPLUGIN_OPTIONS['license_expire']) < time()){
				$this->validate();
			} else return;
			
			$CF_GEOPLUGIN_OPTIONS = $GLOBALS['CF_GEOPLUGIN_OPTIONS'];
			
			$before_date_expire = strtotime('-1 month', (int)$CF_GEOPLUGIN_OPTIONS['license_expire']);
			if($before_date_expire <= time())
			{
				if($emails = $this->get_admins())
				{
					$message = array();
					$message[]= '<p>' . __('Hi there,', CFGP_NAME) . '</p>';
					$message[]= '<p>' . __('Your license expire soon!', CFGP_NAME) . '</p>';
					$message[]= '<p>' . __('Please renew your license before it expires so that you can use all services without interruption.', CFGP_NAME) . '</p>';
					$message[]= '<p>' . sprintf(
						__('Your license expires on %1$s and after that date you will be returned to the limited lookup which may create a limitation on your current WordPress installation.', CFGP_NAME),
						date_i18n( get_option( 'date_format' ), (int)$CF_GEOPLUGIN_OPTIONS['license_expire'] )
					) . '</p>';
					
					
					$message[]= '<p>' . sprintf(
						__('To extend your license, %1$s.', CFGP_NAME),
						'<a href="' . CFGP_STORE . '/my-account/?view-license-key=1&key=' . trim($CF_GEOPLUGIN_OPTIONS['license_key']) . '&hook_action=extend" target="_blank">' . __('CLICK HERE', CFGP_NAME) . '</a>'
					) . '</p>';
					
					$message[]= '<p>&nbsp;</p>';
					$message[]= '<p>' . __('NOTE: You will receive this message a few more times before the license expires.', CFGP_NAME) . '</p>';
					
					$message = apply_filters('cf_geoplugin_notification_license_expire_soon', $message);

					$this->send($emails, __('CF GEO PLUGIN NOTIFICATION - Your license will expire soon', CFGP_NAME), $message);
					set_transient($transient, 1, (60*60*24*7)); // 7 days
				}
			}
		}
	}
	
	public function lookup_expired()
	{
		if( defined( 'CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRED' ) && CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRED ) return;
		
		$CFGEO = $GLOBALS['CFGEO'];
		$transient = 'cf-geoplugin-notification-lookup-expired';
		if(is_numeric($CFGEO['lookup'] ) && (int)$CFGEO['lookup'] < 1 && !get_transient($transient) )
		{
			if($emails = $this->get_admins())
			{
				$message = array();
				$message[]= '<p>' . __('Hi there,', CFGP_NAME) . '</p>';
				$message[]= '<p>' . sprintf(__('You have spent all available geolocation lookup on the %1$s site.', CFGP_NAME), '<a href="' . get_bloginfo('url') . '" target="_blank">' . get_bloginfo('name') . '</a>') . '</p>';
				$message[]= '<p>' . sprintf(
					__('You seem to have a large number of visits to your site and need an %1$s number of geolocation lookup.', CFGP_NAME),
					'<a href="' . CFGP_STORE . '/pricing/" target="_blank">' . __('UNLIMITED', CFGP_NAME) . '</a>'
				) . '</p>';
				$message[]= '<p>' . sprintf(
					__('More information about unlimited lookup %1$s.', CFGP_NAME),
					'<a href="' . CFGP_STORE . '/documentation/quick-start/what-do-i-get-from-unlimited-license/" target="_blank">' . __('you can find here', CFGP_NAME) . '</a>'
				) . '</p>';
				$message[]= '<p>' . sprintf(
					__('But don\'t worry, in 24 hours you will have %1$d lookups again and geo services will continue to work.', CFGP_NAME),
					CFGP_LIMIT
				) . '</p>';
				
				$message = apply_filters('cf_geoplugin_notification_lookup_expired_message', $message);

				$this->send($emails, __('CF GEO PLUGIN NOTIFICATION - Lookup expired', CFGP_NAME), $message);
				set_transient($transient, 1, (60*60*24)); // 24 hours
			}
		}
	}

	public function lookup_expire_soon()
	{
		if( defined( 'CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRE_SOON' ) && CFGP_DISABLE_NOTIFICATION_LOOKUP_EXPIRE_SOON ) return;
		
		$CFGEO = $GLOBALS['CFGEO'];
		$transient = 'cf-geoplugin-notification-lookup-expire-soon';
		if(is_numeric($CFGEO['lookup'] ) && (int)$CFGEO['lookup'] > 1 && (int)$CFGEO['lookup'] <= 50 && !get_transient($transient) )
		{
			if($emails = $this->get_admins())
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
					$CFGEO['lookup'],
					'<a href="' . CFGP_STORE . '/pricing/" target="_blank">' . __('extend your license', CFGP_NAME) . '</a>'
				) . '</p>';
				
				$message = apply_filters('cf_geoplugin_notification_lookup_expire_soon_message', $message);

				$this->send($emails, __('CF GEO PLUGIN NOTIFICATION - Lookup expires soon', CFGP_NAME), $message);
				set_transient($transient, 1, (60*60*24)); // 24 hours
			}
		}
	}
	
	public function send($email, $subject, $message, $headers = array(), $attachments = array()){
		$this->add_filter( 'wp_mail_content_type', '_content_type');
		
		if(is_array($message)) $message = join(PHP_EOL,$message);
		
		$headers = apply_filters('cf_geoplugin_notification_mail_headers', array_merge($headers, array('Content-Type: text/html; charset=UTF-8')), $headers);
		
		$return = wp_mail( $email, $subject, $this->template($subject, $message), $headers, $attachments );
		$this->remove_filter( 'wp_mail_content_type', '_content_type' );
		return $return;
	}
	
	public function _content_type(){
		return 'text/html';
	}
	
	private function get_admins(){
		$emails = array();
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
			
			if(!empty($emails))
			{
				return apply_filters('cf_geoplugin_notification_emails', $emails);
			}
		}
		
		return false;
	}
	
	private function template($subject, $content){
		ob_start(); ?>
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
        .footer span,
        .footer a {
          color: #999999;
          font-size: 12px;
          text-align: center; 
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
        color: #3498db;
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
          border: solid 1px #3498db;
          border-radius: 5px;
          box-sizing: border-box;
          color: #3498db;
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
        background-color: #3498db; 
      }

      .btn-primary a {
        background-color: #3498db;
        border-color: #3498db;
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
	<span class="preheader"><?php echo $subject; ?></span>
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
}
endif;