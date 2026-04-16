<?php
namespace AcrossWP_AI_Model_Manager\Includes;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Define the internationalization functionality
 *
 * @package    AcrossWP_AI_Model_Manager
 * @subpackage AcrossWP_AI_Model_Manager/includes
 */
class I18n {

	/**
	 * Actually load the plugin textdomain on `init`
	 */
	public function do_load_textdomain() {
		load_plugin_textdomain(
			'acrosswp-ai-model-manager',
			false,
			plugin_basename( dirname( \ACROSSWP_AI_MODEL_MANAGER_PLUGIN_FILE ) ) . '/languages/'
		);
	}
}
