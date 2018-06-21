<?php
/*
 * Returns gelocation info from IP adress
 *
 * @since      4.0.0
 * @package    CF_Geoplugin
 * @subpackage CF_Geoplugin/includes
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 *
 * EXAMPLE:
 * ---------------------------------
 * $gp=new CF_Geoplugin_API();
 * $gpReturn=$gp->returns;
 * echo $gpReturn->location;
 */
function find_parent($array, $needle, $parent = null) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $pass = $parent;
            if (is_string($key)) {
                $pass = $key;
            }
            $found = find_parent($value, $needle, $pass);
            if ($found !== false) {
                return $found;
            }
        } else if ($key === $needle) {
            return $parent;
        }
    }

    return false;
}
 class CF_Geoplugin_API extends CF_GEO_D
{
	/**
	 * CF GeoPlugin url path
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $url
	 */
	protected $url = array();
	protected $region_list = array(
		"001" => "World",
		"002" => "Africa",
		"003" => "North America",
		"005" => "South America",
		"009" => "Oceania",
		"011" => "Western Africa",
		"013" => "Central America",
		"014" => "Eastern Africa",
		"015" => "Northern Africa",
		"017" => "Middle Africa",
		"018" => "Southern Africa",
		"019" => "Americas",
		"021" => "Northern America",
		"029" => "Caribbean",
		"030" => "Eastern Asia",
		"034" => "Southern Asia",
		"035" => "South-Eastern Asia",
		"039" => "Southern Europe",
		"053" => "Australia and New Zealand",
		"054" => "Melanesia",
		"057" => "Micronesian Region",
		"061" => "Polynesia",
		"062" => "South-Central Asia",
		"QO" => "Outlying Oceania",
		"QU" => "European Union",
		"142" => "Asia",
		"143" => "Central Asia",
		"145" => "Western Asia",
		"150" => "Europe",
		"151" => "Eastern Europe",
		"154" => "Northern Europe",
		"155" => "Western Europe",
		"172" => "Commonwealth of Independent States",
		"200" => "Czechoslovakia",
		"419" => "Latin America and the Caribbean",
		"830" => "Channel Islands",
	);
	protected $region_country_list = array(
		"Australia and New Zealand" => array(
			"AU" => "Australia",
			"NZ" => "New Zealand",
			"NF" => "Norfolk Island",
		),
		"Caribbean" => array(
			"AI" => "Anguilla",
			"AG" => "Antigua and Barbuda",
			"AW" => "Aruba",
			"BS" => "Bahamas",
			"BB" => "Barbados",
			"VG" => "British Virgin Islands",
			"KY" => "Cayman Islands",
			"CU" => "Cuba",
			"DM" => "Dominica",
			"DO" => "Dominican Republic",
			"GD" => "Grenada",
			"GP" => "Guadeloupe",
			"HT" => "Haiti",
			"JM" => "Jamaica",
			"MQ" => "Martinique",
			"MS" => "Montserrat",
			"AN" => "Netherlands Antilles",
			"PR" => "Puerto Rico",
			"BL" => "Saint Barthélemy",
			"KN" => "Saint Kitts and Nevis",
			"LC" => "Saint Lucia",
			"MF" => "Saint Martin",
			"VC" => "Saint Vincent and the Grenadines",
			"TT" => "Trinidad and Tobago",
			"TC" => "Turks and Caicos Islands",
			"VI" => "U.S. Virgin Islands",
		),
		"Central America" => array(
			"BZ" => "Belize",
			"CR" => "Costa Rica",
			"SV" => "El Salvador",
			"GT" => "Guatemala",
			"HN" => "Honduras",
			"MX" => "Mexico",
			"NI" => "Nicaragua",
			"PA" => "Panama",
		),
		"Central Asia" => array(
			"KZ" => "Kazakhstan",
			"KG" => "Kyrgyzstan",
			"TJ" => "Tajikistan",
			"TM" => "Turkmenistan",
			"UZ" => "Uzbekistan",
		),
		"Channel Islands" => array(
			"GG" => "Guernsey",
			"JE" => "Jersey",
		),
		"Commonwealth of Independent States" => array(
			"AM" => "Armenia",
			"AZ" => "Azerbaijan",
			"BY" => "Belarus",
			"GE" => "Georgia",
			"KZ" => "Kazakhstan",
			"KG" => "Kyrgyzstan",
			"MD" => "Moldova",
			"RU" => "Russia",
			"TJ" => "Tajikistan",
			"TM" => "Turkmenistan",
			"UA" => "Ukraine",
			"UZ" => "Uzbekistan",
		),
		"Eastern Africa" => array(
			"BI" => "Burundi",
			"KM" => "Comoros",
			"DJ" => "Djibouti",
			"ER" => "Eritrea",
			"ET" => "Ethiopia",
			"KE" => "Kenya",
			"MG" => "Madagascar",
			"MW" => "Malawi",
			"MU" => "Mauritius",
			"YT" => "Mayotte",
			"MZ" => "Mozambique",
			"RW" => "Rwanda",
			"RE" => "Réunion",
			"SC" => "Seychelles",
			"SO" => "Somalia",
			"TZ" => "Tanzania",
			"UG" => "Uganda",
			"ZM" => "Zambia",
			"ZW" => "Zimbabwe",
		),
		"Eastern Asia" => array(
			"CN" => "China",
			"HK" => "Hong Kong SAR China",
			"JP" => "Japan",
			"MO" => "Macau SAR China",
			"MN" => "Mongolia",
			"KP" => "North Korea",
			"KR" => "South Korea",
			"TW" => "Taiwan",
		),
		"Eastern Europe" => array(
			"BY" => "Belarus",
			"BG" => "Bulgaria",
			"CZ" => "Czech Republic",
			"HU" => "Hungary",
			"MD" => "Moldova",
			"PL" => "Poland",
			"RO" => "Romania",
			"RU" => "Russia",
			"SK" => "Slovakia",
			"UA" => "Ukraine",
			"SU" => "Union of Soviet Socialist Republics",
		),
		"Melanesia" => array(
			"FJ" => "Fiji",
			"NC" => "New Caledonia",
			"PG" => "Papua New Guinea",
			"SB" => "Solomon Islands",
			"VU" => "Vanuatu",
		),
		"Micronesian Region" => array(
			"GU" => "Guam",
			"KI" => "Kiribati",
			"MH" => "Marshall Islands",
			"FM" => "Micronesia",
			"NR" => "Nauru",
			"MP" => "Northern Mariana Islands",
			"PW" => "Palau",
		),
		"Middle Africa" => array(
			"AO" => "Angola",
			"CM" => "Cameroon",
			"CF" => "Central African Republic",
			"TD" => "Chad",
			"CG" => "Congo - Brazzaville",
			"CD" => "Congo - Kinshasa",
			"GQ" => "Equatorial Guinea",
			"GA" => "Gabon",
			"ST" => "São Tomé and Príncipe",
		),
		"Northern Africa" => array(
			"DZ" => "Algeria",
			"EG" => "Egypt",
			"LY" => "Libya",
			"MA" => "Morocco",
			"SD" => "Sudan",
			"TN" => "Tunisia",
			"EH" => "Western Sahara",
		),
		"Northern America" => array(
			"BM" => "Bermuda",
			"CA" => "Canada",
			"GL" => "Greenland",
			"PM" => "Saint Pierre and Miquelon",
			"US" => "United States",
		),
		"Northern Europe" => array(
			"DK" => "Denmark",
			"EE" => "Estonia",
			"FO" => "Faroe Islands",
			"FI" => "Finland",
			"GG" => "Guernsey",
			"IS" => "Iceland",
			"IE" => "Ireland",
			"IM" => "Isle of Man",
			"JE" => "Jersey",
			"LV" => "Latvia",
			"LT" => "Lithuania",
			"NO" => "Norway",
			"SJ" => "Svalbard and Jan Mayen",
			"SE" => "Sweden",
			"GB" => "Great Britain",
			"AX" => "Åland Islands",
		),
		"Polynesia" => array(
			"AS" => "American Samoa",
			"CK" => "Cook Islands",
			"PF" => "French Polynesia",
			"NU" => "Niue",
			"PN" => "Pitcairn Islands",
			"WS" => "Samoa",
			"TK" => "Tokelau",
			"TO" => "Tonga",
			"TV" => "Tuvalu",
			"WF" => "Wallis and Futuna",
		),
		"South America" => array(
			"AR" => "Argentina",
			"BO" => "Bolivia",
			"BR" => "Brazil",
			"CL" => "Chile",
			"CO" => "Colombia",
			"EC" => "Ecuador",
			"FK" => "Falkland Islands",
			"GF" => "French Guiana",
			"GY" => "Guyana",
			"PY" => "Paraguay",
			"PE" => "Peru",
			"SR" => "Suriname",
			"UY" => "Uruguay",
			"VE" => "Venezuela",
		),
		"South-Eastern Asia" => array(
			"BN" => "Brunei",
			"KH" => "Cambodia",
			"ID" => "Indonesia",
			"LA" => "Laos",
			"MY" => "Malaysia",
			"MM" => "Myanmar [Burma]",
			"PH" => "Philippines",
			"SG" => "Singapore",
			"TH" => "Thailand",
			"TL" => "Timor-Leste",
			"VN" => "Vietnam",
		),
		"Southern Africa" => array(
			"BW" => "Botswana",
			"LS" => "Lesotho",
			"NA" => "Namibia",
			"ZA" => "South Africa",
			"SZ" => "Swaziland",
		),
		"Southern Asia" => array(
			"AF" => "Afghanistan",
			"BD" => "Bangladesh",
			"BT" => "Bhutan",
			"IN" => "India",
			"IR" => "Iran",
			"MV" => "Maldives",
			"NP" => "Nepal",
			"PK" => "Pakistan",
			"LK" => "Sri Lanka",
		),
		"Southern Europe" => array(
			"AL" => "Albania",
			"AD" => "Andorra",
			"BA" => "Bosnia and Herzegovina",
			"HR" => "Croatia",
			"GI" => "Gibraltar",
			"GR" => "Greece",
			"IT" => "Italy",
			"MK" => "Macedonia",
			"MT" => "Malta",
			"ME" => "Montenegro",
			"PT" => "Portugal",
			"SM" => "San Marino",
			"RS" => "Serbia",
			"CS" => "Serbia and Montenegro",
			"SI" => "Slovenia",
			"ES" => "Spain",
			"VA" => "Vatican City",
		),
		"Western Africa" => array(
			"BJ" => "Benin",
			"BF" => "Burkina Faso",
			"CV" => "Cape Verde",
			"CI" => "Côte d’Ivoire",
			"GM" => "Gambia",
			"GH" => "Ghana",
			"GN" => "Guinea",
			"GW" => "Guinea-Bissau",
			"LR" => "Liberia",
			"ML" => "Mali",
			"MR" => "Mauritania",
			"NE" => "Niger",
			"NG" => "Nigeria",
			"SH" => "Saint Helena",
			"SN" => "Senegal",
			"SL" => "Sierra Leone",
			"TG" => "Togo",
		),
		"Western Asia" => array(
			"AM" => "Armenia",
			"AZ" => "Azerbaijan",
			"BH" => "Bahrain",
			"CY" => "Cyprus",
			"GE" => "Georgia",
			"IQ" => "Iraq",
			"IL" => "Israel",
			"JO" => "Jordan",
			"KW" => "Kuwait",
			"LB" => "Lebanon",
			"NT" => "Neutral Zone",
			"OM" => "Oman",
			"PS" => "Palestinian Territories",
			"YD" => "People's Democratic Republic of Yemen",
			"QA" => "Qatar",
			"SA" => "Saudi Arabia",
			"SY" => "Syria",
			"TR" => "Turkey",
			"AE" => "United Arab Emirates",
			"YE" => "Yemen",
		),
		"Western Europe" => array(
			"AT" => "Austria",
			"BE" => "Belgium",
			"DD" => "East Germany",
			"FR" => "France",
			"DE" => "Germany",
			"LI" => "Liechtenstein",
			"LU" => "Luxembourg",
			"FX" => "Metropolitan France",
			"MC" => "Monaco",
			"NL" => "Netherlands",
			"CH" => "Switzerland",
		),
	);
	
