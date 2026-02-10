<?php
/**
 * Address Form Fields for Modal
 *
 * @package Sense7
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$defaults = array(
	'address_type' => 'billing',
	'address_name' => '',
	'address_data' => array(),
);

$args = isset( $args ) ? wp_parse_args( $args, $defaults ) : $defaults;

$address_type = $args['address_type'];
$address_name = $args['address_name'];
$address_data = $args['address_data'];

// Get country for address fields.
$country = ! empty( $address_data[ $address_name . '_country' ] )
	? $address_data[ $address_name . '_country' ]
	: WC()->countries->get_base_country();

// Get address fields from WooCommerce.
$address_fields = WC()->countries->get_address_fields( $country, $address_type . '_' );

// If editing, replace field keys with address_name prefix.
if ( ! empty( $address_name ) ) {
	$custom_fields = array();
	foreach ( $address_fields as $key => $field ) {
		$new_key                   = str_replace( $address_type . '_', $address_name . '_', $key );
		$field['value']            = isset( $address_data[ $new_key ] ) ? $address_data[ $new_key ] : '';
		$custom_fields[ $new_key ] = $field;
	}
	$address_fields = $custom_fields;
}
?>

<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

<div class="woocommerce-address-fields__field-wrapper">
	<?php
	foreach ( $address_fields as $key => $field ) {
		woocommerce_form_field( $key, $field, $field['value'] ?? '' );
	}
	?>
</div>

<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>
