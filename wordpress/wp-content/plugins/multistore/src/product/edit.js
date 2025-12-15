import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import './editor.scss';
import { ToggleControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
	const { postId, postType } = attributes;
	const blockProps = useBlockProps();
	
	// Pobierz listę postów
	const { posts, selectedPost, isLoadingPosts } = useSelect(
		(select) => {
			const { getEntityRecords, getEntityRecord, isResolving } = select(coreStore);
			
			return {
				posts: getEntityRecords('postType', 'product', {
					per_page: 100,
					status: 'publish',
					orderby: 'date',
					order: 'desc'
				}),
				selectedPost: postId ? getEntityRecord( 'postType', postType, postId ) : null,
				isLoadingPosts: isResolving( 'getEntityRecords', ['postType', postType ])
			};
		},
		[postId]
	);

	// Przygotuj opcje dla selecta
	const postOptions = posts ? [
		{ label: __( 'Wybierz post...', 'multistore' ), value: 0 },
		...posts.map(post => ({
			label: post.title.rendered || `Post #${post.id}`,
			value: post.id
		}))
	] : [];

	const ALLOWED_BLOCKS = [
		'core/post-title',
		'core/post-date',
		'core/post-excerpt',
		'core/post-featured-image',
		'core/post-author',
		'core/post-author-name',
		'core/post-terms',
		'core/post-content',
		'core/paragraph',
		'core/heading',
		'core/image',
		'core/group',
		'core/columns',
		'core/column',
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Ustawienia posta', 'multistore')}>
					{isLoadingPosts ? (
						<Spinner />
					) : (
						<SelectControl
							label={__('Wybierz post', 'multistore')}
							value={postId}
							options={postOptions}
							onChange={(value) => setAttributes({ postId: parseInt(value) })}
							help={__('Wybierz post, którego dane będą wyświetlane przez bloki wewnętrzne', 'multistore')}
						/>
					)}
					{selectedPost && (
						<div className="multistore-block__selected-post">
							<strong>{__('Wybrany post:', 'multistore')}</strong>
							<br />
							{selectedPost.title.rendered}
						</div>
					)}
					<ToggleControl
						label={__('Jako odnośnik', 'multistore')}
						checked={attributes.isLink}
						onChange={(value) => setAttributes({ isLink: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<InnerBlocks 
					// allowedBlocks={ ALLOWED_BLOCKS }
					template={[
						['core/post-featured-image'],
						['core/post-title'],
					]}
				/>
			</div>
		</>
	);
}