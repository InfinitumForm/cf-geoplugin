<?php
// If someone try to called this file directly via URL, abort.
if (!defined('WPINC')) { die("Don't mess with us."); }
if (!defined('ABSPATH')) { exit; }

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
    // Header names normalized to $_SERVER keys (no visibility for PHP<7.1)
    const H_SEC_CH_UA                   = 'HTTP_SEC_CH_UA';
    const H_SEC_CH_UA_MOBILE            = 'HTTP_SEC_CH_UA_MOBILE';
    const H_SEC_CH_UA_PLATFORM          = 'HTTP_SEC_CH_UA_PLATFORM';
    const H_SEC_CH_UA_PLATFORM_VERSION  = 'HTTP_SEC_CH_UA_PLATFORM_VERSION';
    const H_SEC_CH_UA_ARCH              = 'HTTP_SEC_CH_UA_ARCH';
    const H_SEC_CH_UA_BITNESS           = 'HTTP_SEC_CH_UA_BITNESS';
    const H_SEC_CH_UA_FULL_VERSION_LIST = 'HTTP_SEC_CH_UA_FULL_VERSION_LIST';

    /**
     * Send headers that request UA Client Hints from the browser.
     * Call as early as possible (before any output).
     */
    public static function emitHeaders()
    {
        if (headers_sent()) {
            return;
        }

        header('Accept-CH: Sec-CH-UA, Sec-CH-UA-Mobile, Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version, Sec-CH-UA-Arch, Sec-CH-UA-Bitness, Sec-CH-UA-Full-Version-List');

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
     * @return array
     */
    public static function detect()
    {
        // Always cast UA to string to avoid TypeError in stripos()
        $ua                 = (string) self::server('HTTP_USER_AGENT');
        $platformRaw        = self::stripQuotes(self::server(self::H_SEC_CH_UA_PLATFORM));
        $platformVersionRaw = self::stripQuotes(self::server(self::H_SEC_CH_UA_PLATFORM_VERSION));
        $arch               = self::stripQuotes(self::server(self::H_SEC_CH_UA_ARCH));
        $bitness            = self::stripQuotes(self::server(self::H_SEC_CH_UA_BITNESS));
        $brands             = self::stripQuotes(self::server(self::H_SEC_CH_UA_FULL_VERSION_LIST)) ?: self::stripQuotes(self::server(self::H_SEC_CH_UA));

        // Normalize platform name
        $platform = self::normalizePlatform($platformRaw, $ua);

        // Normalize version (e.g., "15.0.0")
        $platformVersion = self::normalizeVersion($platformVersionRaw);

        // Windows flags by Client Hints
        $isWindows = (strcasecmp((string)$platform, 'Windows') === 0);
        $major     = self::versionMajor($platformVersion);

        $isWin11   = $isWindows && self::isWindows11ByMajor($major);
        $isWin10   = $isWindows && ($major !== null) && ($major < 13);

        // Fallback from UA if hints missing or non-Chromium
        if (!$isWindows && stripos($ua, 'Windows NT') !== false) {
            $isWindows = true;
            if (!$platform) {
                $platform = 'Windows';
            }
            // UA ne razlikuje pouzdano 10 vs 11
        }

        $osName = self::composeOsName($platform, $isWin11, $isWin10, $ua);

        return array(
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
        );
    }

    /**
     * Allow overriding the Windows 11 threshold (default >= 13).
     * @param mixed $major
     * @return bool
     */
    public static function isWindows11ByMajor($major)
    {
        $m = is_numeric($major) ? (int)$major : null;
        if ($m === null) return false;
        return $m >= 13;
    }

    // ----------------- Internals -----------------

    private static function server($key)
    {
        if (!$key) return null;
        return isset($_SERVER[$key]) && is_string($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    private static function stripQuotes($val)
    {
        if ($val === null) return null;
        $val = trim($val);
        if ($val === '') return null;
        if ($val[0] === '"' && substr($val, -1) === '"') {
            return substr($val, 1, -1);
        }
        return $val;
    }

    private static function normalizePlatform($platform, $ua)
    {
        $p = $platform ? trim($platform) : '';
        if ($p !== '') {
            if (strcasecmp($p, 'macOS') === 0 || strcasecmp($p, 'Mac OS') === 0 || stripos($p, 'Mac') !== false) {
                return 'Mac OS';
            }
            if (strcasecmp($p, 'Windows') === 0) return 'Windows';
            if (strcasecmp($p, 'Linux') === 0)   return 'Linux';
            if (strcasecmp($p, 'Android') === 0) return 'Android';
            if (strcasecmp($p, 'iOS') === 0)     return 'iOS';
            return $p;
        }

        $ua = (string)$ua;
        if ($ua) {
            if (stripos($ua, 'Windows') !== false)    return 'Windows';
            if (stripos($ua, 'Android') !== false)    return 'Android';
            if (stripos($ua, 'iPhone') !== false
                || stripos($ua, 'iPad') !== false
                || stripos($ua, 'iOS') !== false)     return 'iOS';
            if (stripos($ua, 'Mac OS') !== false
                || stripos($ua, 'Macintosh') !== false) return 'Mac OS';
            if (stripos($ua, 'Linux') !== false)      return 'Linux';
        }

        return null;
    }

    private static function normalizeVersion($v)
    {
        if (!$v) return null;
        $v = trim($v);

        // Accept "15", "15.0", "15.0.0" and normalize to 3 parts
        if (preg_match('/^\d+(\.\d+){0,2}$/', $v)) {
            $count = substr_count($v, '.');
            return $v . str_repeat('.0', max(0, 2 - $count));
        }

        // Some browsers separate with semicolons/underscores, try to parse first token as major
        $parts = preg_split('/\s*[\.;,_-]\s*/', $v);
        if ($parts && isset($parts[0]) && ctype_digit($parts[0])) {
            $major = (int)$parts[0];
            $minor = (isset($parts[1]) && ctype_digit($parts[1])) ? (int)$parts[1] : 0;
            $patch = (isset($parts[2]) && ctype_digit($parts[2])) ? (int)$parts[2] : 0;
            return $major . '.' . $minor . '.' . $patch;
        }

        return null;
    }

    private static function versionMajor($v)
    {
        if (!$v) return null;
        if (preg_match('/^(\d+)/', $v, $mm)) {
            return (int)$mm[1];
        }
        return null;
    }

    private static function composeOsName($platform, $isWin11, $isWin10, $ua)
    {
        if (!$platform) {
            return 'Unknown';
        }

        $ua = (string)$ua;

        // Normalize to booleans
        $isWin10 = (bool) $isWin10;
        $isWin11 = (bool) $isWin11;

        if ($platform === 'Windows') {
            if ($isWin11) return 'Windows 11';
            if ($isWin10) return 'Windows 10';
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
