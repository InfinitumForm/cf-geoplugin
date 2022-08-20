<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action('cfgp/settings/nav-tab/after', function(){ ?>
	<a href="javascript:void(0);" class="nav-tab" data-id="#rest-api"<?php echo (CFGP_Options::get('enable_rest',0) ? '' : ' style="display: none;"'); ?>><span class="cfa cfa-code"></span><span class="label"> <?php _e('REST API', 'cf-geoplugin'); ?></span></a>
<?php  });


add_action('cfgp/settings/tab-panel/after', function(){ if(CFGP_Options::get('enable_rest',0)) :
	global $wpdb;
	$api_key = get_option(CFGP_NAME . '-ID');
	$secret_key = CFGP_REST::get('secret_key');	
?>
<div class="cfgp-tab-panel" id="rest-api">
	<section class="cfgp-tab-panel-section" id="rest-api-intro">
        <h2 class="title"><?php _e('REST API Setup', 'cf-geoplugin') ?></h2>
        <?php if(CFGP_License::level() <= 4): ?>
        <p class="text-danger"><?php _e('NOTE: The REST API is only functional for the Business License', 'cf-geoplugin') ?></p>
        <?php endif; ?>
        <p><?php _e('The CF Geo Plugin REST API allows external apps to use geo information and make your WordPress like a geo information provider.', 'cf-geoplugin') ?></p>
        <h2 class="title"><?php _e('API KEY', 'cf-geoplugin') ?>:</h2>
        <div><code style="font-size: large;width: 100%;text-align: center;font-weight: 800;padding: 10px; margin-left:13px;"><?php echo esc_html($api_key); ?></code></div>
        <h2 class="title"><?php _e('Secret API KEY', 'cf-geoplugin') ?>:</h2>
        <div><code id="cf-geoplugin-secret-key" style="font-size: large;width: 100%;text-align: center;font-weight: 800;padding: 10px; margin-left:13px;"><?php echo !empty($secret_key) ? $secret_key : ' - ' . __('Generate Secret Key', 'cf-geoplugin') . ' - '; ?></code> <button type="button"<?php echo ($secret_key ? ' data-confirm="'.esc_attr__('Are you sure you want to regenerate the secret key? If you do this, all your connections will be lost.', 'cf-geoplugin').'"' : '');?> data-nonce="<?php echo wp_create_nonce(CFGP_NAME.'-secret-key'); ?>" class="button" id="cf-geoplugin-generate-secret-key"><?php _e('Generate Secret Key', 'cf-geoplugin') ?></button></div>
        <h2 class="title"><?php _e('Documentation', 'cf-geoplugin') ?>:</h2>
        <p><?php _e('This API is designed to provide easy and secure access to geo information on your site sending simple POST or GET requests and receiving JSON formatted data. Through this API, you can easily connect via any programming language that allows cross domain communication.', 'cf-geoplugin') ?></p>
    </section>

    <div class="nav-tab-wrapper-chosen">
        <nav class="nav-tab-wrapper">
            <a href="javascript:void(0);" class="nav-tab nav-tab-active" data-id="#authentication"><span class="label"> <?php _e('Authentication', 'cf-geoplugin'); ?></span></a>
            <a href="javascript:void(0);" class="nav-tab" data-id="#lookup"><span class="label"> <?php _e('Lookup', 'cf-geoplugin'); ?></span></a>
            <a href="javascript:void(0);" class="nav-tab" data-id="#available-tokens"><span class="label"> <?php _e('Available Tokens', 'cf-geoplugin'); ?></span></a>
        </nav>
        
        <div class="cfgp-tab-panel cfgp-tab-panel-active" id="authentication">
        	<h2 class="title"><?php _e('Authentication endpoint', 'cf-geoplugin') ?>:</h2>
            <p><?php _e('Endpoint used to authenticate connection between CF Geo Plugin on your site and your external app.', 'cf-geoplugin') ?></p>
            <p><code><?php echo admin_url('admin-ajax.php?action=cf_geoplugin_authenticate'); ?></code></p>
            <p><?php _e('Expected GET or POST parameters.', 'cf-geoplugin') ?></p>
            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <tr>
                    <th style="width:25%"><?php _e('Parameter', 'cf-geoplugin') ?></th>
                    <th style="width:13%"><?php _e('Type', 'cf-geoplugin') ?></th>
                    <th style="width:13%"><?php _e('Obligation', 'cf-geoplugin') ?></th>
                    <th><?php _e('Description', 'cf-geoplugin') ?></th>
                </tr>
                <tr>
                    <td><kbd>action</kbd></td>
                    <td>string</td>
                    <td><?php _e('required', 'cf-geoplugin') ?></td>
                    <td><?php _e('Endpoint action. Should always be: <strong>cf_geoplugin_authenticate</strong>', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>api_key</kbd></td>
                    <td>string</td>
                    <td><?php _e('required', 'cf-geoplugin') ?></td>
                    <td><?php _e('API KEY', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>secret_key</kbd></td>
                    <td>string</td>
                    <td><?php _e('required', 'cf-geoplugin') ?></td>
                    <td><?php _e('Secret API KEY', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>app_name</kbd></td>
                    <td>string</td>
                    <td><?php _e('required', 'cf-geoplugin') ?></td>
                    <td><?php _e('Your external application name.', 'cf-geoplugin') ?></td>
                </tr>
            </table>
            <h2 class="title"><?php _e('Return standard JSON API response format', 'cf-geoplugin') ?>:</h2>
            <br><br>
            <pre>{
"error" : false,
"error_message" : NULL,
"code" : 200,
"access_token" : " - generated access token - ",
"message" : "Successful Authentication"
}</pre>
            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <tr>
                    <th style="width:27%"><?php _e('Parameter', 'cf-geoplugin') ?></th>
                    <th style="width:25%"><?php _e('Type', 'cf-geoplugin') ?></th>
                    <th><?php _e('Description', 'cf-geoplugin') ?></th>
                </tr>
                <tr>
                    <td><kbd>error</kbd></td>
                    <td>bool</td>
                    <td>true / false</td>
                </tr>
                <tr>
                    <td><kbd>error_message</kbd></td>
                    <td>string</td>
                    <td><?php _e('Return only when error exists', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>code</kbd></td>
                    <td>integer</td>
                    <td><?php _e('HTTP status code', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>access_token</kbd></td>
                    <td>string</td>
                    <td><?php _e('Return access token only when authentication is successful', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>message</kbd></td>
                    <td>string</td>
                    <td><?php _e('Return only when authentication is successful', 'cf-geoplugin') ?></td>
                </tr>
            </table>
            <p><?php _e('When you receive your access token, you need to save it in a database or integrate it within the code in your external app and it serves for further linking to your site.', 'cf-geoplugin') ?></p>
        </div>
        
        
        <div class="cfgp-tab-panel" id="lookup">
        	<h2 class="title"><?php _e('Lookup endpoint', 'cf-geoplugin') ?>:</h2>
            <p><?php _e('Endpoint used to look up IP address information. To make this work properly, you must have a valid KEY and Access Token API.', 'cf-geoplugin') ?></p>
            <p><code><?php echo admin_url('admin-ajax.php?action=cf_geoplugin_lookup'); ?></code></p>
            <p><?php _e('Expected GET or POST parameters.', 'cf-geoplugin') ?></p>
            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <tr>
                    <th style="width:25%"><?php _e('Parameter', 'cf-geoplugin') ?></th>
                    <th style="width:13%"><?php _e('Type', 'cf-geoplugin') ?></th>
                    <th style="width:13%"><?php _e('Obligation', 'cf-geoplugin') ?></th>
                    <th><?php _e('Description', 'cf-geoplugin') ?></th>
                </tr>
                <tr>
                    <td><kbd>action</kbd></td>
                    <td>string</td>
                    <td><?php _e('required', 'cf-geoplugin') ?></td>
                    <td><?php _e('Endpoint action. Should always be: <strong>cf_geoplugin_lookup</strong>', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>api_key</kbd></td>
                    <td>string</td>
                    <td><?php _e('required', 'cf-geoplugin') ?></td>
                    <td><?php _e('API KEY', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>access_token</kbd></td>
                    <td>string</td>
                    <td><?php _e('required', 'cf-geoplugin') ?></td>
                    <td><?php _e('Generated access token', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>ip</kbd></td>
                    <td>string</td>
                    <td><?php _e('required', 'cf-geoplugin') ?></td>
                    <td><?php _e('Client IP address', 'cf-geoplugin') ?></td>
                </tr>
                <tr>
                    <td><kbd>base_currency</kbd></td>
                    <td>string</td>
                    <td><?php _e('optional', 'cf-geoplugin') ?></td>
                    <td><?php _e('The base currency (transaction currency) - The currency by which conversion is checked by geo location. Default: <strong>'.CFGP_Options::get('base_currency').'</strong>', 'cf-geoplugin') ?></td>
                </tr>
            </table>
            <h2 class="title"><?php _e('Return standard JSON API response format', 'cf-geoplugin') ?>:</h2>
            <br><br>
            <pre>{
<?php
$remove = array(
	'status',
	'available_lookup',
	'version',
	'credit',
	'dmaCode',
	'areaCode',
	'continentCode',
	'currencySymbol',
	'currencyConverter'
);

foreach(CFGP_U::api(false, CFGP_Defaults::API_RETURN) as $key => $value) :
if(!(in_array($key, $remove, true) !== false))
{
if($key == 'error') $value = 'false';
echo "	\"{$key}\" : " . ($value === 0 ? 0 : ($value === '' ? '""' : (is_int($value) || in_array($value, array('true','false')) || is_float($value) ? $value : '"' . str_replace('/','\\/',esc_attr($value)) . '"'))) . ",\n";	
}
endforeach;
?>
	"code" : <?php echo esc_attr(CFGP_U::api('status')) . "\n"; ?>
}</pre>
			<p><?php _e('You can use these JSON information in your external app anywhere. TIP: In order for your external app to be fast, it would be good to make this call once and record in a temporary session that will expire after a few minutes.', 'cf-geoplugin') ?></p>
        </div>
        
        
        <div class="cfgp-tab-panel" id="available-tokens">
        	<h2 class="title"><?php _e('Available Tokens', 'cf-geoplugin') ?>:</h2>
            <p><?php _e('Here is a list of registered access tokens that are active on your site. You can also disable any active access token.', 'cf-geoplugin') ?></p>
            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <thead>
                    <tr>
                        <th style="width:50%"><?php _e('Access Token', 'cf-geoplugin') ?></th>
                        <th style="width:20%"><?php _e('App name', 'cf-geoplugin') ?></th>
                        <th style="width:18%"><?php _e('Date', 'cf-geoplugin') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                	<?php
						$tokens = $wpdb->get_results("SELECT * FROM {$wpdb->cfgp_rest_access_token} WHERE 1");
						if(count($tokens) > 0):
						foreach($tokens as $i => $token):
					?>
                    <tr id="<?php echo esc_attr($token->app_name.'-'.$i); ?>">
                        <th><?php echo esc_html($token->token); ?></th>
                        <td><?php echo esc_html($token->app_name_original); ?></td>
                        <td><?php echo esc_html(date(CFGP_DATE_TIME_FORMAT, strtotime($token->date_created))); ?></td>
                        <td style="text-align:right;"><button type="button" data-remove="#<?php echo esc_attr($token->app_name.'-'.$i); ?>" data-id="<?php echo esc_attr($token->ID); ?>" data-confirm="<?php esc_attr_e('Are you sure you want to remove this access token?', 'cf-geoplugin'); ?>" data-nonce="<?php echo wp_create_nonce(CFGP_NAME.'-token-remove'); ?>" class="button cfgp-button-delete cfgp-button-token-remove"><?php _e('Remove', 'cf-geoplugin') ?></button></td>
                    </tr>
                    <?php endforeach; else: ?>
                	<tr>
                        <td colspan="4"><?php _e('There are no registered applications yet.', 'cf-geoplugin') ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
        	</table>
        </div>
	</div>

</div>
<?php endif; });