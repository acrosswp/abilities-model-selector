=== AcrossAI Model Manager ===
Contributors: okpoojagupta
Donate link: https://github.com/AcrossWP/acrossai-model-manager
Tags: ai, models, manager, preferences, abilities
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.0.3
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Choose your preferred AI model for text generation, image generation, and vision tasks, overriding the WordPress default selection.

== Description ==

AcrossAI Model Manager gives site administrators full control over which AI model WordPress uses for each capability type. By default, WordPress picks the first available model from your configured AI connectors. This plugin adds a settings page under **Settings > AcrossAI Model Manager** where you can pin a specific model per capability — and that model will always be prioritised.

**Capability types supported:**

* **Text Generation** — preferred model for all text-generation tasks
* **Image Generation** — preferred model for image-generation tasks
* **Vision / Multimodal** — preferred model for vision and multimodal tasks

**How it works:**

The plugin hooks into the WordPress AI model preference filters (`wpai_preferred_text_models`, `wpai_preferred_image_models`, `wpai_preferred_vision_models`). When a preference is saved and the corresponding AI provider is connected, the chosen model is moved to the top of the candidate list so WordPress selects it first.

Manager are stored as a single serialised option in the database and are exposed to the WordPress REST API, meaning the settings page saves without a full page reload using a React-powered interface.

**Requirements:**

* WordPress 7.0 or higher.
* The [AI plugin](https://wordpress.org/plugins/ai/) must be installed and activated for Model Preferences to work. Without it, the Model Preferences dropdowns are disabled and no models will appear. You can still use this plugin to set the **HTTP Request Timeout** for the WordPress AI client without the AI plugin.
* At least one AI connector (e.g. Llama.cpp, Hugging Face, OpenAI via the AI Connectors screen) must be configured for Model Preferences to work.

== Installation ==

1. Upload the `acrossai-model-manager` folder to the `/wp-content/plugins/` directory, or install through the WordPress Plugins screen directly.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings > AcrossAI Model Manager**.
4. Choose your preferred model for each capability type from the dropdowns. Only models from configured and connected providers appear.
5. Click **Save Changes**.

== Frequently Asked Questions ==

= Do I need any other plugins for this to work? =

It depends on what you want to use.

* **Model Preferences (choosing a preferred model per capability):** Yes — the [AI plugin](https://wordpress.org/plugins/ai/) must be installed and activated, and at least one AI provider must be configured via the Connectors screen (**Settings > Connectors**). If the AI plugin is not active, the Model Preferences dropdowns are automatically disabled on the settings page.
* **HTTP Request Timeout:** No additional plugin required. The timeout setting works with the WordPress 7.0 built-in AI client directly and takes effect for all AI calls on your site.

= Does this work with the WordPress 7.0 built-in WP AI Client? =

Not fully yet. The WordPress AI client (introduced in WordPress 7.0) does not currently expose a filter that allows plugins to override the model being used. Model Preferences therefore requires the separate [AI plugin](https://wordpress.org/plugins/ai/) which provides the `wpai_preferred_*_models` filter hooks this plugin relies on.

The **HTTP Request Timeout** setting does work directly with the WP AI Client via the `wp_ai_client_default_request_timeout` filter.

Full WP AI Client support for Model Preferences is planned for a future release once WordPress core adds the necessary hooks.

= What happens if my preferred provider loses its API key or connection? =

The plugin checks whether the provider is currently connected before applying the preference. If the provider is disconnected, WordPress falls back to its default model selection — your preference is preserved in the database and will take effect again once the provider is reconnected.

= Where is the preference stored? =

Preferences are stored in the WordPress options table under the key `acai_model_manager_preferences` as a JSON object with one entry per capability type (e.g. `{"text_generation":"openai::gpt-4o"}`).

= Can I set different models for different capability types? =

Yes. Text generation, image generation, and vision can each have their own preferred model independently.

= Will this work with custom or third-party AI providers? =

Any provider registered with the [AI plugin](https://wordpress.org/plugins/ai/) that exposes its models through the standard metadata API will appear automatically in the dropdowns — no additional configuration is needed in this plugin.

== Screenshots ==

1. The AcrossAI Model Manager settings page showing dropdowns for each capability type.

== Changelog ==

= 0.0.3 =
* Added compatibility badges to settings card headers indicating which AI integration each section supports (WP AI Client, AI Plugin, coming soon).
* Model Preferences section is now disabled when the AI plugin is inactive — shows a warning notice with a direct link to the Connectors screen.
* Model Preferences section is also disabled when the AI plugin is active but no AI providers are configured — shows a distinct notice prompting the user to configure a provider via the Connectors screen.
* Added FAQ entry clarifying WP AI Client support status: HTTP Request Timeout works with WP AI Client today; Model Preferences requires the AI plugin and will gain WP AI Client support in a future release.
* Updated requirements section to clarify that the AI plugin is needed only for Model Preferences; the HTTP Request Timeout works with WordPress 7.0 core directly.

= 0.0.2 =
* Updated requirements: now explicitly requires WordPress 7.0+ (built-in AI client) instead of the separate WordPress AI plugin.
* Clarified that at least one AI connector must be configured for models to appear.

= 0.0.1 =
* Initial release.
* Settings page with React UI under Settings > AcrossAI Model Manager.
* Per-capability model preference for text generation, image generation, and vision.
* Integrates with the WordPress AI plugin preference filter hooks.
* REST API support for seamless save without page reload.

== Upgrade Notice ==

= 0.0.2 =
No database changes — update to reflect the correct WordPress 7.0 requirement.

= 0.0.1 =
Initial release — no upgrade steps required.
