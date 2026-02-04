<?php
/**
 * My Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing address', 'woocommerce' ),
			'shipping' => __( 'Shipping address', 'woocommerce' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing address', 'woocommerce' ),
		),
		$customer_id
	);
}

$wc_address_book = null;
$adresses        = array();
if ( class_exists( 'WC_Address_Book' ) ) {
	$wc_address_book = \WC_Address_Book::get_instance();

	foreach ( $get_addresses as $name => $address_title ) {

		if ( false === $wc_address_book->get_wcab_option( $name . '_enabled' ) ) {
			continue;
		}

		$items = $wc_address_book->get_address_book( $customer_id, $name );
		$limit = intval( get_option( "woo_address_book_{$name}_save_limit", 0 ) );

		$adresses[ $name ] = array(
			'items' => $items,
			'total' => count( $items ),
			'limit' => $limit,
		);
	}
}

$oldcol = 1;
$col    = 1;

?>

<p>
	<?php echo apply_filters( 'woocommerce_my_account_my_address_description', esc_html__( 'The following addresses will be used on the checkout page by default.', 'woocommerce' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</p>


<div class="woocommerce-Addresses">
<?php foreach ( $get_addresses as $name => $address_title ) : ?>
	<?php
		$address = wc_get_account_formatted_address( $name );
		$col     = $col * -1;
		$oldcol  = $oldcol * -1;
	?>

	<div class="">
		<header class="woocommerce-Address-title title">
			<h2><?php echo esc_html( $address_title ); ?></h2>
		</header>

		<div
			class="address_book <?php echo esc_attr( $name ); ?>_address_book"
			data-addresses='<?php echo esc_attr( $adresses[ $name ]['total'] ); ?>'
			data-limit='<?php echo esc_attr( $adresses[ $name ]['limit'] ); ?>'
		>
			<div class="woocommerce-Address__items addresses address-book">
				<div class="woocommerce-Address__item wc-address-book-address u-column<?php echo $col < 0 ? 1 : 2; ?> woocommerce-Address">
					<address>
						
						<?php
							echo $address ? wp_kses_post( $address ) : esc_html_e( 'You have not set up this type of address yet.', 'woocommerce' );

							/**
							 * Used to output content after core address fields.
							 *
							 * @param string $name Address type.
							 * @since 8.7.0
							 */
							do_action( 'woocommerce_my_account_after_my_address', $name );
						?>

					</address>
					<div class="wc-address-book-meta">
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="edit">
							<?php
								printf(
									/* translators: %s: Address title */
									$address ? esc_html__( 'Edit %s', 'woocommerce' ) : esc_html__( 'Add %s', 'woocommerce' ),
									esc_html( $address_title )
								);
							?>
						</a>
					</div>
				</div>

				<?php
				if ( isset( $adresses[ $name ] ) ) {

					$woo_address_book_billing_address = get_user_meta( $customer_id, $name . '_address_1', true );

					$hide_shipping_address_book = 1 === (int) $adresses[ $name ]['limit'] && $adresses[ $name ]['total'] <= 1;
					foreach ( $adresses[ $name ]['items'] as $woo_address_book_name => $item ) {

						if ( $woo_address_book_name === $name ) {
							continue;
						}

						$woo_address_book_address = apply_filters(
							'woocommerce_my_account_my_address_formatted_address',
							array(
								'first_name' => $item[ $woo_address_book_name . '_first_name' ] ?? '',
								'last_name'  => $item[ $woo_address_book_name . '_last_name' ] ?? '',
								'company'    => $item[ $woo_address_book_name . '_company' ] ?? '',
								'address_1'  => $item[ $woo_address_book_name . '_address_1' ] ?? '',
								'address_2'  => $item[ $woo_address_book_name . '_address_2' ] ?? '',
								'city'       => $item[ $woo_address_book_name . '_city' ] ?? '',
								'state'      => $item[ $woo_address_book_name . '_state' ] ?? '',
								'postcode'   => $item[ $woo_address_book_name . '_postcode' ] ?? '',
								'country'    => $item[ $woo_address_book_name . '_country' ] ?? '',
							),
							$customer_id,
							$woo_address_book_name
						);

						$formatted_address = WC()->countries->get_formatted_address( $woo_address_book_address );

						if ( $formatted_address ) {
							?>
							<div class="woocommerce-Address__item wc-address-book-address">
								<address>
									<?php echo wp_kses( $formatted_address, array( 'br' => array() ) ); ?>
								</address>
								<div class="wc-address-book-meta">
									<a href="<?php echo esc_url( $wc_address_book->get_address_book_endpoint_url( $woo_address_book_name, $name ) ); ?>" class="wc-address-book-edit"><?php echo esc_attr__( 'Edit', 'woo-address-book' ); ?></a>
									<a id="<?php echo esc_attr( $woo_address_book_name ); ?>" class="wc-address-book-delete"><?php echo esc_attr__( 'Delete', 'woo-address-book' ); ?></a>
									<a id="<?php echo esc_attr( $woo_address_book_name ); ?>" class="wc-address-book-make-primary"><?php echo esc_attr__( 'Make Primary', 'woo-address-book' ); ?></a>
								</div>
							</div>
							<?php
						}
					}
					$wc_address_book->add_additional_address_button( $name );
				}
				?>

			</div>
			
		</div>
	</div>

<?php endforeach; ?>
</div>
