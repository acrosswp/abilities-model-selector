import * as wpElement from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Card,
	CardBody,
	CardHeader,
	SelectControl,
	Button,
	Notice,
	Spinner,
	__experimentalVStack as VStack,
	__experimentalHStack as HStack,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

const {
	models: modelsByCapability = {},
	preferences: initialPreferences = {},
	nonce,
	optionName,
} = window.aiamSettings || {};

apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );

const CAPABILITIES = {
	text_generation: __( 'Text Generation', 'abilities-model-selector' ),
	image_generation: __( 'Image Generation', 'abilities-model-selector' ),
	vision: __( 'Vision / Multimodal', 'abilities-model-selector' ),
};

const DEFAULT_OPTION = {
	value: '',
	label: __( '\u2014 Use WordPress Default \u2014', 'abilities-model-selector' ),
};

function SettingsApp() {
	const { useState } = wpElement;

	const [ preferences, setPreferences ] = useState(
		initialPreferences || {}
	);
	const [ isSaving, setIsSaving ] = useState( false );
	const [ notice, setNotice ] = useState( null );

	const handleChange = ( capKey, value ) => {
		setPreferences( ( prev ) => ( { ...prev, [ capKey ]: value } ) );
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
				message: __( 'Settings saved.', 'abilities-model-selector' ),
			} );
		} catch ( error ) {
			setNotice( {
				type: 'error',
				message:
					error.message ||
					__( 'An error occurred while saving.', 'abilities-model-selector' ),
			} );
		} finally {
			setIsSaving( false );
		}
	};

	return (
		<div className="aiam-settings-app">
			{ notice && (
				<Notice
					status={ notice.type }
					isDismissible
					onRemove={ () => setNotice( null ) }
					className="aiam-notice"
				>
					{ notice.message }
				</Notice>
			) }

			<Card className="aiam-card">
				<CardHeader>
					<strong>
						{ __( 'Model Preferences', 'abilities-model-selector' ) }
					</strong>
				</CardHeader>
				<CardBody>
					<VStack spacing={ 6 }>
						{ Object.entries( CAPABILITIES ).map(
							( [ capKey, capLabel ] ) => {
								const capModels =
									modelsByCapability[ capKey ] || [];
								const options = [ DEFAULT_OPTION, ...capModels ];

								return (
									<SelectControl
										key={ capKey }
										label={ capLabel }
										value={ preferences[ capKey ] || '' }
										options={ options }
										onChange={ ( value ) =>
											handleChange( capKey, value )
										}
										size="__unstable-large"
										__nextHasNoMarginBottom
										help={
											capModels.length === 0
												? __(
														'No configured AI providers found for this capability.',
														'abilities-model-selector'
												  )
												: undefined
										}
									/>
								);
							}
						) }
					</VStack>
				</CardBody>
			</Card>

			<HStack
				justify="flex-start"
				className="aiam-save-row"
			>
				<Button
					variant="primary"
					onClick={ handleSave }
					isBusy={ isSaving }
					disabled={ isSaving }
					size="compact"
				>
					{ isSaving
						? __( 'Saving\u2026', 'abilities-model-selector' )
						: __( 'Save Changes', 'abilities-model-selector' ) }
				</Button>
			</HStack>
		</div>
	);
}

function mount() {
	const rootEl = document.getElementById( 'aiam-settings-root' );
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
