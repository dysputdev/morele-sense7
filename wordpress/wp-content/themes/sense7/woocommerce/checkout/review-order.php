<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="shop_table woocommerce-checkout-review-order-table">

	<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
		<div class="woocommerce-checkout__shipping">

			<h3 class="woocommerce-checkout__heading"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></h3>

			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

		</div>
	<?php endif; ?>
	
	<div class="woocommerce-checkout__cart-items">

		<h3 id="woocommerce-checkout__heading">
			<?php
				// translators: number of items in the cart.
				sprintf( esc_html__( 'Wybrane produkty (%s)', 'sense7' ), esc_attr( WC()->cart->get_cart_contents_count() ) );
			?>
		</h3>
		<?php

		do_action( 'woocommerce_review_order_before_cart_contents' );

		?>

		<div class="woocommerce-cart-form__items">

			<?php wc_get_template( 'woocommerce/global/cart-items.php', array( 'type' => 'checkout' ) ); ?>

		</div>

		<?php do_action( 'woocommerce_review_order_after_cart_contents' ); ?>
	</div>

	<div class="woocommerce-checkout__order-summary">

		<div class="cart-totals">
			
			<?php wc_get_template( 'global/cart-summary-items.php', array( 'type' => 'checkout' ) ); ?>

			<?php wc_get_template( 'global/cart-summary-coupons.php' ); ?>

			<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
				<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
					<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
						<div class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
							<div><?php echo esc_html( $tax->label ); ?></div>
							<div><?php echo wp_kses_post( $tax->formatted_amount ); ?></div>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="tax-total">
						<div><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></div>
						<div><?php wc_cart_totals_taxes_total_html(); ?></div>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

			<?php wc_get_template( 'global/cart-summary-total.php' ); ?>

			<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
		</div>

		<div class="cart-totals__actions">
			<noscript>
				<?php
				/* translators: $1 and $2 opening and closing emphasis tags respectively */
				printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ), '<em>', '</em>' );
				?>
				<br/><button type="submit" class="button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
			</noscript>

			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

			<?php $order_button_text = apply_filters( 'woocommerce_order_button_text', esc_html__( 'Place order', 'woocommerce' ) ); ?>

			<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ) . '" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

			<a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>" class="cart-totals__continue-shopping">
				<?php esc_html_e( 'Wróć do koszyka', 'sense7' ); ?>
			</a>

			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		</div>

	</div>
</div>
