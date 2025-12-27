<?php
/**
 * Related Products Slider Renderer
 *
 * Renders query loop as Splide slider for related products
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Frontend;

/**
 * Class Related_Products_Slider_Renderer
 *
 * Customizes rendering of query loop for related products slider
 *
 * @since 1.0.0
 */
class Related_Products_Slider_Renderer {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'render_block', array( $this, 'render_slider' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Render query loop as slider
	 *
	 * @since 1.0.0
	 * @param string $block_content Block content.
	 * @param array  $block Block data.
	 * @return string Modified block content.
	 */
	public function render_slider( string $block_content, array $block ): string {
		// Check if this is core/query block with our namespace.
		if ( 'core/query' !== $block['blockName'] ) {
			return $block_content;
		}

		if ( ! isset( $block['attrs']['namespace'] ) || 'multistore/related-products-slider' !== $block['attrs']['namespace'] ) {
			return $block_content;
		}

		// Get slider configuration from block attributes.
		$slider_config = $this->build_slider_config( $block['attrs'] );

		// Extract inner content (query loop results).
		$inner_content = $this->extract_inner_content( $block_content );

		// Build slider HTML.
		$slider_html = $this->build_slider_html( $inner_content, $slider_config, $block['attrs'] );

		return $slider_html;
	}

	/**
	 * Build slider configuration from block attributes
	 *
	 * @since 1.0.0
	 * @param array $attributes Block attributes.
	 * @return array Splide configuration.
	 */
	private function build_slider_config( array $attributes ): array {
		$desktop = isset( $attributes['sliderDesktop'] ) ? $attributes['sliderDesktop'] : array();
		$tablet  = isset( $attributes['sliderTablet'] ) ? $attributes['sliderTablet'] : array();
		$mobile  = isset( $attributes['sliderMobile'] ) ? $attributes['sliderMobile'] : array();

		// Default desktop settings.
		$desktop_defaults = array(
			'type'       => 'slide',
			'perPage'    => 4,
			'gap'        => 20,
			'arrows'     => true,
			'pagination' => true,
			'autoplay'   => false,
			'speed'      => 400,
			'rewind'     => true,
		);

		$desktop = wp_parse_args( $desktop, $desktop_defaults );

		// Build config.
		$config = array(
			'type'       => $desktop['type'],
			'perPage'    => absint( $desktop['perPage'] ),
			'gap'        => absint( $desktop['gap'] ),
			'speed'      => absint( $desktop['speed'] ),
			'rewind'     => (bool) $desktop['rewind'],
			'autoplay'   => false,
			'arrows'     => false, // Handled in HTML.
			'pagination' => false, // Handled in HTML.
		);

		// Add autoplay if enabled.
		if ( ! empty( $desktop['autoplay'] ) ) {
			$config['autoplay'] = true;
			$config['interval'] = isset( $desktop['interval'] ) ? absint( $desktop['interval'] ) : 5000;
		}

		// Build breakpoints.
		$breakpoints = array();

		// Tablet breakpoint (768px).
		if ( ! empty( $tablet ) ) {
			$tablet_config = array();
			if ( isset( $tablet['perPage'] ) ) {
				$tablet_config['perPage'] = absint( $tablet['perPage'] );
			}
			if ( isset( $tablet['gap'] ) ) {
				$tablet_config['gap'] = absint( $tablet['gap'] );
			}
			if ( ! empty( $tablet_config ) ) {
				$breakpoints[768] = $tablet_config;
			}
		}

		// Mobile breakpoint (567px).
		if ( ! empty( $mobile ) ) {
			$mobile_config = array();
			if ( isset( $mobile['perPage'] ) ) {
				$mobile_config['perPage'] = absint( $mobile['perPage'] );
			}
			if ( isset( $mobile['gap'] ) ) {
				$mobile_config['gap'] = absint( $mobile['gap'] );
			}
			if ( ! empty( $mobile_config ) ) {
				$breakpoints[567] = $mobile_config;
			}
		}

		if ( ! empty( $breakpoints ) ) {
			$config['breakpoints'] = $breakpoints;
		}

		return $config;
	}

	/**
	 * Extract inner content from query block
	 *
	 * @since 1.0.0
	 * @param string $block_content Block content.
	 * @return string Inner content.
	 */
	private function extract_inner_content( string $block_content ): string {
		// Extract content inside wp-block-post-template.
		if ( preg_match( '/<ul[^>]*class="[^"]*wp-block-post-template[^"]*"[^>]*>(.*?)<\/ul>/s', $block_content, $matches ) ) {
			return $matches[1];
		}

		// Fallback: return original content.
		return $block_content;
	}

	/**
	 * Build slider HTML
	 *
	 * @since 1.0.0
	 * @param string $inner_content Inner content (slides).
	 * @param array  $config Splide configuration.
	 * @param array  $attributes Block attributes.
	 * @return string Slider HTML.
	 */
	private function build_slider_html( string $inner_content, array $config, array $attributes ): string {
		$desktop       = isset( $attributes['sliderDesktop'] ) ? $attributes['sliderDesktop'] : array();
		$show_arrows   = isset( $desktop['arrows'] ) ? (bool) $desktop['arrows'] : true;
		$show_pagination = isset( $desktop['pagination'] ) ? (bool) $desktop['pagination'] : true;

		$config_json = wp_json_encode( $config );

		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'multistore-block-related-products-slider',
			)
		);

