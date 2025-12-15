import { __ } from '@wordpress/i18n';

// Enable custom attributes on Image block
const enableSidebarSelectOnBlocks = [
	'core/post-title',
];

import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { addFilter } from '@wordpress/hooks';

/**
 * Declare our custom attribute
 */
const setPostTitleParagraphOptionAttribute = ( settings, name ) => {
	// Do nothing if it's another block than our defined ones.
	if ( ! enableSidebarSelectOnBlocks.includes( name ) ) {
		return settings;
	}

	return Object.assign( {}, settings, {
		attributes: Object.assign( {}, settings.attributes, {
			useAsParagraph: { type: 'boolean' },
		} ),
	} );
};
addFilter(
	'blocks.registerBlockType',
	'multistore/set-post-title-paragraph-option-attribute',
	setPostTitleParagraphOptionAttribute
);

/**
 * Add Custom Select to Image Sidebar
 */
const withPostTitleParagraphOption = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {

		// If current block is not allowed
		if ( ! enableSidebarSelectOnBlocks.includes( props.name ) ) {
			return (
				<BlockEdit { ...props } />
			);
		}

		const { attributes, setAttributes } = props;
		const { useAsParagraph } = attributes;

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Post Title Settings', 'multistore' ) }
					>
						<ToggleControl
							label={ __( 'Use Post Title as Paragraph', 'multistore' ) }
							checked={ useAsParagraph }
							onChange={ () => setAttributes( { useAsParagraph: ! useAsParagraph } ) }
							help={ __( 'Selecting this option will turn the post title into a paragraph and will ignore the selected heading level.', 'multistore' ) }
						/>

					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'withPostTitleParagraphOption' );

addFilter(
	'editor.BlockEdit',
	'multistore/with-post-title-paragraph-option',
	withPostTitleParagraphOption
);


/**
 * Add custom class to block in Edit
 */
const withPostTitleParagraphOptionProp = createHigherOrderComponent( ( BlockListBlock ) => {
	return ( props ) => {

		return (
			<BlockListBlock { ...props } />
		);
	};
}, 'withPostTitleParagraphOptionProp' );

addFilter(
	'editor.BlockListBlock',
	'multistore/with-post-title-paragraph-option-prop',
	withPostTitleParagraphOptionProp
);
