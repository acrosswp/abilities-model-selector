import * as wpElement from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	BaseControl,
	Card,
	CardBody,
	CardHeader,
	Button,
	Notice,
	__experimentalVStack as VStack,
	__experimentalHStack as HStack,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

const {
	models: modelsByCapability = {},
	preferences: initialPreferences = {},
	nonce,
	optionName,
	aiPluginActive = false,
	connectorsUrl = '',
} = window.acaiModelManagerSettings || {};

apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );

const CAPABILITIES = {
	text_generation: __( 'Text Generation', 'acrossai-model-manager' ),
	image_generation: __( 'Image Generation', 'acrossai-model-manager' ),
	vision: __( 'Vision / Multimodal', 'acrossai-model-manager' ),
};

const DEFAULT_OPTION = {
	value: '',
	label: __( '\u2014 Use WordPress Default \u2014', 'acrossai-model-manager' ),
};

const GENERATION_PARAMS = [
	{
		key: 'temperature',
		label: __( 'Temperature', 'acrossai-model-manager' ),
		help: __(
			'Controls randomness (0.0\u20132.0). Lower = more deterministic. Leave empty to use the provider default.',
			'acrossai-model-manager'
		),
		type: 'float',
		min: 0,
		max: 2,
		step: 0.01,
	},
	{
		key: 'max_tokens',
		label: __( 'Max Tokens', 'acrossai-model-manager' ),
		help: __(
			'Maximum number of tokens to generate. Leave empty to use the provider default.',
			'acrossai-model-manager'
		),
		type: 'int',
		min: 1,
		step: 1,
	},
	{
		key: 'top_p',
		label: __( 'Top P', 'acrossai-model-manager' ),
		help: __(
			'Nucleus sampling (0.0\u20131.0). Limits the token pool to the top cumulative probability. Leave empty to use the provider default.',
			'acrossai-model-manager'
		),
		type: 'float',
		min: 0,
		max: 1,
		step: 0.01,
	},
	{
		key: 'top_k',
		label: __( 'Top K', 'acrossai-model-manager' ),
		help: __(
			'Limits the vocabulary to the top K tokens at each step. Leave empty to use the provider default.',
			'acrossai-model-manager'
		),
		type: 'int',
		min: 1,
		step: 1,
	},
	{
		key: 'presence_penalty',
		label: __( 'Presence Penalty', 'acrossai-model-manager' ),
		help: __(
			'Reduces repetition by penalising tokens that have already appeared (\u22122.0\u20132.0). Leave empty to use the provider default.',
			'acrossai-model-manager'
		),
		type: 'float',
		min: -2,
		max: 2,
		step: 0.01,
	},
	{
		key: 'frequency_penalty',
		label: __( 'Frequency Penalty', 'acrossai-model-manager' ),
		help: __(
			'Reduces repetition by penalising tokens proportional to their frequency (\u22122.0\u20132.0). Leave empty to use the provider default.',
			'acrossai-model-manager'
		),
		type: 'float',
		min: -2,
		max: 2,
		step: 0.01,
	},
];

