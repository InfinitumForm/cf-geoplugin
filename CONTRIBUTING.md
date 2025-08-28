# Contributing to Geo Controller for WordPress

Thank you for your interest in contributing to **Geo Controller for WordPress**! 🙌  
As a community-driven project, your help improves documentation, adds features, and fixes bugs — making this plugin better for everyone.

---

## 📚 Where to Start

| Resource | Link |
|----------|------|
| GitHub Repository | https://github.com/InfinitumForm/cf-geoplugin |
| Official Documentation | https://wpgeocontroller.com/documentation/ |
| WordPress Plugin Page | https://wordpress.org/plugins/cf-geoplugin/ |

---

## 🛠 Ways to Contribute

### 1. Bug Reports & Feature Requests
Encountered an issue or want a new feature?  
Please open an [issue](https://github.com/InfinitumForm/cf-geoplugin/issues) on GitHub with:
- A clear, descriptive title  
- Steps to reproduce  
- Screenshots or error messages (if applicable)  
- Environment details (WordPress version, PHP version, etc.)  

### 2. Fixes, Enhancements & New Features
Want to make improvements? We welcome them!  

1. **Fork** the repository  
2. **Create a new branch**  
   ```bash
   git checkout -b feature/your-feature
   ```
3. **Make your changes**  
   - Follow WordPress coding conventions (PSR-12, sanitization, escaping, etc.)  
   - Document new functionality (inline comments, README updates)  
4. **Test your changes** thoroughly  
5. **Submit a Pull Request** including:  
   - A summary of what you changed  
   - Why it’s needed (issue reference or enhancement description)  
   - Screenshots or code examples (optional)  

All contributors will be credited in the plugin — thank you for joining the **Geo Controller family**!  

---

## ⚙️ Local Development Setup

1. Clone the repository:  
   ```bash
   git clone https://github.com/InfinitumForm/cf-geoplugin.git
   cd cf-geoplugin
   ```
2. Activate the plugin inside a local WordPress installation.  
3. Enable debug mode if necessary:  
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
4. Test with:  
   - Latest WordPress version  
   - PHP versions (7.x, 8.x)  
   - Popular plugins such as WooCommerce, Yoast SEO, etc.  

---

## 🧑‍💻 Code Style

- Follow **PSR-12** coding standard  
- Always escape output (`esc_html()`, `esc_attr()`, `wp_kses_post()`, etc.)  
- Use proper translation functions: `__()`, `_e()`, `esc_html__()`, etc.  
- Update **README.md** and documentation when adding new features  

---

## 💬 Support & Communication

- Use [GitHub Issues](https://github.com/InfinitumForm/cf-geoplugin/issues) for bugs and feature requests  
- Use [WordPress.org Support forum](https://wordpress.org/support/plugin/cf-geoplugin/) for community support  
- Discussions and proposals can also be made through GitHub Discussions (if enabled)  

---

## 📜 Code of Conduct

By contributing, you agree to abide by the [Contributor Covenant Code of Conduct](https://www.contributor-covenant.org/version/2/1/code_of_conduct/).  

---

Thank you for helping us make **Geo Controller** even better!  
— The Geo Controller Dev Team  