		ob_start();
		?>
		<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
			<div class="splide" data-splide='<?php echo esc_attr( $config_json ); ?>'>
				<?php if ( $show_arrows ) : ?>
					<div class="splide__arrows">
						<button class="splide__arrow splide__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Previous slide', 'multistore' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40" aria-hidden="true">
								<path d="m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z"></path>
							</svg>
						</button>
						<button class="splide__arrow splide__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Next slide', 'multistore' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" width="40" height="40" aria-hidden="true">
								<path d="m15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4 14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z"></path>
							</svg>
						</button>
					</div>
				<?php endif; ?>

				<?php if ( $show_pagination ) : ?>
					<ul class="splide__pagination" role="tablist"></ul>
				<?php endif; ?>

				<div class="splide__track">
					<ul class="splide__list">
						<?php
						$li_class_list = preg_match( '/<li[^>]*class="([^"]*wp-block-post[^"]*)"[^>]*>(.*?)<\/li>/s', $inner_content, $matches ) ? $matches[1] : '';
						$wrapped_content = $inner_content;
						if ( $li_class_list ) {
							$wrapped_content = preg_replace( '/<li[^>]*class="([^"]*wp-block-post[^"]*)"[^>]*>/s', '<li class="$1 splide__slide">', $inner_content );
						}
						echo $wrapped_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</ul>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Enqueue assets for slider
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets(): void {
		// Check if we have our slider on the page.
		if ( ! $this->has_related_products_slider() ) {
			return;
		}

		// Enqueue Splide JS (if not already enqueued by slider block).
		if ( ! wp_script_is( 'multistore-related-products-slider-view-script', 'enqueued' ) ) {
			$asset_file = include MULTISTORE_PLUGIN_DIR . 'build/related-products-slider-controls/view.asset.php';

			wp_enqueue_script(
				'multistore-related-products-slider-view-script',
				MULTISTORE_PLUGIN_URL . 'build/related-products-slider-controls/view.js',
				$asset_file['dependencies'],
				$asset_file['version'],
				true
			);
		}

		// Enqueue slider styles.
		wp_enqueue_style(
			'multistore-related-products-slider-style',
			MULTISTORE_PLUGIN_URL . 'build/related-products-slider-controls/style-index.css',
			array(),
			MULTISTORE_PLUGIN_VERSION
		);
	}

	/**
	 * Check if page has related products slider
	 *
	 * @since 1.0.0
	 * @return bool True if has slider.
	 */
	private function has_related_products_slider(): bool {
		global $post;

		if ( ! $post ) {
			return false;
		}

		// Check if content has our slider block.
		return has_block( 'core/query', $post ) && str_contains( $post->post_content, 'multistore/related-products-slider' );
	}
}
