<?php
/**
 * AcrossAI Model Manager — Global helper functions.
 *
 * Intentionally in the global namespace so third-party plugins can call them
 * without needing to know the plugin's PHP namespace.
 *
 * @package AcrossAI_Model_Manager
 * @since   0.0.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'acai_model_manager_apply_defaults' ) ) {

	/**
	 * Applies site-wide AI generation parameter defaults to a prompt builder.
	 *
	 * Call this on a freshly-created prompt builder to fill in any unset parameters
	 * with the values saved on the AcrossAI Model Manager settings page (or filtered
	 * via acai_model_manager_default_* hooks).
	 *
	 * Because PromptBuilder::usingModelConfig() gives precedence to values already
	 * set on the builder, any parameter your plugin sets explicitly always wins over
	 * the site-wide default — regardless of call order.
	 *
	 * Example usage:
	 *
	 *   $result = acai_model_manager_apply_defaults( wp_ai_client_prompt( 'Summarise this.' ) )
	 *       ->generate_text();
	 *
	 *   // Explicit values still win:
	 *   $result = acai_model_manager_apply_defaults(
	 *       wp_ai_client_prompt( 'Be creative.' )->using_temperature( 1.5 )
	 *   )->generate_text();
	 *
	 * @since 0.0.1
	 *
	 * @param WP_AI_Client_Prompt_Builder $builder The prompt builder to apply defaults to.
	 * @return WP_AI_Client_Prompt_Builder The same builder instance with defaults applied.
	 */
	function acai_model_manager_apply_defaults( WP_AI_Client_Prompt_Builder $builder ): WP_AI_Client_Prompt_Builder {
		if ( ! class_exists( 'AcrossAI_Model_Manager\\Includes\\Generation_Params' ) ) {
			return $builder;
		}

		$config = AcrossAI_Model_Manager\Includes\Generation_Params::get_model_config();

		// using_model_config() merges with the builder's existing config, giving
		// precedence to values the calling plugin has already set explicitly.
		$builder->using_model_config( $config );

		return $builder;
	}
}
