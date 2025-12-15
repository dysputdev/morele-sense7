<?php
/**
 * Splide Configuration Helper
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Block\Slider;

/**
 * Build Splide configuration from block attributes
 *
 * @param array $attributes Block attributes containing desktop/tablet/mobile settings.
 * @return array Splide configuration array
 */
function build_splide_config( $attributes ) {
	$desktop = isset( $attributes['desktop'] ) ? $attributes['desktop'] : array();
	$tablet  = isset( $attributes['tablet'] ) ? $attributes['tablet'] : array();
	$mobile  = isset( $attributes['mobile'] ) ? $attributes['mobile'] : array();

	// Custom attributes that aren't part of Splide options.
	$custom_attrs = array(
		'arrowsPosition',
		'arrowsStyle',
		'arrowsClass',
		'arrowPath',
		'paginationType',
		'paginationPosition',
		'paginationClass',
	);

	// Filter out custom attributes.
	$config = array_diff_key( $desktop, array_flip( $custom_attrs ) );

	// Build breakpoints object for responsive settings.
	$breakpoints = array();

	// Tablet breakpoint (768px).
	if ( ! empty( $tablet ) ) {
		$breakpoints[768] = array_diff_key( $tablet, array_flip( $custom_attrs ) );
	}

	// Mobile breakpoint (567px).
	if ( ! empty( $mobile ) ) {
		$breakpoints[567] = array_diff_key( $mobile, array_flip( $custom_attrs ) );
	}

	// Add breakpoints to config if they exist.
	if ( ! empty( $breakpoints ) ) {
		$config['breakpoints'] = $breakpoints;
	}

	// Clean up config - remove empty strings and nulls.
	$config = array_filter(
		$config,
		function ( $value ) {
			return $value !== '' && $value !== null;
		}
	);

	// Convert numeric strings to numbers for certain properties.
	$numeric_props = array( 'width', 'height', 'fixedWidth', 'fixedHeight', 'gap', 'padding' );
	foreach ( $numeric_props as $prop ) {
		if ( isset( $config[ $prop ] ) && is_string( $config[ $prop ] ) && $config[ $prop ] !== '' ) {
			$num = floatval( $config[ $prop ] );
			if ( ! is_nan( $num ) ) {
				$config[ $prop ] = $num;
			}
		}
	}

	// Handle conditional options based on type.
	$type = isset( $config['type'] ) ? $config['type'] : 'slide';

	if ( $type === 'fade' ) {
		// Fade type restrictions.
		unset( $config['perPage'] );
		unset( $config['perMove'] );
		unset( $config['gap'] );
		unset( $config['padding'] );
		unset( $config['focus'] );
		unset( $config['autoWidth'] );
		unset( $config['dragFree'] );
	}

	if ( $type === 'loop' ) {
		// Loop type restrictions.
		unset( $config['rewind'] );
		unset( $config['rewindSpeed'] );
		unset( $config['rewindByDrag'] );
	} else {
		// Only loop type can use clones.
		unset( $config['clones'] );
	}

	// Handle autoWidth/autoHeight conflicts.
	$auto_width  = isset( $config['autoWidth'] ) ? $config['autoWidth'] : false;
	$auto_height = isset( $config['autoHeight'] ) ? $config['autoHeight'] : false;

	if ( $auto_width || $auto_height ) {
		unset( $config['perPage'] );
		unset( $config['perMove'] );
	}

	// Handle dependent options.
	$rewind = isset( $config['rewind'] ) ? $config['rewind'] : false;
	if ( ! $rewind ) {
		unset( $config['rewindSpeed'] );
		unset( $config['rewindByDrag'] );
	}

	$drag = isset( $config['drag'] ) ? $config['drag'] : false;
	if ( ! $drag ) {
		unset( $config['dragFree'] );
		unset( $config['dragMinThreshold'] );
		unset( $config['flickPower'] );
		unset( $config['flickMaxPages'] );
	}

	$wheel = isset( $config['wheel'] ) ? $config['wheel'] : false;
	if ( ! $wheel ) {
		unset( $config['wheelSleep'] );
		unset( $config['releaseWheel'] );
	}

	$autoplay = isset( $config['autoplay'] ) ? $config['autoplay'] : false;
	if ( ! $autoplay || $autoplay === 'false' ) {
		unset( $config['interval'] );
		unset( $config['pauseOnHover'] );
		unset( $config['pauseOnFocus'] );
		unset( $config['resetProgress'] );
	}

	$lazy_load = isset( $config['lazyLoad'] ) ? $config['lazyLoad'] : 'off';
	if ( ! $lazy_load || $lazy_load === 'off' ) {
		unset( $config['preloadPages'] );
	}

	// Remove arrows and pagination as they're handled separately in HTML.
	unset( $config['arrows'] );
	unset( $config['pagination'] );

	return $config;
}

/**
 * Helper function to get value with fallback.
 *
 * @param array  $params - device attributes.
 * @param string $param_key - attribute key.
 * @param mixed  $default_value - default value.
 * @return mixed
 */
