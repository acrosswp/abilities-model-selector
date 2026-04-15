<?php
namespace AI_Model_Preferences\Includes;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Hooks into WordPress AI filters to override the default model selection.
 *
 * @since 1.0.0
 * @package AI_Model_Preferences
 */
class Model_Preferences {

	/**
	 * Filters text generation preferred models.
	 *
	 * @param array $models Current preferred models.
	 * @return array Filtered preferred models.
	 */
	public function filter_text_models( array $models ): array {
		return $this->apply_preference( $models, 'text_generation' );
	}

	/**
	 * Filters image generation preferred models.
	 *
	 * @param array $models Current preferred models.
	 * @return array Filtered preferred models.
	 */
	public function filter_image_models( array $models ): array {
		return $this->apply_preference( $models, 'image_generation' );
	}

	/**
	 * Filters vision preferred models.
	 *
	 * @param array $models Current preferred models.
	 * @return array Filtered preferred models.
	 */
	public function filter_vision_models( array $models ): array {
		return $this->apply_preference( $models, 'vision' );
	}

	/**
	 * Applies the saved preference for a capability key to the models list.
	 *
	 * If a preference is set and the provider is connected, it becomes the
	 * first entry so WordPress picks it first when iterating candidate models.
	 *
	 * @param array  $models  Current preferred models list.
	 * @param string $cap_key Capability key: text_generation, image_generation, or vision.
	 * @return array Updated preferred models list.
	 */
	private function apply_preference( array $models, string $cap_key ): array {
		$preferences = (array) get_option( \AI_Model_Preferences\Admin\Partials\Menu::OPTION_KEY, array() );

		if ( empty( $preferences[ $cap_key ] ) ) {
			return $models;
		}

		$preference = (string) $preferences[ $cap_key ];
		if ( false === strpos( $preference, '::' ) ) {
			return $models;
		}

		list( $provider, $model_id ) = explode( '::', $preference, 2 );

		$provider = sanitize_key( $provider );
		$model_id = sanitize_text_field( $model_id );
		if ( ! $provider || ! $model_id ) {
			return $models;
		}

		// Only apply preference if the provider is connected with an API key.
		if ( ! $this->is_provider_connected( $provider ) ) {
			return $models;
		}

		$preferred_model = array( $provider, $model_id );

		// Prepend preferred model; keep remaining models as fallbacks.
		array_unshift( $models, $preferred_model );
		return $models;
	}

	/**
	 * Checks whether a specific AI provider is configured (has credentials via any source:
	 * database, environment variable, or PHP constant).
	 *
	 * Uses the AiClient registry directly so env-var and constant-based keys are detected,
	 * not just database-stored ones.
	 *
	 * @param string $provider_id The provider ID (e.g. 'openai', 'anthropic').
	 * @return bool True if the provider is configured with credentials.
	 */
	private function is_provider_connected( string $provider_id ): bool {
		if ( ! class_exists( \WordPress\AiClient\AiClient::class ) ) {
			return false;
		}

		try {
			$registry         = \WordPress\AiClient\AiClient::defaultRegistry();
			$has_credentials  = $registry->isProviderConfigured( $provider_id );
		} catch ( \Throwable $e ) {
			$has_credentials = false;
		}

		return (bool) apply_filters( 'aiam_has_ai_credentials', $has_credentials, $provider_id );
	}
}