	protected $currency_symbols = array(
		'AED' => '&#1583;.&#1573;', // ?
		'AFN' => '&#65;&#102;',
		'ALL' => '&#76;&#101;&#107;',
		'AMD' => '',
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
		'CLF' => '', // ?
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
		'RSD' => '&#1044;&#1080;&#1085;&#46;',
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
		'TZS' => '',
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
		'XDR' => '',
		'XOF' => '',
		'XPF' => '&#70;',
		'YER' => '&#65020;',
		'ZAR' => '&#82;',
		'ZMK' => '&#90;&#75;', // ?
		'ZWL' => '&#90;&#36;',
	);
	
	/**
	 * Geoplugin default return fields.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      array      $fields
	 */
	protected $fields = array(
        'ipAddress' => '',
        'ipVersion' => '',
        'ipNumber' => '',
        'countryCode' => '',
        'countryName' => '',
        'regionName' => '',
        'regionCode' => '',
        'cityName' => '',
        'continent' => '',
        'continentCode' => '',
        'address' => '',
        'areaCode' => '',
        'dmaCode' => '',
        'latitude' => '',
        'longitude' => '',
        'timezone' => '',
        'currency' => '',
        'currencySymbol' => '',
        'currencyConverter' => '',
        'currency_symbol' => '',
        'currency_converter' => '',
        'referer' => '',
        'refererIP' => '',
        'timestamp' => '',
        'timestampReadable' => '',
        'currentTime' => '',
        'currentDate' => '',
        'current_time' => '',
        'current_date' => '',
        'error' => '',
        'message' => '',
        'runtime' => '',
        'credit' => '',
        'status' => '',
        'version' => CFGP_VERSION,
        'lookup' => 0,
		'available_lookup' => 0
    );
	
