import Splide from '@splidejs/splide';

document.addEventListener( 'DOMContentLoaded', () => {
	const galleries = document.querySelectorAll( '.multistore-block-product-gallery' );

	if ( ! galleries.length ) {
		return;
	}

	galleries.forEach( ( gallery ) => {

		
		const mainSliderElement = gallery.querySelector( '.multistore-block-product-gallery__main-slider' );
		const thumbnailSliderElement = gallery.querySelector( '.multistore-block-product-gallery__thumbnail-slider' );

		if ( ! mainSliderElement || ! thumbnailSliderElement ) {
			return;
		}

		// Get configurations from data attributes.
		let mainConfig = {};
		let thumbnailConfig = {};

		const mainConfigData = mainSliderElement.getAttribute( 'data-splide' );
		const thumbnailConfigData = thumbnailSliderElement.getAttribute( 'data-splide' );

		if ( mainConfigData ) {
			try {
				mainConfig = JSON.parse( mainConfigData );
			} catch ( e ) {
				console.error( 'Failed to parse main slider config:', e );
			}
		}

		if ( thumbnailConfigData ) {
			try {
				thumbnailConfig = JSON.parse( thumbnailConfigData );
			} catch ( e ) {
				console.error( 'Failed to parse thumbnail slider config:', e );
			}
		}

		// Initialize main slider.
		const mainSlider = new Splide( mainSliderElement, {
			pagination: false,
			navigation: false,
		} );

		// Initialize thumbnail slider.
		const thumbnailSlider = new Splide( thumbnailSliderElement, {
			focus: 'center',
			navigation: true,
			pagination: false,
			cover: true,
			rewind: true,
			fixedWidth: 125,
			fixedHeight: 125,
			gap: "16px",
		} );

		// Sync sliders - main slider controls thumbnails.
		mainSlider.sync( thumbnailSlider );

		// Mount both sliders.
		mainSlider.mount();
		thumbnailSlider.mount();
	} );
} );
