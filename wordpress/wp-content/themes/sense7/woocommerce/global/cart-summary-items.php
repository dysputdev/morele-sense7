<?php

defined( 'ABSPATH' ) || exit;

?>

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
