import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, TextControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { showLabel, labelOn, labelOff } = attributes;

	const blockProps = useBlockProps({
		className: 'multistore-block-product-grouping-toggle',
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Ustawienia', 'multistore')}>
					<ToggleControl
						label={__('Pokaż etykietę', 'multistore')}
						checked={showLabel}
						onChange={(value) => setAttributes({ showLabel: value })}
					/>
					<TextControl
						label={__('Etykieta (włączone)', 'multistore')}
						value={labelOn}
						onChange={(value) => setAttributes({ labelOn: value })}
					/>
					<TextControl
						label={__('Etykieta (wyłączone)', 'multistore')}
						value={labelOff}
						onChange={(value) => setAttributes({ labelOff: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<button
					type="button"
					className="multistore-block-product-grouping-toggle__button multistore-block-product-grouping-toggle__button--on"
					disabled
				>
					<svg
						className="multistore-block-product-grouping-toggle__icon multistore-block-product-grouping-toggle__icon--grouped"
						width="20"
						height="20"
						viewBox="0 0 20 20"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
						aria-hidden="true"
					>
						<rect x="2" y="2" width="7" height="7" rx="1" fill="currentColor" />
						<rect x="11" y="2" width="7" height="7" rx="1" fill="currentColor" />
						<rect x="2" y="11" width="7" height="7" rx="1" fill="currentColor" />
						<rect x="11" y="11" width="7" height="7" rx="1" fill="currentColor" />
					</svg>

					{showLabel && (
						<span className="multistore-block-product-grouping-toggle__label">
							{labelOn}
						</span>
					)}
				</button>
			</div>
		</>
	);
}
