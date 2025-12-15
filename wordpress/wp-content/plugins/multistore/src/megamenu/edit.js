import { __ } from '@wordpress/i18n';
import { InnerBlocks, RichText, useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	
	const blockProps = useBlockProps({
		className: 'multistore-block-megamenu',
	});
	
	const template = [
		['core/group']
	];

	return (
		<div {...blockProps}>
			<InnerBlocks template={template} />
		</div>
	);
}
