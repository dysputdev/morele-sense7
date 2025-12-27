import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

	export default function Save({ attributes }) {
const { sectionId } = attributes;
	// return <InnerBlocks.Content />;
	
	const blockProps = useBlockProps.save({
		className: 'multistore-block-scrollspy-section',
		id: sectionId
	});

	return (
		<div {...blockProps}>
			<InnerBlocks.Content />
		</div>
	);
}
