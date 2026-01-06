import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

import './editor.scss';

export default function Edit({ attributes, setAttributes, context }) {
	const { currencyFormat, prefix, suffix } = attributes;

	// Get postId from context (either from parent block or editor)
	const productId = context['multistore/postId'] || context.postId || (typeof wp !== 'undefined' && wp.data.select('core/editor')?.getCurrentPostId());

	const blockProps = useBlockProps({
		className: 'multistore-block-price-current',
	});

	// Format options
	const formatOptions = [
		{ label: __('Domyślny (99,99 zł)', 'multistore'), value: 'default' },
		{ label: __('Bez symbolu (99,99)', 'multistore'), value: 'no_symbol' },
		{ label: __('Tylko symbol (zł)', 'multistore'), value: 'symbol_only' },
		{ label: __('Kod waluty (PLN)', 'multistore'), value: 'code' }
	];

	// Mock price for editor preview
	const mockPrice = '99,99 zł';

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Ustawienia ceny', 'multistore')} initialOpen={true}>
					<SelectControl
						label={__('Format waluty', 'multistore')}
						value={currencyFormat}
						options={formatOptions}
						onChange={(value) => setAttributes({ currencyFormat: value })}
					/>
					<TextControl
						label={__('Prefiks', 'multistore')}
						value={prefix}
						onChange={(value) => setAttributes({ prefix: value })}
						placeholder={__('np. od', 'multistore')}
					/>
					<TextControl
						label={__('Sufiks', 'multistore')}
						value={suffix}
						onChange={(value) => setAttributes({ suffix: value })}
						placeholder={__('np. brutto', 'multistore')}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<span className="multistore-block-price-current__wrapper">
					{prefix && <span className="multistore-block-price-current__prefix">{prefix} </span>}
					<span className="multistore-block-price-current__value">{mockPrice}</span>
					{suffix && <span className="multistore-block-price-current__suffix"> {suffix}</span>}
				</span>
			</div>
		</>
	);
}
