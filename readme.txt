=== CF Geo Plugin ===
Contributors: ivijanstefan, creativform, ej207
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=creativform@gmail.com
Tags: geo plugin, geo location, geotarget, geo banner, market, conversion, conversion plugin, automatic banner, banner, business, location, google map, gmaps, gmap, seo, seo tool, user experience, ux plugin, contact form, dynamic keyword, ip, ip location, region, position, positioning, marketing, block visitors, defender, block spam, block region, tag, geo, target, local, find ip, ip finder, geo target, geo image, geo content. include content, exclude content, include, exclude, seo redirection, url redirection, country redirection, redirection, flag, country flag, national flag, flags, custom flags, gdpr, localization, ecommerce, legal requiremants, legal, currency
Requires at least: 3.0
Tested up to: 4.9
Requires PHP: 5.6.0
Stable tag: 7.1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The CF GeoPlugin allows you to take full control of your WordPress using geographic information with simple finctionality, shortcodes, PHP or JavaScript.

== Description ==

The **CF Geo Plugin** is **FREE** plugin that allows you to attach content, geographic information and Google maps to posts, pages, widgets and custom templates by using simple Shortcodes, PHP code or JavaScript by user IP address. It also lets you to specify a default geographic location for your entire WordPress blog, do SEO redirection and many more. This plugin is also great for capture leads, SEO and increasing conversions on your blog or landing pages.

= BENEFITS =

> <strong>GEO-MARKETING</strong><br>
> - **Create marketing campaigns** targeted only at certain locations.
> - **Create landing pages** targeted only at certain locations.
> - **Create banners, videos and any other content** targeted only at certain locations.
> 
> <strong>ECOMMERCE</strong><br>
> - **Display local currency**, local symbol or converter
> - **Use currency converter** to calculate price in local currency
> 
> <strong>LOCALIZATION</strong><br>
> - **Redirect incoming traffic** to content in the local language or currency.
> - Businesses with local branches **can direct customers to a relevant physical location** or local microsite.
> 
> <strong>LEGAL REQUIREMENTS</strong></br>
> - **Filter required legal notices**, text, forms, etc. from countries for whom those content may not be relevant.

= Compatibility =

