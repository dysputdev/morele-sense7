import { useBlockProps, InnerBlocks, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	return (
		<div {...useBlockProps.save( { className: 'multistore-block-megamenu' })}>
			<InnerBlocks.Content />
		</div>
	)
}