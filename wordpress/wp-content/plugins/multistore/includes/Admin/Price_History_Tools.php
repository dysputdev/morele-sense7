<?php
/**
 * Price History Admin Tools
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Admin;

use MultiStore\Plugin\WooCommerce\Price_History;

/**
 * Class Price_History_Tools
 *
 * Admin tools for managing price history
 *
 * @since 1.0.0
 */
class Price_History_Tools {

	/**
	 * Price History instance
	 *
	 * @since 1.0.0
	 * @var Price_History
	 */
	private $price_history;

	/**
	 * Show lowest price in admin.
	 *
	 * @var boolean
	 */
	private $display_admin_column = false;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->price_history = new Price_History();

		// Add tools to WooCommerce Status Tools page.
		add_filter( 'woocommerce_debug_tools', array( $this, 'add_woocommerce_tools' ) );

		// Add admin notices for cleanup results.
		add_action( 'admin_notices', array( $this, 'display_cleanup_notices' ) );

		// Add admin column to products list.
		if ( $this->display_admin_column ) {
			add_filter( 'manage_product_posts_columns', array( $this, 'add_lowest_price_column' ), 20 );
			add_action( 'manage_product_posts_custom_column', array( $this, 'render_lowest_price_column' ), 10, 2 );
		}
	}

	/**
	 * Add price history tools to WooCommerce
	 *
	 * @since 1.0.0
	 * @param array $tools Existing tools.
	 * @return array Modified tools.
	 */
	public function add_woocommerce_tools( array $tools ): array {
		$tools['multistore_cleanup_price_history'] = array(
			'name'     => __( 'Cleanup Price History', 'multistore' ),
			'button'   => __( 'Run Cleanup', 'multistore' ),
			'desc'     => __( 'Remove price history records older than 30 days. This is done automatically daily via WP-Cron.', 'multistore' ),
			'callback' => array( $this, 'tool_cleanup_price_history' ),
		);

		$tools['multistore_recalculate_price_history'] = array(
			'name'     => __( 'Recalculate Price History', 'multistore' ),
			'button'   => __( 'Recalculate', 'multistore' ),
			'desc'     => __( 'Log current prices for all products. Useful after plugin activation.', 'multistore' ),
			'callback' => array( $this, 'tool_recalculate_price_history' ),
		);

		$tools['multistore_clear_all_price_history'] = array(
			'name'     => __( 'Clear All Price History', 'multistore' ),
			'button'   => __( 'Clear All', 'multistore' ),
			'desc'     => __( 'WARNING: This will delete all price history records. This action cannot be undone.', 'multistore' ),
			'callback' => array( $this, 'tool_clear_all_price_history' ),
		);

		return $tools;
	}

	/**
	 * Tool: Cleanup old price history
	 *
	 * @since 1.0.0
	 * @return string Result message.
	 */
	public function tool_cleanup_price_history(): string {
		$deleted = $this->price_history->cleanup_old_prices();

		return sprintf(
			/* translators: %d: number of records deleted */
			_n(
				'Deleted %d old price record.',
				'Deleted %d old price records.',
				$deleted,
				'multistore'
			),
			$deleted
		);
	}

	/**
	 * Tool: Recalculate price history
	 *
	 * @since 1.0.0
	 * @return string Result message.
	 */
	public function tool_recalculate_price_history(): string {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		$product_ids = get_posts( $args );
		$logged      = 0;

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product && $product->get_price() > 0 ) {
				if ( $this->price_history->log_price( $product ) ) {
					$logged++;
				}
			}
		}

		return sprintf(
			/* translators: %d: number of products processed */
			_n(
				'Logged current price for %d product.',
				'Logged current prices for %d products.',
				$logged,
				'multistore'
			),
			$logged
		);
	}

	/**
	 * Tool: Clear all price history
	 *
	 * @since 1.0.0
	 * @return string Result message.
	 */
	public function tool_clear_all_price_history(): string {
		global $wpdb;

		$table_name = \MultiStore\Plugin\Database\Price_History_Table::get_table_name();
		$deleted    = $wpdb->query( "DELETE FROM {$table_name}" );

		return sprintf(
			/* translators: %d: number of records deleted */
			__( 'Deleted all %d price history records.', 'multistore' ),
			$deleted
		);
	}

	/**
	 * Display admin notices for cleanup results
	 *
	 * @since 1.0.0
	 */
	public function display_cleanup_notices(): void {
		// This can be used to display notices after manual cleanup runs.
	}

	/**
	 * Add lowest price column to products list
	 *
	 * @since 1.0.0
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_lowest_price_column( array $columns ): array {
		$new_columns = array();

		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;

			// Add after price column.
			if ( 'price' === $key ) {
				$new_columns['lowest_price_30d'] = __( 'Lowest 30d', 'multistore' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render lowest price column content
	 *
	 * @since 1.0.0
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_lowest_price_column( string $column, int $post_id ): void {
		if ( 'lowest_price_30d' !== $column ) {
			return;
		}

		$lowest_price_data = $this->price_history->get_lowest_price( $post_id );

		if ( ! $lowest_price_data ) {
			echo '<span style="color: #999;">—</span>';
			return;
		}

		$lowest_price = (float) $lowest_price_data['price'];
		$product      = wc_get_product( $post_id );
		$current_price = $product ? $product->get_price() : 0;

		echo wc_price( $lowest_price );

		// Show indicator if current price equals lowest price.
		if ( $current_price > 0 && abs( $current_price - $lowest_price ) < 0.01 ) {
			echo ' <span style="color: #46b450;" title="' . esc_attr__( 'Current price is the lowest', 'multistore' ) . '">✓</span>';
		} elseif ( $current_price > $lowest_price ) {
			$diff = ( ( $current_price - $lowest_price ) / $lowest_price ) * 100;
			echo '<br><small style="color: #dc3232;">+' . number_format( $diff, 1 ) . '%</small>';
		}
	}

	/**
	 * Get price history statistics for dashboard
	 *
	 * @since 1.0.0
	 * @return array Statistics data.
	 */
	public function get_statistics(): array {
		global $wpdb;

		$table_name = \MultiStore\Plugin\Database\Price_History_Table::get_table_name();

		$stats = array(
			'total_records'    => $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ),
			'products_tracked' => $wpdb->get_var( "SELECT COUNT(DISTINCT product_id) FROM {$table_name}" ),
			'oldest_record'    => $wpdb->get_var( "SELECT MIN(recorded_at) FROM {$table_name}" ),
			'newest_record'    => $wpdb->get_var( "SELECT MAX(recorded_at) FROM {$table_name}" ),
			'records_last_30d' => $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name} WHERE recorded_at >= %s",
					gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
				)
			),
		);

		return $stats;
	}
}
