/**
 * Editor scripts for MultiStore plugin
 *
 * @package MultiStore\Plugin
 */

import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Register Related Products Slider variation for core/query block
 */
registerBlockVariation( 'core/query', {
	name: 'multistore/related-products-slider',
	title: __( 'Related Products Slider', 'multistore' ),
	description: __( 'Wyświetla powiązane produkty jako slider', 'multistore' ),
	icon: 'slides',
	category: 'woocommerce',
	isActive: ( blockAttributes ) => {
		return blockAttributes.namespace === 'multistore/related-products-slider';
	},
	attributes: {
		namespace: 'multistore/related-products-slider',
		query: {
			postType: 'product',
			inherit: false,
			perPage: 10,
		},
		sliderDesktop: {
			perPage: 4,
			gap: 20,
			arrows: true,
			pagination: true,
			autoplay: false,
			speed: 400,
			rewind: true,
		},
		sliderTablet: {
			perPage: 3,
			gap: 15,
		},
		sliderMobile: {
			perPage: 1,
			gap: 10,
		},
	},
	scope: [ 'inserter' ],
	innerBlocks: [
		[
			'core/post-template',
			{},
			[
				[ 'woocommerce/product-image' ],
				[ 'core/post-title' ],
				[ 'woocommerce/product-price' ],
			],
		],
	],
} );
