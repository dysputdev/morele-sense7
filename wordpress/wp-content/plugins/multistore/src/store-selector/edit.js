import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, TextControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

import './editor.scss';

export default function Edit( { attributes, setAttributes, isSelected } ) {
	const { showFlags, showLanguageNames, title, description } = attributes;
	const [ stores, setStores ] = useState( [] );
	const [ currentStore, setCurrentStore ] = useState( null );

	const blockProps = useBlockProps( {
		className: `multistore-block-store-selector ${ isSelected ? 'is-selected' : '' }`,
	} );

	const titleText =
		title !== ''
			? title
			: __( 'Wybierz kraj i język sklepu:', 'multistore' );
	const descriptionText =
		description !== ''
			? description
			: __(
					'Wysyłka realizowana jest wyłącznie na terenie wybranego kraju.',
					'multistore'
			  );

	// Fetch real stores data
	useEffect( () => {
		// Mock data for editor preview (in real scenario, you'd fetch from REST API)
		const mockStores = [
			{
				blog_id: 1,
				country_code: 'pl',
				country_name: 'Polska',
				flag_url: window.multistoreData?.pluginUrl + 'assets/img/flags/4x3/pl.svg',
				languages: [
					{ code: 'pl', name: 'Polski', is_active: true, url: '#' },
					{ code: 'en', name: 'English', is_active: false, url: '#' },
				],
			},
			{
				blog_id: 2,
				country_code: 'de',
				country_name: 'Deutschland',
				flag_url: window.multistoreData?.pluginUrl + 'assets/img/flags/4x3/de.svg',
				languages: [
					{ code: 'de', name: 'Deutsch', is_active: false, url: '#' },
					{ code: 'en', name: 'English', is_active: false, url: '#' },
					{ code: 'pl', name: 'Polski', is_active: false, url: '#' },
				],
			},
			{
				blog_id: 3,
				country_code: 'fr',
				country_name: 'France',
				flag_url: window.multistoreData?.pluginUrl + 'assets/img/flags/4x3/fr.svg',
				languages: [
					{ code: 'fr', name: 'Français', is_active: false, url: '#' },
					{ code: 'en', name: 'English', is_active: false, url: '#' },
					{ code: 'pl', name: 'Polski', is_active: false, url: '#' },
				],
			},
		];

		setStores( mockStores );
		setCurrentStore( {
			country_code: 'PL',
			language_code: 'PL',
			flag_url: window.multistoreData?.pluginUrl + 'assets/img/flags/4x3/pl.svg',
		} );
	}, [] );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Ustawienia selektora', 'multistore' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Pokaż flagi', 'multistore' ) }
						checked={ showFlags }
						onChange={ ( value ) =>
							setAttributes( { showFlags: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Pokaż nazwy języków', 'multistore' ) }
						checked={ showLanguageNames }
						onChange={ ( value ) =>
							setAttributes( { showLanguageNames: value } )
						}
					/>

					<TextControl
						label={ __( 'Tytuł', 'multistore' ) }
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
						placeholder={ __(
							'Wybierz kraj i język sklepu:',
							'multistore'
						) }
					/>

					<TextControl
						label={ __( 'Opis', 'multistore' ) }
						value={ description }
						onChange={ ( value ) =>
							setAttributes( { description: value } )
						}
						placeholder={ __(
							'Wysyłka realizowana jest wyłącznie na terenie wybranego kraju.',
							'multistore'
						) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<button
					className="multistore-block-store-selector__button"
					type="button"
					aria-expanded="false"
				>
					{ showFlags && currentStore?.flag_url && (
						<img
							src={ currentStore.flag_url }
							alt=""
							className="multistore-block-store-selector__button-flag"
						/>
					) }
					<span className="multistore-block-store-selector__button-text">
						<span>{ currentStore?.country_code || 'PL' }</span>
						<span className="multistore-block-store-selector__button-separator">
							|
						</span>
						<span>{ currentStore?.language_code || 'PL' }</span>
					</span>
				</button>

				<div className="multistore-block-store-selector__container">
					<h3 className="multistore-block-store-selector__title">
						{ titleText }
					</h3>
					<p className="multistore-block-store-selector__description">
						{ descriptionText }
					</p>

					<div className="multistore-block-store-selector__list">
						{ stores.map( ( store ) => (
							<div
								key={ store.blog_id }
								className="multistore-block-store-selector__item"
							>
								<div className="multistore-block-store-selector__country">
									{ showFlags && store.flag_url && (
										<img
											src={ store.flag_url }
											alt={ store.country_name }
											className="multistore-block-store-selector__country-flag"
										/>
									) }
									<span className="multistore-block-store-selector__country-name">
										{ store.country_name }
									</span>
								</div>
								<div className="multistore-block-store-selector__languages">
									{ store.languages.map(
										( lang, langIndex ) => (
											<>
												<span
													key={ lang.code }
													className={ `multistore-block-store-selector__language-link ${
														lang.is_active
															? 'is-active'
															: ''
													}` }
												>
													{ showLanguageNames
														? lang.name
														: lang.code.toUpperCase() }
												</span>
												{ langIndex <
													store.languages.length -
														1 && (
													<span className="multistore-block-store-selector__language-separator">
														|
													</span>
												) }
											</>
										)
									) }
								</div>
							</div>
						) ) }
					</div>
				</div>
			</div>
		</>
	);
}
