# Geo Controller for WordPress  
*(formerly known as CF Geo Plugin)*  

[![WordPress 5.2+ Compatible](https://plugintests.com/plugins/cf-geoplugin/wp-badge.svg)](https://plugintests.com/plugins/cf-geoplugin/latest)  
[![PHP 7+ Compatible](https://plugintests.com/plugins/cf-geoplugin/php-badge.svg)](https://plugintests.com/plugins/cf-geoplugin/latest)  
![Geo Controller version](https://img.shields.io/badge/Geo%20Controller-8.x-green.svg)  

---

## 🌍 What is Geo Controller?  
The **Geo Controller** is a **free WordPress plugin** that allows you to attach content, geographic information, and Google Maps to posts, pages, widgets, and custom templates by detecting the **user’s IP address**.  

With just a few simple **shortcodes, PHP functions, or JavaScript calls**, you can:  
- Display geolocation-specific content to your visitors.  
- Control menus, banners, CSS, and JavaScript based on visitor location.  
- Embed Google Maps with customizable defaults.  
- Improve SEO and conversion rates on landing pages.  

⚡ **Bonus**: You can also set a **default geographic location** for your entire WordPress site.  

---

## ✨ Key Features  
- 🔹 Shortcodes for quick integration anywhere in WordPress.  
- 🔹 PHP and JavaScript support for developers.  
- 🔹 Google Maps integration with customizable zoom, markers, and info windows.  
- 🔹 Geo-based navigation menu control.  
- 🔹 Dynamic CSS/JS based on location.  
- 🔹 REST API endpoints for advanced integrations.  
- 🔹 SEO redirection and geotags support.  
- 🔹 Spam and proxy protection (TOR, IP blacklist, etc).  
- 🔹 GDPR-friendly, with Privacy Policy, Disclaimer, and Accessibility Statement included.  

---

## 🚀 Installation  
1. Download or clone this repository.  
2. Upload the plugin folder to your WordPress site under:  
   ```
   /wp-content/plugins/
   ```  
3. Activate the plugin from the **WordPress Admin → Plugins** page.  
4. Configure the plugin under **Settings → Geo Controller**.  

For the official release version, please download it from the **WordPress.org Plugin Directory**:  
👉 https://wordpress.org/plugins/cf-geoplugin/  

---

## 🛠 Usage  

### Shortcodes Example  
```php
[cfgeo_map latitude="40.7128" longitude="-74.0060" zoom="12"]
```

### PHP Example  
```php
<?php
$data = CFGP_U::api();
echo 'Your country is: ' . esc_html($data->country);
```
### JavaScript Example  
```javascript
cfgeo.get(function(data) {
    alert("Hello visitor from " + data.country);
});
```

More examples and documentation:  
👉 [Official Documentation](https://wpgeocontroller.com/documentation/)  

---

## 📦 Add-ons  

If you need GPS support, try our free **[GPS Add-on](https://github.com/CreativForm/wordpress-geoplugin-gps)**.  
You can download it directly from the repository.  

---

## 🤝 Contributing  
We welcome contributions! 🎉  

- Fork this repository.  
- Create a new branch for your fix or feature.  
- Submit a pull request.  

If you notice any errors, bugs, or have suggestions for improvement, feel free to open an issue.  
All contributors will be credited inside the plugin — become part of the **Geo Controller family**.  

---

## 📧 Contact  
- Website: [wpgeocontroller.com](https://wpgeocontroller.com)  
- WordPress.org Plugin Page: [cf-geoplugin](https://wordpress.org/plugins/cf-geoplugin/)  
- Email: info@wpgeocontroller.com  
- Company: [INFINITUM FORM®](https://infinitumform.com)  

---

## 📜 License  
This plugin is released under the **GNU General Public License v2 (or later)**.  

Copyright © 2014–present [Ivijan-Stefan Stipic](mailto:info@wpgeocontroller.com)  
Developed and maintained by [INFINITUM FORM®](https://infinitumform.com).  
