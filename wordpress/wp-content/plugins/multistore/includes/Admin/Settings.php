<?php
/**
 * Admin Settings
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Admin;

/**
 * Class Settings
 *
 * Handles admin settings page
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Settings page slug
	 *
	 * @since 1.0.0
	 */
	const PAGE_SLUG = 'multistore-settings';

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @since 1.0.0
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'MultiStore Settings', 'multistore' ),
			__( 'MultiStore', 'multistore' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings
	 *
	 * @since 1.0.0
	 */
	public function register_settings(): void {
		register_setting(
			'multistore_settings',
			'multistore_options',
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		add_settings_section(
			'multistore_general_section',
			__( 'General Settings', 'multistore' ),
			array( $this, 'render_general_section' ),
			self::PAGE_SLUG
		);

		// Add settings fields here.
	}

	/**
	 * Render settings page
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'multistore_settings' );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Save Settings', 'multistore' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render general section description
	 *
	 * @since 1.0.0
	 */
	public function render_general_section(): void {
		echo '<p>' . esc_html__( 'Configure general plugin settings.', 'multistore' ) . '</p>';
	}

	/**
	 * Sanitize settings
	 *
	 * @since 1.0.0
	 * @param array $input Settings input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();

		// Add sanitization logic here.

		return $sanitized;
	}
}
