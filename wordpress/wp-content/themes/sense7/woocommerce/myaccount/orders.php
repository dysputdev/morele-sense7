<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<div class="myaccount-orders">
	<h2 class="myaccount-orders__title"><?php esc_html_e( 'Twoje zamówienia', 'sense7' ); ?></h2>

	<?php if ( $has_orders ) : ?>

		<?php
		// Get all WooCommerce order statuses.
		$wc_statuses = wc_get_order_statuses();

		// Get order counts by status.
		$status_counts = array();
		$all_orders    = wc_get_orders(
			array(
				'customer_id' => get_current_user_id(),
				'limit'       => -1,
			)
		);

		$total_count = count( $all_orders );

		foreach ( $all_orders as $order ) {
			$status = $order->get_status();
			if ( ! isset( $status_counts[ $status ] ) ) {
				$status_counts[ $status ] = 0;
			}
			++$status_counts[ $status ];
		}

		$current_status = isset( $_GET['order_status'] ) ? sanitize_text_field( wp_unslash( $_GET['order_status'] ) ) : '';

		// Build status filters from WooCommerce statuses.
		$status_filters = array(
			'' => array(
				'label' => __( 'Wszystkie', 'sense7' ),
				'slug'  => '',
				'count' => $total_count,
			),
		);

		foreach ( $wc_statuses as $status_key => $status_label ) {
			// Remove 'wc-' prefix from status key.
			$slug = str_replace( 'wc-', '', $status_key );
			if ( 'checkout-draft' === $slug ) {
				continue;
			}

			$count = isset( $status_counts[ $slug ] ) ? $status_counts[ $slug ] : 0;

			if ( ! $count ) {
				continue;
			}

			$status_filters[ $slug ] = array(
				'label' => $status_label,
				'slug'  => $slug,
				'count' => $count,
			);
		}
		?>

		<div class="myaccount-orders__filters">
			<?php foreach ( $status_filters as $key => $filter ) : ?>
				<?php
				$slug      = $filter['slug'];
				$count     = $filter['count'];
				$is_active = ( $current_status === $key || ( empty( $current_status ) && empty( $key ) ) );
				?>
				<button type="button" data-filter-status="<?php echo esc_attr( $slug ); ?>" class="myaccount-orders__filter <?php echo $is_active ? 'is-active' : ''; ?>">
					<?php echo esc_html( $filter['label'] ); ?>
					<?php if ( $count > 0 ) : ?>
						<span class="myaccount-orders__filter-count">(<?php echo esc_html( $count ); ?>)</span>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="myaccount-orders__table-wrapper">
			<table class="myaccount-orders__table">
				<thead>
					<tr>
						<th class="myaccount-orders__header myaccount-orders__header--number">
							<span><?php esc_html_e( 'Nr zamówienia', 'sense7' ); ?></span>
							<button type="button" class="myaccount-orders__sort" data-sort="number" aria-label="<?php esc_attr_e( 'Sortuj według numeru', 'sense7' ); ?>">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M6 2V10M6 2L4 4M6 2L8 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</button>
						</th>
						<th class="myaccount-orders__header myaccount-orders__header--date">
							<span><?php esc_html_e( 'Data złożenia', 'sense7' ); ?></span>
							<button type="button" class="myaccount-orders__sort" data-sort="date" aria-label="<?php esc_attr_e( 'Sortuj według daty', 'sense7' ); ?>">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M6 2V10M6 2L4 4M6 2L8 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</button>
						</th>
						<th class="myaccount-orders__header myaccount-orders__header--payment">
							<?php esc_html_e( 'Płatność', 'sense7' ); ?>
						</th>
						<th class="myaccount-orders__header myaccount-orders__header--status">
							<?php esc_html_e( 'Status', 'sense7' ); ?>
						</th>
						<th class="myaccount-orders__header myaccount-orders__header--total">
							<span><?php esc_html_e( 'Wartość', 'sense7' ); ?></span>
							<button type="button" class="myaccount-orders__sort" data-sort="total" aria-label="<?php esc_attr_e( 'Sortuj według wartości', 'sense7' ); ?>">
								<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M6 2V10M6 2L4 4M6 2L8 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</button>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $customer_orders->orders as $customer_order ) {
						$order      = wc_get_order( $customer_order );
						$order_date = $order->get_date_created();
						?>
						<tr class="myaccount-orders__row" data-order-number="<?php echo esc_attr( $order->get_order_number() ); ?>" data-order-date="<?php echo esc_attr( $order_date->getTimestamp() ); ?>" data-order-total="<?php echo esc_attr( $order->get_total() ); ?>" data-order-status="<?php echo esc_attr( $order->get_status() ); ?>">
							<td class="myaccount-orders__cell myaccount-orders__cell--number" data-label="<?php esc_attr_e( 'Nr zamówienia', 'sense7' ); ?>">
								<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="myaccount-orders__order-number">
									<?php echo esc_html( $order->get_order_number() ); ?>
								</a>
							</td>
							<td class="myaccount-orders__cell myaccount-orders__cell--date" data-label="<?php esc_attr_e( 'Data złożenia', 'sense7' ); ?>">
								<time datetime="<?php echo esc_attr( $order_date->date( 'c' ) ); ?>">
									<?php echo esc_html( $order_date->format( 'Y-m-d' ) ); ?>
								</time>
							</td>
							<td class="myaccount-orders__cell myaccount-orders__cell--payment" data-label="<?php esc_attr_e( 'Płatność', 'sense7' ); ?>">
								<?php
								$payment_status = $order->is_paid() ? __( 'Opłacone', 'sense7' ) : __( 'Do zaksięgowania', 'sense7' );

								if ( ! $order->is_paid() && $order->needs_payment() ) {
									?>
									<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="myaccount-orders__payment-button">
										<?php esc_html_e( 'Zapłać ponownie', 'sense7' ); ?>
									</a>
									<?php
								} else {
									echo esc_html( $payment_status );
								}
								?>
							</td>
							<td class="myaccount-orders__cell myaccount-orders__cell--status" data-label="<?php esc_attr_e( 'Status', 'sense7' ); ?>">
								<span class="myaccount-orders__status myaccount-orders__status--<?php echo esc_attr( $order->get_status() ); ?>">
									<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
								</span>
							</td>
							<td class="myaccount-orders__cell myaccount-orders__cell--total" data-label="<?php esc_attr_e( 'Wartość', 'sense7' ); ?>">
								<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>

		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
				<?php if ( 1 !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
				<?php endif; ?>

				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( false ) : ?>
		<div class="myaccount-orders__legend">
			<h3 class="myaccount-orders__legend-title"><?php esc_html_e( 'Statusy zamówień:', 'sense7' ); ?></h3>
			<ul class="myaccount-orders__legend-list">
				<li>
					<span class="myaccount-orders__legend-term"><?php esc_html_e( 'Nowy', 'sense7' ); ?></span>
					<span class="myaccount-orders__legend-desc"><?php esc_html_e( '- Status po złożeniu zamówienia.', 'sense7' ); ?></span>
				</li>

				<li>
					<span class="myaccount-orders__legend-term"><?php esc_html_e( 'W przygotowaniu', 'sense7' ); ?></span>
					<span class="myaccount-orders__legend-desc"><?php esc_html_e( '- Gdy płatność została zatwierdzona i zamówienie czeka do realizacji i na wysyłkę.', 'sense7' ); ?></span>
				</li>

				<li>
					<span class="myaccount-orders__legend-term"><?php esc_html_e( 'Wstrzymane', 'sense7' ); ?></span>
					<span class="myaccount-orders__legend-desc"><?php esc_html_e( '- Gdy zamówienie wymaga dodatkowych informacji.', 'sense7' ); ?></span>
				</li>

				<li>
					<span class="myaccount-orders__legend-term"><?php esc_html_e( 'Wysłane', 'sense7' ); ?></span>
					<span class="myaccount-orders__legend-desc"><?php esc_html_e( '- Gdy zamówienie zostanie odebrane przez kuriera.', 'sense7' ); ?></span>
				</li>

				<li>
					<span class="myaccount-orders__legend-term"><?php esc_html_e( 'Dostarczone', 'sense7' ); ?></span>
					<span class="myaccount-orders__legend-desc"><?php esc_html_e( '- Gdy zamówienie zostanie dostarczone przez kuriera.', 'sense7' ); ?></span>
				</li>

				<li>
					<span class="myaccount-orders__legend-term"><?php esc_html_e( 'Anulowane', 'sense7' ); ?></span>
					<span class="myaccount-orders__legend-desc"><?php esc_html_e( '- Gdy zamówienie zostanie anulowane przez użytkownika lub administratora.', 'sense7' ); ?></span>
				</li>

				<li>
					<span class="myaccount-orders__legend-term"><?php esc_html_e( 'Zwrócone', 'sense7' ); ?></span>
					<span class="myaccount-orders__legend-desc"><?php esc_html_e( '- Gdy kwota zamówienia została zwrócona użytkownikowi.', 'sense7' ); ?></span>
				</li>

				<li>
					<span class="myaccount-orders__legend-term"><?php esc_html_e( 'Nieudane', 'sense7' ); ?></span>
					<span class="myaccount-orders__legend-desc"><?php esc_html_e( '- Gdy płatność za zamówienie się nie powiodła lub została odrzucona przez operatora płatności.', 'sense7' ); ?></span>
				</li>
			</ul>
		</div>
		<?php endif; ?>

	<?php else : ?>

		<?php wc_print_notice( esc_html__( 'No order has been made yet.', 'woocommerce' ) . ' <a class="woocommerce-Button wc-forward button' . esc_attr( $wp_button_class ) . '" href="' . esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ) . '">' . esc_html__( 'Browse products', 'woocommerce' ) . '</a>', 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>

	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
