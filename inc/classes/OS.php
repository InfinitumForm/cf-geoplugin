<?php
/**
 * OS detection utilities
 *
 * @link          https://infinitumform.com/
 * @since         8.0.0
 * @package       cf-geoplugin
 * @author        Ivijan-Stefan Stipic
 * @version       1.2.0
 */

if ( ! defined('WPINC') ) {
    die("Don't mess with us.");
}

if ( ! class_exists('CFGP_OS', false) ) :

final class CFGP_OS
{
    /**
     * Get HTTP User-Agent string (client).
     *
     * @return string
     */
    public static function user_agent(): string
    {
        // Prefer $_SERVER; fallback to legacy globals if present.
        if ( isset($_SERVER['HTTP_USER_AGENT']) && is_string($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] !== '' ) {
            return $_SERVER['HTTP_USER_AGENT'];
        }

        // Legacy globals (very uncommon today, but preserve behavior).
        global $HTTP_USER_AGENT, $HTTP_SERVER_VARS;

        if ( ! empty($HTTP_USER_AGENT) ) {
            return (string) $HTTP_USER_AGENT;
        }

        if ( ! empty($HTTP_SERVER_VARS['HTTP_USER_AGENT']) ) {
            return (string) $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
        }

        return __('undefined', 'cf-geoplugin');
    }

