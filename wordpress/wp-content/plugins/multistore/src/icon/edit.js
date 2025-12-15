import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { IconSelectSettings, IconSelectView, IconSizeMap } from '../components/IconSelect';
import { useEffect } from '@wordpress/element';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	
	const { pack, file, size, customSize, position, data } = attributes;

	const blockProps = useBlockProps({
		className: position ? 'multistore-block-icon has-icon-' + position : 'multistore-block-icon',
	});

	// Migracja danych ACF - wykonuje się tylko raz lub gdy zmienią się dane ACF
	useEffect(() => {
		// Sprawdź czy są dane ACF do migracji
		if (data && Object.keys(data).length > 0) {
			const updates = {};

			// Migracja file jeśli nie jest ustawione lub jest domyślne
			if ((!file || file === '') && data.icon) {
				updates.file = data.icon + '.svg';
			}

			// Migracja size jeśli jest domyślne i istnieją dane ACF
			if (size === 'default' && data.size && data.size !== 'default') {
				if (IconSizeMap[data.size]) {
					updates.size = data.size; // Zachowaj klucz, nie wartość numeryczną
				} else {
					updates.customSize = parseInt(data.size);
					updates.size = 'custom';
				}
			}

			updates.data = {};
			setAttributes(updates);
		}
	}, [data, file, size]);	

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title={ __( 'Ustawienia ikony', 'multistore' ) } initialOpen={true}>
					<IconSelectSettings
						attributes={ attributes }
						setAttributes={ setAttributes }
					/>
				</PanelBody>
			</InspectorControls>

			<IconSelectView
				file={ file }
				pack={ pack }
				size={ size }
				customSize={ customSize }
				// position={ position }
			/>
		</div>
	);
}