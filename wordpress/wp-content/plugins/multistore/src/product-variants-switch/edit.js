import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes, context }) {
	const { layout, showLabel } = attributes;

	const blockProps = useBlockProps({
		className: 'multistore-block-product-variants',
	});

	return (
		<>
			<div {...blockProps}>
				<div className="multistore-block-product-variants__preview">
					{ __( 'Dostępne warianty', 'multistore' ) }
				</div>
			</div>
		</>
	);
}
