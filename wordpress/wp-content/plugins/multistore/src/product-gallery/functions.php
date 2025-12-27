<?php
/**
 * Product Gallery Helper Functions
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Block\Product_Gallery;

/**
 * Build Splide configuration from slider attributes
 *
 * @param array $slider_config Slider configuration from block attributes.
 * @return array Cleaned Splide configuration.
 */
function build_slider_config( $slider_config ) {
	// Custom attributes that aren't part of Splide options.
	$custom_attrs = array(
		'arrowsPosition',
		'arrowsStyle',
		'arrowsClass',
		'arrowPath',
	);

	// Filter out custom attributes.
	$config = array_diff_key( $slider_config, array_flip( $custom_attrs ) );

	// Clean up config - remove empty strings and nulls.
	$config = array_filter(
		$config,
		function ( $value ) {
			return $value !== '' && $value !== null;
		}
	);

	// Convert gap to number if it's a string with px.
	if ( isset( $config['gap'] ) && is_string( $config['gap'] ) ) {
		$config['gap'] = str_replace( 'px', '', $config['gap'] );
	}

	// Remove arrows and pagination as they're handled separately in HTML.
	unset( $config['arrows'] );
	unset( $config['pagination'] );

	return $config;
}

/**
 * Get arrow path from thumbnail slider config
 *
 * @param array $attributes Block attributes.
 * @return string SVG path for arrows.
 */
function get_arrow_path( $attributes ) {
	$thumbnail_config = isset( $attributes['thumbnailSlider'] ) ? $attributes['thumbnailSlider'] : array();
	return isset( $thumbnail_config['arrowPath'] ) ? $thumbnail_config['arrowPath'] : 'm15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z';
}

/**
 * Check if thumbnail slider should show arrows
 *
 * @param array $attributes Block attributes.
 * @return bool Whether to show arrows.
 */
function show_thumbnail_arrows( $attributes ) {
	$thumbnail_config = isset( $attributes['thumbnailSlider'] ) ? $attributes['thumbnailSlider'] : array();
	return isset( $thumbnail_config['arrows'] ) && $thumbnail_config['arrows'];
}

/**
 * Get arrow position for thumbnails
 *
 * @param array $attributes Block attributes.
 * @return string Arrow position class.
 */
function get_arrow_position( $attributes ) {
	$thumbnail_config = isset( $attributes['thumbnailSlider'] ) ? $attributes['thumbnailSlider'] : array();
	return isset( $thumbnail_config['arrowsPosition'] ) ? $thumbnail_config['arrowsPosition'] : 'center-inside';
}
