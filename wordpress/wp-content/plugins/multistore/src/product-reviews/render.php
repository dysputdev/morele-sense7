<?php

namespace MultiStore\Plugin\Block\ProductReviews;

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'multistore-block-product-reviews',
	)
);


$product_id = $block->context['postId'] ?? $block->context['postId'] ?? get_the_ID();
if ( ! $product_id ) {
	return;
}

$is_product = $block->context['postType'] ?? $block->context['postType'] ?? get_post_type( $product_id );
if ( 'product' !== $is_product ) {
	return;
}

$display_summary            = isset( $attributes['displaySummary'] ) ? $attributes['displaySummary'] : false;
$display_all_stores_reviews = isset( $attributes['displayAllStores'] ) ? $attributes['displayAllStores'] : false;

// get product SKU.
$sku = get_post_meta( $product_id, '_sku', true );
if ( empty( $sku ) ) {
	return;
}

$star_icon_full = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5.528 5.27202L0 6.11771L4 10.2142L3.056 16L8 13.269L12.944 15.994L12 10.2082L16 6.11171L10.474 5.27202L8 0L5.528 5.27202Z" fill="currentColor"/>
</svg>';
$star_icon_half = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 2.38283L6.18795 6.24383L2.17097 6.85797L5.07636 9.83546L4.38695 14.0629L8 12.0658L11.6131 14.0629L10.9236 9.83546L13.829 6.85797L9.81205 6.24383L8 2.38283ZM8 0L10.472 5.26716L16 6.11232L12 10.2116L12.944 16L8 13.2672L3.05603 16L4 10.2116L0 6.11232L5.52802 5.26716L8 0Z" fill="currentColor"/>
<path d="M8.00003 1.87402L5.99203 6.05536L1.72803 6.66549L4.76203 10.0266L4.19003 14.6047L8.00003 12.3732V1.87402Z" fill="currentColor"/></svg>';
$star_icon_empty = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 2.38283L6.18795 6.24383L2.17097 6.85797L5.07636 9.83546L4.38695 14.0629L8 12.0658L11.6131 14.0629L10.9236 9.83546L13.829 6.85797L9.81205 6.24383L8 2.38283ZM8 0L10.472 5.26716L16 6.11232L12 10.2116L12.944 16L8 13.2672L3.05603 16L4 10.2116L0 6.11232L5.52802 5.26716L8 0Z" fill="currentColor"/>
</svg>';


global $wpdb;

$reviews = array();
$stats   = array();
// if display all stores, then we need combined reviews.
if ( $display_all_stores_reviews ) {
	$global_query = get_global_query_by_sku( $sku );
	if ( ! empty( $global_query ) ) {
		$reviews = $wpdb->get_results( "SELECT * FROM ({$global_query}) as t ORDER BY t.comment_date_gmt DESC LIMIT {$attributes['perPage']}" );
	
		$stats = $wpdb->get_results(
			"SELECT t.rating, count(*) as total, sum(rating) as total_rating 
			FROM ({$global_query}) as t
			GROUP BY t.rating"
		);
	}
} else {
	// Get reviews only from current blog.
	global $wpdb;
	$reviews = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT c.*, cm.meta_value as rating, 'pl' as store
			FROM {$wpdb->prefix}comments as c
			LEFT JOIN {$wpdb->prefix}commentmeta AS cm
				ON cm.comment_id = c.comment_ID
				AND cm.meta_key = 'rating'
			WHERE c.comment_post_ID = %d
				AND c.comment_approved = 1
				AND cm.meta_value IS NOT NULL
			ORDER BY c.comment_date_gmt DESC
			LIMIT %d",
			$product_id,
			$attributes['perPage']
		)
	);
}

// Calculate statistics.
$total_reviews = 0;
$rating_counts = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );
$total_rating  = 0;

foreach ( $stats as $stat ) {
	$rating         = $stat->rating;
	$total_reviews += $stat->total;
	$total_rating  += $stat->total_rating;

	$rating_counts[ $rating ] += $stat->total;
}
$average_rating = $total_reviews > 0 ? round( $total_rating / $total_reviews, 2 ) : 0;
$average_rating = number_format( $average_rating, 1 );
$recommendation = $total_reviews > 0 ? round( ( ( $rating_counts[5] + $rating_counts[4] ) / $total_reviews ) * 100 ) : 0;

