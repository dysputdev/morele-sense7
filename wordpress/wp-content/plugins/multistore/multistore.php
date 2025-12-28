<?php
/**
 * Plugin Name: MultiStore
 * Plugin URI: https://multistore.pl
 * Description: Custom plugin for MultiStore website functionality
 * Version: 1.0.0
 * Author: MultiStore
 * Author URI: https://multistore.pl
 * Text Domain: multistore
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'MULTISTORE_PLUGIN_VERSION', '1.0.0' );
define( 'MULTISTORE_PLUGIN_FILE', __FILE__ );
define( 'MULTISTORE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MULTISTORE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MULTISTORE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader with scoped dependencies.
// Priority: scoped vendor (production) > regular vendor (development).
if ( file_exists( MULTISTORE_PLUGIN_DIR . 'vendor-scoped/autoload.php' ) ) {
	require_once MULTISTORE_PLUGIN_DIR . 'vendor-scoped/autoload.php';
} elseif ( file_exists( MULTISTORE_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once MULTISTORE_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Main plugin class
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Plugin instance
	 *
	 * @since 1.0.0
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 *
	 * @since 1.0.0
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize plugin
	 *
	 * @since 1.0.0
	 */
	private function init(): void {

		// Load text domain.
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

		add_action( 'init', array( $this, 'initialize_database' ) );

		// Initialize plugin components.
		add_action( 'init', array( $this, 'initialize_components' ) );

		// Enqueue block editor assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );

		// Activation and deactivation hooks.
		register_activation_hook( MULTISTORE_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( MULTISTORE_PLUGIN_FILE, array( $this, 'deactivate' ) );

		// Register WP-CLI commands.
		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			add_action( 'cli_init', array( $this, 'register_cli_commands' ) );
		}
	}

	/**
	 * Load plugin text domain
	 *
	 * @since 1.0.0
	 */
	public function load_text_domain(): void {
		load_plugin_textdomain(
			'multistore',
			false,
			dirname( MULTISTORE_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Initialize plugin database tables.
	 *
	 * @since 1.0.0
	 */
	public function initialize_database(): void {
		// Initialize database tables.

		$database_dir = MULTISTORE_PLUGIN_DIR . 'includes/Database';
		$files        = glob( $database_dir . '/*.php' );
		foreach ( $files as $file ) {
			$filename = basename( $file, '.php' );

			// Pomiń interfejs.
			if ( 'Table_Interface' === $filename ) {
				continue;
			}

			$class_name = "MultiStore\\Plugin\\Database\\{$filename}";
			if ( class_exists( $class_name ) ) {
				$instance = new $class_name();
				if ( method_exists( $instance, 'maybe_create_table' ) ) {
					$instance->maybe_create_table();
				}
			}
		}
	}

	/**
	 * Initialize plugin components
	 *
	 * @since 1.0.0
	 */
	public function initialize_components(): void {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}

		// enable gutenberg for woocommerce.
		add_filter(
			'use_block_editor_for_post_type',
			function( $can_edit, $post_type ) {
				if ( 'product' === $post_type ) {
					$can_edit = true;
				}
				return $can_edit;
			},
			10,
			2
		);

		// enable taxonomy fields for woocommerce with gutenberg on.
		add_filter( 'woocommerce_taxonomy_args_product_cat', fn ( $args ) => ( $args + array( 'show_in_rest' => true ) ) );
		add_filter( 'woocommerce_taxonomy_args_product_tag', fn ( $args ) => ( $args + array( 'show_in_rest' => true ) ) );

		// Initialize price history manager.
		new WooCommerce\Price_History();

		$this->initialize_blocks();

		// Initialize admin components.
		if ( is_admin() ) {
			$this->initialize_admin_components();
		}

		// Initialize frontend components.
		if ( ! is_admin() ) {
			$this->initialize_frontend_components();
		}
	}

	/**
	 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
	 * Behind the scenes, it also registers all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	public function initialize_blocks(): void {
		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
		 * based on the registered block metadata.
		 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
		 *
		 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
		 */
		if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
			wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );

			// load each functions.php file from the build directory.
			foreach ( glob( __DIR__ . '/build/*/functions.php' ) as $file ) {
				require_once $file;
			}
			return;
		}

		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` file.
		 * Added to WordPress 6.7 to improve the performance of block type registration.
		 *
		 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
		 */
		if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
			wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		}
		/**
		 * Registers the block type(s) in the `blocks-manifest.php` file.
		 *
		 * @see https://developer.wordpress.org/reference/functions/register_block_type/
		 */
		$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
		foreach ( array_keys( $manifest_data ) as $block_type ) {
			// if functions.php exists include it.
			if ( file_exists( __DIR__ . "/build/{$block_type}/functions.php" ) ) {
				require_once __DIR__ . "/build/{$block_type}/functions.php";
			}

			register_block_type( __DIR__ . "/build/{$block_type}" );
		}
	}

	/**
	 * Initialize admin components
	 *
	 * @since 1.0.0
	 */
	public function initialize_admin_components(): void {
		new Admin\Price_History_Tools();
		new Admin\Product_Downloads_Metabox();		

		// Debug helper - uncomment to enable.
		// new Admin\Product_Relations\Debug();
	}

	/**
	 * Initialize frontend components
	 *
	 * @since 1.0.0
	 */
	public function initialize_frontend_components(): void {
		new Frontend\Price_History_Display();
		new Frontend\Related_Products_Query();
		new Frontend\Related_Products_Slider_Renderer();
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_editor_assets(): void {
		$asset_file = include MULTISTORE_PLUGIN_DIR . 'build/editor.asset.php';

		wp_enqueue_script(
			'multistore-editor-script',
			MULTISTORE_PLUGIN_URL . 'build/editor.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);
	}

	/**
	 * Display WooCommerce missing notice
	 *
	 * @since 1.0.0
	 */
	public function woocommerce_missing_notice(): void {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				esc_html_e(
					'MultiStore Plugin requires WooCommerce to be installed and activated.',
					'multistore'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Plugin activation
	 *
	 * @since 1.0.0
	 */
	public function activate(): void {
		// Create database tables on activation.
		if ( class_exists( 'MultiStore\Plugin\Database\Price_History_Table' ) ) {
			$table = new Database\Price_History_Table();
			$table->create_table();
		}

		// Schedule price history cleanup cron.
		if ( ! wp_next_scheduled( 'multistore_cleanup_price_history' ) ) {
			wp_schedule_event( time(), 'daily', 'multistore_cleanup_price_history' );
		}

		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation
	 *
	 * @since 1.0.0
	 */
	public function deactivate(): void {
		// Clear scheduled cron events.
		$timestamp = wp_next_scheduled( 'multistore_cleanup_price_history' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'multistore_cleanup_price_history' );
		}

		flush_rewrite_rules();
	}

	/**
	 * Register WP-CLI commands
	 *
	 * @since 1.0.0
	 */
	public function register_cli_commands(): void {
		\WP_CLI::add_command( 'multistore product:import products', 'MultiStore\Plugin\CLI\Import_Products' );
		\WP_CLI::add_command( 'multistore product:import reviews', 'MultiStore\Plugin\CLI\Import_Reviews' );
		\WP_CLI::add_command( 'multistore product:import images', 'MultiStore\Plugin\CLI\Import_Images' );
		\WP_CLI::add_command( 'multistore product:import attributes', 'MultiStore\Plugin\CLI\Import_Attributes' );
		\WP_CLI::add_command( 'multistore product:import galleries', 'MultiStore\Plugin\CLI\Import_Galleries' );
	}
}

// Initialize plugin.
Plugin::get_instance();