	/**
	 * Return all data
	 *
	 * @since    4.0.0
	 * @access   public
	 * @var      array      $returns
	 */
	public $returns = array();
	
	function __construct($options=array())
	{
		/*$this->url = array(
			'https://cdn-cfgeoplugin.com/api/index.php?ip={IP}&sip={SIP}&t={TIME}&r={HOST}&v={VERSION}&m={M}&p={P}',
			'http://159.203.47.151/api/index.php?ip={IP}&sip={SIP}&t={TIME}&r={HOST}&v={VERSION}&m={M}&p={P}',
			'https://cdn-cfgeoplugin.com/api/dns.php?ip={IP}&sip={SIP}&r={HOST}&v={VERSION}&p={P}',
		);*/
		
		$this->url = array(
			'https://cdn-cfgeoplugin.com/api6.0/index.php?ip={IP}&sip={SIP}&t={TIME}&r={HOST}&v={VERSION}&m={M}&p={P}',
			'http://159.203.47.151/api6.0/index.php?ip={IP}&sip={SIP}&t={TIME}&r={HOST}&v={VERSION}&m={M}&p={P}',
			
			'https://cdn-cfgeoplugin.com/api6.0/dns.php?ip={IP}&sip={SIP}&r={HOST}&v={VERSION}&p={P}',
			'http://159.203.47.151/api6.0/dns.php?ip={IP}&sip={SIP}&r={HOST}&v={VERSION}&p={P}',
			
			'https://cdn-cfgeoplugin.com/api6.0/activate.php?action=license_key_validate&store_code={SC}&sku={SKU}&license_key={LC}&domain={D}&activation_id={AI}',
			'http://159.203.47.151/api6.0/activate.php?action=license_key_validate&store_code={SC}&sku={SKU}&license_key={LC}&domain={D}&activation_id={AI}',
		);
		
		$this->validate_license();
		
		$option=array(
			'ip'	=>	false
		);
		// replace default options
		foreach($options as $key=>$value)
		{
			if(!empty($key))
			{
				unset($option[$key]);
				$option[$key]=$value;
			}
		}
		$this->option=$option;
		$this->returns=$this->__returns();
	}
	
