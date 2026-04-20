<?php
namespace AcrossAI_Model_Manager\Includes;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/AcrossWP/acrossai-model-manager
 * @since      0.0.1
 *
 * @package    AcrossAI_Model_Manager
 * @subpackage AcrossAI_Model_Manager/includes
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
 * @package    AcrossAI_Model_Manager
 * @subpackage AcrossAI_Model_Manager/includes
 * @author     WPBoilerplate <contact@wpboilerplate.com>
 */
final class Main {

	/**
	 * The single instance of the class.
	 *
	 * @var AcrossAI_Model_Manager
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
	 * @var      AcrossAI_Model_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'acrossai-model-manager';

		$this->define_constants();

		if ( defined( 'ACAI_MODEL_MANAGER_VERSION' ) ) {
			$this->version = ACAI_MODEL_MANAGER_VERSION;
		} else {
			$this->version = '0.0.1';
		}

		// Load the autoloader class manually before registering it
		$plugin_path = ACAI_MODEL_MANAGER_PLUGIN_PATH;

		require_once $plugin_path . 'includes/Autoloader.php';

		$this->register_autoloader();

		$this->load_composer_dependencies();

		$this->load_dependencies();

		$this->load_hooks();
	}

	/**
	 * Main AcrossAI_Model_Manager Instance.
	 *
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
	 *
	 * @since 0.0.1
	 * @static
	 * @see AcrossAI_Model_Manager()
	 * @return AcrossAI_Model_Manager - Main instance.
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

		$this->define( 'ACAI_MODEL_MANAGER_PLUGIN_BASENAME', plugin_basename( \ACAI_MODEL_MANAGER_PLUGIN_FILE ) );
		$this->define( 'ACAI_MODEL_MANAGER_PLUGIN_PATH', plugin_dir_path( \ACAI_MODEL_MANAGER_PLUGIN_FILE ) );
		$this->define( 'ACAI_MODEL_MANAGER_PLUGIN_URL', plugin_dir_url( \ACAI_MODEL_MANAGER_PLUGIN_FILE ) );
		$this->define( 'ACAI_MODEL_MANAGER_PLUGIN_NAME_SLUG', $this->plugin_name );
		$this->define( 'ACAI_MODEL_MANAGER_PLUGIN_NAME', 'AcrossAI Model Manager' );

		$this->plugin_dir = ACAI_MODEL_MANAGER_PLUGIN_PATH;
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound -- Constants defined via this method are always plugin-prefixed (ACAI_MODEL_MANAGER_*).
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
		$plugin_path = ACAI_MODEL_MANAGER_PLUGIN_PATH;

		// Create autoloader instance
		$this->autoloader = new Autoloader( 'AcrossAI_Model_Manager', $plugin_path );

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
		if ( apply_filters( 'acrossai_model_manager_load', true ) ) {
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
		$plugin_path = ACAI_MODEL_MANAGER_PLUGIN_PATH;

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
	 * - AcrossAI_Model_Manager\Admin\Loader. Orchestrates the hooks of the plugin.
	 * - AcrossAI_Model_Manager\Admin\I18n. Defines internationalization functionality.
	 * - AcrossAI_Model_Manager\Admin\Main. Defines all hooks for the admin area.
	 * - AcrossAI_Model_Manager_Public. Defines all hooks for the public side of the site.
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

		$plugin_admin = new \AcrossAI_Model_Manager\Admin\Main( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		/**
		 * Settings page and plugin action links
		 */
		$menu = new \AcrossAI_Model_Manager\Admin\Partials\Menu( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $menu, 'add_menu' );
		$this->loader->add_action( 'init', $menu, 'register_settings' );
		$this->loader->add_filter(
			'plugin_action_links_' . ACAI_MODEL_MANAGER_PLUGIN_BASENAME,
			$plugin_admin,
			'add_settings_link'
		);
	}

	/**
	 * Register plugin-wide hooks (frontend and admin) such as AI model preference filters.
	 *
	 * Registered directly with add_filter() (not deferred through the Loader) so they
	 * are active from plugin load time rather than only after plugins_loaded fires.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_plugin_hooks() {

		$model_prefs = new Model_Preferences();
		add_filter( 'wpai_preferred_text_models', array( $model_prefs, 'filter_text_models' ), 1111 );
		add_filter( 'wpai_preferred_image_models', array( $model_prefs, 'filter_image_models' ), 1111 );
		add_filter( 'wpai_preferred_vision_models', array( $model_prefs, 'filter_vision_models' ), 1111 );
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
	 * @return    AcrossAI_Model_Manager_Loader    Orchestrates the hooks of the plugin.
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
