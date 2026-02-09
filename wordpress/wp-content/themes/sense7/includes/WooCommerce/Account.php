<?php

namespace Sense7\Theme\WooCommerce;

use WC_AJAX;

class Account {

	/**
	 * Render universal modal
	 *
	 * @param array $args Modal arguments.
	 * @return void
	 */
	public static function render_modal( $args = array() ) {
		get_template_part( 'template-parts/modal', null, $args );
	}
	public function __construct() {

		// Enqueue assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		add_filter( 'woocommerce_account_menu_items', array( $this, 'menu_items' ) );

		add_filter( 'woocommerce_save_account_details_required_fields', array( $this, 'account_details_required_fields' ) );

		add_action( 'template_redirect', array( $this, 'redirect_my_account_pages' ) );
		// if ( class_exists( 'WC_Address_Book' ) ) {
		// 	$class = new \WC_Address_Book();
		// 	add_action( 'woocommerce_account_edit-account_endpoint', array( $class, 'wc_address_book_page' ), 20 );
		// }
		// $class = new \WC_Address_Book();
		// remove_action( 'woocommerce_account_edit-address_endpoint', array( $class, 'wc_address_book_page' ), 20 );

		// add_action( 'woocommerce_customer_save_address', array( $this, 'customer_save_address' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_save_account_field', array( $this, 'save_account_field_ajax' ) );
		add_action( 'wp_ajax_save_address', array( $this, 'save_address_ajax' ) );
		add_action( 'wp_ajax_get_address_form_fields', array( $this, 'get_address_form_fields_ajax' ) );
	}

