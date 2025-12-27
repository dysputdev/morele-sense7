import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes, context }) {
	const { showTitle, title } = attributes;

	const blockProps = useBlockProps({
		className: 'multistore-block-product-specifications',
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Ustawienia specyfikacji', 'multistore')} initialOpen={true}>
					<ToggleControl
						label={__('Pokaż tytuł', 'multistore')}
						checked={showTitle}
						onChange={(value) => setAttributes({ showTitle: value })}
						help={__('Wyświetl tytuł nad tabelą specyfikacji', 'multistore')}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{showTitle && (
					<RichText
						tagName="h2"
						className="multistore-block-product-specifications__title"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
						placeholder={__('Specyfikacja', 'multistore')}
					/>
				)}

				<table className="multistore-block-product-specifications__table">
					<tbody className="multistore-block-product-specifications__tbody">
						<tr className="multistore-block-product-specifications__row">
							<td className="multistore-block-product-specifications__label">
								{__('Blat', 'multistore')}
							</td>
							<td className="multistore-block-product-specifications__value">
								{__('płyta MDF (2 elementy) z folią typu carbon', 'multistore')}
							</td>
						</tr>
						<tr className="multistore-block-product-specifications__row">
							<td className="multistore-block-product-specifications__label">
								{__('Konstrukcja', 'multistore')}
							</td>
							<td className="multistore-block-product-specifications__value">
								{__('stalowa malowana proszkowo', 'multistore')}
							</td>
						</tr>
						<tr className="multistore-block-product-specifications__row">
							<td className="multistore-block-product-specifications__label">
								{__('Kolor', 'multistore')}
							</td>
							<td className="multistore-block-product-specifications__value">
								{__('czarny', 'multistore')}
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</>
	);
}
