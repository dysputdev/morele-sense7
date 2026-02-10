<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="cart-totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2 class="cart-totals__title"><?php esc_html_e( 'Podsumowanie', 'sense7' ); ?></h2>

	<div class="cart-totals__items">

		<div class="cart-totals__item cart-totals__item--subtotal">
			<span class="cart-totals__label">
				<?php
				$cart_count = WC()->cart->get_cart_contents_count();
				/* translators: %d: number of products */
				echo esc_html( sprintf( __( 'Wartość produktów (%d)', 'sense7' ), $cart_count ) );
				?>
			</span>
			<span class="cart-totals__value"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="cart-totals__item cart-totals__item--discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<span class="cart-totals__label">
					<?php
					/* translators: %s: coupon code */
					echo esc_html( sprintf( __( 'Rabat %s', 'sense7' ), strtoupper( $code ) ) );
					?>
				</span>
				<span class="cart-totals__value cart-totals__value--discount"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>

			<div class="cart-totals__item cart-totals__item--shipping">
				<span class="cart-totals__label"><?php esc_html_e( 'Wysyłka', 'sense7' ); ?></span>
				<span class="cart-totals__value">
					<?php
					$packages = WC()->shipping()->get_packages();
					$first_package = reset( $packages );
					$shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

					if ( ! empty( $first_package['rates'] ) && ! empty( $shipping_methods[0] ) ) {
						$chosen_method = $first_package['rates'][ $shipping_methods[0] ];
						if ( 0 == $chosen_method->cost ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
							?>
							<span class="cart-totals__shipping-free"><?php esc_html_e( 'za darmo', 'sense7' ); ?></span>
							<?php
						} else {
							echo wp_kses_post( wc_price( $chosen_method->cost ) );
						}
					} else {
						wc_cart_totals_shipping_html();
					}
					?>
				</span>
			</div>

			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>

		<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>

			<div class="cart-totals__item cart-totals__item--shipping">
				<span class="cart-totals__label"><?php esc_html_e( 'Wysyłka', 'sense7' ); ?></span>
				<span class="cart-totals__value"><?php woocommerce_shipping_calculator(); ?></span>
			</div>

		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="cart-totals__item cart-totals__item--fee">
				<span class="cart-totals__label"><?php echo esc_html( $fee->name ); ?></span>
				<span class="cart-totals__value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) { ?>
			
			<div class="cart-totals__item cart-totals__item--tax">
				<span class="cart-totals__label"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="cart-totals__value"><?php wc_cart_totals_subtotal_html(); ?></span>
			</div>
		<?php } ?>

	</div>

	<?php if ( wc_coupons_enabled() ) : ?>
		<?php
		$applied_coupons = WC()->cart->get_applied_coupons();
		$has_coupons     = ! empty( $applied_coupons );
		?>

		<?php if ( $has_coupons ) : ?>
			<div class="cart-totals__applied-coupons">
				<span class="cart-totals__coupons-label"><?php esc_html_e( 'Kody rabatowe:', 'sense7' ); ?></span>
				<div class="cart-totals__coupons-list">
					<?php foreach ( $applied_coupons as $coupon_code ) : ?>
						<div class="cart-totals__coupon-tag">
							<span class="cart-totals__coupon-code"><?php echo esc_html( strtoupper( $coupon_code ) ); ?></span>
							<a href="<?php echo esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon_code ), wc_get_cart_url() ) ); ?>" class="cart-totals__coupon-remove" aria-label="<?php echo esc_attr( sprintf( __( 'Usuń kupon %s', 'sense7' ), $coupon_code ) ); ?>">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M9 3L3 9M3 3L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php else : ?>
			<div class="cart-totals__coupon-section">
				<button type="button" class="cart-totals__coupon-toggle" data-toggle="coupon-form">
					<?php esc_html_e( 'Masz kod rabatowy?', 'sense7' ); ?>
				</button>

				<div class="cart-totals__coupon-form" id="coupon-form" style="display: none;">
					<form class="woocommerce-coupon-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
						<label for="coupon_code" class="cart-totals__coupon-label"><?php esc_html_e( 'kod rabatowy', 'sense7' ); ?></label>
						<div class="cart-totals__coupon-input-wrapper">
							<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'wpisz kod rabatowy', 'sense7' ); ?>" />
							<button type="submit" class="cart-totals__coupon-submit" name="apply_coupon" value="<?php esc_attr_e( 'Dodaj', 'sense7' ); ?>">
								<?php esc_html_e( 'Dodaj', 'sense7' ); ?>
							</button>
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
						<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
					</form>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

	<div class="cart-totals__total">
		<span class="cart-totals__total-label"><?php esc_html_e( 'Do zapłaty', 'sense7' ); ?></span>
		<span class="cart-totals__total-value"><?php wc_cart_totals_subtotal_html(); ?></span>
	</div>

	<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>

<div class="cart-totals__actions">
	<div class="wc-proceed-to-checkout">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="cart-totals__continue-shopping">
		<?php esc_html_e( 'Kontynuuj zakupy', 'sense7' ); ?>
	</a>
</div>