function get_value( $params, $param_key, $default_value = '' ) {
	return isset( $params[ $param_key ] ) && $params[ $param_key ] ? $params[ $param_key ] : $default_value;
}

/**
 * Build slider data attributes for arrows and navigation
 *
 * @param array $attributes Block attributes containing desktop/tablet/mobile settings.
 * @return string Splide layout data attributes
 */
function get_slider_data_attributes( $attributes ) {
	$attr = array();

	$params = array(
		'desktop' => isset( $attributes['desktop'] ) ? $attributes['desktop'] : array(),
		'tablet'  => isset( $attributes['tablet'] ) ? $attributes['tablet'] : array(),
		'mobile'  => isset( $attributes['mobile'] ) ? $attributes['mobile'] : array(),
	);

	foreach ( $params as $device => $device_params ) {
		$default_arrow_show     = ( 'desktop' === $device ) ? true : null;
		$default_arrow_position = ( 'desktop' === $device ) ? 'center-inside' : '';
		$arrow_show             = get_value( $device_params, 'arrows', $default_arrow_show );
		$arrow_position         = get_value( $device_params, 'arrowsPosition', $default_arrow_position );

		$defaulg_pagination_show     = ( 'desktop' === $device ) ? true : null;
		$default_pagination_position = ( 'desktop' === $device ) ? 'bottom' : '';
		$pagination_show             = get_value( $device_params, 'pagination', $defaulg_pagination_show );
		$pagination_position         = get_value( $device_params, 'paginationPosition', $default_pagination_position );

		if ( $arrow_show ) {
			$attr_name          = sprintf( 'arrows-%s-visible', esc_attr( $device ) );
			$attr[ $attr_name ] = 'true';
		}

		if ( $arrow_position ) {
			$attr_name          = sprintf( 'arrows-%s-position', esc_attr( $device ) );
			$attr[ $attr_name ] = $arrow_position;
		}

		if ( $pagination_show ) {
			$attr_name          = sprintf( 'pagination-%s-visible', esc_attr( $device ) );
			$attr[ $attr_name ] = 'true';
		}

		if ( $pagination_position ) {
			$attr_name          = sprintf( 'pagination-%s-position', esc_attr( $device ) );
			$attr[ $attr_name ] = $pagination_position;
		}
	}

	$data_attr_string = '';
	foreach ( $attr as $attr_name => $attr_value ) {
		$data_attr_string .= ' data-' . esc_attr( $attr_name ) . '="' . esc_attr( $attr_value ) . '"';
	}

	return $data_attr_string;
}

function get_device_attributes( $attributes ) {
	return array(
		'desktop' => isset( $attributes['desktop'] ) ? $attributes['desktop'] : array(),
		'tablet'  => isset( $attributes['tablet'] ) ? $attributes['tablet'] : array(),
		'mobile'  => isset( $attributes['mobile'] ) ? $attributes['mobile'] : array(),
	);
}

function get_arrow_path( $attributes ) {
	$device_attributes = get_device_attributes( $attributes );
	return get_value(
		$device_attributes['desktop'],
		'arrowPath',
		'm15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z'
	);
}

function get_arrow_classes( $attributes ) {
	$classes = array(
		'splide__arrows',
	);

	// get class for each device.
	$device_attributes = get_device_attributes( $attributes );

	foreach ( $device_attributes as $device => $device_attributes ) {
		if ( ! isset( $device_attributes[ $device ]['arrows'] ) ) {
			continue;
		}

		$device_attributes[ $device ]['arrows'] = (bool) $device_attributes[ $device ]['arrows'];

		if ( ! empty( $device_attributes[ $device ]['arrowsClass']['arrowsClass'] ) ) {
			$classes[] = $device_attributes[ $device ]['arrowsClass']['arrowsClass'];
		}
	}

	return join( ' ', $classes );
}

function get_pagination_classes( $attributes ) {
	$classes = array(
		'splide__pagination',
	);

	$device_attributes = get_device_attributes( $attributes );

	foreach ( $device_attributes as $device => $device_attributes ) {
		if ( ! isset( $device_attributes[ $device ]['pagination'] ) ) {
			continue;
		}

		$device_attributes[ $device ]['pagination'] = (bool) $device_attributes[ $device ]['pagination'];

		if ( ! empty( $device_attributes[ $device ]['paginationClass']['paginationClass'] ) ) {
			$classes[] = $device_attributes[ $device ]['paginationClass']['paginationClass'];
		}
	}

	return join( ' ', $classes );
}

/**
 * Wrapper function for global access
 */
if ( ! function_exists( 'multistore_build_splide_config' ) ) {
	/**
	 * Build Splide configuration from block attributes
	 *
	 * @param array $attributes Block attributes.
	 * @return array Splide configuration.
	 */
	function multistore_build_splide_config( $attributes ) {
		return build_splide_config( $attributes );
	}
}
