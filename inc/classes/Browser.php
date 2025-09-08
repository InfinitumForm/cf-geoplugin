<?php
/**
 * File: Browser.php (modernized)
 *
 * Notes:
 * - Uses Client Hints (brands, full versions) when present (Chromium + HTTPS).
 * - Improves Brave/Edge detection (brands-aware) and avoids Safari false-positives.
 * - Keeps legacy UA parsing as fallback.
 * - Platform is resolved via CFGP_OS::get($ua).
 *
 * @author  Chris Schuld (base)
 * @edited  Ivijan-Stefan Stipic / Modernized helper
 */

if (!defined('WPINC')) {
    die("Don't mess with us.");
}

if (!class_exists('CFGP_Browser', false)) :

final class CFGP_Browser
{
    // Browser constants
    public const BROWSER_EDGE         = 'Microsoft Edge';
    public const BROWSER_OPERA        = 'Opera';
    public const BROWSER_OPERA_MINI   = 'Opera Mini';
    public const BROWSER_WEBTV        = 'WebTV';
    public const BROWSER_IE           = 'Internet Explorer';
    public const BROWSER_POCKET_IE    = 'Pocket Internet Explorer';
    public const BROWSER_KONQUEROR    = 'Konqueror';
    public const BROWSER_ICAB         = 'iCab';
    public const BROWSER_OMNIWEB      = 'OmniWeb';
    public const BROWSER_FIREBIRD     = 'Firebird';
    public const BROWSER_FIREFOX      = 'Firefox';
    public const BROWSER_ICEWEASEL    = 'Iceweasel';
    public const BROWSER_SHIRETOKO    = 'Shiretoko';
    public const BROWSER_MOZILLA      = 'Mozilla';
    public const BROWSER_AMAYA        = 'Amaya';
    public const BROWSER_LYNX         = 'Lynx';
    public const BROWSER_SAFARI       = 'Safari';
    public const BROWSER_IPHONE       = 'iPhone';
    public const BROWSER_IPOD         = 'iPod';
    public const BROWSER_IPAD         = 'iPad';
    public const BROWSER_CHROME       = 'Chrome';
    public const BROWSER_BRAVE        = 'Brave';
    public const BROWSER_VIVALDI      = 'Vivaldi';
    public const BROWSER_OPERA_TOUCH  = 'Opera Touch';
    public const BROWSER_ANDROID      = 'Android';
    public const BROWSER_GOOGLEBOT    = 'GoogleBot';
    public const BROWSER_SLURP        = 'Yahoo! Slurp';
    public const BROWSER_W3CVALIDATOR = 'W3C Validator';
    public const BROWSER_BLACKBERRY   = 'BlackBerry';
    public const BROWSER_ICECAT       = 'IceCat';
    public const BROWSER_NOKIA_S60    = 'Nokia S60 OSS Browser';
    public const BROWSER_NOKIA        = 'Nokia Browser';
    public const BROWSER_MSN          = 'MSN Browser';
    public const BROWSER_MSNBOT       = 'MSN Bot';
    public const BROWSER_WEBOS        = 'Web OS Browser';
    public const BROWSER_FB           = 'Facebook Browser';
    public const BROWSER_UNKNOWN      = 'unknown';

    // Platforms (kept for BC where used externally)
    public const PLATFORM_WINDOWS     = 'Windows';
    public const PLATFORM_WINDOWS_CE  = 'Windows CE';
    public const PLATFORM_APPLE       = 'Apple';
    public const PLATFORM_LINUX       = 'Linux';
    public const PLATFORM_OS2         = 'OS/2';
    public const PLATFORM_BEOS        = 'BeOS';
    public const PLATFORM_IPHONE      = 'iPhone';
    public const PLATFORM_IPOD        = 'iPod';
    public const PLATFORM_IPAD        = 'iPad';
    public const PLATFORM_BLACKBERRY  = 'BlackBerry';
    public const PLATFORM_NOKIA       = 'Nokia';
    public const PLATFORM_FREEBSD     = 'FreeBSD';
    public const PLATFORM_OPENBSD     = 'OpenBSD';
    public const PLATFORM_NETBSD      = 'NetBSD';
    public const PLATFORM_SUNOS       = 'SunOS';
    public const PLATFORM_OPENSOLARIS = 'OpenSolaris';
    public const PLATFORM_ANDROID     = 'Android';
    public const PLATFORM_WEBOS       = 'webOS';

