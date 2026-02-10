import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes, context } ) {

	const { showGroupLabel, showItemLabel, visibility, buttonText } = attributes;

	const blockProps = useBlockProps({
		className: 'multistore-block-product-variants-preview',
	})

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Ustawienia', 'multistore' ) }>
					<ToggleControl
						label={ __( 'Pokazuj etykiete grupy', 'multistore' ) }
						checked={ showGroupLabel }
						onChange={ ( value ) => setAttributes( { showGroupLabel: value } ) }
					/>
					<ToggleControl
						label={ __( 'Pokazuj etykiete wariantu', 'multistore' ) }
						checked={ showItemLabel }
						onChange={ ( value ) => setAttributes( { showItemLabel: value } ) }
					/>
					<SelectControl
						label={ __( 'Widoczność przełącznika', 'multistore' ) }
						value={ visibility }
						options={ [
							{ value: 'visible', label: __( 'Widoczne', 'multistore' ) },
							{ value: 'hidden', label: __( 'Ukryte', 'multistore' ) },
						] }
						onChange={ ( value ) => setAttributes( { visibility: value } ) }
					/>
					{ visibility === 'hidden' && (
						<TextControl
							label={ __( 'Tekst przycisku', 'multistore' ) }
							value={ buttonText }
							onChange={ ( value ) => setAttributes( { buttonText: value } ) }
						/>
					)}
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="multistore-block-product-variants-preview__groups">
					<div className="multistore-block-product-variants-preview__group">
					</div>
				</div>
				{ visibility === 'hidden' && (
					<div className="multistore-block-product-variants-preview__more">
						<button type="button" className="button button--primary">
							{ buttonText }
						</button>
					</div>
				)}
			</div>
		</>
	);
}