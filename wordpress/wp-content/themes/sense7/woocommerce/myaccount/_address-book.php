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


<div class="woocommerce-Addresses">
<?php foreach ( $get_addresses as $address_type => $address_title ) : ?>
	<?php
		$address = wc_get_account_formatted_address( $address_type );
		$col     = $col * -1;
		$oldcol  = $oldcol * -1;
	?>

	<div class="">
		<header class="woocommerce-Address-title title">
			<h2><?php echo esc_html( $address_title ); ?></h2>
		</header>

		<div
			class="address_book <?php echo esc_attr( $address_type ); ?>_address_book"
			data-addresses='<?php echo esc_attr( $adresses[ $address_type ]['total'] ); ?>'
			data-limit='<?php echo esc_attr( $adresses[ $address_type ]['limit'] ); ?>'
		>
			<div class="woocommerce-Address__items">
				<?php
				if ( ! isset( $adresses[ $address_type ] ) ) :
					continue;
				endif;
				?>

				<?php foreach ( $adresses[ $address_type ]['items'] as $item_name => $item ) : ?>

					<?php $is_company = ! empty( $item[ $item_name . '_companyname' ] ); ?>

					<div class="woocommerce-Address__item">
						<address class="address-item <?php echo $is_company ? 'is-company' : 'is-individual'; ?>">

							<?php if ( $is_company ) : ?>
								<span class="address-item__comapny">
									<?php echo esc_html( $item[ $item_name . '_companyname' ] ); ?>
								</span><br/>
							<?php endif; ?>
							
							<span class="address-item__name">
								<?php echo esc_html( $item[ $item_name . '_first_name' ] . ' ' . $item[ $item_name . '_last_name' ] ); ?>
							</span><br/>

							<span class="address-item__address">
								<?php echo esc_html( $item[ $item_name . '_address_1' ] ); ?>
								<?php if ( ! empty( $item[ $item_name . '_address_2' ] ) ) : ?>
									<br><?php echo esc_html( $item[ $item_name . '_address_2' ] ); ?>
								<?php endif; ?>
							</span><br/>
							
							<span class="address-item__address">
								<?php echo esc_html( $item[ $item_name . '_postcode' ] ); ?> <?php echo esc_html( $item[ $item_name . '_city' ] ); ?>
							</span><br/>

							<?php if ( $is_company ) : ?>
								<span class="address-item__nip">
									<?php esc_html_e( 'NIP:', 'woocommerce' ); ?> <?php echo esc_html( $item[ $item_name . '_nip' ] ); ?>
								</span><br/>
							<?php endif; ?>

							<?php if ( ! empty( $item[ $item_name . '_phone' ] ) ) : ?>
								<span class="address-item__phone">
									<?php esc_html_e( 'Phone:', 'woocommerce' ); ?> <?php echo esc_html( $item[ $item_name . '_phone' ] ); ?>
								</span>
							<?php endif; ?>

						</address>

						<div class="address-actions">
							<a href="#address-modal"
								data-action="open-modal"
								data-modal-id="address-modal"
								data-address-id="<?php echo esc_attr( $item_name ); ?>"
								data-address-type="<?php echo esc_attr( $address_type ); ?>"
								class="address-action address-action--edit"
							><?php echo esc_attr__( 'Edytuj', 'sense7' ); ?></a>

							<a href="#"
								data-address-id="<?php echo esc_attr( $item_name ); ?>"
								data-address-type="<?php echo esc_attr( $address_type ); ?>"
								class="address-action address-action--delete"
							><?php echo esc_attr__( 'Usuń', 'sense7' ); ?></a>

							<label class="woocommerce-Address__default-address">
								<input type="checkbox"
									value="<?php echo esc_attr( $item_name ); ?>"
									data-address-type="<?php echo esc_attr( $address_type ); ?>"
									data-is-default="<?php echo esc_attr( $item_name === $address_type ? 'true' : 'false' ); ?>"
									class="address-action--set-default"
									<?php checked( $item_name, $address_type ); ?> />
								<span><?php echo esc_html_e( 'Ustaw jako domyślny', 'sense7' ); ?></span>
							</label>
						</div>
					</div>

				<?php endforeach; ?>
				<div class="woocommerce-Address__item add-item">
					<a href="#address-modal"
						data-action="open-modal"
						data-modal-id="address-modal"
						data-address-type="<?php echo esc_attr( $address_type ); ?>"
						class="add button">
						<?php echo esc_attr__( 'Dodaj nowy adres', 'sense7' ); ?>
					</a>
				</div>

			</div>
			
		</div>
	</div>

<?php endforeach; ?>
</div>
