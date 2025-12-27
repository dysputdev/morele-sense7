import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

import './editor.scss';
import { TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes, context }) {
	const { tagName, isLink, customName } = attributes;

	// Get postId from context (either from parent block or editor)
	const postId = context['multistore/postId'] || context.postId || (typeof wp !== 'undefined' && wp.data.select('core/editor')?.getCurrentPostId());

	// Get product data
	const product = useSelect(
		(select) => {
			if (!postId) {
				return null;
			}
			return select(coreStore).getEntityRecord('postType', 'product', postId);
		},
		[postId]
	);

	const blockProps = useBlockProps({
		className: 'multistore-block-simplified-product-name',
	});

	// Tag options
	const tagOptions = [
		{ label: __('H1', 'multistore'), value: 'h1' },
		{ label: __('H2', 'multistore'), value: 'h2' },
		{ label: __('H3', 'multistore'), value: 'h3' },
		{ label: __('H4', 'multistore'), value: 'h4' },
		{ label: __('H5', 'multistore'), value: 'h5' },
		{ label: __('H6', 'multistore'), value: 'h6' },
		{ label: __('Paragraph (p)', 'multistore'), value: 'p' },
		{ label: __('Span', 'multistore'), value: 'span' },
		{ label: __('Div', 'multistore'), value: 'div' },
	];

	// Mock product name for preview
	const productName = customName || product?.title?.rendered || __('Nazwa produktu', 'multistore');

	// Create the element based on tagName
	const TagName = tagName;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Ustawienia bloku', 'multistore')} initialOpen={true}>
					<SelectControl
						label={__('Tag HTML', 'multistore')}
						value={tagName}
						options={tagOptions}
						onChange={(value) => setAttributes({ tagName: value })}
						help={__('Wybierz tag HTML dla nazwy produktu', 'multistore')}
					/>
					<ToggleControl
						label={__('Czy element ma być linkiem', 'multistore')}
						checked={isLink}
						onChange={(value) => setAttributes({ isLink: value })}
						help={
							isLink
								? __('Nazwa produktu będzie linkiem do strony produktu', 'multistore')
								: __('Nazwa produktu nie będzie linkiem', 'multistore')
						}
					/>
					<TextControl
						label={__('Nazwa produktu', 'multistore')}
						value={customName}
						onChange={(value) => setAttributes({ customName: value })}
						help={
							__('Możesz wpisać niestandardową nazwę produktu.', 'multistore')
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<TagName className="multistore-block-simplified-product-name__title">
					{isLink ? (
						<a href="#" onClick={(e) => e.preventDefault()}>
							{productName}
						</a>
					) : (
						productName
					)}
				</TagName>
			</div>
		</>
	);
}
