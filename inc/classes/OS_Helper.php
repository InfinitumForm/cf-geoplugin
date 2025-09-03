<?php
/**
 * Client Hints helper (Windows 10/11 detection, architecture, bitness, brands)
 *
 * Usage:
 *   // 1) (Early) ask browser to send Client Hints (HTTPS + Chromium)
 *   CFGP_ClientHints::emitHeaders();
 *
 *   // 2) Later in request, read what browser sent (falls back to UA)
 *   $info = CFGP_ClientHints::detect();
 *   
 *     $info = [
 *       'platform'         => 'Windows',
 *       'platformVersion'  => '15.0.0', // Chromium mapping (>=13 => Win 11)
 *       'architecture'     => 'x86',    // or 'arm', null if unknown
 *       'bitness'          => '64',     // '32'/'64' or null
 *       'brands'           => 'Chromium;v="139.0.0.0", ...',
 *       'ua'               => 'Mozilla/5.0 ...',
 *       'osName'           => 'Windows 11', // normalized, or 'Windows 10', 'Linux', 'Mac OS', 'Android', 'iOS', 'Unknown'
 *       'is_windows'       => true,
 *       'is_windows_11'    => true,
 *       'is_windows_10'    => false
 *     ]
 *   
 *
 * Notes:
 *   - Works best in Chromium-based browsers over HTTPS.
 *   - Firefox/Safari may omit some hints; we provide safe fallbacks.
 *   - Windows mapping may evolve; adjust thresholds if needed.
 */
if ( ! class_exists('CFGP_ClientHints', false) ) :

final class CFGP_ClientHints
{
    // Header names normalized to $_SERVER keys
    private const H_SEC_CH_UA                   = 'HTTP_SEC_CH_UA';
    private const H_SEC_CH_UA_MOBILE            = 'HTTP_SEC_CH_UA_MOBILE';
    private const H_SEC_CH_UA_PLATFORM          = 'HTTP_SEC_CH_UA_PLATFORM';
    private const H_SEC_CH_UA_PLATFORM_VERSION  = 'HTTP_SEC_CH_UA_PLATFORM_VERSION';
    private const H_SEC_CH_UA_ARCH              = 'HTTP_SEC_CH_UA_ARCH';
    private const H_SEC_CH_UA_BITNESS           = 'HTTP_SEC_CH_UA_BITNESS';
    private const H_SEC_CH_UA_FULL_VERSION_LIST = 'HTTP_SEC_CH_UA_FULL_VERSION_LIST';

    /**
     * Send headers that request UA Client Hints from the browser.
     * Call as early as possible (before any output).
     */
    public static function emitHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // Ask for high-entropy hints we plan to read.
        header('Accept-CH: Sec-CH-UA, Sec-CH-UA-Mobile, Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version, Sec-CH-UA-Arch, Sec-CH-UA-Bitness, Sec-CH-UA-Full-Version-List');

