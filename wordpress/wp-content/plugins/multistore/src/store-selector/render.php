<?php
/**
 * Store Selector Block Template
 *
 * @package MultiStore\Plugin\Block\StoreSelector
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

namespace MultiStore\Plugin\Block\StoreSelector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get block attributes.
$show_flags          = isset( $attributes['showFlags'] ) ? $attributes['showFlags'] : true;
$show_language_names = isset( $attributes['showLanguageNames'] ) ? $attributes['showLanguageNames'] : true;
$title               = isset( $attributes['title'] ) && ! empty( $attributes['title'] ) ? $attributes['title'] : __( 'Wybierz kraj i język sklepu:', 'multistore' );
$description         = isset( $attributes['description'] ) && ! empty( $attributes['description'] ) ? $attributes['description'] : __( 'Wysyłka realizowana jest wyłącznie na terenie wybranego kraju.', 'multistore' );

// Check if Polylang and Multisite are active.
if ( ! function_exists( 'pll_the_languages' ) || ! is_multisite() ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<div class="notice notice-warning">';
		echo '<p>' . esc_html__( 'Selektor sklepu wymaga WordPress Multisite i wtyczki Polylang.', 'multistore' ) . '</p>';
		echo '</div>';
	}
	return;
}

// Get stores data from function.
$stores = get_stores_data();

if ( empty( $stores ) ) {
	return;
}

// Get current store info.
$current_store = get_current_store_info();

// Get wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'multistore-block-store-selector',
	)
);
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
	<button class="multistore-block-store-selector__button" type="button" aria-expanded="false" aria-label="<?php echo esc_attr__( 'Wybierz kraj i język', 'multistore' ); ?>">
		<?php if ( $show_flags && ! empty( $current_store['flag_url'] ) ) : ?>
			<img
				src="<?php echo esc_url( $current_store['flag_url'] ); ?>"
				alt="<?php echo esc_attr( $current_store['country_name'] ); ?>"
				class="multistore-block-store-selector__button-flag"
			/>
		<?php endif; ?>
		<span class="multistore-block-store-selector__button-text">
			<span><?php echo esc_html( $current_store['country_code'] ); ?></span>
			<span class="multistore-block-store-selector__button-separator">|</span>
			<span><?php echo esc_html( $current_store['language_code'] ); ?></span>
		</span>
	</button>

	<div class="multistore-block-store-selector__container" role="dialog" aria-modal="false">
		<h3 class="multistore-block-store-selector__title">
			<?php echo esc_html( $title ); ?>
		</h3>
		<p class="multistore-block-store-selector__description">
			<?php echo esc_html( $description ); ?>
		</p>

		<div class="multistore-block-store-selector__list">
			<?php foreach ( $stores as $store ) : ?>
				<div class="multistore-block-store-selector__item">
					<div class="multistore-block-store-selector__country">
						<?php if ( $show_flags && ! empty( $store['flag_url'] ) ) : ?>
							<img
								src="<?php echo esc_url( $store['flag_url'] ); ?>"
								alt="<?php echo esc_attr( $store['country_name'] ); ?>"
								class="multistore-block-store-selector__country-flag"
							/>
						<?php endif; ?>
						<span class="multistore-block-store-selector__country-name"><?php echo esc_html( $store['country_name'] ); ?></span>
					</div>

					<?php if ( ! empty( $store['languages'] ) ) : ?>
						<div class="multistore-block-store-selector__languages">
							<?php
							$lang_count = count( $store['languages'] );
							foreach ( $store['languages'] as $index => $language ) :
								?>
								<a
									href="<?php echo esc_url( $language['url'] ); ?>"
									class="multistore-block-store-selector__language-link<?php echo $language['is_active'] ? ' is-active' : ''; ?>"
								>
									<?php
									if ( $show_language_names ) {
										echo esc_html( $language['name'] );
									} else {
										echo esc_html( strtoupper( $language['code'] ) );
									}
									?>
								</a>
								<?php if ( $index < $lang_count - 1 ) : ?>
									<span class="multistore-block-store-selector__language-separator">|</span>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
