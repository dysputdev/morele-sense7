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

		// Activation and deactivation hooks.
		register_activation_hook( MULTISTORE_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( MULTISTORE_PLUGIN_FILE, array( $this, 'deactivate' ) );
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

		$database_dir = MULTISTORE_PLUGIN_DIR . '/Database';
		$files        = glob( $database_dir . '/*.php' );
		foreach ( $files as $file ) {
			$filename = basename( $file, '.php' );

			// Pomiń interfejs.
			if ( 'Table_Interface' === $filename ) {
				continue;
			}

			$class_name = "Database\\{$filename}";
			if ( class_exists( $class_name ) ) {
				$reflection = new \ReflectionClass( $class_name );
				$reflection->maybe_create_table();
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

		// Initialize price history manager.
		new WooCommerce\Price_History();

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
	 * Initialize admin components
	 *
	 * @since 1.0.0
	 */
	public function initialize_admin_components(): void {
		new Admin\Price_History_Tools();
	}

	/**
	 * Initialize frontend components
	 *
	 * @since 1.0.0
	 */
	public function initialize_frontend_components(): void {
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
}

// Initialize plugin.
Plugin::get_instance();
