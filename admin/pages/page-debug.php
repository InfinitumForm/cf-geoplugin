<?php
	include_once CFGP_ROOT . '/includes/class-cf-geoplugin-os.php';
	$ip=CFGP_IP;
	
	$set=array();
	if(isset($_POST['ip_address']) && !empty($_POST['ip_address']))
		$set['ip']=trim($_POST['ip_address']);
	$gp=new CF_Geoplugin_API($set);
	$gpReturn=$gp->returns;
	foreach(array('state','continentCode','areaCode','dmaCode','timezoneName','currencySymbol','currencyConverter','ip_number') as $rm){
		unset($gpReturn[$rm]);
	}
	
?>
<div class="wrap">
    <h2><span class="fa fa-bug"></span> <?php _e('Debug Mode',CFGP_NAME); ?></h2>
        <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
	<?php if($ip == '0.0.0.0') : ?>
		<h3 style="color:#cc0000"><span class="fa fa-info-circle"></span> <?php _e('NOTE: You running plugin on local server.',CFGP_NAME); ?></h3>
	<?php endif; ?>
    <p class="about-description"><?php _e('If you have issue with CF GeoPlugin, here you can see what CF GeoPlugin return from our API and how that looks.',CFGP_NAME); ?></p>
    <?php if(isset($gpReturn['error']) && $gpReturn['error']): ?>
    <div class="notice notice-error"><p><strong><?php _e('CF GeoPlugin Warning',CFGP_NAME); ?>:</strong> <?php echo $gpReturn['error_message']?>.</p></div>
    <?php endif; ?>
    <?php if(isset($gpReturn['runtime']) && !empty($gpReturn['runtime'])): ?>
    <div class="welcome-panel text-big">
    	<?php _e('Connection quality',CFGP_NAME); ?>: <?php
        	if(round($gpReturn['runtime'])<=0){
			echo '<span class="green"><span class="fa fa-battery-full"> '.__('exellent',CFGP_NAME).'</span></span>';
		}
		else if(round($gpReturn['runtime']) == 1){
			echo '<span class="green"><span class="fa fa-battery-three-quarters"> '.__('perfect',CFGP_NAME).'</span></span>';
		}
		else if(round($gpReturn['runtime']) == 2){
			echo '<span class="green"><span class="fa fa-battery-half"> '.__('good',CFGP_NAME).'</span></span>';
		}
		else if(round($gpReturn['runtime']) == 3){
			echo '<span class="orange"><span class="fa fa-battery-quarter"> '.__('week',CFGP_NAME).'</span></span>';
		}
		else if(round($gpReturn['runtime']) >= 4){
			echo '<span class="red"><span class="fa fa-battery-empty"> '.__('bad',CFGP_NAME).'</span></span>';
		}
		echo ' ('.$gpReturn['runtime'].'ms)';
		?>
    </div>
    <?php endif; ?>

    <h3><?php _e('IP Lookup',CFGP_NAME); ?></h3>
	<p><?php echo sprintf(__('Enter your custom IP address to see how CF GeoPlugin API version %s works.',CFGP_NAME),CFGP_VERSION); ?></p>
    <form method="post" enctype="multipart/form-data" action="<?php echo  get_admin_url(); ?>admin.php?page=<?php echo $_GET['page']?>&settings-updated=true" target="_self" id="template-options-tab">
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="cf_geo_onyly_timezone"><?php _e('Insert Custom IP',CFGP_NAME); ?>:</label>
                </th>
                <td>
                    <input type="text" value="<?php echo (isset($_POST['ip_address']) && !empty($_POST['ip_address']) ? trim($_POST['ip_address']):''); ?>" name="ip_address" placeholder="<?php echo $ip; ?>" autocomplete="off"><button type="submit"><span class="fa fa-eye"></span> <?php _e('Lookup',CFGP_NAME); ?></button>
                </td>
            </tr>
         </tbody>
    </table>
    </form>
    <h3><?php _e('Your WordPress Informations',CFGP_NAME); ?></h3>
    <p><?php _e('This informations are from your WordPress blog and is only visible to you',CFGP_NAME); ?></p>
    <table width="100%" class="wp-list-table widefat fixed striped pages">
    	<thead>
            <tr>
                <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Name',CFGP_NAME); ?></strong></th>
                <th class="manage-column column-returns column-primary"><strong><?php _e('Value',CFGP_NAME); ?></strong></th>
            </tr>
        </thead>
        <tbody>
        	<tr>
                <td class="code"><?php _e('Site Title',CFGP_NAME); ?></td>
                <td><?php echo get_bloginfo("name"); ?></td>
            </tr>
            <tr>
                <td class="code"><?php _e('Tagline',CFGP_NAME); ?></td>
                <td><?php echo get_bloginfo("description"); ?></td>
            </tr>
            <tr>
                <td class="code"><?php _e('WordPress address (URL)',CFGP_NAME); ?></td>
                <td><?php echo get_bloginfo("wpurl"); ?></td>
            </tr>
            <tr>
                <td class="code"><?php _e('WordPress Host',CFGP_NAME); ?></td>
                <td><?php
                	$url=parse_url(get_bloginfo("wpurl"));
					echo str_replace("www.","",$url['host']);
				?></td>
            </tr>
            <tr>
                <td class="code"><?php _e('Admin Email',CFGP_NAME); ?></td>
                <td><?php echo get_bloginfo("admin_email"); ?></td>
            </tr>
            <tr>
                <td class="code"><?php _e('Encoding for pages and feeds',CFGP_NAME); ?></td>
                <td><?php echo get_bloginfo("charset"); ?></td>
            </tr>
            <tr>
                <td class="code"><?php _e('WordPress Version',CFGP_NAME); ?></td>
                <td><?php echo get_bloginfo("version"); ?></td>
            </tr>
            <tr>
                <td class="code"><?php _e('Content-Type',CFGP_NAME); ?></td>
                <td><?php echo get_bloginfo("html_type"); ?></td>
            </tr>
            <tr>
                <td class="code"><?php _e('Language',CFGP_NAME); ?></td>
                <td><?php echo get_bloginfo("language"); ?></td>
            </tr>
            <tr>
                <td class="code"><?php _e('Server Time',CFGP_NAME); ?></td>
                <td><?php echo date("r"); ?></td>
            </tr>
			<tr>
                <td class="code"><?php _e('WordPress Folder Path',CFGP_NAME); ?></td>
                <td><?php echo ABSPATH; ?></td>
            </tr>
		<?php if (defined('PHP_VERSION')): ?>
			<tr>
                <td class="code"><?php _e('PHP Version',CFGP_NAME); ?></td>
                <td>PHP<?php echo PHP_VERSION ?>, <?php echo CF_GEO_OS::is_php64() ? 64 : 32; ?>-bit</td>
            </tr>
		<?php endif; ?>
        	<tr>
                <td class="code"><?php _e('Operting System',CFGP_NAME); ?></td>
                <td><?php printf(__("%s, %d-bit operating system, x86%s based processor.",CFGP_NAME),
					CF_GEO_OS::getOS(),
					CF_GEO_OS::architecture(),
					CF_GEO_OS::is_os64() ? '_64' : ''
				); ?></td>
            </tr>
			<tr>
                <td class="code"><?php _e('WordPress Debug',CFGP_NAME); ?></td>
                <td><?php echo (WP_DEBUG?'<span style="color:red;">'.__('On',CFGP_NAME).'</span>':__('Off',CFGP_NAME)); ?></td>
            </tr>
        </tbody>
     </table>
     
    <h3><?php echo sprintf(__('CF GeoPlugin API ver.%s Request',CFGP_NAME),CFGP_VERSION); ?></h3>
    <p><?php _e('This informations are sent to CF GeoPlugin API. All of this informations (hostname, IP and timezone) are available for general public, world wide and we only use them for API purpose which helps plugin to determine the exact location of the visitors and prevent accidental collapse between the IP address. Your IP and email address is also a guarantee that you\'re not a robot or some spamming software.',CFGP_NAME); ?><br><?php _e('If you are concerned about your private informations, please read the <a href="http://cfgeoplugin.com/privacy-policy" target="_blank">Privacy Policy</a>',CFGP_NAME); ?></p>
    <table width="100%" class="wp-list-table widefat fixed striped pages">
    	<thead>
            <tr>
                <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Name',CFGP_NAME); ?></strong></th>
                <th class="manage-column column-returns column-primary" width="30%"><strong><?php _e('Value',CFGP_NAME); ?></strong></th>
                <th class="manage-column column-returns column-primary"><strong><?php _e('Info',CFGP_NAME); ?></strong></th>
            </tr>
        </thead>
        <tbody>
        	<tr>
                <td class="code">ip</td>
                <td><?php echo (isset($_POST['ip_address']) && !empty($_POST['ip_address']) ? trim($_POST['ip_address']):$ip); ?></td>
                <td class="desc"><?php _e('Your or Visitor\'s IP Address',CFGP_NAME); ?></td>
            </tr>
            <tr>
                <td class="code">timestamp</td>
                <td><?php echo time(); ?></td>
                <td class="desc"><?php _e('Server Current Unix Timestamp',CFGP_NAME); ?></td>
            </tr>
            <tr>
                <td class="code">sip</td>
                <td><?php echo CFGP_SERVER_IP.(CFGP_PROXY?' <a style="color:#FF0000;" href="'.get_admin_url().'admin.php?page=cf-geoplugin-settings">('.__('Proxy Enabled',CFGP_NAME).')</a>':''); ?></td>
                <td class="desc"><?php _e('Server IP Address',CFGP_NAME); ?></td>
            </tr>
            <tr>
                <td class="code">host</td>
                <td><?php
                	$url=parse_url(get_bloginfo("wpurl"));
					echo str_replace("www.","",$url['host']);
				?></td>
                <td class="desc"><?php _e('Server Host Name',CFGP_NAME); ?></td>
            </tr>
			<tr>
                <td class="code">version</td>
                <td><?php
					echo CFGP_VERSION;
				?></td>
                <td class="desc"><?php _e('CF Geo Plugin Version',CFGP_NAME); ?></td>
            </tr>
			<tr>
                <td class="code">email</td>
                <td><?php
					echo get_bloginfo("admin_email");
				?></td>
                <td class="desc"><?php _e('Admin e-mail address.',CFGP_NAME); ?> <?php _e('Only reason why we collect your email address is because plugin support and robot prevention. Your email address will NOT be spammed or shared with 3rd party in any case and you can any time request from us on email <a href="mailto:support@cfgeoplugin.com">support@cfgeoplugin.com</a> to remove your all personal data from our system by GDPR rules.',CFGP_NAME); ?></td>
            </tr>
			<tr>
                <td class="code">key</td>
                <td><?php
					if($api = get_option('cf_geo_defender_api_key'))
						echo $api;
					else
						echo 'N/A';
					
				?></td>
                <td class="desc"><?php _e('CF Geo Plugin API Key',CFGP_NAME); ?></td>
            </tr>
        </tbody>
     </table>
    
    
    <h3><?php echo sprintf(__('CF GeoPlugin API ver.%s Response',CFGP_NAME),CFGP_VERSION); ?></h3>
    <p><?php _e('This informations are returned from CF GeoPlugin API after successful request',CFGP_NAME); ?></p>
