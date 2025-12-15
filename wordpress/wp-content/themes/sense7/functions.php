<?php
/**
 * Sense7 Block Theme Functions
 *
 * @package Sense7\Theme
 */

namespace Sense7\Theme;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Theme constants.
define( 'SENSE7_THEME_VERSION', '1.0.0' );
define( 'SENSE7_THEME_DIR', get_template_directory() );
define( 'SENSE7_THEME_URL', get_template_directory_uri() );

// Autoloader for theme classes.
if ( file_exists( SENSE7_THEME_DIR . '/includes' ) ) {
	spl_autoload_register(
		function ( $class ) {
			// Check if the class belongs to our namespace.
			$prefix   = 'Sense7\\Theme\\';
			$base_dir = SENSE7_THEME_DIR . '/includes/';

			// Does the class use the namespace prefix?
			$len = strlen( $prefix );
			if ( 0 !== strncmp( $prefix, $class, $len ) ) {
				return;
			}

			// Get the relative class name.
			$relative_class = substr( $class, $len );

			// Replace namespace separators with directory separators.
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// If the file exists, require it.
			if ( file_exists( $file ) ) {
				require $file;
			}
		}
	);
}

/**
 * Main Theme class
 *
 * @since 1.0.0
 */
class Theme {

	/**
	 * Theme instance
	 *
	 * @since 1.0.0
	 * @var Theme
	 */
	private static $instance = null;

	/**
	 * Get theme instance
	 *
	 * @since 1.0.0
	 * @return Theme
	 */
	public static function get_instance(): Theme {
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
	 * Initialize theme
	 *
	 * @since 1.0.0
	 */
	private function init(): void {
		// Theme setup.
		add_action( 'after_setup_theme', array( $this, 'setup' ) );

		// Enqueue assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Block editor assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );

		// Initialize theme components.
		$this->initialize_components();

		add_filter( 'upload_mimes', array( $this, 'cc_mime_types' ) );
	}

	/**
	 * Add SVG support for upload.
	 *
	 * @param array $mimes Mimes.
	 */
	public function cc_mime_types( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';

		return $mimes;
	}

	/**
	 * Theme setup
	 *
	 * @since 1.0.0
	 */
	public function setup(): void {
		// Load text domain.
		load_theme_textdomain( 'sense7', SENSE7_THEME_DIR . '/languages' );

		// Add theme support for block templates.
		add_theme_support( 'block-templates' );
		add_theme_support( 'block-template-parts' );

		// Add editor styles.
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor-style.css' );

		// Add support for responsive embeds.
		add_theme_support( 'responsive-embeds' );

		// Add support for experimental link color.
		add_theme_support( 'experimental-link-color' );

		// Add support for appearance tools.
		add_theme_support( 'appearance-tools' );

		// Add support for wide and full alignments.
		add_theme_support( 'align-wide' );
	}

	/**
	 * Enqueue theme assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets(): void {
		// Enqueue main stylesheet.
		wp_enqueue_style(
			'sense7-style',
			get_stylesheet_uri(),
			array(),
			SENSE7_THEME_VERSION
		);

		// Enqueue additional CSS if needed.
		if ( file_exists( SENSE7_THEME_DIR . '/assets/css/main.css' ) ) {
			wp_enqueue_style(
				'sense7-main',
				SENSE7_THEME_URL . '/assets/css/main.css',
				array( 'sense7-style' ),
				SENSE7_THEME_VERSION
			);
		}

		// Enqueue scripts.
		if ( file_exists( SENSE7_THEME_DIR . '/assets/js/main.js' ) ) {
			wp_enqueue_script(
				'sense7-scripts',
				SENSE7_THEME_URL . '/assets/js/main.js',
				array(),
				SENSE7_THEME_VERSION,
				true
			);

			// Localize script.
			wp_localize_script(
				'sense7-scripts',
				'sense7Theme',
				array(
					'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'sense7_theme_nonce' ),
					'themeUrl' => SENSE7_THEME_URL,
				)
			);
		}
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_editor_assets(): void {
		// Enqueue editor JavaScript if needed.
		if ( file_exists( SENSE7_THEME_DIR . '/assets/js/editor.js' ) ) {
			wp_enqueue_script(
				'sense7-editor-scripts',
				SENSE7_THEME_URL . '/assets/js/editor.js',
				array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ),
				SENSE7_THEME_VERSION,
				true
			);
		}
	}

	/**
	 * Initialize theme components
	 *
	 * @since 1.0.0
	 */
	private function initialize_components(): void {
		// Initialize your custom components here.
		// Example: if ( class_exists( 'Sense7\Theme\Blocks\Custom_Block' ) ) {
		//     new Blocks\Custom_Block();
		// }

		register_block_style(
			'multistore/product',
			array(
				'name'  => 'card',
				'label' => __( 'Card', 'multistore' ),
			)
		);
	}
}

// Initialize theme.
Theme::get_instance();