    // State
    private $_agent        = '';
    private $_browser_name = self::BROWSER_UNKNOWN;
    private $_version      = self::BROWSER_UNKNOWN;
    private $_platform     = self::BROWSER_UNKNOWN;
    private $_os           = self::BROWSER_UNKNOWN;
    private $_is_aol       = false;
    private $_is_mobile    = false;
    private $_is_robot     = false;
    private $_aol_version  = self::BROWSER_UNKNOWN;

    // Client Hints cache
    private $_ch_brands_raw   = null; // Sec-CH-UA / Sec-CH-UA-Full-Version-List
    private $_ch_brands       = [];   // parsed brands => versions
    private $_ch_platform     = null; // Sec-CH-UA-Platform
    private $_ch_platform_ver = null; // Sec-CH-UA-Platform-Version

    /**
     * Singleton factory through CFGP_Cache (kept for BC).
     */
    public static function instance($useragent = '')
    {
        $class    = self::class;
        $instance = function_exists('CFGP_Cache::get') ? CFGP_Cache::get($class) : null;

        if (!$instance) {
            $instance = new self($useragent);
            if (function_exists('CFGP_Cache::set')) {
                CFGP_Cache::set($class, $instance);
            }
        }

        return $instance;
    }

    /**
     * Private ctor; prefer instance().
     */
    private function __construct($useragent = '')
    {
        $this->reset();

        if ($useragent !== '') {
            $this->setUserAgent($useragent);
        } else {
            $this->determine();
        }
    }

    /**
     * Reset state from globals and Client Hints.
     */
    public function reset(): void
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
        if (function_exists('sanitize_text_field')) {
            $ua = sanitize_text_field($ua);
        }
        $this->_agent        = $ua;
        $this->_browser_name = self::BROWSER_UNKNOWN;
        $this->_version      = self::BROWSER_UNKNOWN;
        $this->_platform     = self::BROWSER_UNKNOWN;
        $this->_os           = self::BROWSER_UNKNOWN;
        $this->_is_aol       = false;
        $this->_is_mobile    = false;
        $this->_is_robot     = false;
        $this->_aol_version  = self::BROWSER_UNKNOWN;

