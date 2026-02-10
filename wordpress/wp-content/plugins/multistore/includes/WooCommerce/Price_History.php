<?php
/**
 * Price History Manager
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\WooCommerce;

use MultiStore\Plugin\Database\Price_History_Table;

/**
 * Class Price_History
 *
 * Manages product price history for Omnibus directive compliance
 *
 * @since 1.0.0
 */
class Price_History {

	/**
	 * Number of days to keep price history
	 *
	 * @since 1.0.0
	 * @var int
	 */
	const HISTORY_DAYS = 30;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Hook to save price when product is saved/updated.
		add_action( 'woocommerce_update_product', array( $this, 'log_price_on_update' ), 10, 1 );
		add_action( 'woocommerce_new_product', array( $this, 'log_price_on_update' ), 10, 1 );

		// Hook for price changes via REST API or external sync.
		add_action( 'woocommerce_product_set_price', array( $this, 'log_price_on_change' ), 10, 2 );
		add_action( 'woocommerce_product_set_regular_price', array( $this, 'log_price_on_change' ), 10, 2 );
		add_action( 'woocommerce_product_set_sale_price', array( $this, 'log_price_on_change' ), 10, 2 );

		// Schedule cleanup cron.
		add_action( 'multistore_cleanup_price_history', array( $this, 'cleanup_old_prices' ) );

