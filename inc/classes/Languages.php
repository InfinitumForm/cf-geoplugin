<?php
/**
 * Language setup
 *
 * Automatic Language
 *
 * @link          http://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       2.0.0
 *
 * https://github.com/ocReaper/wpml-automatic-language-with-geoip
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_Language')) : class CFGP_Language extends CFGP_Global {
	
	// ISO-3166-2 language codes
	private static $wp_locale_conversion = array(
		'af' => array(
			'name' => 'Afrikaans',
			'code' => 'af',
			'wp_locale' => 'af'
		) ,
		'ak' => array(
			'name' => 'Akan',
			'code' => 'ak',
			'wp_locale' => 'ak'
		) ,
		'sq' => array(
			'name' => 'Albanian',
			'code' => 'sq',
			'wp_locale' => 'sq'
		) ,
		'am' => array(
			'name' => 'Amharic',
			'code' => 'am',
			'wp_locale' => 'am'
		) ,
		'ar' => array(
			'name' => 'Arabic',
			'code' => 'ar',
			'wp_locale' => 'ar'
		) ,
		'hy' => array(
			'name' => 'Armenian',
			'code' => 'hy',
			'wp_locale' => 'hy'
		) ,
		'rup_MK' => array(
			'name' => 'Aromanian',
			'code' => 'rup',
			'wp_locale' => 'rup_MK'
		) ,
		'as' => array(
			'name' => 'Assamese',
			'code' => 'as',
			'wp_locale' => 'as'
		) ,
		'az' => array(
			'name' => 'Azerbaijani',
			'code' => 'az',
			'wp_locale' => 'az'
		) ,
		'az_TR' => array(
			'name' => 'Azerbaijani (Turkey)',
			'code' => 'az-tr',
			'wp_locale' => 'az_TR'
		) ,
		'ba' => array(
			'name' => 'Bashkir',
			'code' => 'ba',
			'wp_locale' => 'ba'
		) ,
		'eu' => array(
			'name' => 'Basque',
			'code' => 'eu',
			'wp_locale' => 'eu'
		) ,
		'bel' => array(
			'name' => 'Belarusian',
			'code' => 'bel',
			'wp_locale' => 'bel'
		) ,
		'bn_BD' => array(
			'name' => 'Bengali',
			'code' => 'bn',
			'wp_locale' => 'bn_BD'
		) ,
		'bs_BA' => array(
			'name' => 'Bosnian',
			'code' => 'bs',
			'wp_locale' => 'bs_BA'
		) ,
		'bg_BG' => array(
			'name' => 'Bulgarian',
			'code' => 'bg',
			'wp_locale' => 'bg_BG'
		) ,
		'my_MM' => array(
			'name' => 'Burmese',
			'code' => 'mya',
			'wp_locale' => 'my_MM'
		) ,
		'ca' => array(
			'name' => 'Catalan',
			'code' => 'ca',
			'wp_locale' => 'ca'
		) ,
		'bal' => array(
			'name' => 'Catalan (Balear)',
			'code' => 'bal',
			'wp_locale' => 'bal'
		) ,
		'zh_CN' => array(
			'name' => 'Chinese (China)',
			'code' => 'zh-cn',
			'wp_locale' => 'zh_CN'
		) ,
		'zh_HK' => array(
			'name' => 'Chinese (Hong Kong)',
			'code' => 'zh-hk',
			'wp_locale' => 'zh_HK'
		) ,
		'zh_TW' => array(
			'name' => 'Chinese (Taiwan)',
			'code' => 'zh-tw',
			'wp_locale' => 'zh_TW'
		) ,
		'co' => array(
			'name' => 'Corsican',
			'code' => 'co',
			'wp_locale' => 'co'
		) ,
		'hr' => array(
			'name' => 'Croatian',
			'code' => 'hr',
			'wp_locale' => 'hr'
		) ,
		'cs_CZ' => array(
			'name' => 'Czech',
			'code' => 'cs',
			'wp_locale' => 'cs_CZ'
		) ,
		'da_DK' => array(
			'name' => 'Danish',
			'code' => 'da',
			'wp_locale' => 'da_DK'
		) ,
		'dv' => array(
			'name' => 'Dhivehi',
			'code' => 'dv',
			'wp_locale' => 'dv'
		) ,
		'nl_NL' => array(
			'name' => 'Dutch',
			'code' => 'nl',
			'wp_locale' => 'nl_NL'
		) ,
		'nl_BE' => array(
			'name' => 'Dutch (Belgium)',
			'code' => 'nl-be',
			'wp_locale' => 'nl_BE'
		) ,
		'en_US' => array(
			'name' => 'English',
			'code' => 'en',
			'wp_locale' => 'en_US'
		) ,
		'en_AU' => array(
			'name' => 'English (Australia)',
			'code' => 'en-au',
			'wp_locale' => 'en_AU'
		) ,
		'en_CA' => array(
			'name' => 'English (Canada)',
			'code' => 'en-ca',
			'wp_locale' => 'en_CA'
		) ,
		'en_GB' => array(
			'name' => 'English (UK)',
			'code' => 'en-gb',
			'wp_locale' => 'en_GB'
		) ,
		'eo' => array(
			'name' => 'Esperanto',
			'code' => 'eo',
			'wp_locale' => 'eo'
		) ,
		'et' => array(
			'name' => 'Estonian',
			'code' => 'et',
			'wp_locale' => 'et'
		) ,
		'fo' => array(
			'name' => 'Faroese',
			'code' => 'fo',
			'wp_locale' => 'fo'
		) ,
		'fi' => array(
			'name' => 'Finnish',
			'code' => 'fi',
			'wp_locale' => 'fi'
		) ,
		'fr_BE' => array(
			'name' => 'French (Belgium)',
			'code' => 'fr-be',
			'wp_locale' => 'fr_BE'
		) ,
		'fr_FR' => array(
			'name' => 'French (France)',
			'code' => 'fr',
			'wp_locale' => 'fr_FR'
		) ,
		'fy' => array(
			'name' => 'Frisian',
			'code' => 'fy',
			'wp_locale' => 'fy'
		) ,
		'fuc' => array(
			'name' => 'Fulah',
			'code' => 'fuc',
			'wp_locale' => 'fuc'
		) ,
		'gl_ES' => array(
			'name' => 'Galician',
			'code' => 'gl',
			'wp_locale' => 'gl_ES'
		) ,
		'ka_GE' => array(
			'name' => 'Georgian',
			'code' => 'ka',
			'wp_locale' => 'ka_GE'
		) ,
		'de_DE' => array(
			'name' => 'German',
			'code' => 'de',
			'wp_locale' => 'de_DE'
		) ,
		'de_CH' => array(
			'name' => 'German (Switzerland)',
			'code' => 'de-ch',
			'wp_locale' => 'de_CH'
		) ,
		'el' => array(
			'name' => 'Greek',
			'code' => 'el',
			'wp_locale' => 'el'
		) ,
		'gn' => array(
			'name' => 'Guaraní',
			'code' => 'gn',
			'wp_locale' => 'gn'
		) ,
		'gu_IN' => array(
			'name' => 'Gujarati',
			'code' => 'gu',
			'wp_locale' => 'gu_IN'
		) ,
		'haw_US' => array(
			'name' => 'Hawaiian',
			'code' => 'haw',
			'wp_locale' => 'haw_US'
		) ,
		'haz' => array(
			'name' => 'Hazaragi',
			'code' => 'haz',
			'wp_locale' => 'haz'
		) ,
		'he_IL' => array(
			'name' => 'Hebrew',
			'code' => 'he',
			'wp_locale' => 'he_IL'
		) ,
		'hi_IN' => array(
			'name' => 'Hindi',
			'code' => 'hi',
			'wp_locale' => 'hi_IN'
		) ,
		'hu_HU' => array(
			'name' => 'Hungarian',
			'code' => 'hu',
			'wp_locale' => 'hu_HU'
		) ,
		'is_IS' => array(
			'name' => 'Icelandic',
			'code' => 'is',
			'wp_locale' => 'is_IS'
		) ,
		'ido' => array(
			'name' => 'Ido',
			'code' => 'ido',
			'wp_locale' => 'ido'
		) ,
		'id_ID' => array(
			'name' => 'Indonesian',
			'code' => 'id',
			'wp_locale' => 'id_ID'
		) ,
		'ga' => array(
			'name' => 'Irish',
			'code' => 'ga',
			'wp_locale' => 'ga'
		) ,
		'it_IT' => array(
			'name' => 'Italian',
			'code' => 'it',
			'wp_locale' => 'it_IT'
		) ,
		'ja' => array(
			'name' => 'Japanese',
			'code' => 'ja',
			'wp_locale' => 'ja'
		) ,
		'jv_ID' => array(
			'name' => 'Javanese',
			'code' => 'jv',
			'wp_locale' => 'jv_ID'
		) ,
		'kn' => array(
			'name' => 'Kannada',
			'code' => 'kn',
			'wp_locale' => 'kn'
		) ,
		'kk' => array(
			'name' => 'Kazakh',
			'code' => 'kk',
			'wp_locale' => 'kk'
		) ,
		'km' => array(
			'name' => 'Khmer',
			'code' => 'km',
			'wp_locale' => 'km'
		) ,
		'kin' => array(
			'name' => 'Kinyarwanda',
			'code' => 'kin',
			'wp_locale' => 'kin'
		) ,
		'ky_KY' => array(
			'name' => 'Kirghiz',
			'code' => 'ky',
			'wp_locale' => 'ky_KY'
		) ,
		'ko_KR' => array(
			'name' => 'Korean',
			'code' => 'ko',
			'wp_locale' => 'ko_KR'
		) ,
		'ckb' => array(
			'name' => 'Kurdish (Sorani)',
			'code' => 'ckb',
			'wp_locale' => 'ckb'
		) ,
		'lo' => array(
			'name' => 'Lao',
			'code' => 'lo',
			'wp_locale' => 'lo'
		) ,
		'lv' => array(
			'name' => 'Latvian',
			'code' => 'lv',
			'wp_locale' => 'lv'
		) ,
		'li' => array(
			'name' => 'Limburgish',
			'code' => 'li',
			'wp_locale' => 'li'
		) ,
		'lin' => array(
			'name' => 'Lingala',
			'code' => 'lin',
			'wp_locale' => 'lin'
		) ,
		'lt_LT' => array(
			'name' => 'Lithuanian',
			'code' => 'lt',
			'wp_locale' => 'lt_LT'
		) ,
		'lb_LU' => array(
			'name' => 'Luxembourgish',
			'code' => 'lb',
			'wp_locale' => 'lb_LU'
		) ,
		'mk_MK' => array(
			'name' => 'Macedonian',
			'code' => 'mk',
			'wp_locale' => 'mk_MK'
		) ,
		'mg_MG' => array(
			'name' => 'Malagasy',
			'code' => 'mg',
			'wp_locale' => 'mg_MG'
		) ,
		'ms_MY' => array(
			'name' => 'Malay',
			'code' => 'ms',
			'wp_locale' => 'ms_MY'
		) ,
		'ml_IN' => array(
			'name' => 'Malayalam',
			'code' => 'ml',
			'wp_locale' => 'ml_IN'
		) ,
		'mr' => array(
			'name' => 'Marathi',
			'code' => 'mr',
			'wp_locale' => 'mr'
		) ,
		'xmf' => array(
			'name' => 'Mingrelian',
			'code' => 'xmf',
			'wp_locale' => 'xmf'
		) ,
		'mn' => array(
			'name' => 'Mongolian',
			'code' => 'mn',
			'wp_locale' => 'mn'
		) ,
		'me_ME' => array(
			'name' => 'Montenegrin',
			'code' => 'me',
			'wp_locale' => 'me_ME'
		) ,
		'ne_NP' => array(
			'name' => 'Nepali',
			'code' => 'ne',
			'wp_locale' => 'ne_NP'
		) ,
		'nb_NO' => array(
			'name' => 'Norwegian (Bokmål)',
			'code' => 'nb',
			'wp_locale' => 'nb_NO'
		) ,
		'nn_NO' => array(
			'name' => 'Norwegian (Nynorsk)',
			'code' => 'nn',
			'wp_locale' => 'nn_NO'
		) ,
		'ory' => array(
			'name' => 'Oriya',
			'code' => 'ory',
			'wp_locale' => 'ory'
		) ,
		'os' => array(
			'name' => 'Ossetic',
			'code' => 'os',
			'wp_locale' => 'os'
		) ,
		'ps' => array(
			'name' => 'Pashto',
			'code' => 'ps',
			'wp_locale' => 'ps'
		) ,
		'fa_IR' => array(
			'name' => 'Persian',
			'code' => 'fa',
			'wp_locale' => 'fa_IR'
		) ,
		'fa_AF' => array(
			'name' => 'Persian (Afghanistan)',
			'code' => 'fa-af',
			'wp_locale' => 'fa_AF'
		) ,
		'pl_PL' => array(
			'name' => 'Polish',
			'code' => 'pl',
			'wp_locale' => 'pl_PL'
		) ,
		'pt_BR' => array(
			'name' => 'Portuguese (Brazil)',
			'code' => 'pt-br',
			'wp_locale' => 'pt_BR'
		) ,
		'pt_PT' => array(
			'name' => 'Portuguese (Portugal)',
			'code' => 'pt',
			'wp_locale' => 'pt_PT'
		) ,
		'pa_IN' => array(
			'name' => 'Punjabi',
			'code' => 'pa',
			'wp_locale' => 'pa_IN'
		) ,
		'rhg' => array(
			'name' => 'Rohingya',
			'code' => 'rhg',
			'wp_locale' => 'rhg'
		) ,
		'ro_RO' => array(
			'name' => 'Romanian',
			'code' => 'ro',
			'wp_locale' => 'ro_RO'
		) ,
		'ru_RU' => array(
			'name' => 'Russian',
			'code' => 'ru',
			'wp_locale' => 'ru_RU'
		) ,
		'ru_UA' => array(
			'name' => 'Russian (Ukraine)',
			'code' => 'ru-ua',
			'wp_locale' => 'ru_UA'
		) ,
		'rue' => array(
			'name' => 'Rusyn',
			'code' => 'rue',
			'wp_locale' => 'rue'
		) ,
		'sah' => array(
			'name' => 'Sakha',
			'code' => 'sah',
			'wp_locale' => 'sah'
		) ,
		'sa_IN' => array(
			'name' => 'Sanskrit',
			'code' => 'sa-in',
			'wp_locale' => 'sa_IN'
		) ,
		'srd' => array(
			'name' => 'Sardinian',
			'code' => 'srd',
			'wp_locale' => 'srd'
		) ,
		'gd' => array(
			'name' => 'Scottish Gaelic',
			'code' => 'gd',
			'wp_locale' => 'gd'
		) ,
		'sr_RS' => array(
			'name' => 'Serbian',
			'code' => 'sr',
			'wp_locale' => 'sr_RS'
		) ,
		'sd_PK' => array(
			'name' => 'Sindhi',
			'code' => 'sd',
			'wp_locale' => 'sd_PK'
		) ,
		'si_LK' => array(
			'name' => 'Sinhala',
			'code' => 'si',
			'wp_locale' => 'si_LK'
		) ,
		'sk_SK' => array(
			'name' => 'Slovak',
			'code' => 'sk',
			'wp_locale' => 'sk_SK'
		) ,
		'sl_SI' => array(
			'name' => 'Slovenian',
			'code' => 'sl',
			'wp_locale' => 'sl_SI'
		) ,
		'so_SO' => array(
			'name' => 'Somali',
			'code' => 'so',
			'wp_locale' => 'so_SO'
		) ,
		'azb' => array(
			'name' => 'South Azerbaijani',
			'code' => 'azb',
			'wp_locale' => 'azb'
		) ,
		'es_AR' => array(
			'name' => 'Spanish (Argentina)',
			'code' => 'es-ar',
			'wp_locale' => 'es_AR'
		) ,
		'es_CL' => array(
			'name' => 'Spanish (Chile)',
			'code' => 'es-cl',
			'wp_locale' => 'es_CL'
		) ,
		'es_CO' => array(
			'name' => 'Spanish (Colombia)',
			'code' => 'es-co',
			'wp_locale' => 'es_CO'
		) ,
		'es_MX' => array(
			'name' => 'Spanish (Mexico)',
			'code' => 'es-mx',
			'wp_locale' => 'es_MX'
		) ,
		'es_PE' => array(
			'name' => 'Spanish (Peru)',
			'code' => 'es-pe',
			'wp_locale' => 'es_PE'
		) ,
		'es_PR' => array(
			'name' => 'Spanish (Puerto Rico)',
			'code' => 'es-pr',
			'wp_locale' => 'es_PR'
		) ,
		'es_ES' => array(
			'name' => 'Spanish (Spain)',
			'code' => 'es',
			'wp_locale' => 'es_ES'
		) ,
		'es_VE' => array(
			'name' => 'Spanish (Venezuela)',
			'code' => 'es-ve',
			'wp_locale' => 'es_VE'
		) ,
		'su_ID' => array(
			'name' => 'Sundanese',
			'code' => 'su',
			'wp_locale' => 'su_ID'
		) ,
		'sw' => array(
			'name' => 'Swahili',
			'code' => 'sw',
			'wp_locale' => 'sw'
		) ,
		'sv_SE' => array(
			'name' => 'Swedish',
			'code' => 'sv',
			'wp_locale' => 'sv_SE'
		) ,
		'gsw' => array(
			'name' => 'Swiss German',
			'code' => 'gsw',
			'wp_locale' => 'gsw'
		) ,
		'tl' => array(
			'name' => 'Tagalog',
			'code' => 'tl',
			'wp_locale' => 'tl'
		) ,
		'tg' => array(
			'name' => 'Tajik',
			'code' => 'tg',
			'wp_locale' => 'tg'
		) ,
		'tzm' => array(
			'name' => 'Tamazight (Central Atlas)',
			'code' => 'tzm',
			'wp_locale' => 'tzm'
		) ,
		'ta_IN' => array(
			'name' => 'Tamil',
			'code' => 'ta',
			'wp_locale' => 'ta_IN'
		) ,
		'ta_LK' => array(
			'name' => 'Tamil (Sri Lanka)',
			'code' => 'ta-lk',
			'wp_locale' => 'ta_LK'
		) ,
		'tt_RU' => array(
			'name' => 'Tatar',
			'code' => 'tt',
			'wp_locale' => 'tt_RU'
		) ,
		'te' => array(
			'name' => 'Telugu',
			'code' => 'te',
			'wp_locale' => 'te'
		) ,
		'th' => array(
			'name' => 'Thai',
			'code' => 'th',
			'wp_locale' => 'th'
		) ,
		'bo' => array(
			'name' => 'Tibetan',
			'code' => 'bo',
			'wp_locale' => 'bo'
		) ,
		'tir' => array(
			'name' => 'Tigrinya',
			'code' => 'tir',
			'wp_locale' => 'tir'
		) ,
		'tr_TR' => array(
			'name' => 'Turkish',
			'code' => 'tr',
			'wp_locale' => 'tr_TR'
		) ,
		'tuk' => array(
			'name' => 'Turkmen',
			'code' => 'tuk',
			'wp_locale' => 'tuk'
		) ,
		'ug_CN' => array(
			'name' => 'Uighur',
			'code' => 'ug',
			'wp_locale' => 'ug_CN'
		) ,
		'uk' => array(
			'name' => 'Ukrainian',
			'code' => 'uk',
			'wp_locale' => 'uk'
		) ,
		'ur' => array(
			'name' => 'Urdu',
			'code' => 'ur',
			'wp_locale' => 'ur'
		) ,
		'uz_UZ' => array(
			'name' => 'Uzbek',
			'code' => 'uz',
			'wp_locale' => 'uz_UZ'
		) ,
		'vi' => array(
			'name' => 'Vietnamese',
			'code' => 'vi',
			'wp_locale' => 'vi'
		) ,
		'wa' => array(
			'name' => 'Walloon',
			'code' => 'wa',
			'wp_locale' => 'wa'
		) ,
		'cy' => array(
			'name' => 'Welsh',
			'code' => 'cy',
			'wp_locale' => 'cy'
		) ,
		'or' => array(
			'name' => 'Yoruba',
			'code' => 'yor',
			'wp_locale' => 'yor'
		)
	);
	
	// Country to known locale
	private static $country_to_locale = array(
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
	
	private function __construct() {	
		$this->add_action('cfgp/api/return', 'add_new_api_objects', 10, 1);
		$this->add_action('cfgp/api/render/response', 'add_new_api_objects', 10, 1);
		$this->add_action('cfgp/api/results', 'add_new_api_objects', 10, 1);
		$this->add_action('cfgp/api/default/fields', 'add_new_api_objects', 10, 1);
	}
	
	public function add_new_api_objects ($return) {
		$return['locale'] = self::get_locale();
		return $return;
	}

	/**
	 * Return the language code of the visitor
	 *
	 * @param string $language
	 *
	 * @return string
	 */
	public static function get_locale() {
		$get_locale = CFGP_Cache::get('language_locale_code');
		
		if( !$get_locale ) {
			$wp_locale_conversion = apply_filters( 'cfgp/wp_locale_conversion', self::$wp_locale_conversion );
			
			$country_code = strtolower( CFGP_U::api('country_code') );
			$region_code = strtolower( CFGP_U::api('region_code') );
			
			$get_locale = get_bloginfo('language');
			
			foreach($wp_locale_conversion as $locale => $obj) {
				$search_locale = strtolower($locale);
				if( strpos($search_locale, $country_code) !== false && strpos($search_locale, $region_code) !== false ) {
					$get_locale = $locale;
					break;
				} else if( strpos($search_locale, $country_code) !== false ) {
					$get_locale = $locale;
					break;
				}
			}
		}
		
		return $get_locale;
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