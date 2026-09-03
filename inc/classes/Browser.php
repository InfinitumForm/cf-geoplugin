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
    const BROWSER_EDGE         = 'Microsoft Edge';
    const BROWSER_OPERA        = 'Opera';
    const BROWSER_OPERA_MINI   = 'Opera Mini';
    const BROWSER_WEBTV        = 'WebTV';
    const BROWSER_IE           = 'Internet Explorer';
    const BROWSER_POCKET_IE    = 'Pocket Internet Explorer';
    const BROWSER_KONQUEROR    = 'Konqueror';
    const BROWSER_ICAB         = 'iCab';
    const BROWSER_OMNIWEB      = 'OmniWeb';
    const BROWSER_FIREBIRD     = 'Firebird';
    const BROWSER_FIREFOX      = 'Firefox';
    const BROWSER_ICEWEASEL    = 'Iceweasel';
    const BROWSER_SHIRETOKO    = 'Shiretoko';
    const BROWSER_MOZILLA      = 'Mozilla';
    const BROWSER_AMAYA        = 'Amaya';
    const BROWSER_LYNX         = 'Lynx';
    const BROWSER_SAFARI       = 'Safari';
    const BROWSER_IPHONE       = 'iPhone';
    const BROWSER_IPOD         = 'iPod';
    const BROWSER_IPAD         = 'iPad';
    const BROWSER_CHROME       = 'Chrome';
    const BROWSER_BRAVE        = 'Brave';
    const BROWSER_VIVALDI      = 'Vivaldi';
    const BROWSER_OPERA_TOUCH  = 'Opera Touch';
    const BROWSER_ANDROID      = 'Android';
    const BROWSER_GOOGLEBOT    = 'GoogleBot';
    const BROWSER_SLURP        = 'Yahoo! Slurp';
    const BROWSER_W3CVALIDATOR = 'W3C Validator';
    const BROWSER_BLACKBERRY   = 'BlackBerry';
    const BROWSER_ICECAT       = 'IceCat';
    const BROWSER_NOKIA_S60    = 'Nokia S60 OSS Browser';
    const BROWSER_NOKIA        = 'Nokia Browser';
    const BROWSER_MSN          = 'MSN Browser';
    const BROWSER_MSNBOT       = 'MSN Bot';
    const BROWSER_WEBOS        = 'Web OS Browser';
    const BROWSER_FB           = 'Facebook Browser';
    const BROWSER_INSTAGRAM    = 'Instagram Browser';
    const BROWSER_TIKTOK       = 'TikTok Browser';
    const BROWSER_SAMSUNG_INTERNET = 'Samsung Internet';
    const BROWSER_CHROME_IOS   = 'Chrome iOS';
    const BROWSER_FIREFOX_IOS  = 'Firefox iOS';
    const BROWSER_OPERA_IOS    = 'Opera iOS';
    const BROWSER_YANDEX       = 'Yandex Browser';
    const BROWSER_UC           = 'UC Browser';
    const BROWSER_HUAWEI       = 'Huawei Browser';
    const BROWSER_MI           = 'Mi Browser';
    const BROWSER_AMAZON_SILK  = 'Amazon Silk';
    const BROWSER_PUFFIN       = 'Puffin';
    const BROWSER_DUCKDUCKGO   = 'DuckDuckGo Browser';
    const BROWSER_GALEON       = 'Galeon';
    const BROWSER_NETPOSITIVE  = 'NetPositive';
    const BROWSER_NETSCAPE_NAVIGATOR = 'Netscape Navigator';
    const BROWSER_PHOENIX      = 'Phoenix';
    const BROWSER_UNKNOWN      = 'unknown';

    // Platforms (kept for BC where used externally)
    const PLATFORM_WINDOWS     = 'Windows';
    const PLATFORM_WINDOWS_CE  = 'Windows CE';
    const PLATFORM_APPLE       = 'Apple';
    const PLATFORM_LINUX       = 'Linux';
    const PLATFORM_OS2         = 'OS/2';
    const PLATFORM_BEOS        = 'BeOS';
    const PLATFORM_IPHONE      = 'iPhone';
    const PLATFORM_IPOD        = 'iPod';
    const PLATFORM_IPAD        = 'iPad';
    const PLATFORM_BLACKBERRY  = 'BlackBerry';
    const PLATFORM_NOKIA       = 'Nokia';
    const PLATFORM_FREEBSD     = 'FreeBSD';
    const PLATFORM_OPENBSD     = 'OpenBSD';
    const PLATFORM_NETBSD      = 'NetBSD';
    const PLATFORM_SUNOS       = 'SunOS';
    const PLATFORM_OPENSOLARIS = 'OpenSolaris';
    const PLATFORM_ANDROID     = 'Android';
    const PLATFORM_WEBOS       = 'webOS';

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
    private $_ch_mobile       = null; // Sec-CH-UA-Mobile (?0 or ?1)
    private $_client_hints    = [];
    private $_use_client_hints = true;

    /**
     * Singleton factory through CFGP_Cache (kept for BC).
     */
    public static function instance($useragent = '')
    {
        $use_client_hints = ($useragent === '');
        $effective_agent  = $use_client_hints ? self::current_user_agent() : (string) $useragent;
        $cache_key        = self::class . '-' . hash('sha256', $effective_agent . '|' . ($use_client_hints ? self::client_hints_fingerprint() : ''));
        $instance         = null;

        if (class_exists('CFGP_Cache') && method_exists('CFGP_Cache', 'get')) {
            $instance = CFGP_Cache::get($cache_key);
        }

        if ($instance instanceof self) {
            // Callers may call setUserAgent(); never expose the cached object itself.
            return clone $instance;
        }

        if (!($instance instanceof self)) {
            $instance = new self($useragent);
            if (class_exists('CFGP_Cache') && method_exists('CFGP_Cache', 'set')) {
                CFGP_Cache::set($cache_key, clone $instance);
            }
        }

        return $instance;
    }

    /**
     * Private ctor; prefer instance().
     */
    private function __construct($useragent = '')
    {
        if ($useragent !== '') {
            $this->reset(false);
            $this->_agent = (string) $useragent;
            $this->determine();
        } else {
            $this->reset(true);
            $this->determine();
        }
    }

    /**
     * Reset state from globals and Client Hints.
     */
    public function reset($use_client_hints = true)
    {
        $this->_use_client_hints = (bool) $use_client_hints;
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

        // Client Hints describe only the current request, never a supplied historic UA.
        $this->_ch_brands_raw = null;
        $this->_ch_brands = [];
        $this->_ch_platform = null;
        $this->_ch_platform_ver = null;
        $this->_ch_mobile = null;
        $this->_client_hints = [];

        if ($this->_use_client_hints && class_exists('CFGP_ClientHints')) {
            $hints = CFGP_ClientHints::detect();
            if (is_array($hints)) {
                $this->_ch_brands_raw = isset($hints['brands']) ? $hints['brands'] : null;
                $this->_ch_brands = $this->parseBrands($this->_ch_brands_raw);
                $this->_ch_platform = isset($hints['platform']) ? $hints['platform'] : null;
                $this->_ch_platform_ver = isset($hints['platformVersion']) ? $hints['platformVersion'] : null;
                $this->_ch_mobile = isset($hints['mobile']) && $hints['mobile'] === true ? '?1' : (isset($hints['mobile']) && $hints['mobile'] === false ? '?0' : null);
                if (!empty($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) && is_string($_SERVER['HTTP_SEC_CH_UA_PLATFORM'])) {
                    $this->_client_hints = $hints;
                }
            }
        }
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

    public function setVersion($version)
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

    public function setAolVersion($version)
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

    public function setAol($isAol)
    {
        $this->_is_aol = (bool) $isAol;
    }

    protected function setMobile($value = true)
    {
        if ($value && $this->_ch_mobile === '?0') {
            return;
        }

        $this->_is_mobile = (bool) $value;
    }

    protected function setRobot($value = true)
    {
        $this->_is_robot = (bool) $value;
    }

    public function getUserAgent(): string
    {
        return $this->_agent;
    }

    public function setUserAgent($agent_string)
    {
        $this->reset(false);
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

    protected function determine()
    {
        $this->checkMobile();
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

        // Client-Hints provide reliable identification for otherwise indistinguishable forks.
        if ($this->checkChromiumBrands()) {
            return true;
        }

        // Specific browser tokens must be checked before their shared engines.
        return (
            $this->checkBrowserFacebook()
            || $this->checkBrowserInstagram()
            || $this->checkBrowserTikTok()
            || $this->checkBrowserSamsungInternet()
            || $this->checkBrowserEdge()
            || $this->checkBrowserOpera()
            || $this->checkBrowserYandex()
            || $this->checkBrowserVivaldi()
            || $this->checkBrowserDuckDuckGo()
            || $this->checkBrowserUC()
            || $this->checkBrowserHuawei()
            || $this->checkBrowserMi()
            || $this->checkBrowserAmazonSilk()
            || $this->checkBrowserPuffin()
            || $this->checkBrowserFirefoxIOS()
            || $this->checkBrowserFirefox()
            || $this->checkBrowserChromeIOS()
            || $this->checkBrowserChrome()
            || $this->checkBrowserAndroid()
            || $this->checkBrowseriPad()
            || $this->checkBrowseriPod()
            || $this->checkBrowseriPhone()
            || $this->checkBrowserSafari()
            || $this->checkBrowserWebOS()
            || $this->checkBrowserWebTv()
            || $this->checkBrowserInternetExplorer()
            || $this->checkBrowserGaleon()
            || $this->checkBrowserNetscapeNavigator9Plus()
            || $this->checkBrowserOmniWeb()
            || $this->checkBrowserBlackBerry()
            || $this->checkBrowserNokia()
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

        // Only brands that identify a distinct browser belong here. Chromium and
        // Chrome brands intentionally fall through to the UA parser.
        $order = [
            'Brave'    => self::BROWSER_BRAVE,
            'Microsoft Edge' => self::BROWSER_EDGE,
            'Edge'     => self::BROWSER_EDGE,
            'Vivaldi'  => self::BROWSER_VIVALDI,
            'Opera'    => self::BROWSER_OPERA,
        ];

        foreach ($order as $brand => $label) {
            foreach ($this->_ch_brands as $b => $ver) {
                if (strcasecmp($b, $brand) === 0) {
                    $this->setBrowser($label);
                    $this->setVersion($this->edgeVersionFromUA() ?: ($ver ?: $this->extractChromiumVersionFromUA()));
                    return true;
                }
            }
        }

        return false;
    }

    private function parseBrands($raw): array
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

    private function stripQuotes($v)
    {
        if ($v === null) return null;
        $v = trim($v);
        if ($v === '') return null;
        if ($v[0] === '"' && substr($v, -1) === '"') {
            return substr($v, 1, -1);
        }
        return $v;
    }

    private function server(string $key)
    {
        return isset($_SERVER[$key]) && is_string($_SERVER[$key]) ? $_SERVER[$key] : null;
    }

    /**
     * @return string
     */
    private static function current_user_agent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) && is_string($_SERVER['HTTP_USER_AGENT'])
            ? $_SERVER['HTTP_USER_AGENT']
            : '';
    }

    /**
     * Cache only request-scoped hints that affect browser or mobile detection.
     *
     * @return string
     */
    private static function client_hints_fingerprint()
    {
        $keys = [
            'HTTP_SEC_CH_UA_FULL_VERSION_LIST',
            'HTTP_SEC_CH_UA',
            'HTTP_SEC_CH_UA_PLATFORM',
            'HTTP_SEC_CH_UA_PLATFORM_VERSION',
            'HTTP_SEC_CH_UA_MOBILE',
        ];
        $values = [];

        foreach ($keys as $key) {
            $values[] = isset($_SERVER[$key]) && is_string($_SERVER[$key]) ? $_SERVER[$key] : '';
        }

        return implode('|', $values);
    }

    /**
     * Respect a valid Client Hint; otherwise recognize established mobile UA tokens.
     */
    private function checkMobile()
    {
        if ($this->_ch_mobile === '?1') {
            $this->_is_mobile = true;
            return;
        }

        if ($this->_ch_mobile === '?0') {
            $this->_is_mobile = false;
            return;
        }

        if (preg_match('/(?:\bMobile\b|Windows Phone|Android|HarmonyOS|iPhone|iPad|iPod|KaiOS|Opera Mini|Opera Touch|EdgA|EdgiOS|CriOS|FxiOS|SamsungBrowser|UCBrowser|HuaweiBrowser|MiuiBrowser|Silk|Puffin|BlackBerry|Ubuntu Touch|FBAV|Instagram|TikTok)/i', $this->_agent)) {
            $this->_is_mobile = true;
        }
    }

    /**
     * @return string|false
     */
    private function edgeVersionFromUA()
    {
        if (preg_match('/\b(?:EdgiOS|EdgA|Edg|Edge)\/([0-9\.]+)/i', $this->_agent, $match)) {
            return $match[1];
        }

        return false;
    }

    // ----------------- Browser checks (UA fallback) -----------------

    protected function checkBrowserWebOS(): bool
    {
        if (preg_match('/(webos|wos)/i', $this->_agent)) {
            if (preg_match("/FBAV\/([0-9A-Z\.]+)(\;|\s){1}/", $this->_agent, $aversion)) {
                $this->setVersion($aversion[1]);
                $this->setBrowser(self::BROWSER_FB);
                return true;
            } elseif (preg_match('/(?:WEBOS23\s|webos\s)([0-9A-Z\.]+)(?:;|\s)/i', $this->_agent, $aversion)) {
                $this->setVersion($aversion[2]);
                $this->setBrowser(self::BROWSER_WEBOS);
                return true;
            }
        }
        return false;
    }

    protected function checkBrowserEdge(): bool
    {
        // Edg/ (desktop), EdgA/ (Android), EdgiOS/ and legacy Edge/.
        if (($version = $this->edgeVersionFromUA()) !== false) {
            $this->setVersion($version);
            $this->setBrowser(self::BROWSER_EDGE);
            return true;
        }
        return false;
    }

    protected function checkBrowserFacebook(): bool
    {
        $match = [];

        if (preg_match('/\bFBAV\/([0-9\.]+)/i', $this->_agent, $match) || preg_match('/\bFBAN\/[A-Za-z0-9._-]+/i', $this->_agent)) {
            $this->setBrowser(self::BROWSER_FB);
            $this->setMobile(true);
            if (!empty($match[1])) {
                $this->setVersion($match[1]);
            }
            return true;
        }

        return false;
    }

    protected function checkBrowserInstagram(): bool
    {
        if (preg_match('/\bInstagram\s+([0-9\.]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_INSTAGRAM);
            $this->setVersion($match[1]);
            $this->setMobile(true);
            return true;
        }

        return false;
    }

    protected function checkBrowserTikTok(): bool
    {
        $match = [];

        if (preg_match('/\bTikTok\/([0-9\.]+)/i', $this->_agent, $match) || preg_match('/\bBytedanceWebview(?:\/([0-9\.]+))?/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_TIKTOK);
            if (!empty($match[1])) {
                $this->setVersion($match[1]);
            }
            $this->setMobile(true);
            return true;
        }

        return false;
    }

    protected function checkBrowserSamsungInternet(): bool
    {
        if (preg_match('/\bSamsungBrowser\/([0-9\.]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_SAMSUNG_INTERNET);
            $this->setVersion($match[1]);
            $this->setMobile(true);
            return true;
        }

        return false;
    }

    protected function checkBrowserYandex(): bool
    {
        if (preg_match('/\bYaBrowser\/([0-9\.]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_YANDEX);
            $this->setVersion($match[1]);
            return true;
        }

        return false;
    }

    protected function checkBrowserVivaldi(): bool
    {
        if (preg_match('/\bVivaldi\/([0-9\.]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_VIVALDI);
            $this->setVersion($match[1]);
            return true;
        }

        return false;
    }

    protected function checkBrowserDuckDuckGo(): bool
    {
        if (preg_match('/\b(?:DuckDuckGo|Ddg)\/([0-9\.]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_DUCKDUCKGO);
            $this->setVersion($match[1]);
            return true;
        }

        return false;
    }

    protected function checkBrowserUC(): bool
    {
        if (preg_match('/\b(?:UCBrowser|UCWEB)\/([0-9\.]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_UC);
            $this->setVersion($match[1]);
            $this->setMobile(true);
            return true;
        }

        return false;
    }

    protected function checkBrowserHuawei(): bool
    {
        if (preg_match('/\bHuaweiBrowser\/([0-9\.]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_HUAWEI);
            $this->setVersion($match[1]);
            $this->setMobile(true);
            return true;
        }

        return false;
    }

    protected function checkBrowserMi(): bool
    {
        if (preg_match('/\bMiuiBrowser\/([0-9A-Za-z\.\-]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_MI);
            $this->setVersion($match[1]);
            $this->setMobile(true);
            return true;
        }

        return false;
    }

    protected function checkBrowserAmazonSilk(): bool
    {
        if (preg_match('/\bSilk\/([0-9\.]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_AMAZON_SILK);
            $this->setVersion($match[1]);
            $this->setMobile(true);
            return true;
        }

        return false;
    }

    protected function checkBrowserPuffin(): bool
    {
        if (preg_match('/\bPuffin\/([0-9\.]+)/i', $this->_agent, $match)) {
            $this->setBrowser(self::BROWSER_PUFFIN);
            $this->setVersion($match[1]);
            $this->setMobile(true);
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
        if (preg_match('/\bOPiOS\/([0-9\.]+)/i', $this->_agent, $m)) {
            $this->setBrowser(self::BROWSER_OPERA_IOS);
            $this->setVersion($m[1]);
            $this->setMobile(true);
            return true;
        }
        if (preg_match('/\bOPT\/([0-9\.]+)/i', $this->_agent, $m)) {
            $this->setBrowser(self::BROWSER_OPERA_TOUCH);
            $this->setVersion($m[1]);
            $this->setMobile(true);
            return true;
        }
        if (stripos($this->_agent, 'OPR/') !== false || preg_match('/\bOpera(?:\/|\s)/i', $this->_agent)) {
            $this->setBrowser(self::BROWSER_OPERA);
            if (preg_match('/(?:OPR|Opera)\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            } elseif (preg_match('/Version\/([0-9\.]+)/i', $this->_agent, $m)) {
                $this->setVersion($m[1]);
            }
            return true;
        }
        return false;
    }

    protected function checkBrowserChrome(): bool
    {
        if (preg_match('/\bChrome\/([0-9\.]+)/i', $this->_agent, $m)) {
            $this->setBrowser(self::BROWSER_CHROME);
            $this->setVersion($m[1]);
            return true;
        }

        foreach ($this->_ch_brands as $brand => $version) {
            if (strcasecmp($brand, 'Google Chrome') === 0 || strcasecmp($brand, 'Chromium') === 0) {
                $this->setBrowser(self::BROWSER_CHROME);
                $this->setVersion($version ?: self::BROWSER_UNKNOWN);
                return true;
            }
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

    protected function checkBrowserFirefoxIOS(): bool
    {
        if (preg_match('/\bFxiOS\/([0-9\.]+)/i', $this->_agent, $m)) {
            $this->setBrowser(self::BROWSER_FIREFOX_IOS);
            $this->setVersion($m[1]);
            $this->setMobile(true);
            return true;
        }

        return false;
    }

    protected function checkBrowserChromeIOS(): bool
    {
        if (preg_match('/\bCriOS\/([0-9\.]+)/i', $this->_agent, $m)) {
            $this->setBrowser(self::BROWSER_CHROME_IOS);
            $this->setVersion($m[1]);
            $this->setMobile(true);
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
        if (
            stripos($this->_agent, 'Android') !== false
            && preg_match('/\bVersion\/([0-9\.]+)/i', $this->_agent, $m)
            && stripos($this->_agent, 'Safari') !== false
        ) {
            $this->setBrowser(self::BROWSER_ANDROID);
            $this->setMobile(true);
            $this->setVersion($m[1]);
            return true;
        }
        return false;
    }

    // ----------------- Platform via CFGP_OS -----------------

    protected function checkPlatform()
    {
        if ($this->_use_client_hints && class_exists('CFGP_ClientHints')) {
            // Encourage headers early in bootstrap:
            add_action('init', function () {
				if (class_exists('CFGP_ClientHints')) {
					CFGP_ClientHints::emitHeaders();
				}
			});

            if (
                !empty($this->_client_hints['osName'])
                && $this->_client_hints['osName'] !== 'Unknown'
                && (!isset($this->_client_hints['platform']) || $this->_client_hints['platform'] !== 'Windows' || !empty($_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION']))
            ) {
                $this->_platform = $this->_client_hints['osName'];
            }
        }

        if ($this->_platform === self::BROWSER_UNKNOWN) {
            $this->_platform = class_exists('CFGP_OS') ? CFGP_OS::get($this->getUserAgent()) : self::BROWSER_UNKNOWN;
        }
    }
}

endif;
