/**
 * Frontend JavaScript for Related Products Slider
 *
 * @package MultiStore\Plugin
 */

import Splide from '@splidejs/splide';

document.addEventListener( 'DOMContentLoaded', () => {
	const sliders = document.querySelectorAll( '.multistore-block-related-products-slider .splide' );

	if ( ! sliders.length ) {
		return;
	}

	sliders.forEach( ( slider ) => {
		// Get configuration from data attribute.
		let config = {};
		const configData = slider.getAttribute( 'data-splide' );

		if ( configData ) {
			try {
				config = JSON.parse( configData );
			} catch ( e ) {
				console.error( 'Failed to parse Splide config:', e );
			}
		}

		// Initialize Splide with configuration.
		new Splide( slider, config ).mount();
	} );
} );
