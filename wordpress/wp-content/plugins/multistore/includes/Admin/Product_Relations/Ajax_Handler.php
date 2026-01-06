<?php
/**
 * Product Relations AJAX Handler
 *
 * Handles AJAX requests for product relations
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Admin\Product_Relations;

use MultiStore\Plugin\Database\Product_Relation_Groups_Table;

/**
 * Class Ajax_Handler
 *
 * Handles AJAX requests for product relations
 *
 * @since 1.0.0
 */
class Ajax_Handler {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_multistore_create_relation_group', array( $this, 'create_relation_group' ) );
		add_action( 'wp_ajax_multistore_update_relation_group', array( $this, 'update_relation_group' ) );
		add_action( 'wp_ajax_multistore_search_products', array( $this, 'search_products' ) );
	}

	/**
	 * Verify AJAX nonce and capabilities
	 *
	 * @since 1.0.0
	 * @return bool True if valid, false otherwise.
	 */
	private function verify_request(): bool {
		// Check if nonce is set.
		if ( ! isset( $_POST['nonce'] ) ) {
			return false;
		}

		// Verify nonce - use wp_unslash, don't sanitize before verification.
		$nonce = wp_unslash( $_POST['nonce'] );
		if ( ! wp_verify_nonce( $nonce, 'multistore_product_relations' ) ) {
			return false;
		}

		// Check user capabilities.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * AJAX: Create new relation group
	 *
	 * @since 1.0.0
	 */
	public function create_relation_group(): void {
		check_ajax_referer( 'multistore_product_relations', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak uprawnień', 'multistore' ) ) );
		}

		$name                  = sanitize_text_field( $_POST['name'] ?? '' );
		$attribute_id          = absint( $_POST['attribute_id'] ?? 0 );
		$display_on_list       = absint( $_POST['display_on_list'] ?? 0 );
		$display_style_single  = sanitize_text_field( $_POST['display_style_single'] ?? 'image_product' );
		$display_style_archive = sanitize_text_field( $_POST['display_style_archive'] ?? 'image_product' );
		$sort_order            = absint( $_POST['sort_order'] ?? 0 );

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Nazwa grupy jest wymagana', 'multistore' ) ) );
		}

		global $wpdb;
		$table_name = Product_Relation_Groups_Table::get_table_name();

		$data    = array(
			'name'                  => $name,
			'display_on_list'       => $display_on_list,
			'display_style_single'  => $display_style_single,
			'display_style_archive' => $display_style_archive,
			'sort_order'            => $sort_order,
		);
		$formats = array( '%s', '%d', '%s', '%s', '%d' );

		// Add attribute_id if provided.
		if ( $attribute_id > 0 ) {
			$data['attribute_id'] = $attribute_id;
			$formats[]            = '%d';
		}

		$result = $wpdb->insert( $table_name, $data, $formats );

		if ( $result ) {
			wp_send_json_success(
				array(
					'group_id' => $wpdb->insert_id,
					'message'  => __( 'Grupa została utworzona', 'multistore' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Błąd podczas tworzenia grupy', 'multistore' ),
					'error'   => $wpdb->last_error,
				)
			);
		}
	}

	/**
	 * AJAX: Update relation group
	 *
	 * @since 1.0.0
	 */
	public function update_relation_group(): void {
		check_ajax_referer( 'multistore_product_relations', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Brak uprawnień', 'multistore' ) ) );
		}

		$group_id              = absint( $_POST['group_id'] ?? 0 );
		$name                  = sanitize_text_field( $_POST['name'] ?? '' );
		$attribute_id          = absint( $_POST['attribute_id'] ?? 0 );
		$display_on_list       = absint( $_POST['display_on_list'] ?? 0 );
		$display_style_single  = sanitize_text_field( $_POST['display_style_single'] ?? 'image_product' );
		$display_style_archive = sanitize_text_field( $_POST['display_style_archive'] ?? 'image_product' );
		$sort_order            = absint( $_POST['sort_order'] ?? 0 );

		if ( $group_id === 0 ) {
			wp_send_json_error( array( 'message' => __( 'Nieprawidłowe ID grupy', 'multistore' ) ) );
		}

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'message' => __( 'Nazwa grupy jest wymagana', 'multistore' ) ) );
		}

		global $wpdb;
		$table_name = Product_Relation_Groups_Table::get_table_name();

		$data = array(
			'name'                  => $name,
			'display_on_list'       => $display_on_list,
			'display_style_single'  => $display_style_single,
			'display_style_archive' => $display_style_archive,
			'sort_order'            => $sort_order,
		);

		$formats = array( '%s', '%d', '%s', '%s', '%d' );

		// Add attribute_id if provided.
		if ( $attribute_id > 0 ) {
			$data['attribute_id'] = $attribute_id;
			$formats[]            = '%d';
		} else {
			$data['attribute_id'] = null;
			$formats[]            = '%d';
		}

		$result = $wpdb->update(
			$table_name,
			$data,
			array( 'id' => $group_id ),
			$formats,
			array( '%d' )
		);

		if ( false !== $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Grupa została zaktualizowana', 'multistore' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Błąd podczas aktualizacji grupy', 'multistore' ),
					'error'   => $wpdb->last_error,
				)
			);
		}
	}

	/**
	 * AJAX: Search products
	 *
	 * @since 1.0.0
	 */
	public function search_products(): void {
		if ( ! $this->verify_request() ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'multistore' ) ) );
			return;
		}

		$search_term = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
		$exclude_id  = isset( $_POST['exclude_id'] ) ? absint( $_POST['exclude_id'] ) : 0;

		if ( empty( $search_term ) ) {
			wp_send_json_error( array( 'message' => __( 'Search term is required', 'multistore' ) ) );
			return;
		}

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			's'              => $search_term,
			'posts_per_page' => 10,
			'fields'         => 'ids',
		);

		if ( $exclude_id > 0 ) {
			$args['post__not_in'] = array( $exclude_id );
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			wp_send_json_success( array( 'products' => array() ) );
			return;
		}

		$products = array();

		foreach ( $query->posts as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			$products[] = array(
				'id'        => $product_id,
				'name'      => $product->get_name(),
				'sku'       => $product->get_sku(),
				'price'     => $product->get_price_html(),
				'permalink' => get_permalink( $product_id ),
				'image'     => $product->get_image( 'thumbnail' ),
				'image_url' => wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ),
			);
		}

		wp_send_json_success( array( 'products' => $products ) );
	}
}