        // Read Client Hints if present
        $this->_ch_brands_raw   = $this->server('HTTP_SEC_CH_UA_FULL_VERSION_LIST') ?: $this->server('HTTP_SEC_CH_UA');
        $this->_ch_brands       = $this->parseBrands($this->_ch_brands_raw);
        $this->_ch_platform     = $this->stripQuotes($this->server('HTTP_SEC_CH_UA_PLATFORM'));
        $this->_ch_platform_ver = $this->stripQuotes($this->server('HTTP_SEC_CH_UA_PLATFORM_VERSION'));
    }

    // ----------------- Public API -----------------

    public function isBrowser($browserName): bool
    {
        return (0 === strcasecmp($this->_browser_name, trim((string) $browserName)));
    }

    public function getBrowser(): string
    {
        return $this->_browser_name;
    }

    public function setBrowser($browser): string
    {
        $this->_browser_name = (string) $browser;
        return $this->_browser_name;
    }

    public function getPlatform(): string
    {
        return $this->_platform;
    }

    public function setPlatform($platform): string
    {
        $this->_platform = (string) $platform;
        return $this->_platform;
    }

    public function getVersion(): string
    {
        return $this->_version;
    }

    public function setVersion($version): void
    {
        $this->_version = preg_replace('/[^0-9a-zA-Z\.\-]/', '', (string) $version);
        if ($this->_version === '') {
            $this->_version = self::BROWSER_UNKNOWN;
        }
    }

    public function getAolVersion(): string
    {
        return $this->_aol_version;
    }

    public function setAolVersion($version): void
    {
        $this->_aol_version = preg_replace('/[^0-9a-zA-Z\.]/', '', (string) $version);
        if ($this->_aol_version === '') {
            $this->_aol_version = self::BROWSER_UNKNOWN;
        }
    }

    public function isAol(): bool
    {
        return $this->_is_aol;
    }

    public function isMobile(): bool
    {
        return $this->_is_mobile;
    }

    public function isRobot(): bool
    {
        return $this->_is_robot;
    }

    public function setAol($isAol): void
    {
        $this->_is_aol = (bool) $isAol;
    }

    protected function setMobile($value = true): void
    {
        $this->_is_mobile = (bool) $value;
    }

    protected function setRobot($value = true): void
    {
        $this->_is_robot = (bool) $value;
    }

    public function getUserAgent(): string
    {
        return $this->_agent;
    }

    public function setUserAgent($agent_string): void
    {
        $this->reset();
        $this->_agent = (string) $agent_string;
        $this->determine();
    }

    public function isChromeFrame(): bool
    {
        return (stripos($this->_agent, 'chromeframe') !== false);
    }

    public function __toString(): string
    {
        return '<strong>' . esc_html__('Browser Name:', 'cf-geoplugin') . '</strong>' . $this->getBrowser() . '<br/>' . PHP_EOL .
               '<strong>' . esc_html__('Browser Version:', 'cf-geoplugin') . '</strong>' . $this->getVersion() . '<br/>' . PHP_EOL .
               '<strong>' . esc_html__('Browser User Agent String:', 'cf-geoplugin') . '</strong>' . $this->getUserAgent() . '<br/>' . PHP_EOL .
               '<strong>' . esc_html__('Platform:', 'cf-geoplugin') . '</strong>' . $this->getPlatform() . '<br/>';
    }

    // ----------------- Core detection -----------------

    protected function determine(): void
    {
        $this->checkPlatform();   // via CFGP_OS::get($ua)
        $this->checkBrowsers();   // CH-aware + UA fallback
        $this->checkForAol();
    }

    protected function checkBrowsers(): bool
    {
        // Bots first
        if ($this->checkBrowserGoogleBot() || $this->checkBrowserMSNBot() || $this->checkBrowserSlurp() || $this->checkBrowserW3CValidator()) {
            return true;
        }

        // Client-Hints brand-aware Chromium forks (Brave, Edge, Vivaldi, Opera)
        if ($this->checkChromiumBrands()) {
            return true;
        }

        // Traditional checks
        return (
            $this->checkBrowserEdge()
            || $this->checkBrowserWebTv()
            || $this->checkBrowserInternetExplorer()
            || $this->checkBrowserOpera()
            || $this->checkBrowserGaleon()
            || $this->checkBrowserNetscapeNavigator9Plus()
            || $this->checkBrowserFirefox()
            || $this->checkBrowserChrome()
            || $this->checkBrowserOmniWeb()
            // mobile
            || $this->checkBrowserAndroid()
            || $this->checkBrowseriPad()
            || $this->checkBrowseriPod()
            || $this->checkBrowseriPhone()
            || $this->checkBrowserBlackBerry()
            || $this->checkBrowserNokia()
            // webkit generic
            || $this->checkBrowserSafari()
            // others
            || $this->checkBrowserNetPositive()
            || $this->checkBrowserFirebird()
            || $this->checkBrowserKonqueror()
            || $this->checkBrowserIcab()
            || $this->checkBrowserPhoenix()
            || $this->checkBrowserAmaya()
            || $this->checkBrowserLynx()
            || $this->checkBrowserShiretoko()
            || $this->checkBrowserIceCat()
            || $this->checkBrowserMozilla()
            || $this->checkBrowserWebOS()
        );
    }

    // ----------------- Client Hints helpers -----------------

    private function checkChromiumBrands(): bool
    {
        if (empty($this->_ch_brands)) {
            return false;
        }

        // Brand precedence
        $order = [
            'Brave'    => self::BROWSER_BRAVE,
            'Microsoft Edge' => self::BROWSER_EDGE,
            'Edge'     => self::BROWSER_EDGE,
            'Vivaldi'  => self::BROWSER_VIVALDI,
            'Opera'    => self::BROWSER_OPERA,
            'Chromium' => self::BROWSER_CHROME, // treat as Chrome if nothing else
            'Google Chrome' => self::BROWSER_CHROME,
            'Chrome'   => self::BROWSER_CHROME,
        ];

        foreach ($order as $brand => $label) {
            foreach ($this->_ch_brands as $b => $ver) {
                if (stripos($b, $brand) !== false) {
                    $this->setBrowser($label);
                    $this->setVersion($ver ?: $this->extractChromiumVersionFromUA());
                    // Mobile hint
                    if (stripos($this->_agent, 'Mobile') !== false) {
                        $this->setMobile(true);
                    }
                    return true;
                }
            }
        }

        return false;
    }

    private function parseBrands(?string $raw): array
    {
        if (!$raw) return [];
        // Example: Chromium;v="139.0.0.0", "Brave";v="1.69.153"
        $out = [];
        $parts = explode(',', $raw);
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;
            // Match Brand;v="x.y.z"
            if (preg_match('/"?([^";]+)"?;\s*v="([^"]+)"/', $part, $m)) {
                $brand = trim($m[1]);
                $ver   = trim($m[2]);
                $out[$brand] = $ver;
            } else {
                // Fallback "Brand"
                $brand = trim($part, '" ');
                if ($brand !== '') {
                    $out[$brand] = null;
                }
            }
        }
        return $out;
    }

    private function extractChromiumVersionFromUA(): string
    {
        if (preg_match('/(?:Chrome|Edg|OPR|Brave)\/([0-9\.]+)/i', $this->_agent, $m)) {
            return $m[1];
        }
        return self::BROWSER_UNKNOWN;
    }

    private function stripQuotes(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim($v);
        if ($v === '') return null;
        if ($v[0] === '"' && substr($v, -1) === '"') {
            return substr($v, 1, -1);
        }
        return $v;
    }

    private function server(string $key): ?string
    {
        return isset($_SERVER[$key]) && is_string($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    // ----------------- Browser checks (UA fallback) -----------------

    protected function checkBrowserWebOS(): bool
    {
        if (preg_match('/(webos|wos)/i', $this->_agent)) {
            if (preg_match("/FBAV\/([0-9A-Z\.]+)(\;|\s){1}/", $this->_agent, $aversion)) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_FB);
                return true;
            } elseif (preg_match("(WEBOS23\s|webos\s)([0-9A-Z\.]+)(\;|\s){1}/", $this->_agent, $aversion)) {
                $this->setVersion($aversion[2]);
                $this->setBrowser(self::BROWSER_WEBOS);
                return true;
            }
        }
        return false;
    }

    protected function checkBrowserEdge(): bool
    {
        // Edg/xxx (desktop), EdgA/xxx (Android)
        if (preg_match('/\bEdg[A|e|i|]\/([0-9\.]+)/', $this->_agent, $m)) {
            $this->setVersion($m[1]);
            $this->setBrowser(self::BROWSER_EDGE);
            return true;
        }
        return false;
    }

    protected function checkBrowserBlackBerry(): bool
    {
        if (stripos($this->_agent, 'blackberry') !== false) {
            $this->setBrowser(self::BROWSER_BLACKBERRY);
            $this->setMobile(true);
            if (preg_match('/BlackBerry[^\/]*\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkForAol(): bool
    {
        $this->setAol(false);
        $this->setAolVersion(self::BROWSER_UNKNOWN);

        if (stripos($this->_agent, 'aol') !== false) {
            $this->setAol(true);
            if (preg_match('/AOL[^\d]*([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setAolVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserGoogleBot(): bool
    {
        if (stripos($this->_agent, 'googlebot') !== false) {
            $this->setBrowser(self::BROWSER_GOOGLEBOT);
            $this->setRobot(true);
            if (preg_match('/googlebot\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserMSNBot(): bool
    {
        if (stripos($this->_agent, 'msnbot') !== false) {
            $this->setBrowser(self::BROWSER_MSNBOT);
            $this->setRobot(true);
            if (preg_match('/msnbot\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserW3CValidator(): bool
    {
        if (stripos($this->_agent, 'W3C-checklink') !== false || stripos($this->_agent, 'W3C_Validator') !== false) {
            $this->setBrowser(self::BROWSER_W3CVALIDATOR);
            if (preg_match('/(?:W3C-checklink|W3C_Validator)\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserSlurp(): bool
    {
        if (stripos($this->_agent, 'slurp') !== false) {
            $this->setBrowser(self::BROWSER_SLURP);
            $this->setRobot(true);
            if (preg_match('/Slurp\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserInternetExplorer(): bool
    {
        if (stripos($this->_agent, 'microsoft internet explorer') !== false) {
            $this->setBrowser(self::BROWSER_IE);
            $this->setVersion('1.0');
            if (preg_match('/(308|425|426|474|0b1)/i', $this->_agent)) {
                $this->setVersion('1.5');
            }
            return true;
        } elseif (stripos($this->_agent, 'msie') !== false && stripos($this->_agent, 'opera') === false) {
            $this->setBrowser(self::BROWSER_IE);
            if (preg_match('/msie\s+([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            // MSN browser variant
            if (stripos($this->_agent, 'msnb') !== false && preg_match('/MSN\s*([0-9\.]+)/i', $this->_agent, $mm)) {
                $this->setBrowser(self::BROWSER_MSN);
                $this->setVersion($mm[1] ?? $this->getVersion());
            }
            return true;
        } elseif (stripos($this->_agent, 'mspie') !== false || stripos($this->_agent, 'pocket') !== false) {
            $this->setPlatform(self::PLATFORM_WINDOWS_CE);
            $this->setBrowser(self::BROWSER_POCKET_IE);
            $this->setMobile(true);
            if (preg_match('/mspie\s*([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } elseif (preg_match('/\/([0-9\.]+)/', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserOpera(): bool
    {
        if (stripos($this->_agent, 'opera mini') !== false) {
            $this->setBrowser(self::BROWSER_OPERA_MINI);
            $this->setMobile(true);
            if (preg_match('/opera mini\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        if (stripos($this->_agent, 'OPR/') !== false || stripos($this->_agent, 'Opera') !== false) {
            $this->setBrowser(self::BROWSER_OPERA);
            if (preg_match('/(?:OPR|Opera)\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } elseif (preg_match('/Version\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        if (stripos($this->_agent, 'OPT/') !== false) {
            $this->setBrowser(self::BROWSER_OPERA_TOUCH);
            if (preg_match('/OPT\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserChrome(): bool
    {
        // Avoid false positive if Edge/Opera already matched
        if (stripos($this->_agent, 'Chrome') !== false && stripos($this->_agent, 'Edg') === false && stripos($this->_agent, 'OPR') === false) {
            // Some Brave UAs hide brand; CH covers that earlier.
            $this->setBrowser(self::BROWSER_CHROME);
            if (preg_match('/Chrome\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserWebTv(): bool
    {
        if (stripos($this->_agent, 'webtv') !== false) {
            $this->setBrowser(self::BROWSER_WEBTV);
            if (preg_match('/webtv\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserNetPositive(): bool
    {
        if (stripos($this->_agent, 'NetPositive') !== false) {
            $this->setBrowser(self::BROWSER_NETPOSITIVE);
            if (preg_match('/NetPositive\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserGaleon(): bool
    {
        if (stripos($this->_agent, 'galeon') !== false) {
            $this->setBrowser(self::BROWSER_GALEON);
            if (preg_match('/galeon\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserKonqueror(): bool
    {
        if (stripos($this->_agent, 'Konqueror') !== false) {
            $this->setBrowser(self::BROWSER_KONQUEROR);
            if (preg_match('/Konqueror\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserIcab(): bool
    {
        if (stripos($this->_agent, 'icab') !== false) {
            $this->setBrowser(self::BROWSER_ICAB);
            if (preg_match('/icab[\/\s]([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserOmniWeb(): bool
    {
        if (stripos($this->_agent, 'omniweb') !== false) {
            $this->setBrowser(self::BROWSER_OMNIWEB);
            if (preg_match('/omniweb\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } elseif (preg_match('/Version\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserPhoenix(): bool
    {
        if (stripos($this->_agent, 'Phoenix') !== false) {
            $this->setBrowser(self::BROWSER_PHOENIX);
            if (preg_match('/Phoenix\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserFirebird(): bool
    {
        if (stripos($this->_agent, 'Firebird') !== false) {
            $this->setBrowser(self::BROWSER_FIREBIRD);
            if (preg_match('/Firebird\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserNetscapeNavigator9Plus(): bool
    {
        if (stripos($this->_agent, 'Firefox') !== false && preg_match('/Navigator\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
            return true;
        } elseif (stripos($this->_agent, 'Firefox') === false && preg_match('/Netscape6?\/([^ ]*)/i', $this->_agent, $matches)) {
            $this->setVersion($matches[1]);
            $this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);
            return true;
        }
        return false;
    }

    protected function checkBrowserShiretoko(): bool
    {
        if (preg_match('/Shiretoko\/([^ ]*)/i', $this->_agent, $m)) {
            $this->setVersion($m[1]);
            $this->setBrowser(self::BROWSER_SHIRETOKO);
            return true;
        }
        return false;
    }

    protected function checkBrowserIceCat(): bool
    {
        if (preg_match('/IceCat\/([^ ]*)/i', $this->_agent, $m)) {
            $this->setVersion($m[1]);
            $this->setBrowser(self::BROWSER_ICECAT);
            return true;
        }
        return false;
    }

    protected function checkBrowserNokia(): bool
    {
        if (preg_match("/Nokia([^\/]+)\/([^ SP]+)/i", $this->_agent, $m)) {
            $this->setVersion($m[2]);
            $this->setBrowser(stripos($this->_agent, 'Series60') !== false || strpos($this->_agent, 'S60') !== false ? self::BROWSER_NOKIA_S60 : self::BROWSER_NOKIA);
            $this->setMobile(true);
            return true;
        }
        return false;
    }

    protected function checkBrowserFirefox(): bool
    {
        // Avoid Safari false positive by ensuring 'safari' not present without 'firefox'
        if (stripos($this->_agent, 'Firefox') !== false) {
            $this->setBrowser(self::BROWSER_FIREFOX);
            if (preg_match('/Firefox[\/ ]([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } else {
                $this->setVersion(self::BROWSER_UNKNOWN);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserMozilla(): bool
    {
        if (stripos($this->_agent, 'mozilla') !== false && stripos($this->_agent, 'netscape') === false) {
            $this->setBrowser(self::BROWSER_MOZILLA);
            if (preg_match('/rv:([0-9\.a-b]+)/i', $this->_agent, $m)) {
                $this->setVersion(str_replace('rv:', '', $m[1]));
            } elseif (preg_match('/mozilla\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserLynx(): bool
    {
        if (stripos($this->_agent, 'lynx') !== false) {
            $this->setBrowser(self::BROWSER_LYNX);
            if (preg_match('/Lynx\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserAmaya(): bool
    {
        if (stripos($this->_agent, 'amaya') !== false) {
            $this->setBrowser(self::BROWSER_AMAYA);
            if (preg_match('/Amaya\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserSafari(): bool
    {
        // Safari generally includes Version/x and Safari token, but Chrome also includes Safari token.
        if (stripos($this->_agent, 'Safari') !== false && stripos($this->_agent, 'Chrome') === false && stripos($this->_agent, 'Chromium') === false && stripos($this->_agent, 'OPR') === false && stripos($this->_agent, 'Edg') === false) {
            $this->setBrowser(self::BROWSER_SAFARI);
            if (preg_match('/Version\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } else {
                $this->setVersion(self::BROWSER_UNKNOWN);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowseriPhone(): bool
    {
        if (stripos($this->_agent, 'iPhone') !== false) {
            $this->setBrowser(self::BROWSER_IPHONE);
            $this->setMobile(true);
            if (preg_match('/Version\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } else {
                $this->setVersion(self::BROWSER_UNKNOWN);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowseriPad(): bool
    {
        if (stripos($this->_agent, 'iPad') !== false) {
            $this->setBrowser(self::BROWSER_IPAD);
            $this->setMobile(true);
            if (preg_match('/Version\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } else {
                $this->setVersion(self::BROWSER_UNKNOWN);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowseriPod(): bool
    {
        if (stripos($this->_agent, 'iPod') !== false) {
            $this->setBrowser(self::BROWSER_IPOD);
            $this->setMobile(true);
            if (preg_match('/Version\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } else {
                $this->setVersion(self::BROWSER_UNKNOWN);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserAndroid(): bool
    {
        if (stripos($this->_agent, 'Android') !== false) {
            $this->setBrowser(self::BROWSER_ANDROID);
            $this->setMobile(true);
            if (preg_match('/Android\s+([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } else {
                $this->setVersion(self::BROWSER_UNKNOWN);
            }
            return true;
        }
        return false;
    }

    // ----------------- Platform via CFGP_OS -----------------

    protected function checkPlatform(): void
    {
        if (class_exists('CFGP_ClientHints')) {
            // Encourage headers early in bootstrap:
            add_action('init', function () {
				if (class_exists('CFGP_ClientHints')) {
					CFGP_ClientHints::emitHeaders();
				}
			});

			// Get information (if available)
            $ch = CFGP_ClientHints::detect();
            if (!empty($ch['osName']??null) && $ch['osName'] !== 'Unknown') {
                $this->_platform = $ch['osName'];
            }
        }

        if ($this->_platform === self::BROWSER_UNKNOWN) {
            $this->_platform = class_exists('CFGP_OS') ? CFGP_OS::get($this->getUserAgent()) : self::BROWSER_UNKNOWN;
        }
    }
}

endif;