This plugin is compatible with any Wordpress installation, also compatible with *[Contact Form 7](https://wordpress.org/plugins/contact-form-7/)*, *[WooCommerce](https://woocommerce.com/)*, *[Nord VPN proxy service](https://goo.gl/lWm3e6)* and support *[Cloudflare](https://www.cloudflare.com/)*.

> <strong>All CF Geo Plugin 7.x.x Features</strong><br>
>
> * **Geo Plugin** - Allows you to attach geographical information inside your content via Shortcodes, PHP and JavaScript objects
> * **Geo Banner** - Allows you to place dynamic content, images, videos and pages using shortcodes for specfic audience target by geo location
> * **Google Map** - Allows you to attach Google Map inside content
> * **Geo Defender** - Allows you to block acces on blog from specific location
> * **Cloudflare Geolocation Support** - Cloudflare support for visitor geolocation
> * **DNS Lookup** - Allows you to attach visitor DNS informations
> * **SSL Support** - Keep you safe
> * **PROXY Settings** - Allows you to use PROXY for the lookup
> * **Country SEO Redirection** - Allows you to redirect any page, post or article
> * **Country Flag Support** - Allows you to attach visitor or custom country flags inside content
> * **Include Content by Geolocation** - Allows you to include content by geolocation
> * **Exclude Content by Geolocation** - Allows you to exclude content by geolocation
> * **Plugin Autoupdate** - Allows you to keep your plugin up to date automaticaly

= Basics usage and example =

`[cfgeo]`
`[cfgeo_city]`
`[cfgeo return="region"]`
`We just found shoes in [cfgeo_city] that you can buy for 50% discount.`
`[cfgeo ip="127.0.0.1" return="area_code"]`
`[cfgeo exclude="Toronto"] This text is seeing by everyone except Toronto people [/cfgeo]`
`[cfgeo include="New York"] This text seeing only people from New York [/cfgeo]`

= Usage & Example =

**GEO PLUGIN:** Usage is simple. After installation and activation, in your post you just need to insert `[cfgeo]` shortcode and that's it. Enter a shortcode as this and it returns and display IP adress to a visitor. If you like to display region (for example California for users who are from California), you just need to use `return` attribute in your shortcode like this: `[cfgeo return="region"]`. By changing the return settings, you can display any information from list above. Each user who comes to the site will see information related to their area. 

If you whant to track some custom IP and return some information from that IP, you can do that by adding one optional attribute ip like on example `[cfgeo ip="127.0.0.1" return="area_code"]` what will return area code from that IP address.

If you like to ad default values to your shortcode if data is empty you need to add extra attribute in your shortcode like this example `[cfgeo return="country_code" default="US"]` what will return US if geoplugin can't locate country code.

If you need to exclude some content from your page based on user location, you can do that using the `exclude` attribute like this: 

`[cfgeo exclude="Toronto, Québec"] This text is seeing by everyone except people from Toronto and Québec [/cfgeo]`

If you need to display some content in your page based on user location, you can do that using the `include` attribute like this: 

`[cfgeo include="New York, Miami, Germany"] This text seeing only people from New York, Miami and Germany [/cfgeo]`

**GOOGLE MAP:** If you whant to place simple google map in your post or page, you just need to place shortcode [cfgeo_map] and your visitor will see own city on google map by default.

Like example, you can display own company street address inside Google map like this: `[cfgeo_map longitude="-74.0059" latitude="40.7128" zoom="15"]` and pointer will show your street and place where you work.

Google map also allow you to use HTML inside map and display info bar: 

`[cfgeo_map longitude="-74.0059" latitude="40.7128" zoom="15" title="My Company Name"] 
   <h3>My Company Name<h3> 
   <p>No Name Street 35, New York, USA</p> 
   <p>We have what you need</p> 
[/cfgeo_map]`

With this plugin you can easy setup your google map.

**GEO BANNER:** With this plugin you have also ability to make a dynamic content (text, images, banners, videos...) and target your messages to people from certain regions, track IP address, setup WordPress to work on user's timezone, etc. The possibilities are huge, you can increase conversions, use this plugin like support for your custom template, build your own plugin and use CF Geo Plugin like additional option, etc.

You just need to create a new banner, place your content, select rules (country, region, city) save your banner, after that in your page pickup banner shortcode and place inside content.

**COUNTRY FLAG** You can display country flags in text or like image.

If you like to display country flag in your text like icon, you can do that simple like: 

`[cfgeo_flag]` - and you will see flag in your text.

If you like to display country flag in your content like image, you can do that also simple using img or image attributes like: 

`[cfgeo_flag img]` - and you will see image flag in your content

You also can give custom sizes of flags in %, px, in, pt or em using size attribute like this: 

`[cfgeo_flag size="32px"]` - and you will see your flag in that size. You can use this size in image and normal text mode also.

You also can display custom flag using country attribute by placing country code simple like: 

`[cfgeo_flag country="ca"]` - and you will see flag in your text or like image.

We allow you also full controll of this flags and you can place css, class or id attributes to be able use this in any kind of work like this: 

`[cfgeo_flag size="50" css="padding:10px;" class="your-custom-class custom-class custom" id="top-flag"]`

= Info & Contact =

Please visit our website [www.cfgeoplugin.com](https://cfgeoplugin.com/) and fell free to contact us. We will provide for you all services what you need.

Also please inform us if any errors occure via contact form on our website [http://cfgeoplugin.com/](https://cfgeoplugin.com/contact).

Thank you for your concern!

~ Your CF Geo Plugin Team

== Installation ==

You only need to install the  **CF Geo Plugin** through the WordPress plugins screen directly or download ZIP file, unpack and upload plugin folder to the `/wp-content/plugins/` directory.

Afterwards, activate the plugin through the 'Plugins' screen in WordPress, go to your `http://siteexample.com/wp-admin/` area and use the `Settings->CF Geo Plugin` to see all available shortcodes and settings.

== Frequently Asked Questions ==

= How I can test CF Geo Plugin is work on certain country, region or city? =

That part is little bit hard but you can use great VPN service - [NordVPN](https://goo.gl/AEd82e)
With this service you can change your current location and test CF Geo Plugin and its functionality.

= Can I use CF GeopPlugin inside custom template? =

Yes! You can! You just need to follow instructions from your admin panel `http://siteexample.com/wp-admin/` area and use the `Settings->CF Geo Plugin` to see all available shortcodes and settings.
NOTE: Some templates not support WordPress PHP function `[do_shortcode()](https://developer.wordpress.org/reference/functions/do_shortcode/)` and data from CF Geo Plugin will not be visible.

= Can GeoPlugin slow down my site? =

NO, CF Geo Plugin uses the asynchronous data reading from API services. After loading, all data is stored in a session. That session is stored until IP is changed. When user's IP changes, the plugin reads new data and stores in the new session.

= Can I set the different value if shortcode shows no results? =

YES, just add the attribute `default` in shortcode and corresponding text like this:
`[cfgeo return="city" default="Your Default Text"]`

= How to include CF GeopPlugin in PHP? =

In PHP you can use WordPress function `[do_shortcode()](https://developer.wordpress.org/reference/functions/do_shortcode/)` to display data via shortcode inside your PHP file. Also you have option to use PHP class `new CF_Geoplugin` or you can use GLOBALS and get data via `global $CF_Geo` like example:

`
// check if plugin exists
if(class_exists("CF_Geoplugin")){
	// include plugin class
	$cfgeo = new CF_Geoplugin;
	// get data
	$geo = $cfgeo->get();
	// print data
	echo $geo->city;
}
`

or with globals:

`
// object oriented
global $CF_GEO;
echo $CF_GEO->city;

// Array
global $CFGEO;
echo $CFGEO['city'];
`

= How to use CF GeopPlugin inside JavaScript? =

JavaScript is enabled from version 5.2.0. In JavaScript you have 2 ways to get geo data from our plugin, using `cf.geoplugin` or `window.cfgeo` objects. This 2 objects contain all geo informations from our plugin and is available inside public and admin area.
`
<script>
	// Look in your console for all available objects
	console.log(window.cfgeo);
	
	// you can use this everywhere you want
	
	var city = window.cfgeo.city,
		state = window.cfgeo.state,
		country = window.cfgeo.country,
		ip = window.cfgeo.ip;
</script>
`

= How to use CF Geo Plugin inside ContactForm7? =

Sometimes you need to include other HTML, CSS and JavaScript, and jQuery codes inside ContactForm7. Sometimes you need to insert a geolocation in input fields. This is not easy but here is one example with jQuery:

`
[text* city placeholder "* City Name"]
[text* country placeholder "* Country Name"]

<script>
jQuery(document).ready(function(){
    jQuery("input[name^='city']").val(window.cfgeo.city);
	jQuery("input[name^='country']").val(window.cfgeo.country);
});
</script>
`

This code will auto fill value of CF7 city field when a visitor visits the contact page (look screenshots).

* All geo informations you can find inside global `window.cfgeo` object.

= CF Geo Plugin don't display information =

* It might be an error with the provider's [geoPlugin](http://www.geoplugin.com/) network or API and CF Geo Plugin can't display information by own.
* Look inside `CF Geo Plugin` for all available shortcodes and settings,
* Look inside `CF Geo Plugin->Settings` and enable/disable and check all options (SSL, proxy, etc),
* Maby you have PHP error, you can see that if you enable plugin debug in `wp-config.php` inserting on first lines code: `define('WP_CF_GEO_DEBUG', true);`
* Some people use a special software to hide IP address via proxy and geoplugin can't look deeper to get accurate location,
* Look into error.log file to see if a problem is in CF Geo Plugin
* Some servers not allow access to geoplugin sites and maby you need to use proxy setings inside plugin to enable tracking
* You reach some API limitations

Please inform us if any of these errors occure via contact form on our website [http://creativform.com](http://creativform.com/contact/) with message subject: **CF Geo Plugin SUPPORT**

== Screenshots ==

1. CF Geo Plugin Available shortcodes and values
2. CF Google Map shortcodes and setup
3. CF Geo Defender setup
4. CF Geo Plugin global setup
5. Debug Mode for CF Geo Plugin and IP Lookup
6. Adding shortcode in text editor
7. Admin bar quick links
8. Example 1
9. Example 2
10. Example 3
11. SEO Redirection

== Changelog ==

= 7.1.3 =
* Added filter for the X_FORWARDED_FOR
* Fixed previous bugs
* Fixed Woocommerce bug

= 7.1.2 =
* Added support for multisite installation
* Fixed License Key validation problems
* Fixed bug with debugger
* Fixed uninstall bug

= 7.1.1 =
* Fixed popper.js
* Added woocommerce validation description
* Fixed problem with CF admin menu
* Fixed issue with total cart price conversion
* Validation and unistall upgrades

= 7.1.0 =
* Woocommerce integration
* Woocommerce currency converter
* Integrated REST API for the external apps and integrations
* Fixed bugs from the prevous version

= 7.0.4 =
* Fixed Geo Banner shortcode bug

= 7.0.3 =
* Fixed API communication

= 7.0.2 =
* Fixed activation bug
* Added translation functionality

= 7.0.1 =
* Fixed Upgrade

= 7.0.0 =

* New Functionality like multiple SEO redirection, auto update, new shortcode functionality...
* New GUI - better aproach to all options including easy shortcode access
* New Shortcodes - simple shortcodes (BETA: you can choose to not use it), new attributes
* New SEO redirection - now you can setup redirection by region and city as well
* New optimized PHP code
* New locale lookup
* Optimized code
* Better debbuging
* Optimized lookup
* Ability to complete turn off all functionality that you not need
* Maximum secure data
* Fixed bugs from the previous versions
* CSV upload for the SEO redirection
* CSV backup for the SEO redirection
* Localization integration

== Upgrade Notice ==

= 7.1.2 =
IMPORTANT UPGRADE - Added support for multisite installation and fixed license key validation

= 7.0.4 =
IMPORTANT UPGRADE - Fixed Geo Banner shortcode bug

= 7.0.3 =
IMPORTANT UPGRADE - API communication fix

= 7.0.0 =
Please upgrade your plugin to version 7.x


== Other Notes ==

= Plugin Links =

* [CF Geo Plugin Official Website](https://cfgeoplugin.com/)
* [CF Geo Plugin Blog](https://cfgeoplugin.com/blog/)
* [F.A.Q](https://cfgeoplugin.com/faq/)
* [Contact or Support](https://cfgeoplugin.com/contact/)
* [Jobs or Opportunity](https://cfgeoplugin.com/get-involved/)

= DONATION =

Enjoy using *CF Geo Plugin*? Please consider [making a small donation](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=creativform@gmail.com) to support the project's continued development.

= TERMS AND CONDITIONS  =

Please read these Terms and Conditions ("Terms", "Terms and Conditions") carefully before using the [www.cfgeoplugin.com](https://cfgeoplugin.com) website and the CF Geo Plugin WordPress application (the "Service") operated by CF Geo Plugin.

[Read about Terms and Conditions](https://cfgeoplugin.com/terms-and-conditions)

= PRIVACY POLICY =
We respect your privacy and take protecting it seriously. This Privacy Policy covers our collection, use and disclosure of information we collect through our website and service, [www.cfgeoplugin.com](https://cfgeoplugin.com) owned and operated by CF Geo Plugin. It also describes the choices available to you regarding our use of your personal information and how you can access and update this information. The use of information collected through our service shall be limited to the purpose of providing the service for which our Clients have engaged us. Also we respect and take care about Europe General Data Protection Regulation (GDPR) and your freedom and private choices.

[Read about Privacy Policy](https://cfgeoplugin.com/privacy-policy)

For further questions and clarifications, do not hesitate to contact us and we will reply back to you within 48 hours.