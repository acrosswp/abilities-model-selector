<?php
/**
 * Instantiates the AI Model Preferences plugin
 *
 * @package Abilities_Model_Selector
 */

namespace Abilities_Model_Selector;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/AcrossWP/abilities-model-selector
 * @since             0.0.1
 * @package           Abilities_Model_Selector
 *
 * @wordpress-plugin
 * Plugin Name:       Abilities Model Selector
 * Plugin URI:        https://github.com/AcrossWP/abilities-model-selector
 * Description:       A WordPress plugin to manage AI model preferences for users, allowing them to select and save their preferred AI models for various tasks.
 * Version:           0.0.1
 * Author:            okpoojagupta
 * Author URI:        https://github.com/AcrossWP/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       abilities-model-selector
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
define( 'ACWP_ABILITIES_MODEL_SELECTOR_PLUGIN_FILE', __FILE__ );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/activator.php
 */
function acwp_abilities_model_selector_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/activator.php';
	Includes\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/deactivator.php
 */
function acwp_abilities_model_selector_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/deactivator.php';
	Includes\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'Abilities_Model_Selector\acwp_abilities_model_selector_activate' );
register_deactivation_hook( __FILE__, 'Abilities_Model_Selector\acwp_abilities_model_selector_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/main.php';

use Abilities_Model_Selector\Includes\Main;

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function acwp_abilities_model_selector_run() {

	$plugin = Main::instance();

	/**
	 * Run this plugin on the plugins_loaded functions
	 */
	add_action( 'plugins_loaded', array( $plugin, 'run' ), 0 );
}
acwp_abilities_model_selector_run();
