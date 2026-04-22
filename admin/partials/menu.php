<?php
namespace AcrossAI_Model_Manager\Admin\Partials;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles the admin menu and settings page for AcrossAI Model Manager.
 *
 * @since      0.0.1
 * @package    AcrossAI_Model_Manager\Admin\Partials
 */
class Menu {

	const OPTION_KEY        = 'acai_model_manager_preferences';
	const LEGACY_OPTION_KEY = 'aiam_model_preferences';
	const PAGE_SLUG         = 'acrossai-model-manager';

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
			__( 'Model Manager', 'acrossai-model-manager' ),
			__( 'Model Manager', 'acrossai-model-manager' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' ),
			2
		);
	}

	/** Registers the settings option (must fire on init to support REST API saves). */
	public function register_settings(): void {
		$this->migrate_legacy_preferences();

		register_setting(
			'acai_model_manager_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'object',
				'show_in_rest'      => array(
					'schema' => array(
						'type'                 => 'object',
						'properties'           => array(
							'text_generation'   => array(
								'type'    => 'string',
								'default' => '',
							),
							'image_generation'  => array(
								'type'    => 'string',
								'default' => '',
							),
							'vision'            => array(
								'type'    => 'string',
								'default' => '',
							),
							'temperature'       => array(
								'type' => array( 'number', 'null' ),
							),
							'max_tokens'        => array(
								'type' => array( 'integer', 'null' ),
							),
							'top_p'             => array(
								'type' => array( 'number', 'null' ),
							),
							'top_k'             => array(
								'type' => array( 'integer', 'null' ),
							),
							'presence_penalty'  => array(
								'type' => array( 'number', 'null' ),
							),
							'frequency_penalty' => array(
								'type' => array( 'number', 'null' ),
							),
							'request_timeout'   => array(
								'type' => array( 'integer', 'null' ),
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
	 * Sanitizes model preferences and generation parameters before saving.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>
	 */
	public function sanitize_preferences( $input ): array {
		$clean = array();
		if ( ! is_array( $input ) ) {
			return $clean;
		}

		// Model preferences — must be "provider::model_id" format.
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

		// Generation parameters — float values with range validation.
		$float_params = array(
			'temperature'       => array( 'min' => 0.0, 'max' => 2.0 ),
			'top_p'             => array( 'min' => 0.0, 'max' => 1.0 ),
			'presence_penalty'  => array( 'min' => -2.0, 'max' => 2.0 ),
			'frequency_penalty' => array( 'min' => -2.0, 'max' => 2.0 ),
		);

		foreach ( $float_params as $key => $bounds ) {
			if ( ! array_key_exists( $key, $input ) || null === $input[ $key ] || '' === $input[ $key ] ) {
				continue;
			}
			$value = (float) $input[ $key ];
			if ( $value >= $bounds['min'] && $value <= $bounds['max'] ) {
				$clean[ $key ] = $value;
			}
		}

		// Generation parameters — integer values with minimum validation.
		$int_params = array(
			'max_tokens'      => 1,
			'top_k'           => 1,
			'request_timeout' => 1,
		);

		foreach ( $int_params as $key => $min ) {
			if ( ! array_key_exists( $key, $input ) || null === $input[ $key ] || '' === $input[ $key ] ) {
				continue;
			}
			$value = (int) $input[ $key ];
			if ( $value >= $min ) {
				$clean[ $key ] = $value;
			}
		}

		return $clean;
	}

	/**
	 * Migrates stored preferences from the legacy option key once.
	 */
	private function migrate_legacy_preferences(): void {
		if ( false !== get_option( self::OPTION_KEY, false ) ) {
			return;
		}

		$legacy_preferences = get_option( self::LEGACY_OPTION_KEY, false );
		if ( false === $legacy_preferences ) {
			return;
		}

		update_option( self::OPTION_KEY, $legacy_preferences );
	}

	/** Renders the settings page — the React app mounts into #acwpms-settings-root. */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'acrossai-model-manager' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p class="description"><?php esc_html_e( 'Choose the preferred AI model for each capability type. These selections override the WordPress defaults.', 'acrossai-model-manager' ); ?></p>
			<div id="acwpms-settings-root"></div>
		</div>
		<?php
	}
}
