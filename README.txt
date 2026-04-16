=== AcrossWP AI Model Manager ===
Contributors: okpoojagupta, acrosswp
Donate link: https://github.com/AcrossWP/abilities-model-selector
Tags: ai, connectors, models, manager, preferences
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.0.1
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Choose your preferred AI model for text generation, image generation, and vision tasks, overriding the WordPress default selection.

== Description ==

AI Model Manager gives site administrators full control over which AI model WordPress uses for each capability type. By default, WordPress picks the first available model from your configured AI connectors. This plugin adds a settings page under **Settings > AI Model Preferences** where you can pin a specific model per capability — and that model will always be prioritised.

**Capability types supported:**

* **Text Generation** — preferred model for all text-generation tasks
* **Image Generation** — preferred model for image-generation tasks
* **Vision / Multimodal** — preferred model for vision and multimodal tasks

**How it works:**

The plugin hooks into the WordPress AI model preference filters (`wpai_preferred_text_models`, `wpai_preferred_image_models`, `wpai_preferred_vision_models`). When a preference is saved and the corresponding AI provider is connected, the chosen model is moved to the top of the candidate list so WordPress selects it first.

Manager are stored as a single serialised option in the database and are exposed to the WordPress REST API, meaning the settings page saves without a full page reload using a React-powered interface.

**Requirements:**

* The [WordPress AI plugin](https://wordpress.org/plugins/ai/) must be installed and active.
* At least one AI connector (e.g. Llama.cpp, Hugging Face, OpenAI via the AI Connectors screen) must be configured.

== Installation ==

1. Upload the `abilities-model-selector` folder to the `/wp-content/plugins/` directory, or install through the WordPress Plugins screen directly.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings > AI Model Manager**.
4. Choose your preferred model for each capability type from the dropdowns. Only models from configured and connected providers appear.
5. Click **Save Changes**.

== Frequently Asked Questions ==

= Do I need any other plugins for this to work? =

Yes. The WordPress AI plugin must be installed, active, and connected to at least one AI provider via the Connectors screen (**Settings > Connectors**). Without a connected provider, no models will appear in the dropdowns.

= What happens if my preferred provider loses its API key or connection? =

The plugin checks whether the provider is currently connected before applying the preference. If the provider is disconnected, WordPress falls back to its default model selection — your preference is preserved in the database and will take effect again once the provider is reconnected.

= Where is the preference stored? =

Preferences are stored in the WordPress options table under the key `aiam_model_preferences` as a JSON object with one entry per capability type (e.g. `{"text_generation":"openai::gpt-4o"}`).

= Can I set different models for different capability types? =

Yes. Text generation, image generation, and vision can each have their own preferred model independently.

= Will this work with custom or third-party AI providers? =

Any provider that registers itself with the WordPress AI client registry and exposes its models through the standard metadata API will appear automatically in the dropdowns — no additional configuration is needed in this plugin.

== Screenshots ==

1. The AI Model Preferences settings page showing dropdowns for each capability type.

== Changelog ==

= 0.0.1 =
* Initial release.
* Settings page with React UI under Settings > AI Model Preferences.
* Per-capability model preference for text generation, image generation, and vision.
* Integrates with the WordPress AI plugin preference filter hooks.
* REST API support for seamless save without page reload.

== Upgrade Notice ==

= 0.0.1 =
Initial release — no upgrade steps required.
