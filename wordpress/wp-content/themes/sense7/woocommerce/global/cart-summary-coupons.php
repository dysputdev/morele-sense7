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