        // Permissions-Policy opt-in (syntax requires quoted tokens).
        header('Permissions-Policy: ' .
            'ch-ua=("*"), ' .
            'ch-ua-mobile=("*"), ' .
            'ch-ua-platform=("*"), ' .
            'ch-ua-platform-version=("*"), ' .
            'ch-ua-arch=("*"), ' .
            'ch-ua-bitness=("*"), ' .
            'ch-ua-full-version-list=("*")'
        );
    }

    /**
     * Detect platform/OS using Client Hints; fall back to UA when missing.
     *
     * @return array<string, mixed>
     */
    public static function detect(): array
    {
        $ua                 = self::server('HTTP_USER_AGENT');
        $platformRaw        = self::stripQuotes(self::server(self::H_SEC_CH_UA_PLATFORM));
        $platformVersionRaw = self::stripQuotes(self::server(self::H_SEC_CH_UA_PLATFORM_VERSION));
        $arch               = self::stripQuotes(self::server(self::H_SEC_CH_UA_ARCH));
        $bitness            = self::stripQuotes(self::server(self::H_SEC_CH_UA_BITNESS));
        $brands             = self::stripQuotes(self::server(self::H_SEC_CH_UA_FULL_VERSION_LIST)) ?: self::stripQuotes(self::server(self::H_SEC_CH_UA));

        // Normalize platform name
        $platform = self::normalizePlatform($platformRaw, $ua);

        // Normalize version (e.g., "15.0.0") to [major,minor,patch]
        $platformVersion = self::normalizeVersion($platformVersionRaw);

        // Decide Windows edition from platformVersion (Chromium mapping)
        // Observed: Win 10 => "10.0.0"; Win 11 => "13.0.0+" (varies by release).
        $isWindows   = (strcasecmp($platform, 'Windows') === 0);
        $major       = self::versionMajor($platformVersion);
        $isWin11     = $isWindows && $major !== null && $major >= 13; // threshold configurable
        $isWin10     = $isWindows && $major !== null && $major < 13;

        // Fallbacks from UA if hints missing or non-Chromium.
        if (!$isWindows && stripos($ua, 'Windows NT') !== false) {
            $isWindows = true;
            // Distinguish 11 vs 10 is not reliable via UA; default to 10.
            if (!$platform) {
                $platform = 'Windows';
            }
        }

        $osName = self::composeOsName($platform, $isWin11, $isWin10, $ua);

        return [
            'platform'         => $platform ?: 'Unknown',
            'platformVersion'  => $platformVersion,
            'architecture'     => $arch ?: null,
            'bitness'          => $bitness ?: null,
            'brands'           => $brands ?: null,
            'ua'               => $ua ?: null,
            'osName'           => $osName,
            'is_windows'       => $isWindows,
            'is_windows_11'    => $isWin11,
            'is_windows_10'    => $isWin10,
        ];
    }

    /**
     * Allow overriding the Windows 11 threshold (default >= 13).
     * Example: CFGP_ClientHints::isWindows11ByMajor(15) // true
     */
    public static function isWindows11ByMajor(int $major): bool
    {
        // If your telemetry shows different mapping, adjust this constant or wrap with your filter.
        return $major >= 13;
    }

    // ----------------- Internals -----------------

    private static function server(string $key): ?string
    {
        return isset($_SERVER[$key]) && is_string($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    private static function stripQuotes(?string $val): ?string
    {
        if ($val === null) return null;
        $val = trim($val);
        if ($val === '') return null;
        // Many CH values are quoted; remove surrounding quotes if present.
        if ($val[0] === '"' && substr($val, -1) === '"') {
            return substr($val, 1, -1);
        }
        return $val;
    }

    private static function normalizePlatform(?string $platform, ?string $ua): ?string
    {
        $p = $platform ? trim($platform) : '';
        if ($p !== '') {
            // Standardize common names
            if (strcasecmp($p, 'macOS') === 0 || strcasecmp($p, 'Mac OS') === 0 || stripos($p, 'Mac') !== false) {
                return 'Mac OS';
            }
            if (strcasecmp($p, 'Windows') === 0) {
                return 'Windows';
            }
            if (strcasecmp($p, 'Linux') === 0) {
                return 'Linux';
            }
            if (strcasecmp($p, 'Android') === 0) {
                return 'Android';
            }
            if (strcasecmp($p, 'iOS') === 0) {
                return 'iOS';
            }
            return $p; // keep as provided
        }

        // Fallback: infer from UA
        $ua = (string)$ua;
        if ($ua) {
            if (stripos($ua, 'Windows') !== false) return 'Windows';
            if (stripos($ua, 'Android') !== false) return 'Android';
            if (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false || stripos($ua, 'iOS') !== false) return 'iOS';
            if (stripos($ua, 'Mac OS') !== false || stripos($ua, 'Macintosh') !== false) return 'Mac OS';
            if (stripos($ua, 'Linux') !== false) return 'Linux';
        }

        return null;
    }

    private static function normalizeVersion(?string $v): ?string
    {
        if (!$v) return null;
        // Accept forms like 15, "15.0", "15.0.0"; normalize to "15.0.0"
        $parts = preg_split('/\s*[\.;,_-]\s*/', trim($v));
        if (!$parts || !ctype_digit($parts[0])) {
            // Some browsers already provide "15.0.0"; keep it
            if (preg_match('/^\d+(\.\d+){0,2}$/', $v)) {
                // Pad to 3 components
                $count = substr_count($v, '.');
                return $v . str_repeat('.0', max(0, 2 - $count));
            }
            return null;
        }
        $major = (int)$parts[0];
        $minor = isset($parts[1]) && ctype_digit($parts[1]) ? (int)$parts[1] : 0;
        $patch = isset($parts[2]) && ctype_digit($parts[2]) ? (int)$parts[2] : 0;
        return $major . '.' . $minor . '.' . $patch;
    }

    private static function versionMajor(?string $v): ?int
    {
        if (!$v) return null;
        $m = null;
        if (preg_match('/^(\d+)/', $v, $mm)) {
            $m = (int)$mm[1];
        }
        return $m;
    }

    private static function composeOsName(?string $platform, bool $isWin11, bool $isWin10, string $ua): string
    {
		if(!$platform) {
			return 'Unknown';
		}
	
        if ($platform === 'Windows') {
            if ($isWin11) return 'Windows 11';
            if ($isWin10) return 'Windows 10';
            // Unknown Windows variant from UA
            if (stripos($ua, 'Windows NT 6.1') !== false) return 'Windows 7';
            if (stripos($ua, 'Windows NT 6.2') !== false) return 'Windows 8';
            if (stripos($ua, 'Windows NT 6.3') !== false) return 'Windows 8.1';
            return 'Windows';
        }
        if ($platform === 'Mac OS')  return 'Mac OS';
        if ($platform === 'Linux')   return 'Linux';
        if ($platform === 'Android') return 'Android';
        if ($platform === 'iOS')     return 'iOS';
		
        return 'Unknown';
    }
}

endif;