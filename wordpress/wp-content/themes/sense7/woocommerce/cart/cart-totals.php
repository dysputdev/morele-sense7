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

	<?php wc_get_template( 'woocommerce/global/cart-summary-items.php' ); ?>

	<?php
	if ( wc_coupons_enabled() ) {
		wc_get_template( 'woocommerce/global/cart-summary-coupons.php' );
	}
	?>

	<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

	<?php wc_get_template( 'woocommerce/global/cart-summary-total.php' ); ?>

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