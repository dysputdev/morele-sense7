<?php

namespace MultiStore\Plugin\Block\ProductReviews;

function get_global_query_by_sku( $sku ) {
	global $wpdb;

	$current_blog_id = get_current_blog_id();
	$tables          = array();

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

	$comments_query = array();
	foreach ( $sites as $site ) {
		switch_to_blog( $site->blog_id );

		$store_product_id = wc_get_product_id_by_sku( $sku );
		if ( $store_product_id ) {

			$site_lang = function_exists( 'pll_default_language' ) ? pll_default_language() : 'pl';

			$comments_query[] = $wpdb->prepare(
				"SELECT c.*, cm.meta_value as rating, '%s' as store
					FROM {$wpdb->prefix}comments as c
				LEFT JOIN {$wpdb->prefix}commentmeta AS cm
					ON cm.comment_id = c.comment_ID
					AND cm.meta_key = '%s'
				WHERE c.comment_post_ID = %d
					AND c.comment_approved = 1",
				$site_lang,
				'rating',
				$store_product_id
			);
		}
	}

	// restore current blog.
	switch_to_blog( $current_blog_id );

	if ( empty( $comments_query ) ) {
		return false;
	}

	return implode( ' UNION ALL ', $comments_query );
}
