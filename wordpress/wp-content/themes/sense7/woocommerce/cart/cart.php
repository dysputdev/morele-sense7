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

					<?php
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
						$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

						if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
							$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
							?>
							<div class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart-item', $cart_item, $cart_item_key ) ); ?>" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
								<div class="cart-item__select">
									<input type="checkbox" class="cart-item__checkbox" name="cart_item_select[]" value="<?php echo esc_attr( $cart_item_key ); ?>" id="cart-item-<?php echo esc_attr( $cart_item_key ); ?>">
									<label for="cart-item-<?php echo esc_attr( $cart_item_key ); ?>" class="screen-reader-text">
										<?php
										/* translators: %s: product name */
										echo esc_html( sprintf( __( 'Zaznacz %s', 'sense7' ), $product_name ) );
										?>
									</label>
								</div>

								<div class="cart-item__thumbnail">
									<?php
									$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

									if ( ! $product_permalink ) {
										echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									?>
								</div>

								<div class="cart-item__details">
									<div class="cart-item__name">
										<?php
										if ( ! $product_permalink ) {
											echo wp_kses_post( $product_name );
										} else {
											echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
										}

										do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

										// Meta data.
										echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

										// Backorder notification.
										if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
											echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
										}
										?>
									</div>
								</div>

								<div class="cart-item__quantity">
									<?php
									if ( $_product->is_sold_individually() ) {
										$min_quantity = 1;
										$max_quantity = 1;
									} else {
										$min_quantity = 0;
										$max_quantity = $_product->get_max_purchase_quantity();
									}
									?>
									<div class="quantity-selector">
										<button type="button" class="quantity-selector__button quantity-selector__button--minus" data-action="decrease" aria-label="<?php esc_attr_e( 'Zmniejsz ilość', 'sense7' ); ?>">
											<svg width="12" height="2" viewBox="0 0 12 2" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M1 1H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
											</svg>
										</button>
										<input
											type="number"
											class="quantity-selector__input"
											name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]"
											value="<?php echo esc_attr( $cart_item['quantity'] ); ?>"
											min="<?php echo esc_attr( $min_quantity ); ?>"
											max="<?php echo esc_attr( $max_quantity ); ?>"
											step="1"
											aria-label="<?php
											/* translators: %s: product name */
											echo esc_attr( sprintf( __( 'Ilość dla %s', 'sense7' ), $product_name ) );
											?>"
											readonly
										/>
										<button type="button" class="quantity-selector__button quantity-selector__button--plus" data-action="increase" aria-label="<?php esc_attr_e( 'Zwiększ ilość', 'sense7' ); ?>">
											<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M6 1V11M1 6H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
											</svg>
										</button>
									</div>
								</div>

								<div class="cart-item__price">
									<?php
									// Display discount badge if product is on sale.
									$regular_price = $_product->get_regular_price();
									$sale_price    = $_product->get_sale_price();
									$lowest_price  = $price_history->get_lowest_price( $product_id );
									?>

									<div class="cart-item__price-wrapper">
										<?php
										if ( $sale_price && $regular_price > $sale_price ) {
											$discount_percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
											?>
											<div class="cart-item__sale-price">
												<span class="cart-item__discount-badge"><?php echo esc_html( '-' . $discount_percentage . '%' ); ?></span>
												<del class="cart-item__regular-price">
													<?php echo wc_price( $regular_price ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</del>
											</div>
											<?php
										}
										?>
										<span class="cart-item__current-price">
											<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
										
										<?php if ( $sale_price && ! empty( $lowest_price ) ) : ?>
											<div class="cart-item__lowest-price">
												<?php
												/* translators: %s: lowest price */
												$tooltip = sprintf( __( 'Najniższa cena z 30 dni przed obniżką: %s', 'sense7' ), wc_price( $lowest_price['price'] ) );
												multistore_template_part( 'elements/tooltip', null, array( 'tooltip' => $tooltip ) );
												/* translators: %s: lowest price */
												echo wp_kses_post( sprintf( __( 'najniższa cena: %s', 'sense7' ), wc_price( $lowest_price['price'] ) ) );
												?>
											</div>
										<?php endif; ?>
									</div>
								</div>

								<div class="cart-item__remove">
									<?php
									echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 4H14M12.6667 4V13.3333C12.6667 14 12 14.6667 11.3333 14.6667H4.66667C4 14.6667 3.33333 14 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2 6 1.33333 6.66667 1.33333H9.33333C10 1.33333 10.6667 2 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											/* translators: %s is the product name */
											esc_attr( sprintf( __( 'Usuń %s z koszyka', 'sense7' ), wp_strip_all_tags( $product_name ) ) ),
											esc_attr( $product_id ),
											esc_attr( $_product->get_sku() )
										),
										$cart_item_key
									);
									?>
								</div>
							</div>
							<?php
						}
					}
					?>

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