	public function redirect_my_account_pages() {
		global $wp;

		// Only for logged-in users.
		$redirect_to = false;

		// redirect map based on WC()->query->get_query_vars().
		if ( is_account_page() && is_user_logged_in() ) {
			// redirect from main account page to edit account page.
			if ( false === is_wc_endpoint_url() ) {
				$redirect_to = wc_get_account_endpoint_url( 'edit-account' );
			} else if ( is_wc_endpoint_url( 'downloads' ) ) {
				$redirect_to = wc_get_account_endpoint_url( 'edit-account' );
			} elseif ( is_wc_endpoint_url( 'edit-address' ) && empty( $wp->query_vars['edit-address'] ) ) {
				$redirect_to = wc_get_account_endpoint_url( 'edit-account' );
			}

			if ( $redirect_to ) {
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}
	}

	/**
	 * Enqueue theme assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		// Enqueue my account script on my account page.
		if ( is_account_page() && file_exists( SENSE7_THEME_DIR . '/assets/js/myaccount.js' ) ) {
			wp_enqueue_script(
				'sense7-myaccount',
				SENSE7_THEME_URL . '/assets/js/myaccount.js',
				array(),
				SENSE7_THEME_VERSION,
				true
			);

			wp_localize_script(
				'sense7-myaccount',
				'sense7Account',
				array(
					'ajax_url'            => admin_url( 'admin-ajax.php' ),
					'wc_ajax_url'         => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : '?wc-ajax=%%endpoint%%',
					'save_account_nonce'  => wp_create_nonce( 'save_account_field' ),
					'save_address_nonce'  => wp_create_nonce( 'save_address' ),
					'primary_nonce'       => wp_create_nonce( 'woo-address-book-primary' ),
					'delete_nonce'        => wp_create_nonce( 'woo-address-book-delete' ),
					'delete_confirmation' => __( 'Czy na pewno chcesz usunąć ten adres?', 'sense7' ),
				)
			);
		}
	}

	public function menu_items( $items ) {
		$org_items = $items;
		unset(
			$items['dashboard'],
			$items['downloads'],
			$items['edit-address'],
			$items['customer-logout'],
		);
		$items['edit-account'] = __( 'Ustawienia', 'sense7' );

		return $items;
	}

	public function account_details_required_fields() {
		unset( $required_fields['account_first_name'] );
		unset( $required_fields['account_last_name'] );
		return $required_fields;
	}

	public function customer_save_address( $user_id, $address_type, $address, $customer ) {
		if ( 0 < wc_notice_count( 'error' ) ) {
			return;
		}

		wc_add_notice( __( 'Address changed successfully.', 'woocommerce' ) );
		wp_safe_redirect( wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ) ) );
	}

	/**
	 * AJAX handler for saving account field
	 *
	 * @since 1.0.0
	 */
	public function save_account_field_ajax() {
		// Check nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'save_account_details' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid security token.', 'sense7' ),
				)
			);
		}

		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You must be logged in to update your account.', 'sense7' ),
				)
			);
		}

		$user_id     = get_current_user_id();
		$field_name  = isset( $_POST['field_name'] ) ? sanitize_text_field( wp_unslash( $_POST['field_name'] ) ) : '';
		$field_value = isset( $_POST['field_value'] ) ? sanitize_text_field( wp_unslash( $_POST['field_value'] ) ) : '';

		// Validate field name.
		$allowed_fields = array( 'account_display_name', 'account_email' );
		if ( ! in_array( $field_name, $allowed_fields, true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid field name.', 'sense7' ),
				)
			);
		}

		// Validate field value.
		if ( empty( $field_value ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Field value cannot be empty.', 'sense7' ),
				)
			);
		}

		// Process field based on type.
		switch ( $field_name ) {
			case 'account_display_name':
				$result = wp_update_user(
					array(
						'ID'           => $user_id,
						'display_name' => $field_value,
					)
				);

				if ( is_wp_error( $result ) ) {
					wp_send_json_error(
						array(
							'message' => $result->get_error_message(),
						)
					);
				}

				wp_send_json_success(
					array(
						'message' => __( 'Display name updated successfully.', 'sense7' ),
					)
				);
				break;

			case 'account_email':
				// Validate email format.
				if ( ! is_email( $field_value ) ) {
					wp_send_json_error(
						array(
							'message' => __( 'Please provide a valid email address.', 'sense7' ),
						)
					);
				}

				// Check if email is already in use by another user.
				$email_exists = email_exists( $field_value );
				if ( $email_exists && $email_exists !== $user_id ) {
					wp_send_json_error(
						array(
							'message' => __( 'This email address is already registered.', 'sense7' ),
						)
					);
				}

				$result = wp_update_user(
					array(
						'ID'         => $user_id,
						'user_email' => $field_value,
					)
				);

				if ( is_wp_error( $result ) ) {
					wp_send_json_error(
						array(
							'message' => $result->get_error_message(),
						)
					);
				}

				wp_send_json_success(
					array(
						'message' => __( 'Email address updated successfully.', 'sense7' ),
					)
				);
				break;
		}
	}

	/**
	 * AJAX handler for getting address form fields
	 */
	public function get_address_form_fields_ajax() {
		check_ajax_referer( 'save_address', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'sense7' ) ) );
		}

		$user_id      = get_current_user_id();
		$address_type = isset( $_POST['address_type'] ) ? sanitize_text_field( wp_unslash( $_POST['address_type'] ) ) : 'billing';
		$address_name = isset( $_POST['address_name'] ) ? sanitize_text_field( wp_unslash( $_POST['address_name'] ) ) : '';

		// Get address data if editing.
		$address_data = array();
		// if ( ! empty( $address_name ) && $address_name !== $address_type ) {
		if ( class_exists( 'WC_Address_Book' ) ) {
			$wc_address_book = \WC_Address_Book::get_instance();
			$address_book    = $wc_address_book->get_address_book( $user_id, $address_type );

			if ( isset( $address_book[ $address_name ] ) ) {
				$address_data = $address_book[ $address_name ];
			}
		}
		// }

		// Render form fields.
		ob_start();
		wc_get_template(
			'myaccount/_address-form-modal.php',
			array(
				'address_type' => $address_type,
				'address_name' => $address_name ? $address_name : $address_type,
				'address_data' => $address_data,
			)
		);
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * AJAX handler for saving address
	 */
	public function save_address_ajax() {
		check_ajax_referer( 'save_address', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'sense7' ) ) );
		}

		$user_id      = get_current_user_id();
		$address_type = isset( $_POST['address_type'] ) ? sanitize_text_field( wp_unslash( $_POST['address_type'] ) ) : '';
		$address_name = isset( $_POST['address_name'] ) ? sanitize_text_field( wp_unslash( $_POST['address_name'] ) ) : '';

		// Validate address type.
		if ( ! in_array( $address_type, array( 'billing', 'shipping' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid address type.', 'sense7' ) ) );
		}

		if ( ! class_exists( 'WC_Address_Book' ) ) {
			wp_send_json_error( array( 'message' => __( 'WooCommerce Address Book plugin is required.', 'sense7' ) ) );
		}

		$wc_address_book = \WC_Address_Book::get_instance();
		$address_names   = $wc_address_book->get_address_names( $user_id, $address_type );

		// Check if this address already exists (including default address which equals address_type).
		$address_exists = in_array( $address_name, $address_names, true ) || $address_name === $address_type;

		// If adding new address (empty or doesn't exist), get next available name.
		if ( empty( $address_name ) || ! $address_exists ) {
			$address_name = $wc_address_book->set_new_address_name( $address_names, $address_type );
		}

		// Get address fields for the country.
		$country        = isset( $_POST[ $address_name . '_country' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $address_name . '_country' ] ) ) : WC()->countries->get_base_country();
		$address_fields = WC()->countries->get_address_fields( $country, $address_type . '_' );

		// Save address data.
		foreach ( $address_fields as $key => $field ) {
			$field_key   = str_replace( $address_type . '_', $address_name . '_', $key );
			$field_value = isset( $_POST[ $field_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field_key ] ) ) : '';

			// Validate required fields.
			if ( ! empty( $field['required'] ) && empty( $field_value ) ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							/* translators: %s: field label */
							__( '%s is required.', 'sense7' ),
							$field['label']
						),
					)
				);
			}

			update_user_meta( $user_id, $field_key, $field_value );
		}

		// Update address names list.
		if ( class_exists( 'WC_Address_Book' ) ) {
			$wc_address_book = \WC_Address_Book::get_instance();
			$address_names   = $wc_address_book->get_address_names( $user_id, $address_type );

			if ( ! in_array( $address_name, $address_names, true ) ) {
				$address_names[] = $address_name;
				update_user_meta( $user_id, 'wc_address_book_' . $address_type, $address_names );
			}
		}

		// Trigger WooCommerce hook for compatibility.
		do_action( 'woocommerce_customer_save_address', $user_id, $address_type );

		wp_send_json_success(
			array(
				'message'      => __( 'Address saved successfully.', 'sense7' ),
				'address_name' => $address_name,
			)
		);
	}
}
