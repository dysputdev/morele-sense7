import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

export default function Save({ attributes }) {
	const { wrapperId, scrollOffset, activeClass } = attributes;
	
	const blockProps = useBlockProps.save({
		className: 'multistore-block-scrollspy-wrapper',
		id: wrapperId,
		'data-scrollspy-offset': scrollOffset,
		'data-scrollspy-active-class': activeClass
	});

	return (
		<div {...blockProps}>
			<InnerBlocks.Content />
		</div>
	);
}
