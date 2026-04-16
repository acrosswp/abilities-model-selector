<?php
namespace Abilities_Model_Selector\Admin;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/AcrossWP/abilities-model-selector
 * @since      0.0.1
 *
 * @package    Abilities_Model_Selector
 * @subpackage Abilities_Model_Selector/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Abilities_Model_Selector
 * @subpackage Abilities_Model_Selector/admin
 * @author     WPBoilerplate <contact@wpboilerplate.com>
 */
class Main {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The js_asset_file of the backend
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $js_asset_file;

	/**
	 * The css_asset_file of the backend
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $css_asset_file;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->js_asset_file  = include \ACWP_ABILITIES_MODEL_SELECTOR_PLUGIN_PATH . 'build/js/backend.asset.php';
		$this->css_asset_file = include \ACWP_ABILITIES_MODEL_SELECTOR_PLUGIN_PATH . 'build/css/backend.asset.php';
	}

	/**
	 * Register the stylesheets and scripts for the admin area.
	 *
	 * Also handles the settings-page-specific React app assets.
	 *
	 * @since    0.0.1
	 * @param    string    $hook    The current admin page hook.
	 */
	public function enqueue_styles( string $hook ) {

		// Global admin stylesheet (includes all settings-page styles from src/scss/backend.scss).
		$css_deps = $this->css_asset_file['dependencies'];
		if ( 'settings_page_abilities-model-selector' === $hook ) {
			$css_deps = array_unique( array_merge( $css_deps, array( 'wp-components' ) ) );
		}
		wp_enqueue_style( $this->plugin_name, \ACWP_ABILITIES_MODEL_SELECTOR_PLUGIN_URL . 'build/css/backend.css', $css_deps, $this->css_asset_file['version'], 'all' );

		// Settings-page-specific assets.
		if ( 'settings_page_abilities-model-selector' !== $hook ) {
			return;
		}

		// Settings page styles are included in build/css/backend.css (compiled from src/scss/backend.scss).
		// Settings JS is bundled in build/js/backend.js (merged from src/js/settings.js).
		// Localisation data is injected via wp_localize_script() in enqueue_scripts() after the handle is registered.
	}

	/**
	 * Returns all available AI models from configured providers via the AiClient registry.
	 *
	 * @return array<int, array{provider: string, provider_label: string, id: string, label: string, capabilities: list<string>}>
	 */
	private function get_all_ai_models(): array {
		if ( ! class_exists( \WordPress\AiClient\AiClient::class ) ) {
			return array();
		}

		$models = array();

		try {
			$registry = \WordPress\AiClient\AiClient::defaultRegistry();

			foreach ( $registry->getRegisteredProviderIds() as $provider_id ) {
				if ( ! $registry->isProviderConfigured( $provider_id ) ) {
					continue;
				}

				$class_name    = $registry->getProviderClassName( $provider_id );
				$provider_meta = $class_name::metadata();

				foreach ( $class_name::modelMetadataDirectory()->listModelMetadata() as $model_meta ) {
					$models[] = array(
						'provider'       => (string) $provider_id,
						'provider_label' => (string) $provider_meta->getName(),
						'id'             => (string) $model_meta->getId(),
						'label'          => (string) $model_meta->getName(),
						'capabilities'   => array_map( 'strval', $model_meta->getSupportedCapabilities() ),
					);
				}
			}
		} catch ( \Throwable $e ) {
			return array();
		}

		return $models;
	}

	/**
	 * Returns models grouped by capability for the React settings app.
	 *
	 * @return array<string, list<array{value: string, label: string}>>
	 */
	private function get_models_grouped_by_capability(): array {
		// AiClient CapabilityEnum has no 'vision' type — all text/vision models are
		// registered as 'text_generation'. Populate text and vision from the same pool.
		$capability_map = array(
			'text_generation'  => array( 'text_generation' ),
			'image_generation' => array( 'image_generation' ),
			'vision'           => array( 'text_generation' ),
		);

		$all_models = $this->get_all_ai_models();
		$grouped    = array_fill_keys( array_keys( $capability_map ), array() );

		foreach ( $all_models as $model ) {
			foreach ( $model['capabilities'] as $capability ) {
				foreach ( $capability_map as $group_key => $source_caps ) {
					if ( in_array( $capability, $source_caps, true ) ) {
						$entry = array(
							'value' => $model['provider'] . '::' . $model['id'],
							'label' => $model['label'] . ' (' . $model['provider_label'] . ')',
						);
						// Avoid duplicates within the same group.
						if ( ! in_array( $entry, $grouped[ $group_key ], true ) ) {
							$grouped[ $group_key ][] = $entry;
						}
					}
				}
			}
		}

		return $grouped;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts( string $hook = '' ) {

		wp_enqueue_script( $this->plugin_name, \ACWP_ABILITIES_MODEL_SELECTOR_PLUGIN_URL . 'build/js/backend.js', $this->js_asset_file['dependencies'], $this->js_asset_file['version'], false );

		// Inject settings data after the handle is registered (wp_localize_script requires a registered handle).
		if ( 'settings_page_abilities-model-selector' === $hook ) {
			wp_localize_script(
				$this->plugin_name,
				'aiamSettings',
				array(
					'models'      => $this->get_models_grouped_by_capability(),
					'preferences' => (object) get_option( \Abilities_Model_Selector\Admin\Partials\Menu::OPTION_KEY, array() ),
					'nonce'       => wp_create_nonce( 'wp_rest' ),
					'optionName'  => \Abilities_Model_Selector\Admin\Partials\Menu::OPTION_KEY,
				)
			);
		}
	}

	/**
	 * Adds a Settings link to the plugin action links on the Plugins page.
	 *
	 * @param string[] $links Existing action links.
	 * @return string[] Modified action links.
	 */
	public function add_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=abilities-model-selector' ) ),
			esc_html__( 'Settings', 'abilities-model-selector' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}
}
