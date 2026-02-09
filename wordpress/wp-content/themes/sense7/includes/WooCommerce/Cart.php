<?php

namespace Sense7\Theme\WooCommerce;

class Cart {
	public function __construct() {

		// Handle clear cart action.
		add_action( 'template_redirect', array( $this, 'handle_clear_cart' ) );

		// AJAX handlers for cart operations.
		add_action( 'wp_ajax_sense7_update_cart_item', array( $this, 'ajax_update_cart_item' ) );
		add_action( 'wp_ajax_nopriv_sense7_update_cart_item', array( $this, 'ajax_update_cart_item' ) );

		add_action( 'wp_ajax_sense7_remove_cart_item', array( $this, 'ajax_remove_cart_item' ) );
		add_action( 'wp_ajax_nopriv_sense7_remove_cart_item', array( $this, 'ajax_remove_cart_item' ) );

		add_action( 'wp_ajax_sense7_apply_coupon', array( $this, 'ajax_apply_coupon' ) );
		add_action( 'wp_ajax_nopriv_sense7_apply_coupon', array( $this, 'ajax_apply_coupon' ) );

		add_action( 'wp_ajax_sense7_remove_coupon', array( $this, 'ajax_remove_coupon' ) );
		add_action( 'wp_ajax_nopriv_sense7_remove_coupon', array( $this, 'ajax_remove_coupon' ) );

		add_action( 'wp_ajax_sense7_get_cart', array( $this, 'ajax_get_cart' ) );
		add_action( 'wp_ajax_nopriv_sense7_get_cart', array( $this, 'ajax_get_cart' ) );
		
		// Enqueue assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue theme assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		// Enqueue cart script on cart page.
		if ( is_cart() && file_exists( SENSE7_THEME_DIR . '/assets/js/cart.js' ) ) {
			wp_enqueue_script(
				'sense7-cart',
				SENSE7_THEME_URL . '/assets/js/cart.js',
				array( 'jquery' ),
				SENSE7_THEME_VERSION,
				true
			);

			// Localize script with AJAX URL and nonce.
			wp_localize_script(
				'sense7-cart',
				'sense7Cart',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'sense7_cart_nonce' ),
				)
			);
		}

		// Enqueue checkout script on checkout page.
		if ( is_checkout() && file_exists( SENSE7_THEME_DIR . '/assets/js/checkout.js' ) ) {
			wp_enqueue_script(
				'sense7-checkout',
				SENSE7_THEME_URL . '/assets/js/checkout.js',
				array( 'jquery' ),
				SENSE7_THEME_VERSION,
				true
			);
		}

		// Enqueue my account script on my account page.
		if ( is_account_page() && file_exists( SENSE7_THEME_DIR . '/assets/js/myaccount.js' ) ) {
			wp_enqueue_script(
				'sense7-myaccount',
				SENSE7_THEME_URL . '/assets/js/myaccount.js',
				array(),
				SENSE7_THEME_VERSION,
				true
			);
		}
	}

	/**
	 * Handle clear cart action
	 *
	 * @since 1.0.0
	 */
	public function handle_clear_cart(): void {
		if ( isset( $_GET['clear-cart'] ) && '1' === $_GET['clear-cart'] ) {
			if ( function_exists( 'WC' ) && WC()->cart ) {
				WC()->cart->empty_cart();
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}

		// Handle remove coupon action.
		if ( isset( $_GET['remove_coupon'] ) && ! empty( $_GET['remove_coupon'] ) ) {
			if ( function_exists( 'WC' ) && WC()->cart ) {
				$coupon_code = sanitize_text_field( wp_unslash( $_GET['remove_coupon'] ) );
				WC()->cart->remove_coupon( $coupon_code );
				wc_add_notice( __( 'Kupon został usunięty.', 'sense7' ), 'success' );
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
	}

	/**
	 * Verify AJAX nonce
	 *
	 * @since 1.0.0
	 */
	private function verify_ajax_nonce(): bool {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'sense7_cart_nonce' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Get cart data for AJAX response
	 *
	 * @since 1.0.0
	 */
	private function get_cart_data(): array {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return array();
		}

		$cart = WC()->cart;

		return array(
			'items'       => $this->get_cart_items(),
			'items_count' => $cart->get_cart_contents_count(),
			'totals'      => array(
				'subtotal'       => $cart->get_subtotal(),
				'subtotal_tax'   => $cart->get_subtotal_tax(),
				'shipping_total' => $cart->get_shipping_total(),
				'shipping_tax'   => $cart->get_shipping_tax(),
				'discount_total' => $cart->get_discount_total(),
				'discount_tax'   => $cart->get_discount_tax(),
				'total'          => $cart->get_total( 'edit' ),
				'total_tax'      => $cart->get_total_tax(),
			),
			'coupons'     => $this->get_applied_coupons(),
		);
	}

	/**
	 * Get cart items formatted for AJAX
	 *
	 * @since 1.0.0
	 */
	private function get_cart_items(): array {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return array();
		}

		$items = array();

		foreach ( \WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];

			if ( ! $product ) {
				continue;
			}

			$price = wc_get_price_to_display( $product, array( 'qty' => $cart_item['quantity'] ) );

			$items[] = array(
				'key'      => $cart_item_key,
				'quantity' => $cart_item['quantity'],
				'totals'   => array(
					'line_subtotal'     => $cart_item['line_subtotal'],
					'line_subtotal_tax' => $cart_item['line_subtotal_tax'],
					'line_total'        => $price,
					'line_tax'          => $cart_item['line_tax'],
				),
			);
		}

		return $items;
	}

	/**
	 * Get applied coupons formatted for AJAX
	 *
	 * @since 1.0.0
	 */
	private function get_applied_coupons(): array {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return array();
		}

		$coupons = array();

		foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) {
			$coupon = new \WC_Coupon( $coupon_code );

			$coupons[] = array(
				'code'   => $coupon_code,
				'amount' => $coupon->get_amount(),
				'totals' => array(
					'total_discount'     => WC()->cart->get_coupon_discount_amount( $coupon_code ),
					'total_discount_tax' => WC()->cart->get_coupon_discount_tax_amount( $coupon_code ),
				),
			);
		}

		return $coupons;
	}

	/**
	 * AJAX: Update cart item quantity
	 *
	 * @since 1.0.0
	 */
	public function ajax_update_cart_item(): void {
		if ( ! $this->verify_ajax_nonce() ) {
			wp_send_json_error( array( 'message' => __( 'Nieprawidłowe żądanie.', 'sense7' ) ), 403 );
		}

		if ( ! isset( $_POST['cart_item_key'] ) || ! isset( $_POST['quantity'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak wymaganych parametrów.', 'sense7' ) ), 400 );
		}

		$cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) );
		$quantity      = absint( $_POST['quantity'] );

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Koszyk nie jest dostępny.', 'sense7' ) ), 500 );
		}

		$updated = WC()->cart->set_quantity( $cart_item_key, $quantity, true );

		if ( ! $updated ) {
			wp_send_json_error( array( 'message' => __( 'Nie udało się zaktualizować ilości.', 'sense7' ) ), 500 );
		}

		WC()->cart->calculate_totals();

		wp_send_json_success(
			array(
				'message' => __( 'Ilość została zaktualizowana.', 'sense7' ),
				'cart'    => $this->get_cart_data(),
			)
		);
	}

	/**
	 * AJAX: Remove cart item
	 *
	 * @since 1.0.0
	 */
	public function ajax_remove_cart_item(): void {
		if ( ! $this->verify_ajax_nonce() ) {
			wp_send_json_error( array( 'message' => __( 'Nieprawidłowe żądanie.', 'sense7' ) ), 403 );
		}

		if ( ! isset( $_POST['cart_item_key'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak wymaganych parametrów.', 'sense7' ) ), 400 );
		}

		$cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) );

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Koszyk nie jest dostępny.', 'sense7' ) ), 500 );
		}

		$removed = WC()->cart->remove_cart_item( $cart_item_key );

		if ( ! $removed ) {
			wp_send_json_error( array( 'message' => __( 'Nie udało się usunąć produktu.', 'sense7' ) ), 500 );
		}

		WC()->cart->calculate_totals();

		wp_send_json_success(
			array(
				'message' => __( 'Produkt został usunięty z koszyka.', 'sense7' ),
				'cart'    => $this->get_cart_data(),
			)
		);
	}

	/**
	 * AJAX: Apply coupon
	 *
	 * @since 1.0.0
	 */
	public function ajax_apply_coupon(): void {
		if ( ! $this->verify_ajax_nonce() ) {
			wp_send_json_error( array( 'message' => __( 'Nieprawidłowe żądanie.', 'sense7' ) ), 403 );
		}

		if ( ! isset( $_POST['coupon_code'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak wymaganych parametrów.', 'sense7' ) ), 400 );
		}

		$coupon_code = sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) );

		if ( empty( $coupon_code ) ) {
			wp_send_json_error( array( 'message' => __( 'Wprowadź kod kuponu.', 'sense7' ) ), 400 );
		}

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Koszyk nie jest dostępny.', 'sense7' ) ), 500 );
		}

		$result = WC()->cart->apply_coupon( $coupon_code );

		if ( ! $result ) {
			$error_messages = wc_get_notices( 'error' );
			$error_message  = ! empty( $error_messages ) ? wp_strip_all_tags( $error_messages[0]['notice'] ) : __( 'Nie udało się zastosować kuponu.', 'sense7' );
			wc_clear_notices();
			wp_send_json_error( array( 'message' => $error_message ), 400 );
		}

		wc_clear_notices();
		WC()->cart->calculate_totals();

		wp_send_json_success(
			array(
				'message' => __( 'Kupon został zastosowany.', 'sense7' ),
				'cart'    => $this->get_cart_data(),
			)
		);
	}

	/**
	 * AJAX: Remove coupon
	 *
	 * @since 1.0.0
	 */
	public function ajax_remove_coupon(): void {
		if ( ! $this->verify_ajax_nonce() ) {
			wp_send_json_error( array( 'message' => __( 'Nieprawidłowe żądanie.', 'sense7' ) ), 403 );
		}

		if ( ! isset( $_POST['coupon_code'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak wymaganych parametrów.', 'sense7' ) ), 400 );
		}

		$coupon_code = sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) );

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Koszyk nie jest dostępny.', 'sense7' ) ), 500 );
		}

		$removed = WC()->cart->remove_coupon( $coupon_code );

		if ( ! $removed ) {
			wp_send_json_error( array( 'message' => __( 'Nie udało się usunąć kuponu.', 'sense7' ) ), 500 );
		}

		WC()->cart->calculate_totals();

		wp_send_json_success(
			array(
				'message' => __( 'Kupon został usunięty.', 'sense7' ),
				'cart'    => $this->get_cart_data(),
			)
		);
	}

	/**
	 * AJAX: Get cart data
	 *
	 * @since 1.0.0
	 */
	public function ajax_get_cart(): void {
		if ( ! $this->verify_ajax_nonce() ) {
			wp_send_json_error( array( 'message' => __( 'Nieprawidłowe żądanie.', 'sense7' ) ), 403 );
		}

		wp_send_json_success(
			array(
				'cart' => $this->get_cart_data(),
			)
		);
	}
}
