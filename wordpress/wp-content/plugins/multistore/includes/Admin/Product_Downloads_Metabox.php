<?php
/**
 * Product Downloads Metabox
 *
 * Handles metabox for adding downloadable files to WooCommerce products
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Admin;

/**
 * Class Product_Downloads_Metabox
 *
 * Manages product downloads metabox in WooCommerce admin
 *
 * @since 1.0.0
 */
class Product_Downloads_Metabox {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add metabox to product edit screen.
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );

		// Save metabox data.
		add_action( 'save_post_product', array( $this, 'save_metabox' ), 10, 2 );

		// Enqueue admin scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add metabox to product edit screen
	 *
	 * @since 1.0.0
	 */
	public function add_metabox(): void {
		add_meta_box(
			'multistore_product_downloads',
			__( 'Do pobrania', 'multistore' ),
			array( $this, 'render_metabox' ),
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_scripts( string $hook ): void {
		// Only load on product edit screen.
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		global $post;
		if ( ! $post || 'product' !== $post->post_type ) {
			return;
		}

		// Enqueue media uploader.
		wp_enqueue_media();

		// Enqueue styles.
		wp_enqueue_style(
			'multistore-product-downloads',
			MULTISTORE_PLUGIN_URL . 'assets/css/admin/product-downloads.css',
			array(),
			MULTISTORE_PLUGIN_VERSION
		);

		// Enqueue scripts.
		wp_enqueue_script(
			'multistore-product-downloads',
			MULTISTORE_PLUGIN_URL . 'assets/js/admin/product-downloads.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			MULTISTORE_PLUGIN_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'multistore-product-downloads',
			'multistoreProductDownloads',
			array(
				'selectFilesText'  => __( 'Wybierz pliki', 'multistore' ),
				'addFilesText'     => __( 'Dodaj pliki', 'multistore' ),
				'removeText'       => __( 'Usuń', 'multistore' ),
				'noFilesText'      => __( 'Nie dodano żadnych plików', 'multistore' ),
			)
		);
	}

	/**
	 * Render metabox content
	 *
	 * @since 1.0.0
	 * @param \WP_Post $post Post object.
	 */
	public function render_metabox( \WP_Post $post ): void {
		// Add nonce for verification.
		wp_nonce_field( 'multistore_product_downloads', 'multistore_product_downloads_nonce' );

		// Get current downloads.
		$downloads = get_post_meta( $post->ID, '_multistore_product_downloads', true );
		if ( ! is_array( $downloads ) ) {
			$downloads = array();
		}

		?>
		<div class="multistore-product-downloads-metabox">
			<div class="multistore-downloads-list">
				<?php
				if ( ! empty( $downloads ) ) {
					foreach ( $downloads as $index => $attachment_id ) {
						$this->render_file_item( $index, $attachment_id );
					}
				} else {
					?>
					<p class="multistore-no-files"><?php esc_html_e( 'Nie dodano żadnych plików', 'multistore' ); ?></p>
					<?php
				}
				?>
			</div>

			<div class="multistore-add-files">
				<button type="button" class="button button-primary multistore-add-files-button">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Dodaj pliki', 'multistore' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render single file item
	 *
	 * @since 1.0.0
	 * @param int $index File index.
	 * @param int $attachment_id Attachment ID.
	 */
	private function render_file_item( int $index, int $attachment_id ): void {
		$file_url  = wp_get_attachment_url( $attachment_id );
		$file_name = basename( get_attached_file( $attachment_id ) );
		$file_type = wp_check_filetype( $file_url );
		$icon_url  = wp_mime_type_icon( $attachment_id );

		?>
		<div class="multistore-file-item" data-index="<?php echo esc_attr( $index ); ?>">
			<input type="hidden" name="multistore_product_downloads[]" value="<?php echo esc_attr( $attachment_id ); ?>">

			<div class="multistore-file-item-icon">
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="">
			</div>

			<div class="multistore-file-item-details">
				<div class="multistore-file-item-name">
					<?php echo esc_html( $file_name ); ?>
				</div>
				<div class="multistore-file-item-meta">
					<?php
					printf(
						/* translators: %s: file type */
						esc_html__( 'Typ: %s', 'multistore' ),
						esc_html( strtoupper( $file_type['ext'] ) )
					);
					?>
				</div>
			</div>

			<div class="multistore-file-item-actions">
				<button type="button" class="button multistore-remove-file" title="<?php esc_attr_e( 'Usuń plik', 'multistore' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Save metabox data
	 *
	 * @since 1.0.0
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_metabox( int $post_id, \WP_Post $post ): void {
		// Verify nonce.
		if ( ! isset( $_POST['multistore_product_downloads_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['multistore_product_downloads_nonce'] ) ), 'multistore_product_downloads' ) ) {
			return;
		}

		// Check if not autosaving.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check user permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save downloads.
		$downloads = array();
		if ( isset( $_POST['multistore_product_downloads'] ) && is_array( $_POST['multistore_product_downloads'] ) ) {
			foreach ( $_POST['multistore_product_downloads'] as $attachment_id ) {
				$attachment_id = absint( $attachment_id );
				if ( $attachment_id > 0 ) {
					$downloads[] = $attachment_id;
				}
			}
		}

		update_post_meta( $post_id, '_multistore_product_downloads', $downloads );
	}
}
