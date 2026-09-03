<?php
/**
 * Operating system detection utilities.
 *
 * A call without an argument detects the server OS. A call with an argument,
 * including an empty string, detects only the client OS represented by that UA.
 */
if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!class_exists('CFGP_OS', false)) :

final class CFGP_OS
{
    public static function user_agent(): string
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && is_string($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] !== '') {
            return $_SERVER['HTTP_USER_AGENT'];
        }

        global $HTTP_USER_AGENT, $HTTP_SERVER_VARS;

        if (!empty($HTTP_USER_AGENT)) {
            return (string) $HTTP_USER_AGENT;
        }
        if (!empty($HTTP_SERVER_VARS['HTTP_USER_AGENT'])) {
            return (string) $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
        }

        return __('undefined', 'cf-geoplugin');
    }

    public static function is_win(): bool
    {
        if (defined('PHP_OS_FAMILY')) {
            return PHP_OS_FAMILY === 'Windows';
        }

        return (defined('PHP_SHLIB_SUFFIX') && strtolower(PHP_SHLIB_SUFFIX) === 'dll')
            || (defined('DIRECTORY_SEPARATOR') && DIRECTORY_SEPARATOR === '\\')
            || strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public static function is_php64(): bool
    {
        return defined('PHP_INT_SIZE') && PHP_INT_SIZE === 8;
    }

    /**
     * Return true only when the OS architecture is positively identified as 64-bit.
     * A 32-bit PHP process does not by itself prove that the operating system is 32-bit.
     */
    public static function is_os64(): bool
    {
        if (self::is_php64()) {
            return true;
        }

        $architecture = '';
        if (self::is_win()) {
            $architecture = self::environment('PROCESSOR_ARCHITEW6432');
            if ($architecture === '') {
                $architecture = self::environment('PROCESSOR_ARCHITECTURE');
            }
        } elseif (function_exists('php_uname')) {
            $value = @php_uname('m');
            $architecture = is_string($value) ? $value : '';
        }

        return self::is_64bit_architecture($architecture);
    }

    public static function architecture(): int
    {
        return self::is_os64() ? 64 : 32;
    }

    /**
     * Detect the server OS with no argument, or the client OS with an explicit UA.
     *
     * @param string|null $user_agent
     * @return string
     */
    public static function get($user_agent = null): string
    {
        if (func_num_args() === 0) {
            return self::detect_server_os_name();
        }

        return self::detect_client_os((string) $user_agent);
    }

    /**
     * @param string $ua
     * @return string
     */
    private static function detect_client_os($ua): string
    {
        if ($ua === '') {
            return self::unknown();
        }

        $hint_os = self::current_request_hint_os($ua);
        if ($hint_os !== null) {
            return $hint_os;
        }

        // High-confidence device and mobile systems must precede desktop engines.
        if (self::contains($ua, 'Windows Phone')) {
            return 'Windows Phone';
        }
        if (self::contains($ua, 'iPhone') && !self::contains($ua, 'iPod')) {
            return 'iPhone';
        }
        if (self::contains($ua, 'iPad')) {
            return 'iPad';
        }
        if (self::contains($ua, 'iPod')) {
            return 'iPod';
        }
        if (self::contains($ua, 'HarmonyOS') || self::contains($ua, 'Hongmeng')) {
            return 'HarmonyOS';
        }
        if (preg_match('/\b(?:Silk\/|KF[A-Z0-9]+|Kindle Fire)\b/i', $ua)) {
            return 'Fire OS';
        }
        if (self::contains($ua, 'Android')) {
            return 'Android';
        }
        if (self::contains($ua, 'CrOS')) {
            return 'Chrome OS';
        }
        if (self::contains($ua, 'KaiOS')) {
            return 'KaiOS';
        }
        if (self::contains($ua, 'Tizen')) {
            return 'Tizen';
        }
        if (self::contains($ua, 'LGWebOSTV') || self::contains($ua, 'webOS')) {
            return 'WebOS';
        }
        if (preg_match('/(?:SmartTV|Smart-TV|HbbTV|NetCast)/i', $ua)) {
            return 'Smart TV';
        }
        if (self::contains($ua, 'BlackBerry')) {
            return 'BlackBerry';
        }
        if (self::contains($ua, 'Ubuntu Touch')) {
            return 'Ubuntu Touch';
        }

        $windows = self::detect_windows_client($ua);
        if ($windows !== null) {
            return $windows;
        }

        if (self::contains($ua, 'Mac OS X') || self::contains($ua, 'Macintosh')) {
            return 'Mac OS X';
        }
        if (self::contains($ua, 'Mac OS') || self::contains($ua, 'Mac_')) {
            return 'Mac OS';
        }

        $distribution = self::detect_linux_distribution($ua);
        if ($distribution !== null) {
            return $distribution;
        }
        if (self::contains($ua, 'Linux')) {
            return 'Linux';
        }
        if (self::contains($ua, 'FreeBSD')) {
            return 'FreeBSD';
        }
        if (self::contains($ua, 'OpenBSD')) {
            return 'OpenBSD';
        }
        if (self::contains($ua, 'NetBSD')) {
            return 'NetBSD';
        }
        if (self::contains($ua, 'SunOS') || self::contains($ua, 'Solaris')) {
            return 'Solaris';
        }
        if (self::contains($ua, 'Unix')) {
            return 'Unix';
        }

        return self::detect_filtered_legacy_client_os($ua);
    }

    /**
     * Use normalized Client Hints only when the supplied UA is the current request UA.
     * This prevents an order's saved UA from inheriting an administrator's hints.
     *
     * @param string $ua
     * @return string|null
     */
    private static function current_request_hint_os($ua)
    {
        if (!class_exists('CFGP_ClientHints') || !isset($_SERVER['HTTP_USER_AGENT']) || !is_string($_SERVER['HTTP_USER_AGENT']) || $ua !== $_SERVER['HTTP_USER_AGENT']) {
            return null;
        }

        if (empty($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) || !is_string($_SERVER['HTTP_SEC_CH_UA_PLATFORM'])) {
            return null;
        }

        $hints = CFGP_ClientHints::detect();
        if (!is_array($hints) || empty($hints['osName']) || $hints['osName'] === 'Unknown') {
            return null;
        }

        if ($hints['platform'] === 'Windows' && empty($_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'])) {
            return null;
        }

        return (string) $hints['osName'];
    }

    /**
     * @param string $ua
     * @return string|null
     */
    private static function detect_windows_client($ua)
    {
        $map = [
            '/Windows 11/i' => 'Windows 11',
            '/Windows 10/i' => 'Windows 10',
            '/Windows NT 10\.0/i' => 'Windows 10',
            '/Windows NT 6\.3/i' => 'Windows 8.1',
            '/Windows NT 6\.2/i' => 'Windows 8',
            '/Windows NT 6\.1/i' => 'Windows 7',
            '/Windows NT 6\.0/i' => 'Windows Vista',
            '/Windows NT 5\.(?:1|2)/i' => 'Windows XP',
            '/Windows NT 5\.0|Windows 2000/i' => 'Windows 2000',
        ];

        foreach ($map as $pattern => $name) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return self::contains($ua, 'Windows') ? 'Windows' : null;
    }

    /**
     * @param string $ua
     * @return string|null
     */
    private static function detect_linux_distribution($ua)
    {
        $map = [
            '/\bKubuntu\b/i' => 'Linux - Kubuntu',
            '/\bRaspbian\b|\bRaspberry\b/i' => 'Linux - Raspbian',
            '/\bMandriva\b/i' => 'Linux - Mandriva',
            '/\bUbuntu\b/i' => 'Linux - Ubuntu',
            '/\bDebian\b/i' => 'Linux - Debian',
            '/\bFedora\b/i' => 'Linux - Fedora',
            '/\bCentOS\b/i' => 'Linux - CentOS',
            '/\b(?:SUSE|openSUSE)\b/i' => 'Linux - SUSE',
            '/\bGentoo\b/i' => 'Linux - Gentoo',
            '/\bManjaro\b/i' => 'Linux - Manjaro',
            '/\bOpenWrt\b/i' => 'Linux - openWRT',
        ];

        foreach ($map as $pattern => $name) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Preserve extension filters after all built-in, high-confidence checks.
     *
     * @param string $ua
     * @return string
     */
    private static function detect_filtered_legacy_client_os($ua): string
    {
        $patterns = [
            'UP\.Browser' => 'Windows CE',
            'amiga\-aweb|amiga' => 'Amiga',
            'os\/2' => 'OS/2',
            'beos' => 'BeOS',
            'plan9' => 'Plan9',
            'osf' => 'OSF',
            'aix' => 'AIX',
            'irix' => 'IRIX',
        ];

        $patterns = (array) apply_filters('cf_geoplugin_os_version', $patterns);
        foreach ($patterns as $regex => $label) {
            if (self::regex_match('~' . $regex . '~i', $ua)) {
                return (string) $label;
            }
        }

        $regex_patterns = (array) apply_filters('cf_geoplugin_os_version_regex', []);
        foreach ($regex_patterns as $regex => $label) {
            if (self::regex_match('~' . $regex . '~i', $ua)) {
                return (string) $label;
            }
        }

        return self::unknown();
    }

    /**
     * @return string
     */
    private static function detect_server_os_name(): string
    {
        $family = defined('PHP_OS_FAMILY') ? PHP_OS_FAMILY : '';
        if ($family === 'Windows') {
            return self::detect_filtered_server_windows();
        }
        if ($family === 'Darwin') {
            return 'Mac OS';
        }
        if ($family === 'Linux') {
            return self::detect_filtered_server_os('Linux');
        }
        if ($family === 'BSD') {
            return 'BSD';
        }
        if ($family === 'Solaris') {
            return 'Solaris';
        }

        $system = function_exists('php_uname') ? @php_uname('s') : PHP_OS;
        $system = is_string($system) ? $system : '';
        if (self::contains($system, 'Windows')) return self::detect_filtered_server_windows();
        if (self::contains($system, 'Darwin') || self::contains($system, 'Mac')) return 'Mac OS';
        if (self::contains($system, 'Linux')) return self::detect_filtered_server_os('Linux');
        if (self::contains($system, 'FreeBSD')) return 'FreeBSD';
        if (self::contains($system, 'OpenBSD')) return 'OpenBSD';
        if (self::contains($system, 'NetBSD')) return 'NetBSD';
        if (self::contains($system, 'SunOS') || self::contains($system, 'Solaris')) return 'Solaris';

        return $system !== '' ? $system : self::unknown();
    }

    /**
     * Preserve server-oriented filters without reading hostnames or arbitrary files.
     *
     * @param string $base
     * @return string
     */
    private static function detect_filtered_server_os($base): string
    {
        $source = $base;
        if (function_exists('php_uname')) {
            $system = @php_uname('s');
            $release = @php_uname('r');
            $source .= ' ' . (is_string($system) ? $system : '') . ' ' . (is_string($release) ? $release : '');
        }

        $unix = (array) apply_filters('cf_geoplugin_unix_version', [
            'raspberry' => 'Linux - Raspbian', 'kubuntu' => 'Linux - Kubuntu',
            'mandriva' => 'Linux - Mandriva', 'ubuntu' => 'Linux - Ubuntu',
            'debian' => 'Linux - Debian', 'gentoo' => 'Linux - Gentoo',
            'manjaro' => 'Linux - Manjaro', 'opensuse' => 'Linux - openSUSE',
            'openwrt' => 'Linux - openWRT', 'fedora' => 'Linux - Fedora', 'linux' => 'Linux',
        ]);
        foreach ($unix as $regex => $label) {
            if (self::regex_match('~' . $regex . '~i', $source)) {
                return (string) $label;
            }
        }

        $unix_regex = (array) apply_filters('cf_geoplugin_unix_version_regex', []);
        foreach ($unix_regex as $regex => $label) {
            if (self::regex_match('~' . $regex . '~i', $source)) {
                return (string) $label;
            }
        }

        return $base;
    }

    /**
     * @return string
     */
    private static function detect_filtered_server_windows(): string
    {
        $source = '';
        if (function_exists('php_uname')) {
            $system = @php_uname('s');
            $release = @php_uname('r');
            $source = (is_string($system) ? $system : '') . ' ' . (is_string($release) ? $release : '');
        }

        $versions = (array) apply_filters('cf_geoplugin_windows_version', [
            '95', '98', '2000', 'XP Professional', 'XP', '7\.1', '7',
            '8\.1 Pro', '8\.1 Home', '8\.1 Enterprise', '8\.1 OEM', '8\.1',
            '8 Home', '8 Enterprise', '8 OEM', '8', '10\.1',
            '10 Home', '10 Pro Education', '10 Pro', '10 Education', '10 Enterprise LTSB',
            '10 Enterprise', '10 IoT Core', '10 IoT Enterprise', '10 IoT', '10 S', '10 OEM', '10',
            '11\.1', '11 Home', '11 Pro Education', '11 Pro', '11 Education', '11 Enterprise LTSB',
            '11 Enterprise', '11 IoT Core', '11 IoT Enterprise', '11 IoT', '11 S', '11 OEM', '11',
            'server', 'vista', 'me', 'nt',
        ]);

        foreach ($versions as $version) {
            if (self::regex_match('~' . $version . '~i', $source)) {
                return 'Windows ' . str_replace('\\', '', (string) $version);
            }
        }

        return 'Windows';
    }

    private static function environment($key)
    {
        if (isset($_SERVER[$key]) && is_string($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        $value = getenv($key);
        return is_string($value) ? $value : '';
    }

    private static function is_64bit_architecture($architecture): bool
    {
        return (bool) preg_match('/(?:x86_64|amd64|aarch64|arm64|ppc64(?:le)?|s390x|ia64)/i', (string) $architecture);
    }

    private static function contains($haystack, $needle): bool
    {
        return stripos((string) $haystack, (string) $needle) !== false;
    }

    private static function unknown(): string
    {
        // This translated legacy fallback remains for callers that compare its current value.
        return __('undefined', 'cf-geoplugin');
    }

    private static function regex_match(string $pattern, string $subject): bool
    {
        return @preg_match($pattern, $subject) === 1;
    }
}

endif;
