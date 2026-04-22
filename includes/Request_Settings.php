<?php
namespace AcrossAI_Model_Manager\Includes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages the site-wide default HTTP request timeout for WordPress AI Client requests.
 *
 * Hooks into the `wp_ai_client_default_request_timeout` filter that
 * WP_AI_Client_Prompt_Builder applies in its constructor to every prompt object
 * created via wp_ai_client_prompt(). This means the saved timeout is applied
 * globally to all AI requests on the site — no opt-in required by other plugins.
 *
 * @since   0.0.1
 * @package AcrossAI_Model_Manager\Includes
 */
class Request_Settings {

	/**
	 * Filters the default AI client HTTP request timeout.
	 *
	 * Returns the admin-saved timeout (in seconds) when one has been configured,
	 * otherwise passes through the WordPress core default (30 seconds).
	 *
	 * @since 0.0.1
	 *
	 * @param int $timeout The current default timeout in seconds.
	 * @return int The filtered timeout in seconds.
	 */
	public static function filter_timeout( int $timeout ): int {
		$prefs           = get_option( \AcrossAI_Model_Manager\Admin\Partials\Menu::OPTION_KEY, array() );
		$saved_timeout   = isset( $prefs['request_timeout'] ) ? (int) $prefs['request_timeout'] : null;

		if ( null !== $saved_timeout && $saved_timeout >= 1 ) {
			return $saved_timeout;
		}

		return $timeout;
	}
}
