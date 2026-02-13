<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

use MultiStore\Plugin\WooCommerce\Price_History;

$price_history = new Price_History();
do_action( 'woocommerce_before_cart' ); ?>

<div class="woocommerce-cart-wrapper">
	<div class="woocommerce-cart-layout">
		<div class="woocommerce-cart-layout__main">
			<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
				<?php do_action( 'woocommerce_before_cart_table' ); ?>

				<div class="woocommerce-cart-form__header">
					<div class="woocommerce-cart-form__header-left">
						<span class="woocommerce-cart-form__count">
							<?php
							$cart_count = WC()->cart->get_cart_contents_count();
							/* translators: %d: number of products */
							echo esc_html( sprintf( _n( '%d produkt', '%d produkty', $cart_count, 'sense7' ), $cart_count ) );
							?>
						</span>
					</div>
					<div class="woocommerce-cart-form__header-right">
						<button type="button" class="woocommerce-cart-form__action-button woocommerce-cart-form__action-button--remove-selected" data-action="remove-selected">
							<span><?php esc_html_e( 'Usuń zaznaczone', 'sense7' ); ?></span>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M2 4H14M12.6667 4V13.3333C12.6667 14 12 14.6667 11.3333 14.6667H4.66667C4 14.6667 3.33333 14 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2 6 1.33333 6.66667 1.33333H9.33333C10 1.33333 10.6667 2 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
						<button type="button" class="woocommerce-cart-form__action-button woocommerce-cart-form__action-button--remove-all" data-action="remove-all">
							<span><?php esc_html_e( 'Usuń wszystkie', 'sense7' ); ?></span>
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M2 4H14M12.6667 4V13.3333C12.6667 14 12 14.6667 11.3333 14.6667H4.66667C4 14.6667 3.33333 14 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2 6 1.33333 6.66667 1.33333H9.33333C10 1.33333 10.6667 2 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
					</div>
				</div>

				<div class="woocommerce-cart-form__items">

					<?php do_action( 'woocommerce_before_cart_contents' ); ?>

					<?php wc_get_template( 'woocommerce/global/cart-items.php' ); ?>

					<?php do_action( 'woocommerce_cart_contents' ); ?>

				</div>

				<div class="woocommerce-cart-form__actions">
					<?php do_action( 'woocommerce_cart_actions' ); ?>
					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
					<input type="hidden" name="update_cart" value="1">
				</div>

				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
				<?php do_action( 'woocommerce_after_cart_table' ); ?>
			</form>
		</div>

		<div class="woocommerce-cart-layout__sidebar">
			<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

			<div class="cart-collaterals">
				<?php
				/**
				 * Cart collaterals hook.
				 *
				 * @hooked woocommerce_cross_sell_display
				 * @hooked woocommerce_cart_totals - 10
				 */
				do_action( 'woocommerce_cart_collaterals' );
				?>
			</div>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
