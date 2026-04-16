<?php
namespace Abilities_Model_Selector\Admin\Partials;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles the admin menu and settings page for AI Model Preferences.
 *
 * @since      0.0.1
 * @package    Abilities_Model_Selector\Admin\Partials
 */
class Menu {

	const OPTION_KEY = 'aiam_model_preferences';
	const PAGE_SLUG  = 'abilities-model-selector';

	/**
	 * Capability types shown on the settings page.
	 *
	 * @var array<string, string>
	 */
	private static $capabilities = array(
		'text_generation'  => 'Text Generation',
		'image_generation' => 'Image Generation',
		'vision'           => 'Vision / Multimodal',
	);

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @var      string
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @var      string
	 */
	private $version;

	/**
	 * @param string $plugin_name The plugin slug.
	 * @param string $version     The plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/** Adds the Settings sub-menu page. */
	public function add_menu(): void {
		add_options_page(
			__( 'AI Model Preferences', 'abilities-model-selector' ),
			__( 'AI Model Preferences', 'abilities-model-selector' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' ),
			2
		);
	}

	/** Registers the settings option (must fire on init to support REST API saves). */
	public function register_settings(): void {
		register_setting(
			'aiam_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'object',
				'show_in_rest'      => array(
					'schema' => array(
						'type'                 => 'object',
						'properties'           => array(
							'text_generation'  => array(
								'type'    => 'string',
								'default' => '',
							),
							'image_generation' => array(
								'type'    => 'string',
								'default' => '',
							),
							'vision'           => array(
								'type'    => 'string',
								'default' => '',
							),
						),
						'additionalProperties' => false,
					),
				),
				'sanitize_callback' => array( $this, 'sanitize_preferences' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitizes model preferences before saving.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, string>
	 */
	public function sanitize_preferences( $input ): array {
		$clean = array();
		if ( ! is_array( $input ) ) {
			return $clean;
		}
		foreach ( array_keys( self::$capabilities ) as $cap_key ) {
			if ( empty( $input[ $cap_key ] ) ) {
				continue;
			}
			$value = sanitize_text_field( wp_unslash( $input[ $cap_key ] ) );
			if ( false === strpos( $value, '::' ) ) {
				continue;
			}
			list( $provider, $model ) = explode( '::', $value, 2 );
			$provider                 = sanitize_key( $provider );
			$model                    = sanitize_text_field( $model );
			if ( $provider && $model ) {
				$clean[ $cap_key ] = $provider . '::' . $model;
			}
		}
		return $clean;
	}


	/** Renders the settings page — the React app mounts into #aiam-settings-root. */

	/**
	 * Repositions this menu item directly after the Connectors submenu entry.
	 *
	 * Runs on a late admin_menu hook (priority 9999) so all other plugins have
	 * already registered their pages and the submenu array is fully populated.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'abilities-model-selector' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p class="description"><?php esc_html_e( 'Choose the preferred AI model for each capability type. These selections override the WordPress defaults.', 'abilities-model-selector' ); ?></p>
			<div id="aiam-settings-root"></div>
		</div>
		<?php
	}
}
