<?php
/**
 * Store Selector Block Functions
 *
 * @package MultiStore\Plugin\Block\StoreSelector
 */

namespace MultiStore\Plugin\Block\StoreSelector;

/**
 * Get all available stores with their languages
 *
 * @since 1.0.0
 * @return array Array of stores with their configuration.
 */
function get_stores_data(): array {
	// Check if required functions exist.
	if ( ! function_exists( 'pll_the_languages' ) || ! is_multisite() ) {
		return array();
	}

	// Get all sites in the network.
	$sites = get_sites(
		array(
			'public'   => 1,
			'archived' => 0,
			'deleted'  => 0,
		)
	);

	if ( empty( $sites ) ) {
		return array();
	}

	$current_blog_id = get_current_blog_id();
	$current_lang    = function_exists( 'pll_current_language' ) ? pll_current_language() : '';

	$stores = array();

	foreach ( $sites as $site ) {
		switch_to_blog( $site->blog_id );

		// Get country code from WooCommerce settings.
		$country_code = get_option( 'woocommerce_default_country', '' );

		// Extract country code (format: PL, DE:Bayern, etc.).
		if ( strpos( $country_code, ':' ) !== false ) {
			$country_code = strtolower( substr( $country_code, 0, strpos( $country_code, ':' ) ) );
		} else {
			$country_code = strtolower( $country_code );
		}

		// Fallback to site name if country code not found.
		if ( empty( $country_code ) ) {
			$site_name    = get_bloginfo( 'name' );
			$country_code = strtolower( substr( $site_name, 0, 2 ) );
		}

		// Default to 'pl' if still empty.
		if ( empty( $country_code ) ) {
			$country_code = 'pl';
		}

		// Get flag URL.
		$flag_path = MULTISTORE_PLUGIN_DIR . 'assets/img/flags/4x3/' . $country_code . '.svg';
		$flag_url  = file_exists( $flag_path )
			? MULTISTORE_PLUGIN_URL . 'assets/img/flags/4x3/' . $country_code . '.svg'
			: MULTISTORE_PLUGIN_URL . 'assets/img/flags/4x3/pl.svg';

		// Get Polylang languages for this site.
		$languages = array();
		if ( function_exists( 'pll_languages_list' ) ) {
			$pll_languages = pll_languages_list( array( 'fields' => 'slug' ) );

			foreach ( $pll_languages as $lang_slug ) {
				$lang_name = '';
				$lang_url  = '';

				if ( function_exists( 'PLL' ) ) {
					$lang_obj  = PLL()->model->get_language( $lang_slug );
					$lang_name = $lang_obj ? $lang_obj->name : strtoupper( $lang_slug );

					// Get URL for this language.
					$lang_url = function_exists( 'pll_home_url' )
						? pll_home_url( $lang_slug )
						: get_home_url( $site->blog_id, '/' );
				}

				$is_active = ( (int) $site->blog_id === (int) $current_blog_id && $lang_slug === $current_lang );

				$languages[] = array(
					'code'      => $lang_slug,
					'name'      => $lang_name,
					'url'       => $lang_url,
					'is_active' => $is_active,
				);
			}
		}

		$stores[] = array(
			'blog_id'      => $site->blog_id,
			'country_code' => $country_code,
			'country_name' => get_bloginfo( 'name' ),
			'flag_url'     => $flag_url,
			'url'          => get_home_url( $site->blog_id ),
			'languages'    => $languages,
		);

		restore_current_blog();
	}

	return $stores;
}

/**
 * Get current store information
 *
 * @since 1.0.0
 * @return array Current store information.
 */
function get_current_store_info(): array {
	$blog_id = get_current_blog_id();

	// Get country code from WooCommerce settings.
	$country_code = get_option( 'woocommerce_default_country', '' );

	// Extract country code (format: PL, DE:Bayern, etc.).
	if ( strpos( $country_code, ':' ) !== false ) {
		$country_code = strtolower( substr( $country_code, 0, strpos( $country_code, ':' ) ) );
	} else {
		$country_code = strtolower( $country_code );
	}

	// Fallback to site name if country code not found.
	if ( empty( $country_code ) ) {
		$site_name    = get_bloginfo( 'name' );
		$country_code = strtolower( substr( $site_name, 0, 2 ) );
	}

	// Default to 'pl' if still empty.
	if ( empty( $country_code ) ) {
		$country_code = 'pl';
	}

	// Get flag URL.
	$flag_path = MULTISTORE_PLUGIN_DIR . 'assets/img/flags/4x3/' . $country_code . '.svg';
	$flag_url  = file_exists( $flag_path )
		? MULTISTORE_PLUGIN_URL . 'assets/img/flags/4x3/' . $country_code . '.svg'
		: MULTISTORE_PLUGIN_URL . 'assets/img/flags/4x3/pl.svg';

	// Get current language from Polylang.
	$current_lang      = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
	$current_lang_name = strtoupper( $current_lang );

	if ( function_exists( 'PLL' ) && ! empty( $current_lang ) ) {
		$lang_obj          = PLL()->model->get_language( $current_lang );
		$current_lang_name = $lang_obj ? $lang_obj->name : strtoupper( $current_lang );
	}

	return array(
		'blog_id'       => $blog_id,
		'country_code'  => strtoupper( $country_code ),
		'country_name'  => get_bloginfo( 'name' ),
		'flag_url'      => $flag_url,
		'language_code' => strtoupper( $current_lang ),
		'language_name' => $current_lang_name,
	);
}

/**
 * Enqueue block scripts
 *
 * @since 1.0.0
 */
function enqueue_block_scripts(): void {
	// Localize script for editor.
	if ( is_admin() ) {
		wp_localize_script(
			'multistore-store-selector-editor-script',
			'multistoreData',
			array(
				'pluginUrl' => MULTISTORE_PLUGIN_URL,
			)
		);
	}
}
add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_block_scripts' );
