<?php

// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CFGP_Bots', false)): class CFGP_Bots
{
	/**
	 * Check if current request is from a bot / crawler / search engine.
	 *
	 * Strategy (fast → safe):
	 *  1) Cache short-circuit (IP+UA).
	 *  2) Quick UA signature match (broad but careful).
	 *  3) Fast IP match against known ranges (supports single IP, start-end, CIDR).
	 *  4) Optional rDNS verification for major engines (off by default; enable via filter).
	 *
	 * Notes:
	 *  - No cURL. Uses WP HTTP guidelines and core PHP DNS funcs only if verification is enabled.
	 *  - Keeps legacy filter 'cfgp/crawler/ip/range' and adds 'cfgp/crawler/ip/cidrs'.
	 *  - CFGP_Cache is request-local and ignores TTL; the TTL applies only to the transient fallback.
	 *  - Enabled DNS verification fails closed, so lookup failures also disable SEO crawler exemptions.
	 */
	public static function validate($ip = false)
	{
		// ---------------- 0) Resolve IP & UA ----------------
		if (empty($ip)) {
			$ip = method_exists('CFGP_IP', 'get') ? \CFGP_IP::get() : ($_SERVER['REMOTE_ADDR'] ?? '');
		}
		$ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

		// Pre hook: allow hard overrides (e.g., admin exemptions)
		$pre = apply_filters('cfgp/is_bot/pre', null, $ip, $ua);
		if (is_bool($pre)) {
			return $pre;
		}

		// ---------------- Cache short-circuit ----------------
		$cache_ttl  = (int) apply_filters('cfgp/is_bot/cache_ttl', 300); // 5 min for transient fallback.
		$cache_key  = 'cfgp_is_bot_' . md5($ip . '|' . $ua);
		$use_cfgp_cache = class_exists('CFGP_Cache') && method_exists('CFGP_Cache', 'get');
		$cache_get      = $use_cfgp_cache ? \CFGP_Cache::get($cache_key) : get_transient($cache_key);
		$cache_miss     = $use_cfgp_cache ? null === $cache_get : false === $cache_get;
		if (!$cache_miss) {
			return (bool) $cache_get;
		}

		// ---------------- 1) User-Agent signatures (fast path) ----------------
		// Keep list precise to avoid false positives; general tokens use word boundaries.
		$ua_signatures = apply_filters('cfgp/crawler/ua/signatures', [
			// Major search engines
			'googlebot', 'google-inspectiontool', 'googleother', 'google-agent', 'adsbot-google', 'mediapartners-google',
			'apis-google', 'storebot-google', 'bingbot', 'bingpreview', 'msnbot', 'slurp', 'yandex(bot|images|mobile)?',
			'baiduspider', 'duckduckbot', 'sogou', 'exabot', 'seznambot', 'qwantbot', 'petalbot', 'applebot',
			// AI crawlers
			'gptbot', 'chatgpt-user', 'oai-searchbot', 'claudebot', 'claude-web', 'anthropic-ai',
			'perplexitybot', 'perplexity-user', 'cohere-ai', 'google-extended', 'bytespider', 'ccbot',
			'meta-externalagent', 'meta-externalfetch', 'meta-externalfetcher', 'amazonbot',
			// Social/link preview
			'facebookexternalhit', 'facebot', 'twitterbot', 'linkedinbot', 'pinterestbot', 'skypeuripreview',
			'slackbot', 'discordbot', 'telegrambot', 'whatsapp', 'linebot', 'nuzzel', 'embedly', 'quora link preview',
			// SEO/crawlers
			'ahrefsbot', 'semrushbot', 'mj12bot', 'dotbot', 'rogerbot', 'linkdexbot', 'siteimprove',
			'screaming frog', 'chrome-lighthouse', 'lighthouse',
			// Validators/tools
			'w3c_validator', 'validator\.w3\.org', 'ia_archiver', 'httpclient', 'okhttp', 'go-http-client',
			'python-requests', 'libwww-perl', 'node-fetch',
			// Generic endings (safe with word boundaries)
			'\bbot\b', '\bcrawl(er)?\b', '\bspider\b'
		]);

		$ua_is_bot = false;
		if ($ua !== '') {
			$pattern = '/' . implode('|', $ua_signatures) . '/i';
			if (preg_match($pattern, $ua)) {
				$ua_is_bot = true;
			}
		}

		// Optional reverse DNS verification for major engines (to avoid spoofing).
		$verify_rdns = (bool) apply_filters('cfgp/crawler/verify_rdns', false);
		if ($ua_is_bot && $verify_rdns && self::should_verify_by_ua($ua)) {
			if (!self::verify_bot_rdns($ip, $ua)) {
				self::set_is_bot_cache($cache_key, false, $cache_ttl);
				return false;
			}
		}

		if ($ua_is_bot) {
			self::set_is_bot_cache($cache_key, true, $cache_ttl);
			return true;
		}

		// ---------------- 2) IP ranges (fast numeric compare) ----------------
		// Legacy start-end ranges (array 'start' => 'end' OR single IP values).
		$legacy_ranges = (array) apply_filters('cfgp/crawler/ip/range', []);
		if ($ip && self::ip_matches_legacy_ranges($ip, $legacy_ranges)) {
			self::set_is_bot_cache($cache_key, true, $cache_ttl);
			return true;
		}

		// CIDR ranges (preferable & maintainable)
		$cidrs = (array) apply_filters('cfgp/crawler/ip/cidrs', [
			// Examples (expand via filter in production):
			// Search crawler ranges (partial/public examples)
			'66.249.64.0/19', '40.77.167.0/24',
			// Yandex (partial)
			'5.255.253.0/24', '77.88.0.0/18',
			// Facebook (link preview) (partial)
			'31.13.24.0/21', '31.13.64.0/18', '69.171.224.0/19',
		]);

		if ($ip && self::ip_in_any_cidr($ip, $cidrs)) {
			self::set_is_bot_cache($cache_key, true, $cache_ttl);
			return true;
		}

		// ---------------- Result ----------------
		self::set_is_bot_cache($cache_key, false, $cache_ttl);
		return false;
	}

	/** Cache setter helper */
	private static function set_is_bot_cache($key, $value, $ttl)
	{
		if (class_exists('CFGP_Cache') && method_exists('CFGP_Cache', 'set')) {
			\CFGP_Cache::set($key, (bool) $value, (int) $ttl);
		} else {
			set_transient($key, (int) (bool) $value, (int) $ttl);
		}
	}

	/** Decide if this UA should be rDNS-verified (major engines only) */
	private static function should_verify_by_ua(string $ua): bool
	{
		$verify_tokens = [
			'googlebot', 'google-inspectiontool', 'googleother', 'google-agent',
			'bingbot',
			'slurp',           // Yahoo
			'yandex',          // Yandex*
			'baiduspider',
			'duckduckbot',
			'applebot',
		];
		return (bool) preg_match('/' . implode('|', array_map('preg_quote', $verify_tokens)) . '/i', $ua);
	}

	/**
	 * Verify bot via rDNS (PTR) + forward DNS:
	 *  1) PTR for IP must end with an allowed domain for the engine.
	 *  2) Forward A/AAAA for PTR must include original IP.
	 * Expensive → opt-in via filter 'cfgp/crawler/verify_rdns'.
	 */
	private static function verify_bot_rdns(string $ip, string $ua): bool
	{
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			return false;
		}

		// Map UA → allowed PTR suffixes
		$map = [
			'googlebot'            => ['.googlebot.com', '.google.com'],
			'google-inspectiontool'=> ['.google.com'],
			'googleother'          => ['.google.com'],
			'google-agent'         => ['.google.com'],
			'bingbot'              => ['.search.msn.com'],
			'slurp'                => ['.crawl.yahoo.net'],
			'yandex'               => ['.yandex.ru', '.yandex.net'],
			'baiduspider'          => ['.baidu.com', '.baidu.jp'],
			'duckduckbot'          => ['.duckduckgo.com'],
			'applebot'             => ['.applebot.apple.com'],
		];

		$key = null;
		foreach ($map as $needle => $domains) {
			if (stripos($ua, $needle) !== false) {
				$key = $needle;
				break;
			}
		}
		if (!$key) {
			return false;
		}

		// PTR lookup
		$ptr = @gethostbyaddr($ip);
		if (!$ptr || $ptr === $ip) {
			return false;
		}
		$ptr = strtolower(rtrim($ptr, '.'));

		// PTR suffix check
		$ok_suffix = false;
		foreach ($map[$key] as $suffix) {
			if (substr($ptr, -strlen($suffix)) === $suffix) {
				$ok_suffix = true; break;
			}
		}
		if (!$ok_suffix) {
			return false;
		}

		// Forward-verify A/AAAA contains original IP
		$forward_ok = false;

		// IPv4 (A)
		$alist = @gethostbynamel($ptr);
		if (is_array($alist) && in_array($ip, $alist, true)) {
			$forward_ok = true;
		}

		// IPv6 (AAAA)
		if (!$forward_ok && function_exists('dns_get_record')) {
			$aaaa = @dns_get_record($ptr, DNS_AAAA);
			if (is_array($aaaa)) {
				foreach ($aaaa as $rec) {
					if (!empty($rec['ipv6']) && self::ip_equal($rec['ipv6'], $ip)) {
						$forward_ok = true; break;
					}
				}
			}
		}

		return $forward_ok;
	}

	/** Check if IP matches any legacy start-end ranges or single IPs */
	private static function ip_matches_legacy_ranges(string $ip, array $ranges): bool
	{
		if (!$ranges) {
			return false;
		}

		// Try IPv6 first
		if (self::is_ipv6($ip)) {
			$bin_ip = @inet_pton($ip);
			if (!$bin_ip) {
				return false;
			}

			foreach ($ranges as $start => $end) {
				// Allow value-only items to be single IPs
				if (is_int($start)) {
					$single = (string) $end;
					if (self::ip_equal($ip, $single)) {
						return true;
					}
					continue;
				}
				$start_bin = @inet_pton((string) $start);
				$end_bin   = @inet_pton((string) $end);
				if ($start_bin && $end_bin && self::bin_between($bin_ip, $start_bin, $end_bin)) {
					return true;
				}
			}
			return false;
		}

		// IPv4 fast path (unsigned comparisons)
		$ip_l = ip2long($ip);
		if ($ip_l === false) {
			return false;
		}
		$ip_l = sprintf('%u', $ip_l);

		foreach ($ranges as $start => $end) {
			if (is_int($start)) {
				// Single IP item
				$single_l = ip2long((string) $end);
				if ($single_l !== false && sprintf('%u', $single_l) === $ip_l) {
					return true;
				}
				continue;
			}

			$start_l = ip2long((string) $start);
			$end_l   = ip2long((string) $end);
			if ($start_l === false || $end_l === false) {
				continue;
			}
			$start_l = sprintf('%u', $start_l);
			$end_l   = sprintf('%u', $end_l);

			if ($ip_l >= $start_l && $ip_l <= $end_l) {
				return true;
			}
		}
		return false;
	}

	/** Check if IP matches any CIDR range */
	private static function ip_in_any_cidr(string $ip, array $cidrs): bool
	{
		foreach ($cidrs as $cidr) {
			if (self::cidr_match($ip, (string) $cidr)) {
				return true;
			}
		}
		return false;
	}

	/** CIDR match for IPv4/IPv6 */
	private static function cidr_match(string $ip, string $cidr): bool
	{
		$ip   = trim($ip);
		$cidr = trim($cidr);

		if (!filter_var($ip, FILTER_VALIDATE_IP) || $cidr === '') {
			return false;
		}

		if (strpos($cidr, '/') === false) {
			// Treat plain IP as /32 or /128
			$cidr .= self::is_ipv6($cidr) ? '/128' : '/32';
		} elseif (substr_count($cidr, '/') !== 1) {
			return false;
		}
		list($subnet, $mask) = array_map('trim', explode('/', $cidr, 2));
		if (!filter_var($subnet, FILTER_VALIDATE_IP) || !preg_match('/^\d+$/D', $mask)) {
			return false;
		}
		$mask = (int) $mask;

		$ip_bin     = @inet_pton($ip);
		$subnet_bin = @inet_pton($subnet);
		if ($ip_bin === false || $subnet_bin === false || strlen($ip_bin) !== strlen($subnet_bin)) {
			return false;
		}

		$max_mask = 8 * strlen($ip_bin);
		if ($mask < 0 || $mask > $max_mask) {
			return false;
		}

		$bytes = intdiv($mask, 8);
		$bits  = $mask % 8;

		if ($bytes > 0 && substr($ip_bin, 0, $bytes) !== substr($subnet_bin, 0, $bytes)) {
			return false;
		}

		if ($bits === 0) {
			return true;
		}

		$mask_byte = chr((~(0xff >> $bits)) & 0xff);
		return ($ip_bin[$bytes] & $mask_byte) === ($subnet_bin[$bytes] & $mask_byte);
	}

	/** Binary range compare (IPv6/IPv4 generic) */
	private static function bin_between(string $val, string $start, string $end): bool
	{
		return strlen($val) === strlen($start)
			&& strlen($val) === strlen($end)
			&& $val >= $start
			&& $val <= $end;
	}

	/** Helpers */
	private static function is_ipv6(string $ip): bool
	{
		return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}

	private static function ip_equal(string $a, string $b): bool
	{
		$pa = @inet_pton($a);
		$pb = @inet_pton($b);
		return ($pa !== false && $pb !== false && $pa === $pb);
	}

} endif;
