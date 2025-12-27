import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

export default function Edit({ attributes, setAttributes, clientId, context }) {
	const { sectionId, label } = attributes;
	const wrapperId = context['multistore/scrollspy-wrapper-id'];

	const blockProps = useBlockProps({
		className: 'multistore-block-scrollspy-section'
	});

	// Generate unique ID on mount
	useEffect(() => {
		if ( ! sectionId ) {
			setAttributes({ sectionId: `section-${clientId}` });
		}
	}, []);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Ustawienia sekcji', 'multistore' ) }>
					<TextControl
						label={ __( 'Section Label', 'multistore' ) }
						value={ label }
						onChange={ ( value ) => setAttributes( { label: value } ) }
						help={ __( 'Nazwa wyświetlana w nawigacji', 'multistore' ) }
					/>
					<TextControl
						label={ __( 'ID sekcji', 'multistore' ) }
						value={ sectionId }
						onChange={ ( value ) => setAttributes( { sectionId: value } ) }
						help={ __( 'Idyfikator sekcji', 'multistore' ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="multistore-block-scrollspy-section__label">
					{label || __('Sekcja bez etykiety', 'multistore')}
				</div>
				<InnerBlocks />
			</div>
		</>
	);
}