<?php
/**
 * Instantiates the AcrossAI Model Manager plugin
 *
 * @package AcrossAI_Model_Manager
 */

namespace AcrossAI_Model_Manager;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/AcrossWP/acrossai-model-manager
 * @since             0.0.1
 * @package           AcrossAI_Model_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       AcrossAI Model Manager
 * Plugin URI:        https://github.com/AcrossWP/acrossai-model-manager
 * Description:       A WordPress plugin to manage AcrossAI Model Manager for users, allowing them to select and save their preferred AI models for various tasks.
 * Version:           0.0.1
 * Author:            okpoojagupta
 * Author URI:        http://github.com/okpoojagupta/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       acrossai-model-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ACAI_MODEL_MANAGER_PLUGIN_FILE', __FILE__ );
define( 'ACAI_MODEL_MANAGER_VERSION', '0.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/activator.php
 */
function acai_model_manager_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/Activator.php';
	Includes\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/deactivator.php
 */
function acai_model_manager_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/Deactivator.php';
	Includes\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'AcrossAI_Model_Manager\acai_model_manager_activate' );
register_deactivation_hook( __FILE__, 'AcrossAI_Model_Manager\acai_model_manager_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/Main.php';

use AcrossAI_Model_Manager\Includes\Main;

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function acai_model_manager_run() {

	$plugin = Main::instance();

	/**
	 * Run this plugin on the plugins_loaded functions
	 */
	add_action( 'plugins_loaded', array( $plugin, 'run' ), 0 );
}
acai_model_manager_run();