<?php if(count($gpReturn)>0): ?>
    <table width="100%" class="wp-list-table widefat fixed striped pages">
    	<thead>
            <tr>
                <th class="manage-column column-shortcode column-primary" width="30%"><strong><?php _e('Return Code',CFGP_NAME); ?></strong></th>
                <th class="manage-column column-returns column-primary"><strong><?php _e('Value',CFGP_NAME); ?></strong></th>
            </tr>
        </thead>
        <tbody>
		<?php if(isset($gpReturn['country_code']) && !empty($gpReturn['country_code'])): ?>
			<tr>
				<td class="code"></td>
				<td><?php echo do_shortcode('[cf_geo_flag country="'.$gpReturn['country_code'].'" img size="10%"]'); ?></td>
			</tr>
		<?php endif; ?>
	<?php foreach($gpReturn as $name=>$value): ?>
	
    		<tr>
                <td class="code"><?php echo $name; ?></td>
                <td><?php echo (empty($value)?'-':$value); ?></td>
            </tr>
	
	<?php endforeach; ?>
    	</tbody>
    </table>
<?php else: ?>
<h3 style="color:red;"><?php _e('Please provide IP for lookup aboove!',CFGP_NAME); ?></h3>
<?php endif; ?>
<?php
$cf_geo_enable_gmap=get_option("cf_geo_enable_gmap");
if($cf_geo_enable_gmap == 'true' && isset($gpReturn['latitude']) && isset($gpReturn['longitude'])):
	if(count($gpReturn)>0 && isset($gpReturn['latitude']) && !isset($gpReturn['latitude'])) :?>    
    <h3>Google Map</h3>
     <p><?php echo sprintf(__("Google Map can't be displayed because of error: %s",CFGP_NAME),$gpReturn['error_message']); ?></p><br>
    <?php else: ?>
    <?php echo do_shortcode("[cf_geo_map id='debug_mode' zoom=12 latitude=".$gpReturn['latitude']." longitude=".$gpReturn['longitude']."]".((isset($gpReturn['address']) && !empty($gpReturn['address']))?"<h4>Address</h4><p>".$gpReturn['address']."</p>[/cf_geo_map]":'')); ?>
<?php endif; ?>
<?php endif; ?>
    <!-- Begin MailChimp Signup Form -->
<link href="//cdn-images.mailchimp.com/embedcode/horizontal-slim-10_7.css" rel="stylesheet" type="text/css">
<div id="mc_embed_signup">
<form action="//cfgeoplugin.us13.list-manage.com/subscribe/post?u=eef1aea9af4bb6df0f70aa95f&amp;id=91f42faf83" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
    <div id="mc_embed_signup_scroll">
	<label for="mce-EMAIL"><?php _e('Be Always Informed About New Versions & Updates',CFGP_NAME); ?></label>
	<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_eef1aea9af4bb6df0f70aa95f_91f42faf83" tabindex="-1" value=""></div>
    <div class="clear"><input type="submit" value="<?php _e('Subscribe',CFGP_NAME); ?>" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
    <p><small><?php _e('We Will Not Spam You! Your Email Is Safe And We Will Not Give Your Informations To Anyone.',CFGP_NAME); ?></small></p>
    </div>
</form>
</div><br><br>
</div>
            
            <?php require_once plugin_dir_path(__FILE__) . 'include/sidebar.php'; ?>  
                 
                 
        </div>
    </div>
</div>