		// Register cron schedule.
		if ( ! wp_next_scheduled( 'multistore_cleanup_price_history' ) ) {
			wp_schedule_event( time(), 'daily', 'multistore_cleanup_price_history' );
		}
	}

	/**
	 * Log price when product is updated
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 */
	public function log_price_on_update( int $product_id ): void {
		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return;
		}

		$this->log_price( $product );
	}

	/**
	 * Log price when price property changes
	 *
	 * @since 1.0.0
	 * @param mixed            $value   New price value.
	 * @param \WC_Product|null $product Product object.
	 */
	public function log_price_on_change( $value, $product ): void {
		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		// Schedule logging for after the save completes.
		add_action(
			'shutdown',
			function () use ( $product ) {
				$this->log_price( $product );
			}
		);
	}

	/**
	 * Log product price to history
	 *
	 * @since 1.0.0
	 * @param \WC_Product $product Product object.
	 * @return bool True on success, false on failure.
	 */
	public function log_price( \WC_Product $product ): bool {
		global $wpdb;

		$product_id = $product->get_id();
		$price      = $product->get_price();

		// Skip if no price.
		if ( empty( $price ) || $price <= 0 ) {
			return false;
		}

		$table_name = Price_History_Table::get_table_name();

		// Check if we already have this exact price logged today.
		$today_start = gmdate( 'Y-m-d 00:00:00' );
		$today_end   = gmdate( 'Y-m-d 23:59:59' );

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name}
				WHERE product_id = %d
				AND price = %f
				AND recorded_at BETWEEN %s AND %s",
				$product_id,
				$price,
				$today_start,
				$today_end
			)
		);

		// Don't log duplicate prices on the same day.
		if ( $existing > 0 ) {
			return false;
		}

		$result = $wpdb->insert(
			$table_name,
			array(
				'product_id'    => $product_id,
				'sku'           => $product->get_sku(),
				'price'         => $price,
				'regular_price' => $product->get_regular_price(),
				'sale_price'    => $product->get_sale_price(),
				'recorded_at'   => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%f', '%f', '%f', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Get lowest price from last N days
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @param int $days       Number of days to look back (default: 30).
	 * @return array|null Array with price data or null if not found.
	 */
	public function get_lowest_price( int $product_id, int $days = self::HISTORY_DAYS ): ?array {
		global $wpdb;

		$table_name = Price_History_Table::get_table_name();
		$date_from  = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT price, regular_price, sale_price, recorded_at
				FROM {$table_name}
				WHERE product_id = %d
				AND recorded_at >= %s
				ORDER BY price ASC, recorded_at DESC
				LIMIT 1",
				$product_id,
				$date_from
			),
			ARRAY_A
		);

		if ( ! $result ) {
			return null;
		}

		return $result;
	}

	/**
	 * Get lowest price by SKU from last N days
	 *
	 * @since 1.0.0
	 * @param string $sku  Product SKU.
	 * @param int    $days Number of days to look back (default: 30).
	 * @return array|null Array with price data or null if not found.
	 */
	public function get_lowest_price_by_sku( string $sku, int $days = self::HISTORY_DAYS ): ?array {
		global $wpdb;

		$table_name = Price_History_Table::get_table_name();
		$date_from  = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT product_id, price, regular_price, sale_price, recorded_at
				FROM {$table_name}
				WHERE sku = %s
				AND recorded_at >= %s
				ORDER BY price ASC, recorded_at DESC
				LIMIT 1",
				$sku,
				$date_from
			),
			ARRAY_A
		);

		if ( ! $result ) {
			return null;
		}

		return $result;
	}

	/**
	 * Get price history for a product
	 *
	 * @since 1.0.0
	 * @param int    $product_id Product ID.
	 * @param int    $days       Number of days to look back (default: 30).
	 * @param string $order      Order direction (ASC or DESC).
	 * @return array Array of price records.
	 */
	public function get_price_history( int $product_id, int $days = self::HISTORY_DAYS, string $order = 'DESC' ): array {
		global $wpdb;

		$table_name = Price_History_Table::get_table_name();
		$date_from  = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		$order      = strtoupper( $order ) === 'ASC' ? 'ASC' : 'DESC';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name}
				WHERE product_id = %d
				AND recorded_at >= %s
				ORDER BY recorded_at {$order}",
				$product_id,
				$date_from
			),
			ARRAY_A
		);

		return $results ?: array();
	}

	/**
	 * Cleanup price records older than specified days
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to keep (default: 30).
	 * @return int Number of deleted records.
	 */
	public function cleanup_old_prices( int $days = self::HISTORY_DAYS ): int {
		global $wpdb;

		$table_name = Price_History_Table::get_table_name();
		$date_limit = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE recorded_at < %s",
				$date_limit
			)
		);

		return (int) $deleted;
	}

	/**
	 * Delete all price history for a product
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @return int Number of deleted records.
	 */
	public function delete_product_history( int $product_id ): int {
		global $wpdb;

		$table_name = Price_History_Table::get_table_name();

		$deleted = $wpdb->delete(
			$table_name,
			array( 'product_id' => $product_id ),
			array( '%d' )
		);

		return (int) $deleted;
	}

	/**
	 * Check if product has price history
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @param int $days       Number of days to check (default: 30).
	 * @return bool True if has history, false otherwise.
	 */
	public function has_price_history( int $product_id, int $days = self::HISTORY_DAYS ): bool {
		global $wpdb;

		$table_name = Price_History_Table::get_table_name();
		$date_from  = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name}
				WHERE product_id = %d
				AND recorded_at >= %s",
				$product_id,
				$date_from
			)
		);

		return $count > 0;
	}

	/**
	 * Get statistics for price history
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @param int $days       Number of days to analyze (default: 30).
	 * @return array Array with min, max, avg prices.
	 */
	public function get_price_statistics( int $product_id, int $days = self::HISTORY_DAYS ): array {
		global $wpdb;

		$table_name = Price_History_Table::get_table_name();
		$date_from  = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					MIN(price) as min_price,
					MAX(price) as max_price,
					AVG(price) as avg_price,
					COUNT(*) as record_count
				FROM {$table_name}
				WHERE product_id = %d
				AND recorded_at >= %s",
				$product_id,
				$date_from
			),
			ARRAY_A
		);

		if ( ! $stats ) {
			return array(
				'min_price'    => null,
				'max_price'    => null,
				'avg_price'    => null,
				'record_count' => 0,
			);
		}

		return $stats;
	}

	/**
	 * Manually log price for a product
	 *
	 * Useful for bulk imports or backfilling data
	 *
	 * @since 1.0.0
	 * @param int    $product_id   Product ID.
	 * @param float  $price        Price to log.
	 * @param string $recorded_at  Date/time of the price (default: now).
	 * @param float  $regular_price Regular price (optional).
	 * @param float  $sale_price   Sale price (optional).
	 * @return bool True on success, false on failure.
	 */
	public function manual_log_price( int $product_id, float $price, ?string $recorded_at = null, ?float $regular_price = null, ?float $sale_price = null ): bool {
		global $wpdb;

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return false;
		}

		$table_name = Price_History_Table::get_table_name();

		$result = $wpdb->insert(
			$table_name,
			array(
				'product_id'    => $product_id,
				'sku'           => $product->get_sku(),
				'price'         => $price,
				'regular_price' => $regular_price,
				'sale_price'    => $sale_price,
				'recorded_at'   => $recorded_at ?: current_time( 'mysql' ),
			),
			array( '%d', '%s', '%f', '%f', '%f', '%s' )
		);

		return false !== $result;
	}
}
