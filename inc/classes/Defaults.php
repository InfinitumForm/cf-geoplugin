<?php
/**
 * Requirements Check
 *
 * Check plugin requirements
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

if(!class_exists('CFGP_Defaults')) :
class CFGP_Defaults {
	
	// Define license codes
	const BASIC_LICENSE 		= 'CFGEO1M';
	const PERSONAL_LICENSE 		= 'CFGEOSWL';
	const PERSONAL_LICENSE_4Y 	= 'CFGEOSWL4Y';
	const FREELANCER_LICENSE 	= 'CFGEO3WL';
	const FREELANCER_LICENSE_4Y = 'CFGEO3WL4Y';
	const BUSINESS_LICENSE 		= 'CFGEODWL';
	const BUSINESS_LICENSE_4Y 	= 'CFGEODWL4Y';
	const LIFETIME_LICENSE		= 'LIFETIME';
	const DEVELOPER_LICENSE 	= 'CFGEODEV';
	
	// License options
	const LICENSE = array(
		'key'			=>	'',
		'id'			=>	'',
		'expire'		=>	'',
		'expire_date'	=>	'',
		'url'			=>	'',
		'sku'			=>	'',
		'expired'		=>	'',
		'status'		=>	''
	);
	
	// License options
	const REST = array(
		'secret_key'	=> '',
	);
	
	// Plugin options
	const OPTIONS = array(
		'enable_beta'					=>	1,
		'enable_simple_shortcode'		=>	1,
		'enable_seo_csv'				=>	1,
		'enable_seo_redirection'		=>	1,
		'enable_defender'				=>	1,
		'enable_gmap'					=>	0,
		'enable_cache'					=>	0,
		'enable_menus_control'			=>	1,
		'enable_banner'					=>	1,
		'enable_cloudflare'				=>	0,
		'enable_dns_lookup'				=>	0,
		'enable_rest'					=>	1,
		'rest_api_mode'					=>  'ajax',
		'proxy_ip'						=>	'',
		'proxy_port'					=>	'',
		'proxy'							=>	0,
		'proxy_username'				=>	'',
		'proxy_password'				=>	'',
		'enable_ssl'					=>	0,
		'timeout'						=>	5,
		'map_api_key'					=>	'',
		'map_zoom'						=>	8,
		'map_scrollwheel'				=>	1,
		'map_navigationControl'			=>	1,
		'map_scaleControl'				=>	1,
		'map_mapTypeControl'			=>	1,
		'map_draggable'					=>	0,
		'map_width'						=>	'100%',
		'map_height'					=>	'400px',
		'map_infoMaxWidth'				=>	200,
		'map_latitude'					=>	'',
		'map_longitude'					=>	'',
		'block_country'					=>	'',
		'block_region'					=>	'',
		'block_ip'						=>	'',
		'block_city'					=>	'',
		'block_country_messages'		=>	'',
		'block_proxy'					=>	0,
		'redirect_enable'				=>	0,
		'redirect_mode'					=>	2,
		'redirect_disable_bots'			=>	0,
		'redirect_country'				=>	'',
		'redirect_region'				=>	'',
		'redirect_city'					=>	'',
		'redirect_url'					=>	'',
	//	'measurement_unit'				=>	'km',
		'redirect_http_code'			=>	302,
		'base_currency'					=>	'USD',
		'rest_secret'					=>	'',
		'plugin_activated'				=>	'',
		'enable_spam_ip'				=>	0,
		'first_plugin_activation'		=>	1,
		'log_errors'					=>	0,
		'enable_seo_posts'				=>	array('post', 'page'),
		'enable_geo_tag'				=>	array(),
		'enable_css'					=>	1,
		'enable_js'						=>	1,
		'hide_http_referrer_headers' 	=>	0,
		'notification_recipient_emails'	=>	'',
		'notification_recipient_type' 	=>	'all',
		'ip_whitelist'					=>	'127.0.0.1'
	);


	// Beta options
	const BETA_OPTIONS = array(
		'enable_simple_shortcode'
	);
	
	/*
	 * API calls used inside CF Geo Plugin.
	 */
	const API = array(
		// Standard CF Geo Plugin API URLs
		'main'				=>	'http://159.203.150.139/v2/',
		'authenticate'		=>	'http://159.203.150.139/v2/authentication',
		'converter'			=>	'http://159.203.150.139/v2/currency-converter',
		'countries'			=>	'http://159.203.150.139/v2/countries',
		'regions'			=>	'http://159.203.150.139/v2/regions',
		'cities'			=>	'http://159.203.150.139/v2/cities',
		// SSL URLs
		'ssl_main'			=>	'https://cdn-cfgeoplugin.com/v2/',
		'ssl_authenticate'	=>	'https://cdn-cfgeoplugin.com/v2/authentication',
		'ssl_converter'		=>	'https://cdn-cfgeoplugin.com/v2/currency-converter',
		'ssl_countries'		=>	'https://cdn-cfgeoplugin.com/v2/countries',	
		'ssl_regions'		=>	'https://cdn-cfgeoplugin.com/v2/regions',
		'ssl_cities'		=>	'https://cdn-cfgeoplugin.com/v2/cities',		
		// 3rd party IPFY free API call for finding real IP address on the local machines
		'ipfy'				=>	'https://api.ipify.org',
		'smartIP'			=>	'https://smart-ip.net/myip',
		'indent'			=>	'https://ident.me',
		'googleapis_map'	=>	'//maps.googleapis.com/maps'
	);
	
	/*
	 * API Return values.
	 */
	const API_RETURN = array(
		'ip' => NULL,
		'ip_version' 			=> NULL,
		'ip_number' 			=> NULL,
		'ip_server' 			=> NULL,
		'ip_dns_host' 			=> NULL,
		'ip_dns_provider' 		=> NULL,
		'isp' 					=> NULL,
		'isp_organization' 		=> NULL,
		'isp_as' 				=> NULL,
		'isp_asname' 			=> NULL,
		'is_local_server' 		=> false,
		'continent' 			=> NULL,
		'continent_code'	 	=> NULL,
		'country'				=> NULL,
		'country_code' 			=> NULL,
		'country_code_3' 		=> NULL,
		'country_code_numeric' 	=> NULL,
		'region' 				=> NULL,
		'region_code' 			=> NULL,
		'district'				=> NULL,
		'city' 					=> NULL,
		'postcode' 				=> NULL,
		'address' 				=> NULL,
		'is_eu' 				=> 0,
		'calling_code' 			=> NULL,
		'latitude' 				=> NULL,
		'longitude' 			=> NULL,
		'timezone' 				=> NULL,
		'timezone_offset' 		=> NULL,
		'timezone_abbreviation'	=> NULL,
		'timestamp' 			=> NULL,
		'timestamp_readable' 	=> NULL,
		'current_date' 			=> NULL,
		'current_time' 			=> NULL,
		'currency' 				=> 0,
		'currency_symbol' 		=> NULL,
		'currency_converter'	=> NULL,
		'base_convert'			=> NULL,
		'base_convert_symbol'	=> NULL,
		'is_vat' 				=> 0,
		'vat_rate' 				=> NULL,
		
		'browser' 				=> NULL,
		'browser_version' 		=> NULL,
		'platform' 				=> NULL,
		'is_mobile' 			=> 0,
		
		'is_proxy' 				=> 0,
		'is_spam' 				=> 0,
		'limited' 				=> 0,
		'available_lookup' 		=> NULL,
		'limit' 				=> NULL,
		'license_hash' 			=> NULL,
		'gps'					=> 0,
		'error' 				=> 0,
		'error_message' 		=> NULL,
		'runtime' 				=> 0,
		'status' 				=> NULL,
		'official_url' 			=> NULL,
		'credit' 				=> NULL,
		'version' 				=> CFGP_VERSION,
	);
	
	/*
	 * API fields.
	 */
	const API_FIELDS = array(
		'ip' => NULL,
		'ip_version' => NULL,
		'ip_number' => NULL,
		'ip_server' => NULL,
		'ip_dns_host' => NULL,
		'ip_dns_provider' => NULL,
		'isp' => NULL,
		'isp_organization' => NULL,
		'isp_as' => NULL,
		'isp_asname' => NULL,
		'is_local_server' => false,
		'continent' => NULL,
		'continent_code' => NULL,
		'country' => NULL,
		'country_code' => NULL,
		'country_code_3' => NULL,
		'country_code_numeric' => NULL,
		'region' => NULL,
		'region_code' => NULL,
		'district' => NULL,
		'city' => NULL,
		'postcode' => NULL,
		'address' => NULL,
		'is_eu' => false,
		'calling_code' => NULL,
		'latitude' => NULL,
		'longitude' => NULL,
		'timezone' => NULL,
		'timezone_offset' => NULL,
		'timezone_abbreviation' => NULL,
		'timestamp' => NULL,
		'timestamp_readable' => NULL,
		'current_date' => NULL,
		'current_time' => NULL,
		'currency' => NULL,
		'currency_symbol' => NULL,
		'currency_converter' => NULL,
		'base_convert' => NULL,
		'base_convert_symbol' => NULL,
		'is_vat' => false,
		'vat_rate' => NULL,
		'mobile' => false,
		'proxy' => false,
		'is_spam' => false,
		'limited' => NULL,
		'available_lookup' => NULL,
		'limit' => NULL,
		'license_hash' => NULL,
		'error' => 0,
		'error_message' => NULL,
		'runtime' => 0,
		'status' => NULL,
		'official_url' => NULL,
		'credit' => NULL
	);
	
	const CONTINENT_LIST = array(
		'AF' => 'Africa',
		'NA' => 'North America',
		'OC' => 'Oceania',
		'AN' => 'Antarctica',
		'AS' => 'Asia',
		'EU' => 'Europe',
		'SA' => 'South America'
	);
	
	const COUNTRY_LIST = array(
		'AF' => 'Afghanistan',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'BQ' => 'British Antarctic Territory',
		'IO' => 'British Indian Ocean Territory',
		'VG' => 'British Virgin Islands',
		'BN' => 'Brunei',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CT' => 'Canton and Enderbury Islands',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos [Keeling] Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CG' => 'Congo - Brazzaville',
		'CD' => 'Congo - Kinshasa',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'CI' => 'Côte d’Ivoire',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'NQ' => 'Dronning Maud Land',
		'DD' => 'East Germany',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'FQ' => 'French Southern and Antarctic Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island and McDonald Islands',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong SAR China',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JT' => 'Johnston Island',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Laos',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macau SAR China',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'FX' => 'Metropolitan France',
		'MX' => 'Mexico',
		'FM' => 'Micronesia',
		'MI' => 'Midway Islands',
		'MD' => 'Moldova',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar [Burma]',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NT' => 'Neutral Zone',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'KP' => 'North Korea',
		'VD' => 'North Vietnam',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PC' => 'Pacific Islands Trust Territory',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territories',
		'PA' => 'Panama',
		'PZ' => 'Panama Canal Zone',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'YD' => 'People\'s Democratic Republic of Yemen',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn Islands',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RO' => 'Romania',
		'RU' => 'Russia',
		'RW' => 'Rwanda',
		'RE' => 'Réunion',
		'BL' => 'Saint Barthélemy',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'CS' => 'Serbia and Montenegro',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'KR' => 'South Korea',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syria',
		'ST' => 'São Tomé and Príncipe',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'UM' => 'U.S. Minor Outlying Islands',
		'PU' => 'U.S. Miscellaneous Pacific Islands',
		'VI' => 'U.S. Virgin Islands',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'SU' => 'Union of Soviet Socialist Republics',
		'AE' => 'United Arab Emirates',
		'GB' => 'Great Britain',
		'US' => 'United States',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VA' => 'Vatican City',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'WK' => 'Wake Island',
		'WF' => 'Wallis and Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
		'AX' => 'Åland Islands',
	);
	
	const COUNTRY_REGION_LIST = array(
		'Australia and New Zealand' => array(
			'AU' => 'Australia',
			'NZ' => 'New Zealand',
			'NF' => 'Norfolk Island',
		),
		'Caribbean' => array(
			'AI' => 'Anguilla',
			'AG' => 'Antigua and Barbuda',
			'AW' => 'Aruba',
			'BS' => 'Bahamas',
			'BB' => 'Barbados',
			'VG' => 'British Virgin Islands',
			'KY' => 'Cayman Islands',
			'CU' => 'Cuba',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'HT' => 'Haiti',
			'JM' => 'Jamaica',
			'MQ' => 'Martinique',
			'MS' => 'Montserrat',
			'AN' => 'Netherlands Antilles',
			'PR' => 'Puerto Rico',
			'BL' => 'Saint Barthélemy',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'VC' => 'Saint Vincent and the Grenadines',
			'TT' => 'Trinidad and Tobago',
			'TC' => 'Turks and Caicos Islands',
			'VI' => 'U.S. Virgin Islands',
		),
		'Central America' => array(
			'BZ' => 'Belize',
			'CR' => 'Costa Rica',
			'SV' => 'El Salvador',
			'GT' => 'Guatemala',
			'HN' => 'Honduras',
			'MX' => 'Mexico',
			'NI' => 'Nicaragua',
			'PA' => 'Panama',
		),
		'Central Asia' => array(
			'KZ' => 'Kazakhstan',
			'KG' => 'Kyrgyzstan',
			'TJ' => 'Tajikistan',
			'TM' => 'Turkmenistan',
			'UZ' => 'Uzbekistan',
		),
		'Channel Islands' => array(
			'GG' => 'Guernsey',
			'JE' => 'Jersey',
		),
		'Commonwealth of Independent States' => array(
			'AM' => 'Armenia',
			'AZ' => 'Azerbaijan',
			'BY' => 'Belarus',
			'GE' => 'Georgia',
			'KZ' => 'Kazakhstan',
			'KG' => 'Kyrgyzstan',
			'MD' => 'Moldova',
			'RU' => 'Russia',
			'TJ' => 'Tajikistan',
			'TM' => 'Turkmenistan',
			'UA' => 'Ukraine',
			'UZ' => 'Uzbekistan',
		),
		'Eastern Africa' => array(
			'BI' => 'Burundi',
			'KM' => 'Comoros',
			'DJ' => 'Djibouti',
			'ER' => 'Eritrea',
			'ET' => 'Ethiopia',
			'KE' => 'Kenya',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MZ' => 'Mozambique',
			'RW' => 'Rwanda',
			'RE' => 'Réunion',
			'SC' => 'Seychelles',
			'SO' => 'Somalia',
			'TZ' => 'Tanzania',
			'UG' => 'Uganda',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		),
		'Eastern Asia' => array(
			'CN' => 'China',
			'HK' => 'Hong Kong SAR China',
			'JP' => 'Japan',
			'MO' => 'Macau SAR China',
			'MN' => 'Mongolia',
			'KP' => 'North Korea',
			'KR' => 'South Korea',
			'TW' => 'Taiwan',
		),
		'Eastern Europe' => array(
			'BY' => 'Belarus',
			'BG' => 'Bulgaria',
			'CZ' => 'Czech Republic',
			'HU' => 'Hungary',
			'MD' => 'Moldova',
			'PL' => 'Poland',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'SK' => 'Slovakia',
			'UA' => 'Ukraine',
			'SU' => 'Union of Soviet Socialist Republics',
		),
		'Melanesia' => array(
			'FJ' => 'Fiji',
			'NC' => 'New Caledonia',
			'PG' => 'Papua New Guinea',
			'SB' => 'Solomon Islands',
			'VU' => 'Vanuatu',
		),
		'Micronesian Region' => array(
			'GU' => 'Guam',
			'KI' => 'Kiribati',
			'MH' => 'Marshall Islands',
			'FM' => 'Micronesia',
			'NR' => 'Nauru',
			'MP' => 'Northern Mariana Islands',
			'PW' => 'Palau',
		),
		'Middle Africa' => array(
			'AO' => 'Angola',
			'CM' => 'Cameroon',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CG' => 'Congo - Brazzaville',
			'CD' => 'Congo - Kinshasa',
			'GQ' => 'Equatorial Guinea',
			'GA' => 'Gabon',
			'ST' => 'São Tomé and Príncipe',
		),
		'Northern Africa' => array(
			'DZ' => 'Algeria',
			'EG' => 'Egypt',
			'LY' => 'Libya',
			'MA' => 'Morocco',
			'SD' => 'Sudan',
			'TN' => 'Tunisia',
			'EH' => 'Western Sahara',
		),
		'Northern America' => array(
			'BM' => 'Bermuda',
			'CA' => 'Canada',
			'GL' => 'Greenland',
			'PM' => 'Saint Pierre and Miquelon',
			'US' => 'United States',
		),
		'Northern Europe' => array(
			'DK' => 'Denmark',
			'EE' => 'Estonia',
			'FO' => 'Faroe Islands',
			'FI' => 'Finland',
			'GG' => 'Guernsey',
			'IS' => 'Iceland',
			'IE' => 'Ireland',
			'IM' => 'Isle of Man',
			'JE' => 'Jersey',
			'LV' => 'Latvia',
			'LT' => 'Lithuania',
			'NO' => 'Norway',
			'SJ' => 'Svalbard and Jan Mayen',
			'SE' => 'Sweden',
			'GB' => 'Great Britain',
			'AX' => 'Åland Islands',
		),
		'Polynesia' => array(
			'AS' => 'American Samoa',
			'CK' => 'Cook Islands',
			'PF' => 'French Polynesia',
			'NU' => 'Niue',
			'PN' => 'Pitcairn Islands',
			'WS' => 'Samoa',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TV' => 'Tuvalu',
			'WF' => 'Wallis and Futuna',
		),
		'South America' => array(
			'AR' => 'Argentina',
			'BO' => 'Bolivia',
			'BR' => 'Brazil',
			'CL' => 'Chile',
			'CO' => 'Colombia',
			'EC' => 'Ecuador',
			'FK' => 'Falkland Islands',
			'GF' => 'French Guiana',
			'GY' => 'Guyana',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'SR' => 'Suriname',
			'UY' => 'Uruguay',
			'VE' => 'Venezuela',
		),
		'South-Eastern Asia' => array(
			'BN' => 'Brunei',
			'KH' => 'Cambodia',
			'ID' => 'Indonesia',
			'LA' => 'Laos',
			'MY' => 'Malaysia',
			'MM' => 'Myanmar [Burma]',
			'PH' => 'Philippines',
			'SG' => 'Singapore',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'VN' => 'Vietnam',
		),
		'Southern Africa' => array(
			'BW' => 'Botswana',
			'LS' => 'Lesotho',
			'NA' => 'Namibia',
			'ZA' => 'South Africa',
			'SZ' => 'Swaziland',
		),
		'Southern Asia' => array(
			'AF' => 'Afghanistan',
			'BD' => 'Bangladesh',
			'BT' => 'Bhutan',
			'IN' => 'India',
			'IR' => 'Iran',
			'MV' => 'Maldives',
			'NP' => 'Nepal',
			'PK' => 'Pakistan',
			'LK' => 'Sri Lanka',
		),
		'Southern Europe' => array(
			'AL' => 'Albania',
			'AD' => 'Andorra',
			'BA' => 'Bosnia and Herzegovina',
			'HR' => 'Croatia',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'IT' => 'Italy',
			'MK' => 'Macedonia',
			'MT' => 'Malta',
			'ME' => 'Montenegro',
			'PT' => 'Portugal',
			'SM' => 'San Marino',
			'RS' => 'Serbia',
			'CS' => 'Serbia and Montenegro',
			'SI' => 'Slovenia',
			'ES' => 'Spain',
			'VA' => 'Vatican City',
		),
		'Western Africa' => array(
			'BJ' => 'Benin',
			'BF' => 'Burkina Faso',
			'CV' => 'Cape Verde',
			'CI' => 'Côte d’Ivoire',
			'GM' => 'Gambia',
			'GH' => 'Ghana',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'LR' => 'Liberia',
			'ML' => 'Mali',
			'MR' => 'Mauritania',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'SH' => 'Saint Helena',
			'SN' => 'Senegal',
			'SL' => 'Sierra Leone',
			'TG' => 'Togo',
		),
		'Western Asia' => array(
			'AM' => 'Armenia',
			'AZ' => 'Azerbaijan',
			'BH' => 'Bahrain',
			'CY' => 'Cyprus',
			'GE' => 'Georgia',
			'IQ' => 'Iraq',
			'IL' => 'Israel',
			'JO' => 'Jordan',
			'KW' => 'Kuwait',
			'LB' => 'Lebanon',
			'NT' => 'Neutral Zone',
			'OM' => 'Oman',
			'PS' => 'Palestinian Territories',
			'YD' => 'People\'s Democratic Republic of Yemen',
			'QA' => 'Qatar',
			'SA' => 'Saudi Arabia',
			'SY' => 'Syria',
			'TR' => 'Turkey',
			'AE' => 'United Arab Emirates',
			'YE' => 'Yemen',
		),
		'Western Europe' => array(
			'AT' => 'Austria',
			'BE' => 'Belgium',
			'DD' => 'East Germany',
			'FR' => 'France',
			'DE' => 'Germany',
			'LI' => 'Liechtenstein',
			'LU' => 'Luxembourg',
			'FX' => 'Metropolitan France',
			'MC' => 'Monaco',
			'NL' => 'Netherlands',
			'CH' => 'Switzerland',
		),
	);
	
	const CURRENCY_BY_COUNTRY = array(
		'AFN' => array( 'AF' ),
		'ALL' => array( 'AL' ),
		'DZD' => array( 'DZ' ),
		'USD' => array( 'AS', 'IO', 'GU', 'MH', 'FM', 'MP', 'PW', 'PR', 'TC', 'US', 'UM', 'VI' ),
		'EUR' => array( 'AD', 'AT', 'BE', 'CY', 'EE', 'FI', 'FR', 'GF', 'TF', 'DE', 'GR', 'GP', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'MQ', 'YT', 'MC', 'ME', 'NL', 'PT', 'RE', 'PM', 'SM', 'SK', 'SI', 'ES' ),
		'AOA' => array( 'AO' ),
		'XCD' => array( 'AI', 'AQ', 'AG', 'DM', 'GD', 'MS', 'KN', 'LC', 'VC' ),
		'ARS' => array( 'AR' ),
		'AMD' => array( 'AM' ),
		'AWG' => array( 'AW' ),
		'AUD' => array( 'AU', 'CX', 'CC', 'HM', 'KI', 'NR', 'NF', 'TV' ),
		'AZN' => array( 'AZ' ),
		'BSD' => array( 'BS' ),
		'BHD' => array( 'BH' ),
		'BDT' => array( 'BD' ),
		'BBD' => array( 'BB' ),
		'BYR' => array( 'BY' ),
		'BZD' => array( 'BZ' ),
		'XOF' => array( 'BJ', 'BF', 'ML', 'NE', 'SN', 'TG' ),
		'BMD' => array( 'BM' ),
		'BTN' => array( 'BT' ),
		'BOB' => array( 'BO' ),
		'BAM' => array( 'KM', 'BA' ),
		'BWP' => array( 'BW' ),
		'NOK' => array( 'BV', 'NO', 'SJ' ),
		'BRL' => array( 'BR' ),
		'BND' => array( 'BN' ),
		'BGN' => array( 'BG' ),
		'BIF' => array( 'BI' ),
		'KHR' => array( 'KH' ),
		'XAF' => array( 'CM', 'CF', 'TD', 'CG', 'GQ', 'GA' ),
		'CAD' => array( 'CA' ),
		'CVE' => array( 'CV' ),
		'KYD' => array( 'KY' ),
		'CLP' => array( 'CL' ),
		'CNY' => array( 'CN' ),
		'HKD' => array( 'HK' ),
		'COP' => array( 'CO' ),
		'KMF' => array( 'KM' ),
		'CDF' => array( 'CD' ),
		'NZD' => array( 'CK', 'NZ', 'NU', 'PN', 'TK' ),
		'CRC' => array( 'CR' ),
		'HRK' => array( 'HR' ),
		'CUP' => array( 'CU' ),
		'CZK' => array( 'CZ' ),
		'DKK' => array( 'DK', 'FO', 'GL' ),
		'DJF' => array( 'DJ' ),
		'DOP' => array( 'DO' ),
		'ECS' => array( 'EC' ),
		'EGP' => array( 'EG' ),
		'SVC' => array( 'SV' ),
		'ERN' => array( 'ER' ),
		'ETB' => array( 'ET' ),
		'FKP' => array( 'FK' ),
		'FJD' => array( 'FJ' ),
		'GMD' => array( 'GM' ),
		'GEL' => array( 'GE' ),
		'GHS' => array( 'GH' ),
		'GIP' => array( 'GI' ),
		'QTQ' => array( 'GT' ),
		'GGP' => array( 'GG' ),
		'GNF' => array( 'GN' ),
		'GWP' => array( 'GW' ),
		'GYD' => array( 'GY' ),
		'HTG' => array( 'HT' ),
		'HNL' => array( 'HN' ),
		'HUF' => array( 'HU' ),
		'ISK' => array( 'IS' ),
		'INR' => array( 'IN' ),
		'IDR' => array( 'ID' ),
		'IRR' => array( 'IR' ),
		'IQD' => array( 'IQ' ),
		'GBP' => array( 'IM', 'JE', 'GS', 'GB' ),
		'ILS' => array( 'IL' ),
		'JMD' => array( 'JM' ),
		'JPY' => array( 'JP' ),
		'JOD' => array( 'JO' ),
		'KZT' => array( 'KZ' ),
		'KES' => array( 'KE' ),
		'KPW' => array( 'KP' ),
		'KRW' => array( 'KR' ),
		'KWD' => array( 'KW' ),
		'KGS' => array( 'KG' ),
		'LAK' => array( 'LA' ),
		'LBP' => array( 'LB' ),
		'LSL' => array( 'LS' ),
		'LRD' => array( 'LR' ),
		'LYD' => array( 'LY' ),
		'CHF' => array( 'LI', 'CH' ),
		'MKD' => array( 'MK' ),
		'MGF' => array( 'MG' ),
		'MWK' => array( 'MW' ),
		'MYR' => array( 'MY' ),
		'MVR' => array( 'MV' ),
		'MRO' => array( 'MR' ),
		'MUR' => array( 'MU' ),
		'MXN' => array( 'MX' ),
		'MDL' => array( 'MD' ),
		'MNT' => array( 'MN' ),
		'MAD' => array( 'MA', 'EH' ),
		'MZN' => array( 'MZ' ),
		'MMK' => array( 'MM' ),
		'NAD' => array( 'NA' ),
		'NPR' => array( 'NP' ),
		'ANG' => array( 'AN' ),
		'XPF' => array( 'NC', 'WF' ),
		'NIO' => array( 'NI' ),
		'NGN' => array( 'NG' ),
		'OMR' => array( 'OM' ),
		'PKR' => array( 'PK' ),
		'PAB' => array( 'PA' ),
		'PGK' => array( 'PG' ),
		'PYG' => array( 'PY' ),
		'PEN' => array( 'PE' ),
		'PHP' => array( 'PH' ),
		'PLN' => array( 'PL' ),
		'QAR' => array( 'QA' ),
		'RON' => array( 'RO' ),
		'RUB' => array( 'RU' ),
		'RWF' => array( 'RW' ),
		'SHP' => array( 'SH' ),
		'WST' => array( 'WS' ),
		'STD' => array( 'ST' ),
		'SAR' => array( 'SA' ),
		'RSD' => array( 'RS' ),
		'SCR' => array( 'SC' ),
		'SLL' => array( 'SL' ),
		'SGD' => array( 'SG' ),
		'SBD' => array( 'SB' ),
		'SOS' => array( 'SO' ),
		'ZAR' => array( 'ZA' ),
		'SSP' => array( 'SS' ),
		'LKR' => array( 'LK' ),
		'SDG' => array( 'SD' ),
		'SRD' => array( 'SR' ),
		'SZL' => array( 'SZ' ),
		'SEK' => array( 'SE' ),
		'SYP' => array( 'SY' ),
		'TWD' => array( 'TW' ),
		'TJS' => array( 'TJ' ),
		'TZS' => array( 'TZ' ),
		'THB' => array( 'TH' ),
		'TOP' => array( 'TO' ),
		'TTD' => array( 'TT' ),
		'TND' => array( 'TN' ),
		'TRY' => array( 'TR' ),
		'TMT' => array( 'TM' ),
		'UGX' => array( 'UG' ),
		'UAH' => array( 'UA' ),
		'AED' => array( 'AE' ),
		'UYU' => array( 'UY' ),
		'UZS' => array( 'UZ' ),
		'VUV' => array( 'VU' ),
		'VEF' => array( 'VE' ),
		'VND' => array( 'VN' ),
		'YER' => array( 'YE' ),
		'ZMW' => array( 'ZM' ),
		'ZWD' => array( 'ZW' )
	);
	
	const CURRENCY_SYMBOL = array(
		'AED' => '&#1583;.&#1573;', // ?
		'AFN' => '&#65;&#102;',
		'ALL' => '&#76;&#101;&#107;',
		'AMD' => '&#1423;',
		'ANG' => '&#402;',
		'AOA' => '&#75;&#122;', // ?
		'ARS' => '&#36;',
		'AUD' => '&#36;',
		'AWG' => '&#402;',
		'AZN' => '&#1084;&#1072;&#1085;',
		'BAM' => '&#75;&#77;',
		'BBD' => '&#36;',
		'BDT' => '&#2547;', // ?
		'BGN' => '&#1083;&#1074;',
		'BHD' => '.&#1583;.&#1576;', // ?
		'BIF' => '&#70;&#66;&#117;', // ?
		'BMD' => '&#36;',
		'BND' => '&#36;',
		'BOB' => '&#36;&#98;',
		'BRL' => '&#82;&#36;',
		'BSD' => '&#36;',
		'BTN' => '&#78;&#117;&#46;', // ?
		'BWP' => '&#80;',
		'BYR' => '&#112;&#46;',
		'BZD' => '&#66;&#90;&#36;',
		'CAD' => '&#36;',
		'CDF' => '&#70;&#67;',
		'CHF' => '&#67;&#72;&#70;',
		'CLF' => '&#85;&#70;', // ?
		'CLP' => '&#36;',
		'CNY' => '&#165;',
		'COP' => '&#36;',
		'CRC' => '&#8353;',
		'CUP' => '&#8396;',
		'CVE' => '&#36;', // ?
		'CZK' => '&#75;&#269;',
		'DJF' => '&#70;&#100;&#106;', // ?
		'DKK' => '&#107;&#114;',
		'DOP' => '&#82;&#68;&#36;',
		'DZD' => '&#1583;&#1580;', // ?
		'EGP' => '&#163;',
		'ETB' => '&#66;&#114;',
		'EUR' => '&#8364;',
		'FJD' => '&#36;',
		'FKP' => '&#163;',
		'GBP' => '&#163;',
		'GEL' => '&#4314;', // ?
		'GHS' => '&#162;',
		'GIP' => '&#163;',
		'GMD' => '&#68;', // ?
		'GNF' => '&#70;&#71;', // ?
		'GTQ' => '&#81;',
		'GYD' => '&#36;',
		'HKD' => '&#36;',
		'HNL' => '&#76;',
		'HRK' => '&#107;&#110;',
		'HTG' => '&#71;', // ?
		'HUF' => '&#70;&#116;',
		'IDR' => '&#82;&#112;',
		'ILS' => '&#8362;',
		'INR' => '&#8377;',
		'IQD' => '&#1593;.&#1583;', // ?
		'IRR' => '&#65020;',
		'ISK' => '&#107;&#114;',
		'JEP' => '&#163;',
		'JMD' => '&#74;&#36;',
		'JOD' => '&#74;&#68;', // ?
		'JPY' => '&#165;',
		'KES' => '&#75;&#83;&#104;', // ?
		'KGS' => '&#1083;&#1074;',
		'KHR' => '&#6107;',
		'KMF' => '&#67;&#70;', // ?
		'KPW' => '&#8361;',
		'KRW' => '&#8361;',
		'KWD' => '&#1583;.&#1603;', // ?
		'KYD' => '&#36;',
		'KZT' => '&#1083;&#1074;',
		'LAK' => '&#8365;',
		'LBP' => '&#163;',
		'LKR' => '&#8360;',
		'LRD' => '&#36;',
		'LSL' => '&#76;', // ?
		'LTL' => '&#76;&#116;',
		'LVL' => '&#76;&#115;',
		'LYD' => '&#1604;.&#1583;', // ?
		'MAD' => '&#1583;.&#1605;.', //?
		'MDL' => '&#76;',
		'MGA' => '&#65;&#114;', // ?
		'MKD' => '&#1076;&#1077;&#1085;',
		'MMK' => '&#75;',
		'MNT' => '&#8366;',
		'MOP' => '&#77;&#79;&#80;&#36;', // ?
		'MRO' => '&#85;&#77;', // ?
		'MUR' => '&#8360;', // ?
		'MVR' => '.&#1923;', // ?
		'MWK' => '&#77;&#75;',
		'MXN' => '&#36;',
		'MYR' => '&#82;&#77;',
		'MZN' => '&#77;&#84;',
		'NAD' => '&#36;',
		'NGN' => '&#8358;',
		'NIO' => '&#67;&#36;',
		'NOK' => '&#107;&#114;',
		'NPR' => '&#8360;',
		'NZD' => '&#36;',
		'OMR' => '&#65020;',
		'PAB' => '&#66;&#47;&#46;',
		'PEN' => '&#83;&#47;&#46;',
		'PGK' => '&#75;', // ?
		'PHP' => '&#8369;',
		'PKR' => '&#8360;',
		'PLN' => '&#122;&#322;',
		'PYG' => '&#71;&#115;',
		'QAR' => '&#65020;',
		'RON' => '&#108;&#101;&#105;',
		//'RSD' => '&#1044;&#1080;&#1085;&#46;',
		'RSD' => '&#1076;&#1080;&#1085;',
		'RUB' => '&#1088;&#1091;&#1073;',
		'RWF' => '&#1585;.&#1587;',
		'SAR' => '&#65020;',
		'SBD' => '&#36;',
		'SCR' => '&#8360;',
		'SDG' => '&#163;', // ?
		'SEK' => '&#107;&#114;',
		'SGD' => '&#36;',
		'SHP' => '&#163;',
		'SLL' => '&#76;&#101;', // ?
		'SOS' => '&#83;',
		'SRD' => '&#36;',
		'STD' => '&#68;&#98;', // ?
		'SVC' => '&#36;',
		'SYP' => '&#163;',
		'SZL' => '&#76;', // ?
		'THB' => '&#3647;',
		'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
		'TMT' => '&#109;',
		'TND' => '&#1583;.&#1578;',
		'TOP' => '&#84;&#36;',
		'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
		'TTD' => '&#36;',
		'TWD' => '&#78;&#84;&#36;',
		'TZS' => '&#84;&#83;&#104;',
		'UAH' => '&#8372;',
		'UGX' => '&#85;&#83;&#104;',
		'USD' => '&#36;',
		'UYU' => '&#36;&#85;',
		'UZS' => '&#1083;&#1074;',
		'VEF' => '&#66;&#115;',
		'VND' => '&#8363;',
		'VUV' => '&#86;&#84;',
		'WST' => '&#87;&#83;&#36;',
		'XAF' => '&#70;&#67;&#70;&#65;',
		'XCD' => '&#36;',
		'XDR' => '&#83;&#68;&#82;',
		'XOF' => '&#67;&#70;&#65;',
		'XPF' => '&#70;',
		'YER' => '&#65020;',
		'ZAR' => '&#82;',
		'ZMK' => '&#90;&#75;', // ?
		'ZWL' => '&#90;&#36;',
		'RTG' => '&#90;&#36;'
	);

	const CURRENCY_NAME = array(
		'AED' => 'United Arab Emirates dirham',
		'AFN' => 'Afghan afghani',
		'ALL' => 'Albanian lek',
		'AMD' => 'Armenian dram',
		'ANG' => 'Netherlands Antillean guilder',
		'AOA' => 'Angolan kwanza',
		'ARS' => 'Argentine peso',
		'AUD' => 'Australian dollar',
		'AWG' => 'Aruban florin',
		'AZN' => 'Azerbaijani manat',
		'BAM' => 'Bosnia and Herzegovina convertible mark',
		'BBD' => 'Barbados dollar',
		'BDT' => 'Bangladeshi taka',
		'BGN' => 'Bulgarian lev',
		'BHD' => 'Bahraini dinar',
		'BIF' => 'Burundian franc',
		'BMD' => 'Bermudian dollar',
		'BND' => 'Brunei dollar',
		'BOB' => 'Boliviano',
		'BRL' => 'Brazilian real',
		'BSD' => 'Bahamian dollar',
		'BTN' => 'Bhutanese ngultrum',
		'BWP' => 'Botswana pula',
		'BYN' => 'New Belarusian ruble',
		'BYR' => 'Belarusian ruble',
		'BZD' => 'Belize dollar',
		'CAD' => 'Canadian dollar',
		'CDF' => 'Congolese franc',
		'CHF' => 'Swiss franc',
		'CLF' => 'Unidad de Fomento',
		'CLP' => 'Chilean peso',
		'CNY' => 'Renminbi|Chinese yuan',
		'COP' => 'Colombian peso',
		'CRC' => 'Costa Rican colon',
		'CUC' => 'Cuban convertible peso',
		'CUP' => 'Cuban peso',
		'CVE' => 'Cape Verde escudo',
		'CZK' => 'Czech koruna',
		'DJF' => 'Djiboutian franc',
		'DKK' => 'Danish krone',
		'DOP' => 'Dominican peso',
		'DZD' => 'Algerian dinar',
		'EGP' => 'Egyptian pound',
		'ERN' => 'Eritrean nakfa',
		'ETB' => 'Ethiopian birr',
		'EUR' => 'Euro',
		'FJD' => 'Fiji dollar',
		'FKP' => 'Falkland Islands pound',
		'GBP' => 'Pound sterling',
		'GEL' => 'Georgian lari',
		'GHS' => 'Ghanaian cedi',
		'GIP' => 'Gibraltar pound',
		'GMD' => 'Gambian dalasi',
		'GNF' => 'Guinean franc',
		'GTQ' => 'Guatemalan quetzal',
		'GYD' => 'Guyanese dollar',
		'HKD' => 'Hong Kong dollar',
		'HNL' => 'Honduran lempira',
		'HRK' => 'Croatian kuna',
		'HTG' => 'Haitian gourde',
		'HUF' => 'Hungarian forint',
		'IDR' => 'Indonesian rupiah',
		'ILS' => 'Israeli new shekel',
		'INR' => 'Indian rupee',
		'IQD' => 'Iraqi dinar',
		'IRR' => 'Iranian rial',
		'ISK' => 'Icelandic króna',
		'JMD' => 'Jamaican dollar',
		'JOD' => 'Jordanian dinar',
		'JPY' => 'Japanese yen',
		'KES' => 'Kenyan shilling',
		'KGS' => 'Kyrgyzstani som',
		'KHR' => 'Cambodian riel',
		'KMF' => 'Comoro franc',
		'KPW' => 'North Korean won',
		'KRW' => 'South Korean won',
		'KWD' => 'Kuwaiti dinar',
		'KYD' => 'Cayman Islands dollar',
		'KZT' => 'Kazakhstani tenge',
		'LAK' => 'Lao kip',
		'LBP' => 'Lebanese pound',
		'LKR' => 'Sri Lankan rupee',
		'LRD' => 'Liberian dollar',
		'LSL' => 'Lesotho loti',
		'LYD' => 'Libyan dinar',
		'MAD' => 'Moroccan dirham',
		'MDL' => 'Moldovan leu',
		'MGA' => 'Malagasy ariary',
		'MKD' => 'Macedonian denar',
		'MMK' => 'Myanmar kyat',
		'MNT' => 'Mongolian tögrög',
		'MOP' => 'Macanese pataca',
		'MRO' => 'Mauritanian ouguiya',
		'MUR' => 'Mauritian rupee',
		'MVR' => 'Maldivian rufiyaa',
		'MWK' => 'Malawian kwacha',
		'MXN' => 'Mexican peso',
		'MXV' => 'Mexican Unidad de Inversion',
		'MYR' => 'Malaysian ringgit',
		'MZN' => 'Mozambican metical',
		'NAD' => 'Namibian dollar',
		'NGN' => 'Nigerian naira',
		'NIO' => 'Nicaraguan córdoba',
		'NOK' => 'Norwegian krone',
		'NPR' => 'Nepalese rupee',
		'NZD' => 'New Zealand dollar',
		'OMR' => 'Omani rial',
		'PAB' => 'Panamanian balboa',
		'PEN' => 'Peruvian Sol',
		'PGK' => 'Papua New Guinean kina',
		'PHP' => 'Philippine peso',
		'PKR' => 'Pakistani rupee',
		'PLN' => 'Polish złoty',
		'PYG' => 'Paraguayan guaraní',
		'QAR' => 'Qatari riyal',
		'RON' => 'Romanian leu',
		'RSD' => 'Serbian dinar',
		'RUB' => 'Russian ruble',
		'RWF' => 'Rwandan franc',
		'SAR' => 'Saudi riyal',
		'SBD' => 'Solomon Islands dollar',
		'SCR' => 'Seychelles rupee',
		'SDG' => 'Sudanese pound',
		'SEK' => 'Swedish krona',
		'SGD' => 'Singapore dollar',
		'SHP' => 'Saint Helena pound',
		'SLL' => 'Sierra Leonean leone',
		'SOS' => 'Somali shilling',
		'SRD' => 'Surinamese dollar',
		'SSP' => 'South Sudanese pound',
		'STD' => 'São Tomé and Príncipe dobra',
		'SVC' => 'Salvadoran colón',
		'SYP' => 'Syrian pound',
		'SZL' => 'Swazi lilangeni',
		'THB' => 'Thai baht',
		'TJS' => 'Tajikistani somoni',
		'TMT' => 'Turkmenistani manat',
		'TND' => 'Tunisian dinar',
		'TOP' => 'Tongan paʻanga',
		'TRY' => 'Turkish lira',
		'TTD' => 'Trinidad and Tobago dollar',
		'TWD' => 'New Taiwan dollar',
		'TZS' => 'Tanzanian shilling',
		'UAH' => 'Ukrainian hryvnia',
		'UGX' => 'Ugandan shilling',
		'USD' => 'United States dollar',
		'UYI' => 'Uruguay Peso en Unidades Indexadas',
		'UYU' => 'Uruguayan peso',
		'UZS' => 'Uzbekistan som',
		'VEF' => 'Venezuelan bolívar',
		'VND' => 'Vietnamese đồng',
		'VUV' => 'Vanuatu vatu',
		'WST' => 'Samoan tala',
		'XAF' => 'Central African CFA franc',
		'XCD' => 'East Caribbean dollar',
		'XOF' => 'West African CFA franc',
		'XDR' => 'Special drawing rights',
		'XPF' => 'CFP franc',
		'XXX' => 'No currency',
		'YER' => 'Yemeni rial',
		'ZAR' => 'South African rand',
		'ZMW' => 'Zambian kwacha',
		'ZWL' => 'Zimbabwean dollar',
		'RTG' => 'Zimbabwean dollar'
	);
	
	const COUNTRY_TO_LOCALE = array(
		'ad' => 'ca',
		'ae' => 'ar',
		'af' => 'fa,ps',
		'ag' => 'en',
		'ai' => 'en',
		'al' => 'sq',
		'am' => 'hy',
		'an' => 'nl,en',
		'ao' => 'pt',
		'aq' => 'en',
		'ar' => 'es',
		'as' => 'en,sm',
		'at' => 'de',
		'au' => 'en',
		'aw' => 'nl,pap',
		'ax' => 'sv',
		'az' => 'az',
		'ba' => 'bs,hr,sr',
		'bb' => 'en',
		'bd' => 'bn',
		'be' => 'nl,fr,de',
		'bf' => 'fr',
		'bg' => 'bg',
		'bh' => 'ar',
		'bi' => 'fr',
		'bj' => 'fr',
		'bl' => 'fr',
		'bm' => 'en',
		'bn' => 'ms',
		'bo' => 'es,qu,ay',
		'br' => 'pt',
		'bq' => 'nl,en',
		'bs' => 'en',
		'bt' => 'dz',
		'bv' => 'no',
		'bw' => 'en,tn',
		'by' => 'be,ru',
		'bz' => 'en',
		'ca' => 'en,fr',
		'cc' => 'en',
		'cd' => 'fr',
		'cf' => 'fr',
		'cg' => 'fr',
		'ch' => 'de,fr,it,rm',
		'ci' => 'fr',
		'ck' => 'en,rar',
		'cl' => 'es',
		'cm' => 'fr,en',
		'cn' => 'zh',
		'co' => 'es',
		'cr' => 'es',
		'cu' => 'es',
		'cv' => 'pt',
		'cw' => 'nl',
		'cx' => 'en',
		'cy' => 'el,tr',
		'cz' => 'cs',
		'de' => 'de',
		'dj' => 'fr,ar,so',
		'dk' => 'da',
		'dm' => 'en',
		'do' => 'es',
		'dz' => 'ar',
		'ec' => 'es',
		'ee' => 'et',
		'eg' => 'ar',
		'eh' => 'ar,es,fr',
		'er' => 'ti,ar,en',
		'es' => 'es,ast,ca,eu,gl',
		'et' => 'am,om',
		'fi' => 'fi,sv,se',
		'fj' => 'en',
		'fk' => 'en',
		'fm' => 'en',
		'fo' => 'fo',
		'fr' => 'fr',
		'ga' => 'fr',
		'gb' => 'en,ga,cy,gd,kw',
		'gd' => 'en',
		'ge' => 'ka',
		'gf' => 'fr',
		'gg' => 'en',
		'gh' => 'en',
		'gi' => 'en',
		'gl' => 'kl,da',
		'gm' => 'en',
		'gn' => 'fr',
		'gp' => 'fr',
		'gq' => 'es,fr,pt',
		'gr' => 'el',
		'gs' => 'en',
		'gt' => 'es',
		'gu' => 'en,ch',
		'gw' => 'pt',
		'gy' => 'en',
		'hk' => 'zh,en',
		'hm' => 'en',
		'hn' => 'es',
		'hr' => 'hr',
		'ht' => 'fr,ht',
		'hu' => 'hu',
		'id' => 'id',
		'ie' => 'en,ga',
		'il' => 'he',
		'im' => 'en',
		'in' => 'hi,en',
		'io' => 'en',
		'iq' => 'ar,ku',
		'ir' => 'fa',
		'is' => 'is',
		'it' => 'it,de,fr',
		'je' => 'en',
		'jm' => 'en',
		'jo' => 'ar',
		'jp' => 'ja',
		'ke' => 'sw,en',
		'kg' => 'ky,ru',
		'kh' => 'km',
		'ki' => 'en',
		'km' => 'ar,fr',
		'kn' => 'en',
		'kp' => 'ko',
		'kr' => 'ko,en',
		'kw' => 'ar',
		'ky' => 'en',
		'kz' => 'kk,ru',
		'la' => 'lo',
		'lb' => 'ar,fr',
		'lc' => 'en',
		'li' => 'de',
		'lk' => 'si,ta',
		'lr' => 'en',
		'ls' => 'en,st',
		'lt' => 'lt',
		'lu' => 'lb,fr,de',
		'lv' => 'lv',
		'ly' => 'ar',
		'ma' => 'ar',
		'mc' => 'fr',
		'md' => 'ru,uk,ro',
		'me' => 'srp,sq,bs,hr,sr',
		'mf' => 'fr',
		'mg' => 'mg,fr',
		'mh' => 'en,mh',
		'mk' => 'mk',
		'ml' => 'fr',
		'mm' => 'my',
		'mn' => 'mn',
		'mo' => 'zh,en,pt',
		'mp' => 'ch',
		'mq' => 'fr',
		'mr' => 'ar,fr',
		'ms' => 'en',
		'mt' => 'mt,en',
		'mu' => 'mfe,fr,en',
		'mv' => 'dv',
		'mw' => 'en,ny',
		'mx' => 'es',
		'my' => 'ms,zh,en',
		'mz' => 'pt',
		'na' => 'en,sf,de',
		'nc' => 'fr',
		'ne' => 'fr',
		'nf' => 'en,pih',
		'ng' => 'en',
		'ni' => 'es',
		'nl' => 'nl',
		'no' => 'nb,nn,no,se',
		'np' => 'ne',
		'nr' => 'na,en',
		'nu' => 'niu,en',
		'nz' => 'en,mi',
		'om' => 'ar',
		'pa' => 'es',
		'pe' => 'es',
		'pf' => 'fr',
		'pg' => 'en,tpi,ho',
		'ph' => 'en,tl',
		'pk' => 'en,ur',
		'pl' => 'pl',
		'pm' => 'fr',
		'pn' => 'en,pih',
		'pr' => 'es,en',
		'ps' => 'ar,he',
		'pt' => 'pt',
		'pw' => 'en,pau,ja,sov,tox',
		'py' => 'es,gn',
		'qa' => 'ar',
		're' => 'fr',
		'ro' => 'ro',
		'rs' => 'sr',
		'ru' => 'ru',
		'rw' => 'rw,fr,en',
		'sa' => 'ar',
		'sb' => 'en',
		'sc' => 'fr,en,crs',
		'sd' => 'ar,en',
		'se' => 'sv',
		'sg' => 'en,ms,zh,ta',
		'sh' => 'en',
		'si' => 'sl',
		'sj' => 'no',
		'sk' => 'sk',
		'sl' => 'en',
		'sm' => 'it',
		'sn' => 'fr',
		'so' => 'so,ar',
		'sr' => 'nl',
		'st' => 'pt',
		'ss' => 'en',
		'sv' => 'es',
		'sx' => 'nl,en',
		'sy' => 'ar',
		'sz' => 'en,ss',
		'tc' => 'en',
		'td' => 'fr,ar',
		'tf' => 'fr',
		'tg' => 'fr',
		'th' => 'th',
		'tj' => 'tg,ru',
		'tk' => 'tkl,en,sm',
		'tl' => 'pt,tet',
		'tm' => 'tk',
		'tn' => 'ar',
		'to' => 'en',
		'tr' => 'tr',
		'tt' => 'en',
		'tv' => 'en',
		'tw' => 'zh',
		'tz' => 'sw,en',
		'ua' => 'uk',
		'ug' => 'en,sw',
		'um' => 'en',
		'us' => 'en,es',
		'uy' => 'es',
		'uz' => 'uz,kaa',
		'va' => 'it',
		'vc' => 'en',
		've' => 'es',
		'vg' => 'en',
		'vi' => 'en',
		'vn' => 'vi',
		'vu' => 'bi,en,fr',
		'wf' => 'fr',
		'ws' => 'sm,en',
		'ye' => 'ar',
		'yt' => 'fr',
		'za' => 'zu,xh,af,st,tn,en',
		'zm' => 'en',
		'zw' => 'en,sn,nd'
	);
}
endif;