    /**
     * Check if PHP is running on Windows.
     *
     * @return bool
     */
    public static function is_win(): bool
    {
        if ( defined('PHP_OS_FAMILY') ) {
            return PHP_OS_FAMILY === 'Windows';
        }

        // Fallbacks for older environments.
        if ( defined('PHP_SHLIB_SUFFIX') && strtolower(PHP_SHLIB_SUFFIX) === 'dll' ) {
            return true;
        }

        if ( defined('DIRECTORY_SEPARATOR') && DIRECTORY_SEPARATOR === '\\' ) {
            return true;
        }

        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    /**
     * Check if current PHP build is 64-bit.
     * (If PHP is 64-bit, OS is effectively 64-bit too.)
     *
     * @return bool
     */
    public static function is_php64(): bool
    {
        return (defined('PHP_INT_SIZE') && PHP_INT_SIZE === 8);
    }

    /**
     * Check if OS is 64-bit.
     *
     * @return bool
     */
    public static function is_os64(): bool
    {
        // Most reliable heuristic: PHP build bitness.
        if ( self::is_php64() ) {
            return true;
        }

        // Conservative fallback: assume 32-bit if PHP is 32-bit.
        return false;
    }

    /**
     * Get OS architecture (32 or 64).
     *
     * @return int
     */
    public static function architecture(): int
    {
        return self::is_os64() ? 64 : 32;
    }

    /**
     * Get operating system name.
     *
     * Behavior:
     * - If $user_agent is provided (non-empty), detect CLIENT OS from UA.
     * - Otherwise, detect SERVER OS via php_uname()/PHP_OS_FAMILY.
     *
     * @param string|null $user_agent
     * @return string
     */
    public static function get(?string $user_agent = null): string
    {
        // SERVER OS detection (no UA provided)
        if ( $user_agent === null || $user_agent === '' ) {
            $name = self::detect_server_os_name();

            // Build extended map (Windows versions, Linux distros, macOS codenames) to match php_uname('a') if present.
            $os_array = [];

            foreach ( apply_filters('cf_geoplugin_windows_version', [
                '95','98','2000','XP Professional','XP',
                '7\.1','7',
                '8\.1 Pro','8\.1 Home','8\.1 Enterprise','8\.1 OEM','8\.1',
                '8 Home','8 Enterprise','8 OEM','8',
                '10\.1',
                '10 Home','10 Pro Education','10 Pro','10 Education','10 Enterprise LTSB','10 Enterprise','10 IoT Core','10 IoT Enterprise','10 IoT','10 S','10 OEM','10',
                '11\.1',
                '11 Home','11 Pro Education','11 Pro','11 Education','11 Enterprise LTSB','11 Enterprise','11 IoT Core','11 IoT Enterprise','11 IoT','11 S','11 OEM','11',
                'server','vista','me','nt',
            ]) as $ver ) {
                $os_array['windows ' . $ver] = 'Windows ' . $ver;
            }

            $os_array['microsoft windows'] = 'Microsoft Windows';
            $os_array['windows']           = 'Windows';

            foreach ( apply_filters('cf_geoplugin_unix_version', [
                'raspberry' => 'Linux - Raspbian',
                'jessie'    => 'Linux - Debian Jessie',
                'squeeze'   => 'Linux - Debian Squeeze',
                'wheezy'    => 'Linux - Debian Wheezy',
                'stretch'   => 'Linux - Debian Stretch',
                'kubuntu'   => 'Linux - Kubuntu',
                'mandriva'  => 'Linux - Mandriva',
                'lubuntu'   => 'Linux - Lubuntu',
                'ubuntu'    => 'Linux - Ubuntu',
                'debian'    => 'Linux - Debian',
                'gentoo'    => 'Linux - Gentoo',
                'manjaro'   => 'Linux - Manjaro',
                'opensuse'  => 'Linux - openSUSE',
                'openwrt'   => 'Linux - openWRT',
                'fedora'    => 'Linux - Fedora',
                'linux'     => 'Linux',
                'sierra'    => 'Mac OS - Sierra',
                'mavericks' => 'Mac OS - Mavericks',
                'yosemite'  => 'Mac OS - Yosemite',
                'mac os x'  => 'Mac OS X',
                'os x'      => 'Mac OS X',
                'mac os'    => 'Mac OS',
                'mac'       => 'Mac OS',
                'android'   => 'Android',
            ]) as $regex => $label ) {
                if ( self::regex_match('~' . $regex . '~i', $name) ) {
                    return $label;
                }
            }

            foreach ( (array) apply_filters('cf_geoplugin_unix_version_regex', [
                'Mac OS X 10\.1[^0-9]' => 'Mac OS X Puma',
            ]) as $regex => $label ) {
                if ( self::regex_match('~' . $regex . '~i', $name) ) {
                    return $label;
                }
            }

            // Fallback to normalized family name.
            return $name !== '' ? $name : __('undefined', 'cf-geoplugin');
        }

        // CLIENT OS detection (UA provided)
        $ua = (string) $user_agent;

        $os_array = (array) apply_filters('cf_geoplugin_os_version', [
            'win11'                 => 'Windows 11',
            'windows 11'            => 'Windows 11',
            'windows 11 enterprise' => 'Windows 11',
            'windows 11 home'       => 'Windows 11',
            'windows 11 pro'        => 'Windows 11',
            'windows nt 11'         => 'Windows 11',

            'win10'                 => 'Windows 10',
            'windows 10'            => 'Windows 10',
            'windows 10 enterprise' => 'Windows 10',
            'windows 10 home'       => 'Windows 10',
            'windows 10 pro'        => 'Windows 10',
            'windows nt 10'         => 'Windows 10',

            'windows nt 6\.3'       => 'Windows 8.1',
            'windows nt 6\.2'       => 'Windows 8',
            'windows nt 6\.1|windows nt 7\.0' => 'Windows 7',
            'windows nt 6\.0'       => 'Windows Vista',
            'windows nt 5\.2'       => 'Windows Server 2003/XP x64',
            'windows nt 5\.1'       => 'Windows XP',
            'windows 2000|windows nt 5\.0|windows nt5\.1' => 'Windows 2000',
            'windows me|Win 9x 4\.90' => 'Windows ME',
            'windows 98|win98'      => 'Windows 98',
            'windows 95|win95'      => 'Windows 95',
            'win16'                 => 'Windows 3.11',
            'win32'                 => 'Windows',

            'mac os x 10\.1[^0-9]|Mac OS X 10\.1[^0-9]' => 'Mac OS X Puma',
            'macintosh|mac os x'   => 'Mac OS X',
            'Mac\_PowerPC|mac\_powerpc' => 'Macintosh PowerPC',
            'mac|mac os'           => 'Mac OS',

            '(fedora)'             => 'Linux - Fedora',
            '(kubuntu)'            => 'Linux - Kubuntu',
            '(ubuntu)'             => 'Linux - Ubuntu',
            '(debian)'             => 'Linux - Debian',
            '(CentOS)'             => 'Linux - CentOS',
            '(SUSE)'               => 'Linux - SUSE',
            '(Mandriva)'           => 'Linux - Mandriva',
            '(Dropline)'           => 'Linux - Slackware (Dropline GNOME)',
            '(ASPLinux)'           => 'Linux - ASPLinux',
            'raspbian|raspberry'   => 'Linux - Raspbian',
            'linux'                => 'Linux',
            'freebsd'              => 'FreeBSD',
            'openbsd'              => 'OpenBSD',
            'netbsd'               => 'NetBSD',
            'android'              => 'Android',

            'iphone'               => 'iPhone',
            'ipad'                 => 'iPad',
            'ipod'                 => 'iPod',
            'ios'                  => 'iOS',

            '(SmartTV|LGwebOSTV)'  => 'Smart TV',
            'webos'                => 'WebOS',
            'blackberry'           => 'BlackBerry',

            'unix'                 => 'Unix',
            'sunos'                => 'SunOS',
            'aix'                  => 'AIX',
            'irix'                 => 'IRIX',
            'os\/2'                => 'OS/2',
            'plan9'                => 'Plan9',
            'osf'                  => 'OSF',
            'amiga\-aweb|amiga'    => 'Amiga',
            'beos'                 => 'BeOS',
            'risc os'              => 'RISC OS',
            'Solaris'              => 'Solaris',
            'webtv'                => 'WebTV',
            'dos x86'              => 'DOS',

            // Generic/unknown agents
            'Java'                 => 'Unknown',
            'libwww\-perl'         => 'Unix',
            'UP\.Browser'          => 'Windows CE',
        ]);

        $regex_check = (array) apply_filters('cf_geoplugin_os_version_regex', [
            '(media center pc)\.([0-9]{1,2}\.[0-9]{1,2})'         => 'Windows Media Center',
            '(win)([0-9]{1,2}\.[0-9x]{1,2})'                      => 'Windows',
            '(win)([0-9]{2})'                                     => 'Windows',
            '(windows)([0-9x]{2})'                                => 'Windows',
            '(winnt)([0-9]{1,2}\.[0-9]{1,2}){0,1}'                => 'Windows NT',
            '(windows nt)(([0-9]{1,2}\.[0-9]{1,2}){0,1})'         => 'Windows NT',
            '(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})'          => 'Java',
            '(mac|Macintosh)'                                     => 'Mac OS',
            '(amigaos)([0-9]{1,2}\.[0-9]{1,2})'                   => 'AmigaOS',
            '([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})'                => 'Linux',
            '(msproxy)/([0-9]{1,2}\.[0-9]{1,2})'                  => 'Windows',
            '(msie)([0-9]{1,2}\.[0-9]{1,2})'                      => 'Windows',
        ]);

        // First pass: direct regex keys.
        foreach ( $os_array as $regex => $label ) {
            if ( self::regex_match('~' . $regex . '~i', $ua) ) {
                return $label;
            }
        }

        // Second pass: broader patterns.
        foreach ( $regex_check as $regex => $label ) {
            if ( self::regex_match('~' . $regex . '~i', $ua) ) {
                return $label;
            }
        }

        return __('undefined', 'cf-geoplugin');
    }

    /**
     * Detect server OS human-friendly name.
     *
     * @return string
     */
    private static function detect_server_os_name(): string
    {
        // Prefer PHP_OS_FAMILY when available.
        if ( defined('PHP_OS_FAMILY') ) {
            switch ( PHP_OS_FAMILY ) {
                case 'Windows':
                    return 'Windows';
                case 'Darwin':
                    // Distinguish macOS vs generic "Darwin".
                    return 'Mac OS';
                case 'BSD':
                    return 'BSD';
                case 'Solaris':
                    return 'Solaris';
                case 'Linux':
                    return 'Linux';
                default:
                    // Fallback to uname.
                    break;
            }
        }

        // Fallback to php_uname / PHP_OS
        $raw = function_exists('php_uname') ? php_uname('a') : PHP_OS;
        $name = is_string($raw) ? trim($raw) : '';

        if ( $name === '' ) {
            return __('undefined', 'cf-geoplugin');
        }

        // Normalize some common tokens quickly.
        if ( stripos($name, 'win') !== false ) {
            return 'Windows';
        }
        if ( stripos($name, 'darwin') !== false || stripos($name, 'mac') !== false ) {
            return 'Mac OS';
        }
        if ( stripos($name, 'linux') !== false ) {
            return 'Linux';
        }
        if ( stripos($name, 'freebsd') !== false ) {
            return 'FreeBSD';
        }
        if ( stripos($name, 'openbsd') !== false ) {
            return 'OpenBSD';
        }
        if ( stripos($name, 'netbsd') !== false ) {
            return 'NetBSD';
        }
        if ( stripos($name, 'sunos') !== false || stripos($name, 'solaris') !== false ) {
            return 'Solaris';
        }

        return $name;
    }

    /**
     * Safe preg_match wrapper that suppresses warnings on invalid patterns.
     *
     * @param string $pattern
     * @param string $subject
     * @return bool
     */
    private static function regex_match(string $pattern, string $subject): bool
    {
        // Suppress warnings from invalid vendor patterns, return false on failure.
        $ok = @preg_match($pattern, $subject);
        return $ok === 1;
    }
}

endif;
