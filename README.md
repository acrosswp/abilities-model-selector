# AI Model Preferences

> A WordPress plugin that lets administrators pin a specific AI model for each capability type, overriding the WordPress default model selection.

[![WordPress 6.7+](https://img.shields.io/badge/WordPress-6.7%2B-blue.svg)](https://wordpress.org)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![License GPL-2.0+](https://img.shields.io/badge/License-GPL%20v2%2B-red.svg)](http://www.gnu.org/licenses/gpl-2.0.html)

---

## Overview

By default, WordPress picks the first available model from your configured AI connectors. **AI Model Preferences** adds a settings page under **Settings > AI Model Preferences** where you can choose a preferred model for each AI capability type. The chosen model is moved to the top of the WordPress candidate list so it is always selected first.

Supported capability types:

| Capability | Description |
|---|---|
| **Text Generation** | Preferred model for all text-generation tasks |
| **Image Generation** | Preferred model for image-generation tasks |
| **Vision / Multimodal** | Preferred model for vision and multimodal tasks |

---

## Requirements

- **WordPress** 6.7 or higher
- **PHP** 7.4 or higher
- **[WordPress AI plugin](https://wordpress.org/plugins/ai/)** must be installed and active
- At least one AI connector configured via **Settings > Connectors**

---

## Installation

1. Clone or download this repository into your `/wp-content/plugins/` directory:
   ```bash
   cd /wp-content/plugins/
   git clone https://github.com/AcrossWP/abilities-model-selector.git
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JS dependencies and build assets:
   ```bash
   npm install && npm run build
   ```
4. Activate the plugin via the **Plugins** screen in WordPress.
5. Go to **Settings > AI Model Preferences** and choose your preferred model per capability.

---

## Development

### Build commands

| Command | Description |
|---|---|
| `npm run build` | Production build |
| `npm run start` | Watch mode (development) |
| `npm run lint:js` | Lint JavaScript |
| `npm run lint:css` | Lint SCSS/CSS |
| `npm run format` | Auto-format with Prettier |

### Project structure

```
abilities-model-selector/
├── admin/
│   ├── Main.php                  # Admin enqueue + settings link
│   └── partials/
│       └── menu.php              # Settings page registration + render
├── includes/
│   ├── Autoloader.php            # PSR-4 autoloader
│   ├── Model_Preferences.php     # AI preference filter hooks
│   ├── activator.php             # Plugin activation
│   ├── deactivator.php           # Plugin deactivation
│   ├── i18n.php                  # Internationalisation
│   ├── loader.php                # Hook registration
│   └── main.php                  # Core plugin class + bootstrap
├── src/
│   ├── js/
│   │   └── backend.js            # React settings app
│   └── scss/
│       └── backend.scss          # Admin styles
├── build/                        # Compiled assets (git-ignored)
├── languages/                    # Translation files
├── abilities-model-selector.php      # Plugin entry point
├── composer.json
├── package.json
└── webpack.config.js
```

### How the preference filter works

The plugin registers three WordPress AI filter hooks at priority `1000`:

```php
add_filter( 'wpai_preferred_text_models',   [ $model_prefs, 'filter_text_models' ],   1000 );
add_filter( 'wpai_preferred_image_models',  [ $model_prefs, 'filter_image_models' ],  1000 );
add_filter( 'wpai_preferred_vision_models', [ $model_prefs, 'filter_vision_models' ], 1000 );
```

When a preference is saved, `Model_Preferences` reads the `aiam_model_preferences` option, checks the provider is connected, and prepends the preferred `[provider, model_id]` pair to the models array so WordPress selects it first.

### Settings storage

Preferences are stored as a JSON object in the WordPress options table under the key `aiam_model_preferences`:

```json
{
  "text_generation":  "openai::gpt-4o",
  "image_generation": "openai::dall-e-3",
  "vision":           "openai::gpt-4o"
}
```

The option is registered with `show_in_rest: true`, which allows the React settings app to save without a page reload via `POST /wp/v2/settings`.

---

## Frequently Asked Questions

**What if my preferred provider is disconnected?**
The plugin checks provider connectivity before applying the preference. If the provider is disconnected, WordPress falls back to its default model selection. Your saved preference is preserved and will take effect again once the provider is reconnected.

**Will third-party AI providers appear in the dropdowns?**
Yes. Any provider that registers itself with the WordPress AI client registry and exposes model metadata through the standard API will appear automatically.

**Can I set a different model for each capability?**
Yes. Text generation, image generation, and vision are each configured independently.

---

## Changelog

### 0.0.1

- Initial release
- React-powered settings UI under Settings > AI Model Preferences
- Per-capability model preference for text generation, image generation, and vision
- Integrates with WordPress AI plugin preference filter hooks
- REST API support for save without page reload

---

## Credits

| Role | Name / Project |
|---|---|
| **Plugin author** | [okpoojagupta](https://github.com/AcrossWP) |
| **Plugin scaffold** | [WPBoilerplate](https://github.com/WPBoilerplate/wordpress-plugin-boilerplate) — PSR-4 autoloader, Loader pattern, webpack, PHPCS/PHPStan config |
| **AI integration** | [WordPress AI plugin](https://github.com/WordPress/ai) — `wpai_preferred_*_models` filter hooks and `WordPress\AiClient` registry |
| **Build tooling** | [@wordpress/scripts](https://github.com/WordPress/gutenberg/tree/trunk/packages/scripts) — webpack, Babel, SCSS |
| **UI components** | [@wordpress/components](https://github.com/WordPress/gutenberg/tree/trunk/packages/components) — `SelectControl`, `Button`, `Notice`, `Card` |
| **Dependency isolation** | [Mozart by coenjacobs](https://github.com/coenjacobs/mozart) — scoped Composer dependencies |

---

## License

GPL-2.0+. See [LICENSE.txt](LICENSE.txt) for details.
