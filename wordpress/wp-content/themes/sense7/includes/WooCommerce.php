<?php

namespace Sense7\Theme;

class WooCommerce {
	public function __construct() {

		// Enqueue assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		$this->init_components();
	}

	/**
	 * Init WooCommerce components.
	 *
	 * @return void
	 */
	public function init_components() {
		new WooCommerce\Account();
		new WooCommerce\Cart();
		new WooCommerce\Checkout();

		new WooCommerce\MiniCart();
	}

	/**
	 * Enqueue theme assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		return;
	}

	/**
	 * Add VAT number field to billing fields
	 *
	 * @param array $fields Billing fields.
	 * @return array
	 * @since 1.0.0
	 */
	public function add_billing_vat_field( array $fields ): array {
		// Add VAT number field after company field.
		$new_fields = array();
		foreach ( $fields as $key => $field ) {
			$new_fields[ $key ] = $field;

			if ( 'billing_company' === $key ) {
				$new_fields['billing_vat_number'] = array(
					'type'        => 'text',
					'label'       => __( 'NIP', 'sense7' ),
					'placeholder' => __( 'Numer NIP', 'sense7' ),
					'required'    => false,
					'class'       => array( 'form-row-wide', 'company-field', 'hidden' ),
					'priority'    => 31,
				);
			}
		}

		return $new_fields;
	}

	/**
	 * Save VAT number field to order meta
	 *
	 * @param int $order_id Order ID.
	 * @return void
	 * @since 1.0.0
	 */
	public function save_vat_field( int $order_id ): void {
		if ( ! empty( $_POST['billing_vat_number'] ) ) {
			update_post_meta( $order_id, '_billing_vat_number', sanitize_text_field( wp_unslash( $_POST['billing_vat_number'] ) ) );
		}
	}

	/**
	 * Display VAT number in admin order details
	 *
	 * @param WC_Order $order Order object.
	 * @return void
	 * @since 1.0.0
	 */
	public function display_vat_in_admin( $order ): void {
		$vat_number = $order->get_meta( '_billing_vat_number' );
		if ( $vat_number ) {
			echo '<p><strong>' . esc_html__( 'NIP:', 'sense7' ) . '</strong> ' . esc_html( $vat_number ) . '</p>';
		}
	}

	/**
	 * Display VAT number in order details for customer
	 *
	 * @param WC_Order $order Order object.
	 * @return void
	 * @since 1.0.0
	 */
	public function display_vat_in_order( $order ): void {
		$vat_number = $order->get_meta( '_billing_vat_number' );
		if ( $vat_number ) {
			echo '<p><strong>' . esc_html__( 'NIP:', 'sense7' ) . '</strong> ' . esc_html( $vat_number ) . '</p>';
		}
	}
}