function SettingsApp() {
	const { useState, createInterpolateElement } = wpElement;

	const [ preferences, setPreferences ] = useState(
		initialPreferences || {}
	);
	const [ isSaving, setIsSaving ] = useState( false );
	const [ notice, setNotice ] = useState( null );

	const handleChange = ( key, value ) => {
		setPreferences( ( prev ) => ( { ...prev, [ key ]: value } ) );
	};

	const handleParamChange = ( param, rawValue ) => {
		if ( rawValue === '' ) {
			handleChange( param.key, null );
			return;
		}
		const parsed =
			param.type === 'int'
				? parseInt( rawValue, 10 )
				: parseFloat( rawValue );
		if ( ! isNaN( parsed ) ) {
			handleChange( param.key, parsed );
		}
	};

	const handleSave = async () => {
		setIsSaving( true );
		setNotice( null );
		try {
			await apiFetch( {
				path: '/wp/v2/settings',
				method: 'POST',
				data: { [ optionName ]: preferences },
			} );
			setNotice( {
				type: 'success',
				message: __( 'Settings saved.', 'acrossai-model-manager' ),
			} );
		} catch ( error ) {
			setNotice( {
				type: 'error',
				message:
					error.message ||
					__(
						'An error occurred while saving.',
						'acrossai-model-manager'
					),
			} );
		} finally {
			setIsSaving( false );
		}
	};

	return (
		<div className="acwpms-settings-app">
			{ notice && (
				<Notice
					status={ notice.type }
					isDismissible
					onRemove={ () => setNotice( null ) }
					className="acwpms-notice"
				>
					{ notice.message }
				</Notice>
			) }

			{ /* Model Preferences */ }
			<Card className="acwpms-card">
				<CardHeader>
					<HStack justify="flex-start" spacing={ 3 }>
						<strong>
							{ __(
								'Model Preferences',
								'acrossai-model-manager'
							) }
						</strong>
						<span className="acwpms-badge acwpms-badge--ai-plugin">
							{ __( 'AI Plugin', 'acrossai-model-manager' ) }
						</span>
						<span className="acwpms-badge acwpms-badge--coming-soon">
							{ __( 'WP AI Client — coming soon', 'acrossai-model-manager' ) }
						</span>
					</HStack>
				</CardHeader>
				<CardBody>
					{ ( () => {
						// True when at least one capability has at least one configured provider.
						const hasAnyProvider = Object.values(
							modelsByCapability
						).some(
							( group ) => Object.keys( group ).length > 0
						);

						// Section is disabled when the AI plugin is inactive OR when
						// it is active but no providers are configured yet.
						const modelPreferencesDisabled =
							! aiPluginActive || ! hasAnyProvider;

						return (
							<>
								{ ! aiPluginActive && (
									<Notice
										status="warning"
										isDismissible={ false }
										className="acwpms-notice"
									>
										{ createInterpolateElement(
											__(
												'Model Preferences require the AI plugin. Please visit the <a>Connectors screen</a> to install and activate the AI plugin, then return here to configure your preferred models.',
												'acrossai-model-manager'
											),
											{
												a: <a href={ connectorsUrl } />,
											}
										) }
									</Notice>
								) }
								{ aiPluginActive && ! hasAnyProvider && (
									<Notice
										status="warning"
										isDismissible={ false }
										className="acwpms-notice"
									>
										{ createInterpolateElement(
											__(
												'No AI providers are configured. Please visit the <a>Connectors screen</a> to add at least one AI provider, then return here to configure your preferred models.',
												'acrossai-model-manager'
											),
											{
												a: <a href={ connectorsUrl } />,
											}
										) }
									</Notice>
								) }
								<VStack spacing={ 6 }>
									{ Object.entries( CAPABILITIES ).map(
										( [ capKey, capLabel ] ) => {
											const providerGroups =
												modelsByCapability[
													capKey
												] || {};
											const capHasProviders =
												Object.keys( providerGroups )
													.length > 0;
											const selectId = `acwpms-${ capKey }`;

											return (
												<BaseControl
													key={ capKey }
													label={ capLabel }
													id={ selectId }
													help={
														! modelPreferencesDisabled &&
														! capHasProviders
															? __(
																	'No configured AI providers found for this capability.',
																	'acrossai-model-manager'
															  )
															: undefined
													}
													__nextHasNoMarginBottom
												>
													<select
														id={ selectId }
														className="acwpms-provider-select"
														value={
															preferences[
																capKey
															] || ''
														}
														disabled={
															modelPreferencesDisabled
														}
														onChange={ ( e ) =>
															handleChange(
																capKey,
																e.target.value
															)
														}
										>
											<option value="">
												{ DEFAULT_OPTION.label }
											</option>
											{ Object.entries(
												providerGroups
											).map(
												( [
													providerId,
													group,
												] ) => (
													<optgroup
														key={ providerId }
														label={ group.label }
													>
														{ group.models.map(
															( model ) => (
																<option
																	key={
																		model.value
																	}
																	value={
																		model.value
																	}
																>
																	{
																		model.label
																	}
																</option>
															)
														) }
													</optgroup>
												)
											) }
										</select>
									</BaseControl>
								);
							}
						) }
								</VStack>
							</>
						);
					} )() }
				</CardBody>
			</Card>

			{ /* Generation Parameters — hidden, code preserved for future use */ }
			{ false && (
				<Card className="acwpms-card acwpms-params-card">
					<CardHeader>
						<strong>
							{ __(
								'Generation Parameters',
								'acrossai-model-manager'
							) }
						</strong>
					</CardHeader>
					<CardBody>
						<p className="acwpms-params-description">
							{ __(
								'These are site-wide defaults applied via acai_model_manager_apply_defaults(). Leave a field empty to use the provider\u2019s default. Plugins that set a parameter explicitly always take priority over these defaults.',
								'acrossai-model-manager'
							) }
						</p>
						<VStack spacing={ 4 }>
							{ GENERATION_PARAMS.map( ( param ) => {
								const inputId = `acwpms-param-${ param.key }`;
								const currentValue = preferences[ param.key ];
								return (
									<BaseControl
										key={ param.key }
										label={ param.label }
										help={ param.help }
										id={ inputId }
										__nextHasNoMarginBottom
									>
										<input
											type="number"
											id={ inputId }
											className="acwpms-param-input"
											value={
												currentValue !== null &&
												currentValue !== undefined
													? currentValue
													: ''
											}
											min={ param.min }
											max={ param.max }
											step={ param.step }
											placeholder={ __(
												'Provider default',
												'acrossai-model-manager'
											) }
											onChange={ ( e ) =>
												handleParamChange(
													param,
													e.target.value
												)
											}
										/>
									</BaseControl>
								);
							} ) }
						</VStack>
					</CardBody>
				</Card>
			) }

			{ /* Request Settings */ }
			<Card className="acwpms-card acwpms-params-card">
				<CardHeader>
					<HStack justify="flex-start" spacing={ 3 }>
						<strong>
							{ __(
								'Request Settings',
								'acrossai-model-manager'
							) }
						</strong>
						<span className="acwpms-badge acwpms-badge--wp-ai-client">
							{ __( 'WP AI Client', 'acrossai-model-manager' ) }
						</span>
						<span className="acwpms-badge acwpms-badge--ai-plugin">
							{ __( 'AI Plugin', 'acrossai-model-manager' ) }
						</span>
					</HStack>
				</CardHeader>
				<CardBody>
					<VStack spacing={ 4 }>
						<BaseControl
							label={ __(
								'HTTP Request Timeout (seconds)',
								'acrossai-model-manager'
							) }
							help={ __(
								'Maximum time in seconds to wait for an AI provider response. Applied globally to every wp_ai_client_prompt() call on this site. Leave empty to use the WordPress default (30 seconds).',
								'acrossai-model-manager'
							) }
							id="acwpms-param-request_timeout"
							__nextHasNoMarginBottom
						>
							<input
								type="number"
								id="acwpms-param-request_timeout"
								className="acwpms-param-input"
								value={
									preferences.request_timeout !== null &&
									preferences.request_timeout !== undefined
										? preferences.request_timeout
										: ''
								}
								min={ 1 }
								step={ 1 }
								placeholder="30"
								onChange={ ( e ) =>
									handleParamChange(
										{ key: 'request_timeout', type: 'int' },
										e.target.value
									)
								}
							/>
						</BaseControl>
					</VStack>
				</CardBody>
			</Card>

			<HStack
				justify="flex-start"
				className="acwpms-save-row"
			>
				<Button
					variant="primary"
					onClick={ handleSave }
					isBusy={ isSaving }
					disabled={ isSaving }
					size="compact"
				>
					{ isSaving
						? __( 'Saving\u2026', 'acrossai-model-manager' )
						: __( 'Save Changes', 'acrossai-model-manager' ) }
				</Button>
			</HStack>
		</div>
	);
}

function mount() {
	const rootEl = document.getElementById( 'acwpms-settings-root' );
	if ( ! rootEl ) {
		return;
	}
	const { createRoot, render } = wpElement;
	if ( typeof createRoot === 'function' ) {
		createRoot( rootEl ).render( <SettingsApp /> );
	} else if ( typeof render === 'function' ) {
		render( <SettingsApp />, rootEl );
	}
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', mount );
} else {
	mount();
}
