<?php

namespace MultiStore\Plugin\Block\Icon;

use DOMDocument;

/**
 * Dodaj zmienne JavaScript dla pluginu
 */
function block_enqueue_scripts() {
	if ( is_admin() ) {
		// Dodatkowe skrypty dla panelu administracyjnego.
		wp_localize_script(
			'multistore-icon-editor-script',
			'MultiStoreData',
			array(
				'iconsUrl'  => MULTISTORE_PLUGIN_URL . 'assets/icons/',
				'iconsPath' => MULTISTORE_PLUGIN_DIR . 'assets/icons/',
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'icon_nonce' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\block_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\block_enqueue_scripts' );


/**
 * AJAX endpoint do pobierania listy ikon
 */
function get_icon_list() {
	// Sprawdź nonce.
	if ( ! wp_verify_nonce( $_POST['nonce'], 'icon_nonce' ) ) {
		wp_die( 'Błąd bezpieczeństwa' );
	}

	$icons_dir   = 'assets/icons/';
	$icons_path  = MULTISTORE_PLUGIN_DIR . $icons_dir;
	$icons_url   = MULTISTORE_PLUGIN_URL . $icons_dir;
	$icons       = array();
	$directories = array();

	if ( is_dir( $icons_path ) ) {
		$direcotry = scandir( $icons_path );
		foreach ( $direcotry as $file ) {
			if ( ! is_dir( $icons_path . $file ) ) {
				continue;
			}
			if ( '.' !== $file && '..' !== $file ) {
				$directories[ 'multistore/' . $file ] = array(
					'url'   => $icons_url . $file,
					'path'  => $icons_path . $file,
					'name'  => $file,
					'icons' => array(),
				);
			}
		}
	}

	apply_filters( 'multistore_icon_dir', $directories );

	foreach ( $directories as $package => $details ) {
		// only directories.
		if ( ! isset( $details['path'] ) || empty( $details['path'] ) || ! is_dir( $details['path'] ) ) {
			continue;
		}
		$icon_pack = $details['name'];

		$icons[ $icon_pack ] = array();

		$files = scandir( $details['path'] );
		foreach ( $files as $file ) {
			if ( 'svg' === pathinfo( $file, PATHINFO_EXTENSION ) ) {
				$icons[ $icon_pack ][] = $file;
			}
		}

		if ( empty( $icons[ $icon_pack ] ) ) {
			unset( $icons[ $icon_pack ] );
		}
	}

	wp_send_json_success( $icons );
}
add_action( 'wp_ajax_get_icon_list', __NAMESPACE__ . '\get_icon_list' );
add_action( 'wp_ajax_nopriv_get_icon_list', __NAMESPACE__ . '\get_icon_list' );

/**
 * Oczyszczenie SVG
 *
 * @param string $svg_content - Zawartość SVG.
 * @return string
 */
function get_clear_svg_content( $svg_content, $icon_size ) {

	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadXML( $svg_content );

	// Pobierz element <svg>.
	$svg = $dom->getElementsByTagName( 'svg' )->item( 0 );

	if ( $svg ) {
		if ( $icon_size ) {
			$svg->setAttribute( 'width', $icon_size );
			$svg->setAttribute( 'height', $icon_size );
		}
		$svg->setAttribute( 'class', 'multistore-icon' );
		$svg->setAttribute( 'role', 'img' );
		$svg->setAttribute( 'aria-hidden', 'true' );
	}

	$defaults = array(
		'fill'            => true,
		'stroke'          => true,
		'stroke-width'    => true,
		'stroke-linecap'  => true,
		'stroke-linejoin' => true,
		'class'           => true,
		'mask'            => true,
		'clip-path'       => true,
		'style'           => true,
		'transform'       => true,
	);

	$svg_content = $dom->saveXML();

	$allowed_tags = array(
		'svg'      => array(
			'class'       => true,
			'role'        => true,
			'aria-hidden' => true,
			'xmlns'       => true,
			'width'       => true,
			'height'      => true,
			'viewbox'     => true,
			'viewBox'     => true,
			'fill'        => true,
			'stroke'      => true,
		),
		'path'     => array_merge( $defaults, array( 'd' => true ) ),
		'circle'   => array_merge(
			$defaults,
			array(
				'cx' => true,
				'cy' => true,
				'r'  => true,
			)
		),
		'rect'     => array_merge(
			$defaults,
			array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'rx'     => true,
				'ry'     => true,
			)
		),
		'line'     => array_merge(
			$defaults,
			array(
				'x1' => true,
				'y1' => true,
				'x2' => true,
				'y2' => true,
			)
		),
		'polyline' => array_merge( $defaults, array( 'points' => true ) ),
		'polygon'  => array_merge( $defaults, array( 'points' => true ) ),
		'g'        => array_merge( $defaults, array() ),
		'defs'     => array(),
		'style'    => array(),
		'mask'     => array(),
	);

	return wp_kses( $svg_content, $allowed_tags );
}
