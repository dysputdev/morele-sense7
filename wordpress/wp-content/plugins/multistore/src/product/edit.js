import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, Button, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

/**
 * Product Search Component
 */
function ProductSearch({ value, onChange, selectedPost }) {
	const [searchQuery, setSearchQuery] = useState('');
	const [searchResults, setSearchResults] = useState([]);
	const [isSearching, setIsSearching] = useState(false);
	const [showResults, setShowResults] = useState(false);

	// Search products via AJAX
	useEffect(() => {
		if (searchQuery.length < 2) {
			setSearchResults([]);
			return;
		}

		setIsSearching(true);

		const searchTimeout = setTimeout(() => {
			apiFetch({
				path: `/wp/v2/product?search=${encodeURIComponent(searchQuery)}&per_page=20&status=publish`,
			})
				.then((results) => {
					setSearchResults(results);
					setIsSearching(false);
					setShowResults(true);
				})
				.catch((error) => {
					console.error('Product search error:', error);
					setIsSearching(false);
					setSearchResults([]);
				});
		}, 300);

		return () => clearTimeout(searchTimeout);
	}, [searchQuery]);

	const handleSelectProduct = (productId, productTitle) => {
		onChange(productId);
		setSearchQuery('');
		setShowResults(false);
		setSearchResults([]);
	};

	const handleClearSelection = () => {
		onChange(0);
		setSearchQuery('');
		setShowResults(false);
	};

	return (
		<div className="multistore-product-search">
			{selectedPost ? (
				<div className="multistore-product-search__selected">
					<div className="multistore-product-search__selected-info">
						<strong>{__('Wybrany produkt:', 'multistore')}</strong>
						<div className="multistore-product-search__selected-title">
							{selectedPost.title.rendered}
						</div>
						<div className="multistore-product-search__selected-id">
							ID: {selectedPost.id}
						</div>
					</div>
					<Button
						isDestructive
						isSmall
						onClick={handleClearSelection}
					>
						{__('Usuń wybór', 'multistore')}
					</Button>
				</div>
			) : (
				<div className="multistore-product-search__input-wrapper">
					<input
						type="text"
						className="multistore-product-search__input"
						placeholder={__('Wpisz nazwę produktu...', 'multistore')}
						value={searchQuery}
						onChange={(e) => setSearchQuery(e.target.value)}
						onFocus={() => searchResults.length > 0 && setShowResults(true)}
						onBlur={() => setTimeout(() => setShowResults(false), 200)}
					/>
					{isSearching && (
						<div className="multistore-product-search__spinner">
							<Spinner />
						</div>
					)}
					{showResults && searchResults.length > 0 && (
						<div className="multistore-product-search__results">
							{searchResults.map((product) => (
								<button
									key={product.id}
									type="button"
									className="multistore-product-search__result-item"
									onClick={() => handleSelectProduct(product.id, product.title.rendered)}
								>
									<span className="multistore-product-search__result-title">
										{product.title.rendered}
									</span>
									<span className="multistore-product-search__result-id">
										ID: {product.id}
									</span>
								</button>
							))}
						</div>
					)}
					{showResults && searchQuery.length >= 2 && searchResults.length === 0 && !isSearching && (
						<div className="multistore-product-search__no-results">
							{__('Nie znaleziono produktów', 'multistore')}
						</div>
					)}
				</div>
			)}
		</div>
	);
}

export default function Edit({ attributes, setAttributes }) {
	const { postId, postType } = attributes;
	const blockProps = useBlockProps();

	// Get selected post data
	const { selectedPost } = useSelect(
		(select) => {
			const { getEntityRecord } = select(coreStore);

			return {
				selectedPost: postId ? getEntityRecord('postType', postType, postId) : null,
			};
		},
		[postId, postType]
	);

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
				<PanelBody title={__('Ustawienia produktu', 'multistore')}>
					<ProductSearch
						value={postId}
						onChange={(value) => setAttributes({ postId: parseInt(value) })}
						selectedPost={selectedPost}
					/>
					<p className="description">
						{__('Wyszukaj produkt, którego dane będą wyświetlane przez bloki wewnętrzne', 'multistore')}
					</p>
					<ToggleControl
						label={__('Jako odnośnik', 'multistore')}
						checked={attributes.isLink}
						onChange={(value) => setAttributes({ isLink: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
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