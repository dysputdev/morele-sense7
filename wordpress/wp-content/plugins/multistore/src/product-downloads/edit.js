import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes, context }) {
	const { showTitle, title } = attributes;

	const blockProps = useBlockProps({
		className: 'multistore-block-product-downloads',
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Ustawienia plików do pobrania', 'multistore')} initialOpen={true}>
					<ToggleControl
						label={__('Pokaż tytuł', 'multistore')}
						checked={showTitle}
						onChange={(value) => setAttributes({ showTitle: value })}
						help={__('Wyświetl tytuł nad listą plików', 'multistore')}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{showTitle && (
					<RichText
						tagName="h2"
						className="multistore-block-product-downloads__title"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
						placeholder={__('Do pobrania', 'multistore')}
					/>
				)}

				<ul className="multistore-block-product-downloads__list">
					<li className="multistore-block-product-downloads__item">
						<a href="#" className="multistore-block-product-downloads__link">
							<span className="multistore-block-product-downloads__icon">📄</span>
							<span className="multistore-block-product-downloads__name">
								{__('Instrukcja_uzytkownika_Sense7_Nomad.pdf', 'multistore')}
							</span>
						</a>
					</li>
					<li className="multistore-block-product-downloads__item">
						<a href="#" className="multistore-block-product-downloads__link">
							<span className="multistore-block-product-downloads__icon">📄</span>
							<span className="multistore-block-product-downloads__name">
								{__('Warunki_gwarancji.pdf', 'multistore')}
							</span>
						</a>
					</li>
				</ul>
			</div>
		</>
	);
}
