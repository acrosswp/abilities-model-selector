<?php
namespace AcrossAI_Model_Manager\Includes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use WordPress\AiClient\Providers\Models\DTO\ModelConfig;

/**
 * Manages site-wide AI generation parameter defaults.
 *
 * Reads saved preferences and builds a ModelConfig that can be applied to any
 * WP_AI_Client_Prompt_Builder as default parameters. Because PromptBuilder's
 * usingModelConfig() merge gives precedence to values already set on the builder,
 * these truly act as site-wide *defaults*: a calling plugin's explicit values
 * always win — regardless of call order.
 *
 * Each parameter is also individually filterable so that other plugins can
 * override defaults programmatically without touching the admin UI.
 *
 * @since   0.0.1
 * @package AcrossAI_Model_Manager\Includes
 */
class Generation_Params {

	/**
	 * Generation parameter keys stored in the shared option.
	 *
	 * @var list<string>
	 */
	const PARAM_KEYS = array(
		'temperature',
		'max_tokens',
		'top_p',
		'top_k',
		'presence_penalty',
		'frequency_penalty',
	);

	/**
	 * Builds a ModelConfig populated with the site-wide generation parameter defaults.
	 *
	 * Only parameters that have a saved (non-null) value are set on the config.
	 * This ensures ModelConfig::toArray() omits unset params, so PromptBuilder's
	 * array_merge correctly lets the calling plugin's explicit values win.
	 *
	 * @since 0.0.1
	 *
	 * @return ModelConfig
	 */
	public static function get_model_config(): ModelConfig {
		$prefs = get_option( \AcrossAI_Model_Manager\Admin\Partials\Menu::OPTION_KEY, array() );

		$temperature       = isset( $prefs['temperature'] ) ? (float) $prefs['temperature'] : null;
		$max_tokens        = isset( $prefs['max_tokens'] ) ? (int) $prefs['max_tokens'] : null;
		$top_p             = isset( $prefs['top_p'] ) ? (float) $prefs['top_p'] : null;
		$top_k             = isset( $prefs['top_k'] ) ? (int) $prefs['top_k'] : null;
		$presence_penalty  = isset( $prefs['presence_penalty'] ) ? (float) $prefs['presence_penalty'] : null;
		$frequency_penalty = isset( $prefs['frequency_penalty'] ) ? (float) $prefs['frequency_penalty'] : null;

		/**
		 * Filters the site-wide default temperature for AI generation.
		 *
		 * Return null to leave temperature unset (provider decides).
		 *
		 * @since 0.0.1
		 *
		 * @param float|null $temperature Temperature value (0.0–2.0), or null.
		 */
		$temperature = apply_filters( 'acai_model_manager_default_temperature', $temperature );

		/**
		 * Filters the site-wide default max tokens for AI generation.
		 *
		 * Return null to leave max tokens unset (provider decides).
		 *
		 * @since 0.0.1
		 *
		 * @param int|null $max_tokens Maximum output tokens, or null.
		 */
		$max_tokens = apply_filters( 'acai_model_manager_default_max_tokens', $max_tokens );

		/**
		 * Filters the site-wide default top-p value for AI generation.
		 *
		 * Return null to leave top-p unset (provider decides).
		 *
		 * @since 0.0.1
		 *
		 * @param float|null $top_p Top-p nucleus sampling (0.0–1.0), or null.
		 */
		$top_p = apply_filters( 'acai_model_manager_default_top_p', $top_p );

		/**
		 * Filters the site-wide default top-k value for AI generation.
		 *
		 * Return null to leave top-k unset (provider decides).
		 *
		 * @since 0.0.1
		 *
		 * @param int|null $top_k Top-k sampling, or null.
		 */
		$top_k = apply_filters( 'acai_model_manager_default_top_k', $top_k );

		/**
		 * Filters the site-wide default presence penalty for AI generation.
		 *
		 * Return null to leave presence penalty unset (provider decides).
		 *
		 * @since 0.0.1
		 *
		 * @param float|null $presence_penalty Presence penalty (-2.0–2.0), or null.
		 */
		$presence_penalty = apply_filters( 'acai_model_manager_default_presence_penalty', $presence_penalty );

		/**
		 * Filters the site-wide default frequency penalty for AI generation.
		 *
		 * Return null to leave frequency penalty unset (provider decides).
		 *
		 * @since 0.0.1
		 *
		 * @param float|null $frequency_penalty Frequency penalty (-2.0–2.0), or null.
		 */
		$frequency_penalty = apply_filters( 'acai_model_manager_default_frequency_penalty', $frequency_penalty );

		$config = new ModelConfig();

		if ( null !== $temperature ) {
			$config->setTemperature( (float) $temperature );
		}
		if ( null !== $max_tokens && $max_tokens > 0 ) {
			$config->setMaxTokens( (int) $max_tokens );
		}
		if ( null !== $top_p ) {
			$config->setTopP( (float) $top_p );
		}
		if ( null !== $top_k && $top_k > 0 ) {
			$config->setTopK( (int) $top_k );
		}
		if ( null !== $presence_penalty ) {
			$config->setPresencePenalty( (float) $presence_penalty );
		}
		if ( null !== $frequency_penalty ) {
			$config->setFrequencyPenalty( (float) $frequency_penalty );
		}

		return $config;
	}
}