?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>

	<?php if ( $display_summary && $total_reviews > 0 ) : ?>
		<div class="multistore-block-product-reviews__summary">
			<div class="multistore-block-product-reviews__summary-header">
				<div class="multistore-block-product-reviews__rating-score">
					<span class="multistore-block-product-reviews__rating-number"><?php echo esc_html( $average_rating ); ?></span>
					<span class="multistore-block-product-reviews__rating-max">/5</span>
				</div>
				<div class="multistore-block-product-reviews__rating-stars">
					<?php
					$full_stars  = floor( $average_rating );
					$half_star   = ( $average_rating - $full_stars ) >= 0.5;
					$empty_stars = 5 - $full_stars - ( $half_star ? 1 : 0 );

					for ( $i = 0; $i < $full_stars; $i++ ) {
						echo '<span class="multistore-block-product-reviews__star multistore-block-product-reviews__star--full">' . $star_icon_full . '</span>';
					}
					if ( $half_star ) {
						echo '<span class="multistore-block-product-reviews__star multistore-block-product-reviews__star--half">' . $star_icon_half . '</span>';
					}
					for ( $i = 0; $i < $empty_stars; $i++ ) {
						echo '<span class="multistore-block-product-reviews__star multistore-block-product-reviews__star--empty">' . $star_icon_empty . '</span>';
					}
					?>
				</div>
				<div class="multistore-block-product-reviews__total">
					(<?php echo esc_html( $total_reviews ); ?>)
				</div>
			</div>

			<div class="multistore-block-product-reviews__recommendation">
				<?php echo esc_html( $recommendation ); ?>% osób poleca ten produkt
			</div>

			<div class="multistore-block-product-reviews__breakdown">
				<?php
				foreach ( array( 5, 4, 3, 2, 1 ) as $star ) {
					$count      = $rating_counts[ $star ];
					$percentage = $total_reviews > 0 ? round( ( $count / $total_reviews ) * 100 ) : 0;
					?>
					<div class="multistore-block-product-reviews__breakdown-item">
						<span class="multistore-block-product-reviews__breakdown-label"><?php echo esc_html( $star ); ?></span>
						<?php echo $star_icon_full; ?>
						<div class="multistore-block-product-reviews__breakdown-bar">
							<div class="multistore-block-product-reviews__breakdown-fill" style="width: <?php echo esc_attr( $percentage ); ?>%"></div>
						</div>
						<span class="multistore-block-product-reviews__breakdown-percentage"><?php echo esc_html( $percentage ); ?>%</span>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $reviews ) ) : ?>
		<div class="multistore-block-product-reviews__list">
			<?php foreach ( $reviews as $review ) : ?>
				<?php
				$comment_rating  = isset( $review->rating ) ? intval( $review->rating ) : 0;
				$comment_author  = $review->comment_author;
				$comment_date    = $review->comment_date_gmt;
				$comment_content = $review->comment_content;
				$store_lang      = isset( $review->store ) ? $review->store : 'pl';

				// Calculate relative time.
				$time_diff = human_time_diff( strtotime( $comment_date ), current_time( 'timestamp' ) );
				?>
				<div class="multistore-block-product-reviews__item">
					<div class="multistore-block-product-reviews__item-header">
						<span class="multistore-block-product-reviews__flag">
							<?php
							$flag_path = MULTISTORE_PLUGIN_DIR . 'assets/img/flags/4x3/' . $store_lang . '.svg';
							$flag_url  = file_exists( $flag_path )
								? MULTISTORE_PLUGIN_URL . 'assets/img/flags/4x3/' . $store_lang . '.svg'
								: MULTISTORE_PLUGIN_URL . 'assets/img/flags/4x3/pl.svg';

							$flag_emoji = '';
							switch ( $store_lang ) {
								case 'pl':
									$flag_emoji = '🇵🇱';
									break;
								case 'de':
									$flag_emoji = '🇩🇪';
									break;
								case 'ro':
									$flag_emoji = '🇷🇴';
									break;
								default:
									$flag_emoji = '🏳️';
							}
							// echo esc_html( $flag_emoji );
							?>

							<img
								src="<?php echo esc_url( $flag_url ); ?>"
								alt="<?php echo esc_attr( $store_lang ); ?>"
								class="multistore-block-product-reviews__flag"
							/>
						</span>
						<span class="multistore-block-product-reviews__author"><?php echo esc_html( $comment_author ); ?></span>
						<span class="multistore-block-product-reviews__date"><?php echo esc_html( $time_diff ); ?> temu</span>
						<span class="multistore-block-product-reviews__verified">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
								<path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
							</svg>
							Opinia potwierdzona zakupem
						</span>
					</div>

					<div class="multistore-block-product-reviews__item-rating">
						<?php
						for ( $i = 1; $i <= 5; $i++ ) {
							$class = $i <= $comment_rating ? 'multistore-block-product-reviews__star--full' : 'multistore-block-product-reviews__star--empty';
							echo '<span class="multistore-block-product-reviews__star ' . esc_attr( $class ) . '">★</span>';
						}
						?>
					</div>

					<div class="multistore-block-product-reviews__item-content">
						<?php echo wp_kses_post( wpautop( $comment_content ) ); ?>
					</div>

					<?php if ( 'pl' !== $store_lang ) : ?>
						<div class="multistore-block-product-reviews__item-translate">
							<a href="#" class="multistore-block-product-reviews__translate-link">
								Przetłumacz opinię na Polski
							</a>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="multistore-block-product-reviews__empty">
			<?php esc_html_e( 'Brak opinii dla tego produktu.', 'multistore' ); ?>
		</div>
	<?php endif; ?>

</div>