import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	ToggleControl,
	Placeholder,
	Spinner,
} from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import Splide from '@splidejs/splide';
import './editor.scss';

export default function Edit( { attributes, setAttributes, context } ) {
	const { showFeaturedImage, mainSlider, thumbnailSlider } = attributes;
	const { postId, postType } = context;

	const [ productImages, setProductImages ] = useState( [] );
	const mainSliderRef = useRef( null );
	const thumbnailSliderRef = useRef( null );
	const splideMainInstance = useRef( null );
	const splideThumbnailInstance = useRef( null );

	const blockProps = useBlockProps( {
		className: 'multistore-block-product-gallery',
	} );

	// Get product data from WordPress.
	const { product, featuredImage, galleryImages, isResolving } = useSelect(
		( select ) => {
			if ( ! postId || postType !== 'product' ) {
				return {
					product: null,
					featuredImage: null,
					galleryImages: [],
					isResolving: false,
				};
			}

			const { getEntityRecord, getMedia, isResolving: isResolvingSelector } = select( coreStore );

			const productData = getEntityRecord( 'postType', 'product', postId );
			const featuredMediaId = productData?.featured_media;
			const galleryIds = productData?.meta?._product_image_gallery
				? productData.meta._product_image_gallery.split( ',' ).map( ( id ) => parseInt( id ) ).filter( ( id ) => id > 0 )
				: [];

			return {
				product: productData,
				featuredImage: featuredMediaId ? getMedia( featuredMediaId ) : null,
				galleryImages: galleryIds.map( ( id ) => getMedia( id ) ).filter( Boolean ),
				isResolving: isResolvingSelector( 'getEntityRecord', [ 'postType', 'product', postId ] ) ||
					( featuredMediaId && isResolvingSelector( 'getMedia', [ featuredMediaId ] ) ) ||
					galleryIds.some( ( id ) => isResolvingSelector( 'getMedia', [ id ] ) ),
			};
		},
		[ postId, postType ]
	);

	// Build images array from product data.
	useEffect( () => {
		const images = [];

		if ( showFeaturedImage && featuredImage ) {
			images.push( {
				id: featuredImage.id,
				url: featuredImage.source_url,
				alt: featuredImage.alt_text || '',
			} );
		}

		if ( galleryImages && galleryImages.length > 0 ) {
			galleryImages.forEach( ( img ) => {
				if ( img ) {
					images.push( {
						id: img.id,
						url: img.source_url,
						alt: img.alt_text || '',
					} );
				}
			} );
		}

		setProductImages( images );
	}, [ featuredImage, galleryImages, showFeaturedImage ] );

	// Initialize Splide sliders in editor.
	useEffect( () => {
		// Destroy existing instances.
		if ( splideMainInstance.current ) {
			splideMainInstance.current.destroy();
			splideMainInstance.current = null;
		}
		if ( splideThumbnailInstance.current ) {
			splideThumbnailInstance.current.destroy();
			splideThumbnailInstance.current = null;
		}

		// Don't initialize if no images.
		if ( ! mainSliderRef.current || ! thumbnailSliderRef.current || productImages.length === 0 ) {
			return;
		}

		// Small delay to ensure DOM is ready.
		const timer = setTimeout( () => {
			const mainElement = mainSliderRef.current;
			const thumbnailElement = thumbnailSliderRef.current;

			if ( ! mainElement || ! thumbnailElement ) {
				return;
			}

			// Initialize main slider.
			splideMainInstance.current = new Splide( mainElement, {
				type: mainSlider.type || 'slide',
				rewind: mainSlider.rewind || true,
				speed: mainSlider.speed || 400,
				arrows: false,
				pagination: false,
				drag: true,
				perPage: 1,
			} );

			// Initialize thumbnail slider.
			splideThumbnailInstance.current = new Splide( thumbnailElement, {
				type: thumbnailSlider.type || 'slide',
				rewind: thumbnailSlider.rewind || true,
				speed: thumbnailSlider.speed || 400,
				arrows: true,
				pagination: false,
				isNavigation: true,
				perPage: thumbnailSlider.perPage || 4,
				gap: thumbnailSlider.gap || '10px',
				focus: thumbnailSlider.focus || 'center',
				drag: true,
			} );

			// Sync sliders.
			splideMainInstance.current.sync( splideThumbnailInstance.current );

			// Mount both.
			splideMainInstance.current.mount();
			splideThumbnailInstance.current.mount();
		}, 100 );

		return () => {
			clearTimeout( timer );
			if ( splideMainInstance.current ) {
				splideMainInstance.current.destroy();
				splideMainInstance.current = null;
			}
			if ( splideThumbnailInstance.current ) {
				splideThumbnailInstance.current.destroy();
				splideThumbnailInstance.current = null;
			}
		};
	}, [ productImages, mainSlider, thumbnailSlider ] );

	// Check if we're in the right context.
	if ( ! postId || postType !== 'product' ) {
		return (
			<div { ...blockProps }>
				<Placeholder
					icon="images-alt"
					label={ __( 'Product Gallery', 'multistore' ) }
					instructions={ __( 'This block can only be used in a product context.', 'multistore' ) }
				/>
			</div>
		);
	}

	// Show loading state.
	if ( isResolving ) {
		return (
			<div { ...blockProps }>
				<Placeholder
					icon="images-alt"
					label={ __( 'Product Gallery', 'multistore' ) }
				>
					<Spinner />
				</Placeholder>
			</div>
		);
	}

	// Show empty state if no images.
	if ( productImages.length === 0 ) {
		return (
			<div { ...blockProps }>
				<Placeholder
					icon="images-alt"
					label={ __( 'Product Gallery', 'multistore' ) }
					instructions={ __( 'No images found for this product. Please add images in the product gallery.', 'multistore' ) }
				/>
			</div>
		);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Gallery Settings', 'multistore' ) }>
					<ToggleControl
						label={ __( 'Show Featured Image', 'multistore' ) }
						checked={ showFeaturedImage }
						onChange={ ( value ) =>
							setAttributes( { showFeaturedImage: value } )
						}
						help={ __( 'Include the featured image in the gallery.', 'multistore' ) }
					/>
				</PanelBody>

				<PanelBody title={ __( 'Thumbnail Settings', 'multistore' ) } initialOpen={ false }>
					<RangeControl
						label={ __( 'Thumbnails Per Page', 'multistore' ) }
						value={ thumbnailSlider.perPage || 4 }
						onChange={ ( value ) =>
							setAttributes( {
								thumbnailSlider: { ...thumbnailSlider, perPage: value },
							} )
						}
						min={ 2 }
						max={ 8 }
					/>
					<RangeControl
						label={ __( 'Gap Between Thumbnails (px)', 'multistore' ) }
						value={ parseInt( thumbnailSlider.gap ) || 10 }
						onChange={ ( value ) =>
							setAttributes( {
								thumbnailSlider: { ...thumbnailSlider, gap: `${ value }px` },
							} )
						}
						min={ 0 }
						max={ 50 }
					/>
					<ToggleControl
						label={ __( 'Show Arrows', 'multistore' ) }
						checked={ thumbnailSlider.arrows || false }
						onChange={ ( value ) =>
							setAttributes( {
								thumbnailSlider: { ...thumbnailSlider, arrows: value },
							} )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{/* Main Slider */}
				<div className="multistore-block-product-gallery__main">
					<div
						ref={ mainSliderRef }
						className="splide multistore-block-product-gallery__main-slider"
					>
						<div className="splide__track">
							<ul className="splide__list">
								{ productImages.map( ( image ) => (
									<li key={ image.id } className="splide__slide">
										<img
											src={ image.url }
											alt={ image.alt }
											className="multistore-block-product-gallery__image"
										/>
									</li>
								) ) }
							</ul>
						</div>
					</div>
				</div>

				{/* Thumbnail Slider */}
				<div className="multistore-block-product-gallery__thumbnails">
					<div
						ref={ thumbnailSliderRef }
						className="splide multistore-block-product-gallery__thumbnail-slider"
					>
						{ thumbnailSlider.arrows && (
							<div className="splide__arrows">
								<button className="splide__arrow splide__arrow--prev" type="button">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										viewBox="0 0 40 40"
										width="40"
										height="40"
									>
										<path d="m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z"></path>
									</svg>
								</button>
								<button className="splide__arrow splide__arrow--next" type="button">
									<svg
										xmlns="http://www.w3.org/2000/svg"
										viewBox="0 0 40 40"
										width="40"
										height="40"
									>
										<path d="m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z"></path>
									</svg>
								</button>
							</div>
						) }

						<div className="splide__track">
							<ul className="splide__list">
								{ productImages.map( ( image ) => (
									<li key={ image.id } className="splide__slide">
										<img
											src={ image.url }
											alt={ image.alt }
											className="multistore-block-product-gallery__thumbnail"
										/>
									</li>
								) ) }
							</ul>
						</div>
					</div>
				</div>
			</div>
		</>
	);
}