	protected function array_find_deep($array, $search, $keys = array())
	{
		foreach($array as $key => $value) {
			if (is_array($value)) {
				$sub = $this->array_find_deep($value, $search, array_merge($keys, array($key)));
				if (count($sub)) {
					return $sub;
				}
			} elseif ($value === $search) {
				return array_merge($keys, array($key));
			}
		}

		return array();
	}
	
	protected function get_currency_countries($find) {
        $a = array(
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
            'BAM' => array( 'BA' ),
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
            'ZWD' => array( 'ZW' ),
        );
		$res = $this->array_find_deep($a, $find);
		if(isset($res[0]))
			return $res[0];
		else
			return '';
    }
	
	
	private function __returns()
	{
		$result=$this->__get_data($this->option['ip']);
		if($result!==false)
		{
			$provider=$this->get_provider_info($result->ipAddress);
			
			$lng = $result->longitude;
			$lat = $result->latitude;
			
			$countryCode = $result->countryCode;
			
			$on=get_option("cf_geo_enable_cloudflare");
			if (isset($_SERVER["HTTP_CF_IPCOUNTRY"]) && $on == 'true') {
				$countryCode = $_SERVER["HTTP_CF_IPCOUNTRY"];
			}
			
			$url=CF_GEO_D::URL();
			$url=strtolower($url->url);
			
			$encrypt = new CF_Geoplugin_Defender;
			$defender = $encrypt->enable;
			
			if($defender===true || is_admin())
			{
				$currency = $this->get_currency_countries($countryCode);
				$ipv = $result->ipVersion;
				
				if(isset($this->currency_symbols[$currency]))
					$currency_symbol = $this->currency_symbols[$currency];
				else
					$currency_symbol = $result->currencySymbol;
			}
			else
			{
				$currency = '';
				$currency_symbol = '';
				$ipv = '';
			}
			
			$continent = empty($result->continent) ? find_parent($this->region_country_list, $countryCode) : $result->continent;
			$continentCode =  $result->continentCode;
			if(empty($continentCode)){
				$continentCodeArr = array_flip($this->region_list);
				$continentCode = isset($continentCodeArr[$continent]) ? $continentCodeArr[$continent] : '';
			}
			
			$return = array(
                'ip' => $result->ipAddress,
                'ip_version' => $ipv,
                'ip_dns' => $provider->dns,
                'ip_dns_host' => $provider->host,
                'ip_dns_provider' => $provider->provider,
                'ip_number' => $result->ipNumber,
                'country_code' => $countryCode,
                'country' => $result->countryName,
                'region' => $result->regionName, //regionCode
                'region_code' => $result->regionCode,
                'state' => $result->regionName, // deprecated
                'city' => $result->cityName,
                'continent' => $continent,
                'continent_code' => $continentCode,
                'continentCode' => $continentCode, // deprecated
                'address' => $result->address,
                'area_code' => $result->areaCode,
                'areaCode' => $result->areaCode, // deprecated
                'dma_code' => $result->dmaCode,
                'dmaCode' => $result->dmaCode, // deprecated
                'latitude' => $lat,
                'longitude' => $lng,
                'timezone' => $result->timezone,
                'timezoneName' => $result->timezone, // deprecated
                'currency' => $currency,
                'currency_symbol' => $currency_symbol,
                'currencySymbol' => $currency_symbol, // deprecated
                'currency_converter' => $result->currencyConverter,
                'currencyConverter' => $result->currencyConverter, // deprecated
                'host' => $result->referer,
                'ip_host' => $result->refererIP,
                'timestamp' => $result->timestamp,
                'timestamp_readable' => $result->timestampReadable,
                'current_time' => $result->currentTime,
                'current_date' => $result->currentDate,
                'error' => $result->error,
                'error_message' => $result->message,
                'runtime' => abs($result->runtime),
                'credit' => $result->credit,
                'status' => empty($result->status) ? (!empty($result->ipAddress) ? 200 : 500) : $result->status,
                'version' => CFGP_VERSION,
                'lookup' => $result->available_lookup
            );
			return $return;
		}
		else return array();
	}
	/* GET HOST */
	protected function get_provider_info($ip=false)
	{
		$this->check_validations();
		$return = array(
			"ip"		=>	$ip,
			"provider"	=>	'',
			"host"		=>	'',
			"dns"		=>	'',
			"error"		=>	true
		);
		
		$on=get_option("cf_geo_enable_dns_lookup");
		$api = get_option('cf_geo_defender_api_key');
		
		if( $on == 'true')
		{
			if(isset($_SESSION[CFGP_PREFIX . 'api_provider_session']) && isset($_SESSION[CFGP_PREFIX . 'api_provider_session']['ip']) && $_SESSION[CFGP_PREFIX . 'api_provider_session']['ip'] == $ip){
				return (object) $_SESSION[CFGP_PREFIX . 'api_provider_session'];
			}
			$urlReplace=array_map("rawurlencode",array($ip, CFGP_SERVER_IP,$this->get_host(),CFGP_VERSION,$api));
			$url = str_replace(array('{IP}','{SIP}','{HOST}','{VERSION}','{P}'), $urlReplace, $this->url[2] );
			
			$data = $this->api_fetch_url($url);
			
			if($data !== false){
				$data=json_decode($data, true);
				$return = array_merge($return, $data);
			} else {
				$url = str_replace(array('{IP}','{SIP}','{HOST}','{VERSION}','{P}'), $urlReplace, $this->url[3] );
				$data = $this->api_fetch_url($url);
				if($data !== false){
					$data=json_decode($data, true);
					$return = array_merge($return, $data);
				}
			}
			
			$_SESSION[CFGP_PREFIX . 'api_provider_session'] = $return;
		}
		return (object) $return;
	}
	
