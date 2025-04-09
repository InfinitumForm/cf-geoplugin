<?php
/**
 * Requirements Check
 *
 * Check plugin requirements
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 *
 * @package       cf-geoplugin
 *
 * @author        Ivijan-Stefan Stipic
 *
 * @version       1.0.0
 */
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_Requirements', false)) : class CFGP_Requirements
{
    private $title = 'Geo Controller';
    private $php   = '7.0.0';
    private $wp    = '5.4';
    private $slug  = 'cf-geoplugin';
    private $file;
    private $required_php_extensions = [];

    public function __construct($args)
    {
        foreach ([ 'title', 'php', 'wp', 'file' ] as $setting) {
            if (isset($args[$setting]) && property_exists($this, $setting)) {
                $this->{$setting} = $args[$setting];
            }
        }

        if (is_admin()) {
            $this->update_database_alert();
        }

        $this->required_php_extensions = [
            'curl_version' => (object)[
                'name' => esc_html('cURL', 'cf-geoplugin'),
                'desc' => esc_html('cURL PHP extension', 'cf-geoplugin'),
                'link' => esc_url('https://www.php.net/manual/en/curl.installation.php'),
            ],
            'mb_substr' => (object)[
                'name' => esc_html('Multibyte String', 'cf-geoplugin'),
                'desc' => esc_html('Multibyte String PHP extension (mbstring)', 'cf-geoplugin'),
                'link' => esc_url('https://www.php.net/manual/en/mbstring.installation.php'),
            ],
        ];

        add_action("in_plugin_update_message-{$this->slug}/{$this->slug}.php", [&$this, 'in_plugin_update_message'], 10, 2);
    }

    /*
     * Update database alert
     */
    private function update_database_alert()
    {
        $current_db_version = (get_option(CFGP_NAME . '-db-version') ?? '0.0.0');

        if (version_compare($current_db_version, CFGP_DATABASE_VERSION, '!=')) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-info" id="cf-geoplugin-database-update">';
                echo '<p><strong>'.sprintf(esc_html__('%1$s database update required!', 'cf-geoplugin'), esc_html($this->title), esc_html(CFGP_DATABASE_VERSION)).'</strong></p>';
                echo '<p>'.sprintf(esc_html__('%1$s has been updated! To keep things running smoothly, we have to update your database to the newest version.', 'cf-geoplugin'), esc_html($this->title), esc_html(CFGP_DATABASE_VERSION)).'</p>';
                echo '<p class="submit"><a href="' . esc_url(add_query_arg([
                    'cf_geoplugin_db_update' => 'true',
                    'cf_geoplugin_nonce'     => wp_create_nonce('cf_geoplugin_db_update'),
                ])) . '" class="button button-primary">'.esc_html__('Update Database', 'cf-geoplugin').'</a></p>';
                echo '</div>';
            });

            return false;
        }
    }

    /*
     * Detect if plugin passes all checks
     */
    public function passes()
    {
        $passes = ($this->validate_php_version() && $this->validate_wp_version() && $this->validate_php_modules());

        if (!$passes) {
            add_action('admin_notices', function () {
                if (isset($this->file)) {
                    deactivate_plugins(plugin_basename($this->file));
                    wp_mail(
                        get_option('admin_email'),
                        sprintf(esc_html__('NOTICE: The %s is disabled for some reason!', 'cf-geoplugin'), esc_html($this->title)),
                        sprintf(esc_html__("There has been some incompatibility with your server and %s is disabled.\r\n\r\nPlease visit your admin panel, go to plugins page and check what is causing this problem.", 'cf-geoplugin'), esc_html($this->title))
                    );
                }
            });
        }

        return $passes;
    }

    /*
     * Check PHP modules
     */
    private function validate_php_modules()
    {
        if (empty($this->required_php_extensions)) {
            return true;
        }

        $modules = array_map('function_exists', array_keys($this->required_php_extensions));
        $modules = array_filter($modules, function ($m) {return !empty($m);});

        if (count($modules) === count($this->required_php_extensions)) {
            return true;
        }

        add_action('admin_notices', function () {
            echo '<div class="notice notice-error">';
            printf('<p><strong>%s</strong></p><ol>', sprintf(esc_html__('%s requires the following PHP modules (extensions) to be activated:', 'cf-geoplugin'), esc_html($this->title)));

            foreach ($this->required_php_extensions as $fn => $obj) {
                if (!function_exists($fn)) {
                    printf('<li>%1$s - <a href="%2s" target="_blank">%3$s</a></li>', esc_html($obj->desc), esc_url($obj->link), esc_html__('install', 'cf-geoplugin'));
                }
            }
            echo '</ol>';
            printf('<p>%s</p>', esc_html__('Without these PHP modules you will not be able to use this plugin.', 'cf-geoplugin'));
            printf('<p>%s</p>', esc_html__('Your hosting providers can help you to solve this problem. Contact them and request activation of the missing PHP modules.', 'cf-geoplugin'));
            echo '</div>';
        });

        return false;
    }

    /*
     * Check PHP version
     */
    private function validate_php_version()
    {
        if (version_compare(phpversion(), $this->php, '>=')) {
            return true;
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error">';
                echo '<p>'.sprintf(esc_html__('The %1$s cannot run on PHP versions older than PHP %2$s. Please contact your host and ask them to upgrade.', 'cf-geoplugin'), esc_html($this->title), esc_html($this->php)).'</p>';
                echo '</div>';
            });

            return false;
        }
    }

    /*
     * Check WordPress version
     */
    private function validate_wp_version()
    {
        if (version_compare(get_bloginfo('version'), $this->wp, '>=')) {
            return true;
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error">';
                echo '<p>'.sprintf(esc_html__('The %1$s cannot run on WordPress versions older than %2$s. Please update your WordPress installation.', 'cf-geoplugin'), esc_html($this->title), esc_html($this->wp)).'</p>';
                echo '</div>';
            });

            return false;
        }
    }

    /*
     * Check WordPress version
     */
    public function in_plugin_update_message($args, $response)
    {

        if (isset($response->upgrade_notice) && strlen(trim($response->upgrade_notice)) > 0) : ?>
<style media="all" id="cfgp-plugin-update-message-css">
/* <![CDATA[ */
.cf-geoplugin-upgrade-notice{
padding: 10px;
color: #000;
margin-top: 10px
}
.cf-geoplugin-upgrade-notice-list ol{
list-style-type: decimal;
padding-left:0;
margin-left: 15px;
}
.cf-geoplugin-upgrade-notice + p{
display:none;
}
.cf-geoplugin-upgrade-notice-info{
margin-top:32px;
font-weight:600;
}
/* ]]> */
</style>
<div class="cf-geoplugin-upgrade-notice">
<h3><?php printf(esc_html__('Important upgrade notice for the version %s:', 'cf-geoplugin'), esc_html($response->new_version)); ?></h3>
<div class="cf-geoplugin-upgrade-notice-list">
	<?php echo wp_kses_post(str_replace(
	    [
	        '<ul>',
	        '</ul>',
	    ], [
	        '<ol>',
	        '</ol>',
	    ],
	    $response->upgrade_notice
	)); ?>
</div>
<div class="cf-geoplugin-upgrade-notice-info">
	<?php esc_html_e('NOTE: Before doing the update, it would be a good idea to backup your WordPress installations and settings.', 'cf-geoplugin'); ?>
</div>
</div> 
		<?php endif;
    }
} endif;
