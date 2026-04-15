<?php
namespace AI_Model_Preferences\Includes;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com//AcrossWP/ai-model-preferences
 * @since      0.0.1
 *
 * @package    AI_Model_Preferences
 * @subpackage AI_Model_Preferences/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    AI_Model_Preferences
 * @subpackage AI_Model_Preferences/includes
 * @author     WPBoilerplate <contact@wpboilerplate.com>
 */
final class Main {

	/**
	 * The single instance of the class.
	 *
	 * @var AI_Model_Preferences
	 * @since 0.0.1
	 */
	protected static $_instance = null;

	/**
	 * The autoloader instance.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Autoloader    $autoloader    The plugin autoloader instance.
	 */
	protected $autoloader;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      AI_Model_Preferences_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The plugin dir path
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_path    The string for plugin dir path
	 */
	protected $plugin_path;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	protected $plugin_dir;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {

		$this->plugin_name = 'ai-model-preferences';

		$this->define_constants();

		if ( defined( 'AI_MODEL_PREFERENCES_VERSION' ) ) {
			$this->version = AI_MODEL_PREFERENCES_VERSION;
		} else {
			$this->version = '0.0.1';
		}

		// Load the autoloader class manually before registering it
		$plugin_path = AI_MODEL_PREFERENCES_PLUGIN_PATH;

		require_once $plugin_path . 'includes/Autoloader.php';

		$this->register_autoloader();

		$this->load_composer_dependencies();

		$this->load_dependencies();

		$this->load_hooks();
	}

	/**
	 * Main AI_Model_Preferences Instance.
	 *
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see AI_Model_Preferences()
	 * @return AI_Model_Preferences - Main instance.
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Define WCE Constants
	 */
	private function define_constants() {

		$this->define( 'AI_MODEL_PREFERENCES_PLUGIN_BASENAME', plugin_basename( \AI_MODEL_PREFERENCES_PLUGIN_FILE ) );
		$this->define( 'AI_MODEL_PREFERENCES_PLUGIN_PATH', plugin_dir_path( \AI_MODEL_PREFERENCES_PLUGIN_FILE ) );
		$this->define( 'AI_MODEL_PREFERENCES_PLUGIN_URL', plugin_dir_url( \AI_MODEL_PREFERENCES_PLUGIN_FILE ) );
		$this->define( 'AI_MODEL_PREFERENCES_PLUGIN_NAME_SLUG', $this->plugin_name );
		$this->define( 'AI_MODEL_PREFERENCES_PLUGIN_NAME', 'AI Model Preferences' );

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_file = defined( 'AI_MODEL_PREFERENCES_PLUGIN_FILE' )
			? \AI_MODEL_PREFERENCES_PLUGIN_FILE
			: \AI_MODEL_PREFERENCES_PLUGIN_FILE;
		$plugin_data = get_plugin_data( $plugin_file );
		$version     = $plugin_data['Version'];
		$this->define( 'AI_MODEL_PREFERENCES_VERSION', $version );

		$this->define( 'AI_MODEL_PREFERENCES_PLUGIN_URL', $version );

		$this->plugin_dir = AI_MODEL_PREFERENCES_PLUGIN_PATH;
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Register the plugin's autoloader.
	 *
	 * This autoloader will automatically load classes from the plugin's namespace
	 * when they are instantiated.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function register_autoloader() {
		// Get the plugin path
		$plugin_path = AI_MODEL_PREFERENCES_PLUGIN_PATH;

		// Create autoloader instance
		$this->autoloader = new Autoloader( 'AI_Model_Preferences', $plugin_path );

		// Register the autoloader
		spl_autoload_register( array( $this->autoloader, 'autoload' ) );
	}

	/**
	 * Register all the hook once all the active plugins are loaded
	 *
	 * Uses the plugins_loaded to load all the hooks and filters
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	public function load_hooks() {

		/**
		 * Check if plugin can be loaded safely or not
		 *
		 * @since    0.0.1
		 */
		if ( apply_filters( 'ai-model-preferences-load', true ) ) {
			$this->define_admin_hooks();
			$this->define_plugin_hooks();
		}
	}

	/**
	 * Load the required composer dependencies for this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_composer_dependencies() {

		/**
		 * Add composer file
		 */
		$plugin_path = AI_MODEL_PREFERENCES_PLUGIN_PATH;

		if ( file_exists( $plugin_path . 'vendor/autoload.php' ) ) {
			require_once $plugin_path . 'vendor/autoload.php';
		}

		/**
		 * Check if class exists or not
		 */
		if ( class_exists( 'WPBoilerplate\\RegisterBlocks\\RegisterBlocks' ) ) {
			new \WPBoilerplate\RegisterBlocks\RegisterBlocks( $this->plugin_dir );
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - AI_Model_Preferences\Admin\Loader. Orchestrates the hooks of the plugin.
	 * - AI_Model_Preferences\Admin\I18n. Defines internationalization functionality.
	 * - AI_Model_Preferences\Admin\Main. Defines all hooks for the admin area.
	 * - AI_Model_Preferences_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		$this->loader = Loader::instance();
	}



	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new \AI_Model_Preferences\Admin\Main( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		/**
		 * Settings page and plugin action links
		 */
		$menu = new \AI_Model_Preferences\Admin\Partials\Menu( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $menu, 'add_menu' );
		$this->loader->add_action( 'init', $menu, 'register_settings' );
		$this->loader->add_filter(
			'plugin_action_links_' . AI_MODEL_PREFERENCES_PLUGIN_BASENAME,
			$plugin_admin,
			'add_settings_link'
		);
	}

	/**
	 * Register plugin-wide hooks (frontend and admin) such as AI model preference filters.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_plugin_hooks() {

		$model_prefs = new Model_Preferences();
		$this->loader->add_filter( 'wpai_preferred_text_models', $model_prefs, 'filter_text_models', 1111 );
		$this->loader->add_filter( 'wpai_preferred_image_models', $model_prefs, 'filter_image_models', 1111 );
		$this->loader->add_filter( 'wpai_preferred_vision_models', $model_prefs, 'filter_vision_models', 1111 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    AI_Model_Preferences_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * The reference to the autoloader instance.
	 *
	 * @since     0.0.1
	 * @return    Autoloader    The plugin autoloader instance.
	 */
	public function get_autoloader() {
		return $this->autoloader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
