<?php
namespace Abilities_Model_Selector\Includes;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Define the internationalization functionality
 *
 * @package    Abilities_Model_Selector
 * @subpackage Abilities_Model_Selector/includes
 */
class I18n {

	/**
	 * Actually load the plugin textdomain on `init`
	 */
	public function do_load_textdomain() {
		load_plugin_textdomain(
			'abilities-model-selector',
			false,
			plugin_basename( dirname( \ACWP_ABILITIES_MODEL_SELECTOR_PLUGIN_FILE ) ) . '/languages/'
		);
	}
}