	private function get_host(){
		$homeURL = get_home_url();
		$hostInfo = parse_url($homeURL);
		return strtolower($hostInfo['host']);
	}
	
	## Get JSON from URL using IP ##
	protected function __get_data($ip=false)
	{
		$this->check_validations();
		// Current or custom IP
		$ip = ($ip!==false?$ip:CFGP_IP);
		
		if(isset($_SESSION[CFGP_PREFIX . 'api_session']) && isset($_SESSION[CFGP_PREFIX . 'api_session']['ipAddress']) && $_SESSION[CFGP_PREFIX . 'api_session']['ipAddress'] == $ip){
			return (object) $_SESSION[CFGP_PREFIX . 'api_session'];
		}
		
		$api = get_option('cf_geo_defender_api_key');
		if(!in_array($ip,$this->BLACKLIST_IP))
		{
			$result = $this->fields;
			// Configure GET function
			$urlReplace=array_map("rawurlencode",array($ip, CFGP_SERVER_IP,time(),$this->get_host(),CFGP_VERSION,get_bloginfo("admin_email"),$api));
			$url = str_replace(array('{IP}','{SIP}','{TIME}','{HOST}','{VERSION}','{M}','{P}'), $urlReplace, $this->url[0] );
			
			// Get content from URL
			$return=$this->api_fetch_url($url);
			// Return objects from JSON data
			if($return!=false)
			{
				$return=json_decode($return, true);
				
				if(is_array($return))
					$result = array_merge($result, $return);
				
					$_SESSION[CFGP_PREFIX . 'api_session'] = $result;
				
				return (object) $result;
			}
			else
			{
				$url = str_replace(array('{IP}','{SIP}','{TIME}','{HOST}','{VERSION}','{M}','{P}'), $urlReplace, $this->url[1] );
				// Get content from URL
				$return=$this->api_fetch_url($url);
				// Return objects from JSON data
				if($return!=false)
				{
					$return=json_decode($return, true);
					
					if(is_array($return))
						$result = array_merge($result, $return);
					
						$_SESSION[CFGP_PREFIX . 'api_session'] = $result;
					
					return (object) $result;
				}
				else return false;
			};
		}
		return false;
	}
	## Fetch URL ##
	protected function api_fetch_url($url){
		if(function_exists("curl_init"))
		{
			$cURL = curl_init();
				curl_setopt($cURL, CURLOPT_URL, $url);
				
				if(CFGP_PROXY){
					
					curl_setopt($cURL, CURLOPT_PROXY, get_option("cf_geo_enable_proxy_ip"));
					curl_setopt($cURL, CURLOPT_PROXYPORT, get_option("cf_geo_enable_proxy_port"));
					
					$username=get_option("cf_geo_enable_proxy_username");
					$password=get_option("cf_geo_enable_proxy_password");
					if(!empty($username)){
						curl_setopt($cURL, CURLOPT_PROXYUSERPWD, $username.":".$password);
					}
				}
				
				curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT , (int)get_option("cf_geo_connection_timeout")); 
				curl_setopt($cURL, CURLOPT_TIMEOUT , (int)get_option("cf_geo_timeout"));
				curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, (
					(is_bool(get_option("cf_geo_enable_ssl")) ? get_option("cf_geo_enable_ssl")!==false : get_option("cf_geo_enable_ssl") == 'true')
					&& cf_geo_is_ssl() ? true : false)
				);
				curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array('Accept: application/json'));
				$result = curl_exec($cURL);
			curl_close($cURL);
		}
		else
		{
			$result = file_get_contents($url);
		}
		return $result;
	}

	protected function validate_license()
	{
		if(isset($_GET['page']) && isset($_GET['action']) && $_GET['page'] == 'cf-geoplugin' && $_GET['action'] == 'activate_license'):
			$D = new CF_GEO_D;
			$url = $D->URL();
			$urlReplace = array(
				get_option('cf_geo_store_code'),
				get_option('cf_geo_license_sku'),
				get_option('cf_geo_license_key'),
				$url->hostname,
				get_option('cf_geo_license_id'),
			);
			$url = str_replace(array('{SC}','{SKU}','{LC}','{D}','{AI}'), $urlReplace, $this->url[4] );
			$data = $this->api_fetch_url($url);

			if($data === false){
				$url = str_replace(array('{SC}','{SKU}','{LC}','{D}','{AI}'), $urlReplace, $this->url[5] );
				$data = $this->api_fetch_url($url);
				if($data !== false){
					$data=json_decode($data, true);
					if(isset($data->error) && $data->error)
					{
						update_option('cf_geo_license_id', '', true);
						update_option('cf_geo_license_expire', '', true);
						update_option('cf_geo_license_expire_date', '', true);
						update_option('cf_geo_license_url', '', true);
						update_option('cf_geo_license_expired', '', true);
						update_option('cf_geo_license_status', '', true);
						update_option('cf_geo_license_sku', '', true);
						update_option('cf_geo_license', 0, true);
					}
				}
			}
			else
			{
				$data=json_decode($data, true);
				if(isset($data->error) && $data->error)
				{
					update_option('cf_geo_license_id', '', true);
					update_option('cf_geo_license_expire', '', true);
					update_option('cf_geo_license_expire_date', '', true);
					update_option('cf_geo_license_url', '', true);
					update_option('cf_geo_license_expired', '', true);
					update_option('cf_geo_license_status', '', true);
					update_option('cf_geo_license_sku', '', true);
					update_option('cf_geo_license', 0, true);
				}
			}
		endif;
	}	
	
	private function check_validations(){
		if(CFGP_ACTIVATED)
		{
			$expire = (int) get_option('cf_geo_license_expire');
			if(time() > $expire)
			{
				update_option('cf_geo_license_id', '', true);
				update_option('cf_geo_license_expire', '', true);
				update_option('cf_geo_license_expire_date', '', true);
				update_option('cf_geo_license_url', '', true);
				update_option('cf_geo_license_expired', '', true);
				update_option('cf_geo_license_status', '', true);
				update_option('cf_geo_license_sku', '', true);
				update_option('cf_geo_license', 0, true);
			}
		}
	}
}