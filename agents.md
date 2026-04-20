# AcrossAI Model Manager — AI Agent Instructions

## Overview

**AcrossAI Model Manager** is a WordPress plugin that gives site administrators control over which AI model WordPress uses for each capability type (text generation, image generation, vision/multimodal). It integrates with the [WordPress AI plugin](https://wordpress.org/plugins/ai/) by hooking into its model-preference filters and exposing a React-powered settings page under **Settings > AcrossAI Model Manager**.

- **Plugin slug**: `acrossai-model-manager`
- **Text domain**: `acrossai-model-manager`
- **Version**: `0.0.1` (defined as `ACAI_MODEL_MANAGER_VERSION` constant in `acrossai-model-manager.php`)
- **PHP namespace root**: `AcrossAI_Model_Manager\`
- **Constant prefix**: `ACAI_MODEL_MANAGER_`
- **Option key**: `acai_model_manager_preferences`
- **Legacy option key** (migration source): `aiam_model_preferences`
- **Settings page slug**: `acrossai-model-manager`
- **Required plugin dependency**: WordPress AI plugin (`WordPress\AiClient` classes must be present)

---

## Requirements

| Requirement | Minimum | Recommended |
|---|---|---|
| PHP | 7.4 (enforced by Composer) | 8.0+ |
| WordPress | 4.9.1 | 6.7+ |
| Node.js | 18 | 20 |

> **CRITICAL**: `composer.json` enforces `"php": ">=7.4"`. Composer will refuse to install on older PHP. Always run `php -v` before starting work.

---

## Directory Structure

```
acrossai-model-manager/
├── acrossai-model-manager.php    # Plugin bootstrap — constants, hooks, runs Main
├── uninstall.php                 # Runs on plugin deletion
├── index.php                     # Security file (silence)
│
├── includes/                     # Core plugin classes (namespace: AcrossAI_Model_Manager\Includes\)
│   ├── Main.php                  # Singleton orchestrator — boots everything
│   ├── Loader.php                # Deferred hook registration queue (singleton)
│   ├── Autoloader.php            # PSR-4 autoloader for AcrossAI_Model_Manager\ namespace
│   ├── Activator.php             # Static activate() — runs on plugin activation
│   ├── Deactivator.php           # Static deactivate() — runs on plugin deactivation
│   ├── I18n.php                  # Textdomain loader (no-op; WP 4.6+ handles it)
│   ├── Model_Preferences.php     # Filters wpai_preferred_* hooks with saved prefs
│   └── index.php                 # Security file
│
├── admin/                        # Admin-area classes (namespace: AcrossAI_Model_Manager\Admin\)
│   ├── Main.php                  # Enqueues assets; provides model data to JS
│   ├── index.php
│   └── partials/                 # (namespace: AcrossAI_Model_Manager\Admin\Partials\)
│       ├── Menu.php              # Settings page: add_menu, register_setting, render_page
│       └── index.php
│
├── src/                          # Source files (not distributed)
│   ├── js/
│   │   └── backend.js            # React settings app source
│   ├── scss/
│   │   └── backend.scss          # Admin styles source
│   └── media/
│       ├── bookshelf.webp
│       └── purple-sunset.webp
│
├── build/                        # Compiled output (auto-generated, IS distributed)
│   ├── js/
│   │   ├── backend.js            # Minified React bundle (~2.8 KB)
│   │   └── backend.asset.php     # WP asset dependencies + content hash
│   ├── css/
│   │   ├── backend.css           # Compiled styles (~1.2 KB)
│   │   ├── backend-rtl.css       # RTL variant
│   │   └── backend.asset.php
│   └── media/
│       ├── bookshelf.webp
│       └── purple-sunset.webp
│
├── languages/
│   ├── acrossai-model-manager.pot
│   └── index.php
│
├── .wordpress-org/               # WordPress.org assets (NOT distributed)
│   ├── banner-1544x500.jpeg
│   ├── banner-772x250.jpeg
│   ├── icon-128x128.png
│   └── icon-256x256.png
│
├── .github/
│   └── workflows/
│       ├── build-zip.yml                  # Builds ZIP on push to main
│       └── wordpress-plugin-deploy.yml    # Deploys to WP.org on tag push
│
├── composer.json                 # PHP deps + PSR-4 autoload config
├── package.json                  # npm scripts + JS deps
├── webpack.config.js             # Custom Webpack config extending @wordpress/scripts
├── phpcs.xml.dist                # PHPCS ruleset (WordPress coding standards)
├── phpstan.neon.dist             # PHPStan static analysis config
├── .distignore                   # Files excluded from plugin-zip
├── .wp-env.json                  # wp-env local environment config
└── agents.md                     # This file
```

---

## Plugin Constants

All defined in `acrossai-model-manager.php` or `includes/Main.php::define_constants()`.

| Constant | Value | Where defined |
|---|---|---|
| `ACAI_MODEL_MANAGER_PLUGIN_FILE` | `__FILE__` (main plugin file path) | `acrossai-model-manager.php` |
| `ACAI_MODEL_MANAGER_VERSION` | `'0.0.1'` | `acrossai-model-manager.php` |
| `ACAI_MODEL_MANAGER_PLUGIN_BASENAME` | `plugin_basename(__FILE__)` | `includes/Main.php` |
| `ACAI_MODEL_MANAGER_PLUGIN_PATH` | `plugin_dir_path(__FILE__)` | `includes/Main.php` |
| `ACAI_MODEL_MANAGER_PLUGIN_URL` | `plugin_dir_url(__FILE__)` | `includes/Main.php` |
| `ACAI_MODEL_MANAGER_PLUGIN_NAME_SLUG` | `'acrossai-model-manager'` | `includes/Main.php` |
| `ACAI_MODEL_MANAGER_PLUGIN_NAME` | `'AcrossAI Model Manager'` | `includes/Main.php` |

> **IMPORTANT**: Never call `get_plugin_data()` inside `define_constants()` or anywhere that runs before `init`. It translates plugin header strings internally which triggers `_load_textdomain_just_in_time` too early (WP 6.7+ notice). The version is already a plain constant — use `ACAI_MODEL_MANAGER_VERSION` directly.

---

## Execution Flow

```
acrossai-model-manager.php
  └─ defines ACAI_MODEL_MANAGER_PLUGIN_FILE, ACAI_MODEL_MANAGER_VERSION
  └─ registers activation/deactivation hooks
  └─ require includes/Main.php
  └─ acai_model_manager_run()
       └─ Main::instance()           ← singleton created
            ├─ define_constants()
            ├─ require includes/Autoloader.php (manually)
            ├─ register_autoloader() → spl_autoload_register()
            ├─ load_composer_dependencies() → vendor/autoload.php
            ├─ load_dependencies()   → Loader::instance()
            └─ load_hooks()
                 ├─ apply_filters('acrossai_model_manager_load', true)
                 ├─ define_admin_hooks()
                 └─ define_plugin_hooks()
       └─ add_action('plugins_loaded', [$plugin, 'run'], 0)
            └─ Loader::run() → registers all queued actions & filters with WP
```

### define_admin_hooks() registers (via Loader queue):
| Hook | Component | Method | Priority |
|---|---|---|---|
| `admin_enqueue_scripts` | `Admin\Main` | `enqueue_styles` | 10 |
| `admin_enqueue_scripts` | `Admin\Main` | `enqueue_scripts` | 10 |
| `admin_menu` | `Admin\Partials\Menu` | `add_menu` | 10 |
| `init` | `Admin\Partials\Menu` | `register_settings` | 10 |
| `plugin_action_links_{BASENAME}` | `Admin\Main` | `add_settings_link` | 10 |

### define_plugin_hooks() registers (directly, NOT via Loader — intentionally early):
| Hook | Component | Method | Priority |
|---|---|---|---|
| `wpai_preferred_text_models` | `Model_Preferences` | `filter_text_models` | 1111 |
| `wpai_preferred_image_models` | `Model_Preferences` | `filter_image_models` | 1111 |
| `wpai_preferred_vision_models` | `Model_Preferences` | `filter_vision_models` | 1111 |

> Priority 1111 is intentionally high to ensure this plugin's preference runs after any other plugin that may also filter these hooks.

---

## Class Reference

### `AcrossAI_Model_Manager\Includes\Main` — `includes/Main.php`

Final singleton class. Bootstraps the entire plugin.

| Method | Visibility | Description |
|---|---|---|
| `instance()` | public static | Returns/creates singleton |
| `__construct()` | public | Runs full boot sequence |
| `define_constants()` | private | Defines all `ACAI_MODEL_MANAGER_*` constants |
| `define($name, $value)` | private | Safe `define()` wrapper (phpcs:ignore comment present) |
| `register_autoloader()` | private | Creates `Autoloader` and registers via `spl_autoload_register` |
| `load_composer_dependencies()` | private | Loads `vendor/autoload.php`; boots `WPBoilerplate\RegisterBlocks` if present |
| `load_dependencies()` | private | Creates `Loader::instance()` |
| `load_hooks()` | public | Gates all hooks behind `acrossai_model_manager_load` filter |
| `define_admin_hooks()` | private | Queues all admin hooks via Loader |
| `define_plugin_hooks()` | private | Registers AI preference filters directly |
| `run()` | public | Called on `plugins_loaded`; executes `Loader::run()` |
| `get_plugin_name()` | public | Returns `'acrossai-model-manager'` |
| `get_version()` | public | Returns plugin version string |
| `get_loader()` | public | Returns `Loader` instance |
| `get_autoloader()` | public | Returns `Autoloader` instance |

---

### `AcrossAI_Model_Manager\Includes\Loader` — `includes/Loader.php`

Singleton. Collects all hook registrations and fires them in bulk on `run()`.

| Method | Visibility | Description |
|---|---|---|
| `instance()` | public static | Returns/creates singleton |
| `add_action($hook, $component, $callback, $priority, $accepted_args)` | public | Queues an action |
| `add_filter($hook, $component, $callback, $priority, $accepted_args)` | public | Queues a filter |
| `run()` | public | Calls `add_action`/`add_filter` for every queued item |

Each queue entry shape: `['hook', 'component', 'callback', 'priority', 'accepted_args']`

---

### `AcrossAI_Model_Manager\Includes\Autoloader` — `includes/Autoloader.php`

PSR-4 autoloader for the plugin's own namespace. Registered via `spl_autoload_register`.

**Namespace → directory map:**
| Namespace suffix | Directory |
|---|---|
| `Includes\` | `includes/` |
| `Admin\` | `admin/` |
| `Public\` | `public/` |

Tries multiple filename casing variants (e.g. `ClassName.php`, `class-classname.php`) so both PascalCase files and WP-style kebab files resolve correctly.

---

### `AcrossAI_Model_Manager\Includes\Model_Preferences` — `includes/Model_Preferences.php`

Core feature class. Reads saved preferences and prepends the preferred model to the WordPress AI model candidate arrays.

| Method | Visibility | Description |
|---|---|---|
| `filter_text_models(array $models): array` | public | Hook callback for `wpai_preferred_text_models` |
| `filter_image_models(array $models): array` | public | Hook callback for `wpai_preferred_image_models` |
| `filter_vision_models(array $models): array` | public | Hook callback for `wpai_preferred_vision_models` |
| `apply_preference(array $models, string $cap_key): array` | private | Core logic (see below) |
| `is_provider_connected(string $provider_id): bool` | private | Checks AiClient registry |

**`apply_preference()` logic:**
1. Load option `acai_model_manager_preferences` (returns array keyed by capability).
2. Check if a preference exists for `$cap_key`.
3. Parse `provider::model_id` format from the saved string.
4. Call `is_provider_connected($provider_id)` — checks `WordPress\AiClient\AiClient::defaultRegistry()->isProviderConfigured($provider_id)`.
5. Also applies filter `acai_model_manager_has_ai_credentials` (bool) for external override.
6. If connected: prepend `"provider::model_id"` to front of `$models` array.
7. Return modified array.

---

### `AcrossAI_Model_Manager\Admin\Main` — `admin/Main.php`

Handles asset enqueueing and supplies model data to the React settings app.

| Method | Visibility | Description |
|---|---|---|
| `__construct($plugin_name, $version)` | public | Loads `build/js/backend.asset.php` and `build/css/backend.asset.php` |
| `enqueue_styles(string $hook)` | public | Always enqueues `build/css/backend.css`; adds `wp-components` dependency on settings page |
| `enqueue_scripts(string $hook)` | public | Enqueues `build/js/backend.js`; calls `wp_localize_script()` on settings page with full model data |
| `get_all_ai_models(): array` | private | Queries `AiClient::defaultRegistry()` for all configured providers and their models |
| `get_models_grouped_by_capability(): array` | private | Transforms model list into grouped structure for JS select elements |
| `add_settings_link(array $links): array` | public | Prepends "Settings" link on the plugins page |

**`wp_localize_script()` data object** (`window.acaiModelManagerSettings`):
```js
{
  models: {
    text_generation:  { provider_id: { label: string, models: [{ value, label }] } },
    image_generation: { ... },
    vision:           { ... }
  },
  preferences: { text_generation: 'provider::model_id', ... },
  nonce: '<wp_rest nonce>',
  optionName: 'acai_model_manager_preferences'
}
```

**Model value format**: `"provider_id::model_id"` (double-colon separator)

**Capability mapping note**: The `text_generation` capability from `AiClient` is mapped to both `text_generation` and `vision` groups (since multimodal text models serve vision tasks too).

---

### `AcrossAI_Model_Manager\Admin\Partials\Menu` — `admin/partials/Menu.php`

Settings page registration, WordPress Settings API integration, and React mount point.

| Constant | Value |
|---|---|
| `OPTION_KEY` | `'acai_model_manager_preferences'` |
| `LEGACY_OPTION_KEY` | `'aiam_model_preferences'` |
| `PAGE_SLUG` | `'acrossai-model-manager'` |

**Capabilities map** (static private `$capabilities`):
```php
'text_generation'  => 'Text Generation'
'image_generation' => 'Image Generation'
'vision'           => 'Vision / Multimodal'
```

| Method | Visibility | Description |
|---|---|---|
| `add_menu()` | public | `add_options_page()` → Settings menu; slug `acrossai-model-manager` |
| `register_settings()` | public | Calls `migrate_legacy_preferences()`; calls `register_setting()` with full REST schema and sanitize callback |
| `sanitize_preferences($input): array` | public | Validates `provider::model_id` format; sanitizes each part |
| `migrate_legacy_preferences()` | private | One-time copy from `aiam_model_preferences` → `acai_model_manager_preferences` if new key absent |
| `render_page()` | public | Checks `manage_options`; renders `<div id="acwpms-settings-root"></div>` for React |

**`register_setting()` configuration:**
- Setting group: `'acai_model_manager_settings_group'`
- Option name: `'acai_model_manager_preferences'`
- Type: `'object'`
- `show_in_rest`: `true` with schema exposing each capability as a string property
- Sanitize callback: `[$this, 'sanitize_preferences']`

---

### `AcrossAI_Model_Manager\Includes\I18n` — `includes/I18n.php`

`do_load_textdomain()` is a no-op. WordPress 4.6+ automatically loads translations for plugins hosted on WordPress.org. Do NOT add `load_plugin_textdomain()` back — it will trigger `_load_textdomain_just_in_time` too early in WP 6.7+.

---

### `AcrossAI_Model_Manager\Includes\Activator` / `Deactivator`

Both `activate()` and `deactivate()` static methods are currently empty stubs. Add setup/teardown logic here as needed (e.g., flushing rewrite rules, scheduling cron events, creating DB tables).

---

## WordPress Settings Storage

| Key | Type | Location |
|---|---|---|
| `acai_model_manager_preferences` | Serialized array/object | `wp_options` |

**Stored structure:**
```php
[
    'text_generation'  => 'openai::gpt-4o',   // or '' for no preference
    'image_generation' => 'openai::dall-e-3',
    'vision'           => 'openai::gpt-4o',
]
```

**REST API access**: Exposed via the standard `/wp/v2/settings` endpoint. The React app uses `@wordpress/api-fetch` with a `wp_rest` nonce middleware to GET and POST preferences — no custom REST routes are registered.

---

## REST API

No custom routes are registered (`register_rest_route()` is not called anywhere). The plugin uses WordPress's built-in `/wp/v2/settings` endpoint exclusively.

**Save flow (React → WP):**
1. `apiFetch({ path: '/wp/v2/settings', method: 'POST', data: { acai_model_manager_preferences: {...} } })`
2. WordPress validates against the REST schema defined in `register_setting()`
3. `Menu::sanitize_preferences()` runs as the sanitize callback
4. Option saved to `wp_options`

---

## JavaScript Frontend

### `src/js/backend.js` → `build/js/backend.js`

React single-page app mounted on `<div id="acwpms-settings-root">`.

**WordPress script dependencies** (from `build/js/backend.asset.php`):
`react-jsx-runtime`, `wp-api-fetch`, `wp-components`, `wp-element`, `wp-i18n`

**Components:**
- **`SettingsApp`** — Main component
  - State: `preferences` (object), `isSaving` (bool), `notice` (`{type, message}|null`)
  - Renders a `<Card>` with `<CardHeader>` + `<CardBody>` (from `@wordpress/components`)
  - For each of the 3 capabilities renders a `<BaseControl>` with a native `<select>`
  - `<optgroup>` per provider, `<option>` per model with value `"provider::model_id"`
  - Save button POSTs to `/wp/v2/settings` via `apiFetch()`
  - Displays success/error `<Notice>` after save

- **`mount()`** — Entry point
  - Targets `#acwpms-settings-root`
  - Uses React 18 `createRoot()` if available, falls back to legacy `render()`
  - Runs via `domReady` or immediately if DOM is ready

**Global object read by JS:** `window.acaiModelManagerSettings` (set via `wp_localize_script`)

---

## Styles

### `src/scss/backend.scss` → `build/css/backend.css`

**CSS class prefix**: `.acwpms-`

| Selector | Purpose |
|---|---|
| `#acwpms-settings-root` | Mount point — `margin-top: 20px` |
| `.acwpms-settings-app` | React app wrapper — max-width 720px, resets component margins |
| `.acwpms-provider-select` | Model `<select>` — full width (max 480px), min-height 36px, blue focus border `#3858e9` |
| `.acwpms-models-table` | Legacy table styles (unused in current UI) |

---

## Build System

### npm Commands

```bash
npm run start             # Development build + file watcher (source maps on)
npm run build             # Production build
npm run build-production  # NODE_ENV=production production build (used by CI)
npm run plugin-zip        # Creates acrossai-model-manager.zip via wp-scripts
npm run lint:js           # ESLint
npm run lint:css          # Stylelint
npm run format            # Prettier
npm run env:start         # Start wp-env local environment
npm run env:stop          # Stop wp-env
```

### webpack.config.js

Extends `@wordpress/scripts` default config. Custom additions:
- **Entry points**: `src/js/backend.js` → `build/js/backend.js`, `src/scss/backend.scss` → `build/css/backend.css`
- **Block support**: Auto-discovers `src/blocks/**/block.json` and adds `index.js` / `view.js` entries
- **SCSS support**: Block core stylesheets from `src/scss/blocks/core/*.scss`
- **Plugins**: `RemoveEmptyScriptsPlugin` (removes orphan JS for CSS-only entries), `CopyPlugin` (`src/media/` → `build/media/`, `src/fonts/` → `build/fonts/`)

---

## Distribution (plugin-zip)

`npm run plugin-zip` uses `wp-scripts plugin-zip` which respects `.distignore`.

**Excluded from zip** (`.distignore`):
```
/.wordpress-org, /.git, /.github, /node_modules, /src
.gitattributes, .distignore, .gitignore
composer.json, composer.lock
package.json, package-lock.json
README.md, webpack.config.js, .travis.yml
```

**Included in zip**: All PHP files, `build/`, `languages/`, `README.txt`, `LICENSE.*`, `uninstall.php`

**To build a release zip:**
```bash
npm run build-production
npm run plugin-zip
# Output: acrossai-model-manager.zip (~344 KB)
```

---

## Composer

```json
{
  "require": {
    "php": ">=7.4",
    "coenjacobs/mozart": "^0.7"
  },
  "autoload": {
    "psr-4": {
      "AcrossAI_Model_Manager\\Includes\\": "includes/",
      "AcrossAI_Model_Manager\\Admin\\":    "admin/",
      "AcrossAI_Model_Manager\\Public\\":   "public/"
    }
  }
}
```

**Mozart** (`coenjacobs/mozart ^0.7`) is included to scope third-party library namespaces and prevent conflicts. If adding external PHP libraries, configure Mozart in `composer.json` under `extra.mozart` and run `vendor/bin/mozart compose`.

**Adding a new Composer package:**
```bash
composer require vendor/package-name
```
Then add the class instantiation check in `includes/Main.php::load_composer_dependencies()`:
```php
if ( class_exists( 'Vendor\\Package\\ClassName' ) ) {
    new \Vendor\Package\ClassName( $this->plugin_dir );
}
```

---

## Code Quality

### PHPCS (`phpcs.xml.dist`)
- Ruleset: `WordPress` (WordPress Coding Standards)
- Excludes: `vendor/`, `build/`, `node_modules/`
- Excluded rule: `WordPress.Files.FileName` (allows PascalCase filenames)
- Run: `vendor/bin/phpcs`

### PHPStan (`phpstan.neon.dist`)
- Run: `vendor/bin/phpstan analyse`

### Known suppressions
- `includes/Main.php::define()` — `phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound` because all constants passed to this private method are always `ACAI_MODEL_MANAGER_*` prefixed.

---

## GitHub Actions

### `build-zip.yml` — triggers on push to `main`
1. Checkout → Setup Node 20 → `npm install` → `npm run build-production` → `npm run plugin-zip` → Upload `acrossai-model-manager.zip` as artifact

### `wordpress-plugin-deploy.yml` — triggers on tag push
1. Checkout → `npm install` → `npm run build` → Deploy to WordPress.org SVN via `10up/action-wordpress-plugin-deploy` (uses `SVN_USERNAME` / `SVN_PASSWORD` secrets)

---

## Hooks Reference (complete)

### Actions registered
| Hook | Callback | Priority | Notes |
|---|---|---|---|
| `register_activation_hook` | `acai_model_manager_activate()` | — | Calls `Activator::activate()` |
| `register_deactivation_hook` | `acai_model_manager_deactivate()` | — | Calls `Deactivator::deactivate()` |
| `plugins_loaded` | `Main::run()` | 0 | Executes Loader |
| `admin_enqueue_scripts` | `Admin\Main::enqueue_styles()` | 10 | |
| `admin_enqueue_scripts` | `Admin\Main::enqueue_scripts()` | 10 | Localizes JS on settings page |
| `admin_menu` | `Menu::add_menu()` | 10 | Adds Settings > AcrossAI Model Manager |
| `init` | `Menu::register_settings()` | 10 | Registers option + REST schema |

### Filters registered
| Hook | Callback | Priority | Notes |
|---|---|---|---|
| `acrossai_model_manager_load` | _(external)_ | — | Return false to prevent all hooks loading |
| `plugin_action_links_{BASENAME}` | `Admin\Main::add_settings_link()` | 10 | Adds Settings link on plugins page |
| `wpai_preferred_text_models` | `Model_Preferences::filter_text_models()` | 1111 | Prepends saved text model preference |
| `wpai_preferred_image_models` | `Model_Preferences::filter_image_models()` | 1111 | Prepends saved image model preference |
| `wpai_preferred_vision_models` | `Model_Preferences::filter_vision_models()` | 1111 | Prepends saved vision model preference |
| `acai_model_manager_has_ai_credentials` | _(external)_ | — | Override provider connectivity check (bool) |

---

## Security

- **Capability check**: `render_page()` and `enqueue_scripts()` gate on `current_user_can('manage_options')`
- **REST nonce**: `wp_create_nonce('wp_rest')` passed to JS; `apiFetch` sends it as `X-WP-Nonce` header
- **Sanitization**: `Menu::sanitize_preferences()` is the `register_setting()` sanitize callback — validates `provider::model_id` format, sanitizes with `sanitize_key()` and `sanitize_text_field()`
- **No direct `$_POST` access**: All saves go through the WordPress REST API + Settings API pipeline

---

## Adding a New Capability Type

1. Add the capability key + label to `$capabilities` in `admin/partials/Menu.php`
2. Add a corresponding property to the REST schema in `register_settings()`
3. Add a new filter method to `includes/Model_Preferences.php` following the existing pattern
4. Register the new filter in `includes/Main.php::define_plugin_hooks()`
5. The React UI iterates `window.acaiModelManagerSettings.models` dynamically — no JS changes needed if the server-side data is populated correctly

---

## Internationalization

- Text domain: `acrossai-model-manager`
- Domain path: `/languages`
- POT file: `languages/acrossai-model-manager.pot`
- `I18n::do_load_textdomain()` is intentionally empty — WordPress 4.6+ auto-loads translations for WordPress.org-hosted plugins. Do **not** add `load_plugin_textdomain()` here.
- Translation functions used in PHP: `__()`, `esc_html__()`
- Translation functions used in JS: `__()` from `@wordpress/i18n`

---

## Version Bumping Checklist

When releasing a new version:
- [ ] Update `Version:` header in `acrossai-model-manager.php`
- [ ] Update `ACAI_MODEL_MANAGER_VERSION` constant in `acrossai-model-manager.php`
- [ ] Update `Stable tag:` in `README.txt`
- [ ] Add changelog entry in `README.txt` under `== Changelog ==`
- [ ] Tag the Git commit — this triggers `wordpress-plugin-deploy.yml`

---

## Known Issues / Technical Decisions

| Decision | Reason |
|---|---|
| `define_plugin_hooks()` uses direct `add_filter()` instead of `Loader` | Model preference filters must be active from plugin load time, before `plugins_loaded` fires and `Loader::run()` is called |
| Priority 1111 on AI preference filters | Ensures this plugin wins over any other plugin that may also filter these hooks at default priority |
| `/wp/v2/settings` instead of custom REST route | Simpler; built-in WordPress nonce + schema validation; no route namespace collision risk |
| `ACAI_MODEL_MANAGER_VERSION` defined as a plain string constant | Avoids calling `get_plugin_data()` which internally translates header strings and triggers `_load_textdomain_just_in_time` too early (WP 6.7+ bug) |
| `I18n::do_load_textdomain()` is a no-op | `load_plugin_textdomain()` was discouraged since WP 4.6 (Plugin Check warning); WP.org auto-loads translations |
