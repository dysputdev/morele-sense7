import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

import './editor.scss';

export default function Edit({ attributes, setAttributes, context }) {
	const { currencyFormat, prefix, suffix, showDaysAgo } = attributes;

	// Get postId from context (either from parent block or editor)
	const postId = context['multistore/postId'] || context.postId || (typeof wp !== 'undefined' && wp.data.select('core/editor')?.getCurrentPostId());

	const blockProps = useBlockProps({
		className: 'multistore-block-price-lowest',
	});

	// Format options
	const formatOptions = [
		{ label: __('Domyślny (89,99 zł)', 'multistore'), value: 'default' },
		{ label: __('Bez symbolu (89,99)', 'multistore'), value: 'no_symbol' },
		{ label: __('Tylko symbol (zł)', 'multistore'), value: 'symbol_only' },
		{ label: __('Kod waluty (PLN)', 'multistore'), value: 'code' }
	];

	// Mock price for editor preview
	const mockPrice = '89,99 zł';
	const mockDaysAgo = 15;

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
						placeholder={__('np. Najniższa cena:', 'multistore')}
					/>
					<TextControl
						label={__('Sufiks', 'multistore')}
						value={suffix}
						onChange={(value) => setAttributes({ suffix: value })}
						placeholder={__('np. w ostatnich 30 dniach', 'multistore')}
					/>
					<ToggleControl
						label={__('Pokaż liczbę dni temu', 'multistore')}
						checked={showDaysAgo}
						onChange={(value) => setAttributes({ showDaysAgo: value })}
						help={__('Wyświetl ile dni temu była ta cena', 'multistore')}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<span className="multistore-block-price-lowest__wrapper">
					{prefix && <span className="multistore-block-price-lowest__prefix">{prefix} </span>}
					<span className="multistore-block-price-lowest__value">{mockPrice}</span>
					{suffix && <span className="multistore-block-price-lowest__suffix"> {suffix}</span>}
					{showDaysAgo && <span className="multistore-block-price-lowest__days"> ({mockDaysAgo} {__('dni temu', 'multistore')})</span>}
				</span>
			</div>
		</>
	);
}
