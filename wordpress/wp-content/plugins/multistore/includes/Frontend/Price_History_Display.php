<?php
/**
 * Price History Frontend Display
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Frontend;

use MultiStore\Plugin\WooCommerce\Price_History;

/**
 * Class Price_History_Display
 *
 * Handles frontend display of lowest price information (Omnibus directive)
 *
 * @since 1.0.0
 */
class Price_History_Display {

	/**
	 * Price History instance
	 *
	 * @since 1.0.0
	 * @var Price_History
	 */
	private $price_history;

	/**
	 * Auto append Price History infor to product summary
	 *
	 * @var boolean
	 */
	private $auto_append = false;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->price_history = new Price_History();

		// Display lowest price info on single product page.
		if ( $this->auto_append ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'display_lowest_price_info' ), 11 );
		}

		// Add shortcode for manual placement.
		add_shortcode( 'multistore_lowest_price', array( $this, 'lowest_price_shortcode' ) );
	}

	/**
	 * Display lowest price information
	 *
	 * @since 1.0.0
	 */
	public function display_lowest_price_info(): void {
		global $product;

		if ( ! $product ) {
			return;
		}

		$product_id   = $product->get_id();
		$current_price = $product->get_price();

		// Skip if no current price.
		if ( empty( $current_price ) || $current_price <= 0 ) {
			return;
		}

		$lowest_price_data = $this->price_history->get_lowest_price( $product_id );

		// Skip if no price history or if we don't have enough data.
		if ( ! $lowest_price_data ) {
			return;
		}

		// Allow filtering whether to display.
		if ( ! apply_filters( 'multistore_show_lowest_price_info', true, $product_id, $lowest_price_data ) ) {
			return;
		}

		$this->render_lowest_price_info( $product, $lowest_price_data );
	}

	/**
	 * Render lowest price information HTML
	 *
	 * @since 1.0.0
	 * @param \WC_Product $product           Product object.
	 * @param array       $lowest_price_data Lowest price data.
	 */
	private function render_lowest_price_info( \WC_Product $product, array $lowest_price_data ): void {
		$lowest_price = (float) $lowest_price_data['price'];
		$recorded_at  = $lowest_price_data['recorded_at'];

		// Format the price.
		$formatted_price = wc_price( $lowest_price );

		// Calculate days ago.
		$days_ago = $this->get_days_ago( $recorded_at );

		/**
		 * Filter: Customize the lowest price message
		 *
		 * @since 1.0.0
		 * @param string      $message           Default message.
		 * @param \WC_Product $product           Product object.
		 * @param array       $lowest_price_data Lowest price data.
		 */
		$message = apply_filters(
			'multistore_lowest_price_message',
			sprintf(
				/* translators: 1: formatted price, 2: number of days */
				__( 'Lowest price in the last 30 days: %1$s (%2$d days ago)', 'multistore' ),
				$formatted_price,
				$days_ago
			),
			$product,
			$lowest_price_data
		);

		?>
		<div class="multistore-lowest-price-info">
			<?php
			/**
			 * Hook: Before lowest price info display
			 *
			 * @since 1.0.0
			 * @param \WC_Product $product           Product object.
			 * @param array       $lowest_price_data Lowest price data.
			 */
			do_action( 'multistore_before_lowest_price_info', $product, $lowest_price_data );
			?>

			<p class="multistore-lowest-price-message">
				<?php echo wp_kses_post( $message ); ?>
			</p>

			<?php
			/**
			 * Hook: After lowest price info display
			 *
			 * @since 1.0.0
			 * @param \WC_Product $product           Product object.
			 * @param array       $lowest_price_data Lowest price data.
			 */
			do_action( 'multistore_after_lowest_price_info', $product, $lowest_price_data );
			?>
		</div>
		<?php
	}

	/**
	 * Calculate days ago from a date
	 *
	 * @since 1.0.0
	 * @param string $date Date string.
	 * @return int Number of days ago.
	 */
	private function get_days_ago( string $date ): int {
		$recorded_timestamp = strtotime( $date );
		$current_timestamp  = current_time( 'timestamp' );
		$diff               = $current_timestamp - $recorded_timestamp;

		return (int) floor( $diff / DAY_IN_SECONDS );
	}

	/**
	 * Shortcode for displaying lowest price
	 *
	 * Usage: [multistore_lowest_price] or [multistore_lowest_price id="123"]
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered content.
	 */
	public function lowest_price_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			array(
				'id'     => get_the_ID(),
				'format' => 'full', // 'full', 'price', 'date'.
			),
			$atts,
			'multistore_lowest_price'
		);

		$product_id = (int) $atts['id'];

		if ( ! $product_id ) {
			return '';
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return '';
		}

		$lowest_price_data = $this->price_history->get_lowest_price( $product_id );

		if ( ! $lowest_price_data ) {
			return '';
		}

		$lowest_price = (float) $lowest_price_data['price'];
		$recorded_at  = $lowest_price_data['recorded_at'];

		switch ( $atts['format'] ) {
			case 'price':
				return wc_price( $lowest_price );

			case 'date':
				return wp_date( get_option( 'date_format' ), strtotime( $recorded_at ) );

			case 'full':
			default:
				$days_ago = $this->get_days_ago( $recorded_at );
				return sprintf(
					/* translators: 1: formatted price, 2: number of days */
					__( 'Lowest price in the last 30 days: %1$s (%2$d days ago)', 'multistore' ),
					wc_price( $lowest_price ),
					$days_ago
				);
		}
	}

	/**
	 * Get lowest price data for a product (for use in templates)
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @return array|null Lowest price data or null.
	 */
	public static function get_lowest_price_data( int $product_id ): ?array {
		$price_history = new Price_History();
		return $price_history->get_lowest_price( $product_id );
	}

	/**
	 * Get formatted lowest price string (for use in templates)
	 *
	 * @since 1.0.0
	 * @param int $product_id Product ID.
	 * @return string Formatted price or empty string.
	 */
	public static function get_formatted_lowest_price( int $product_id ): string {
		$price_history     = new Price_History();
		$lowest_price_data = $price_history->get_lowest_price( $product_id );

		if ( ! $lowest_price_data ) {
			return '';
		}

		return wc_price( (float) $lowest_price_data['price'] );
	}
}
