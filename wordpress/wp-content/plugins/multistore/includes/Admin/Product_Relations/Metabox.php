<?php
/**
 * Product Relations Metabox
 *
 * Handles metabox for managing product relations in groups
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Admin\Product_Relations;

use MultiStore\Plugin\Repository\Relations_Repository;

/**
 * Class Metabox
 *
 * Manages product relations metabox in WooCommerce admin
 *
 * @since 1.0.0
 */
class Metabox {

	/**
	 * Relations manager instance
	 *
	 * @since 1.0.0
	 * @var Relations_Repository
	 */
	private $relations_repository;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->relations_repository = new Relations_Repository();

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
			'multistore_product_relations',
			__( 'Relacje produktów', 'multistore' ),
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

		// Enqueue WordPress media uploader.
		wp_enqueue_media();

		// Enqueue styles.
		wp_enqueue_style(
			'multistore-product-relations',
			MULTISTORE_PLUGIN_URL . 'assets/css/admin/product-relations.css',
			array(),
			MULTISTORE_PLUGIN_VERSION
		);

		// Enqueue scripts.
		wp_enqueue_script(
			'multistore-product-relations',
			MULTISTORE_PLUGIN_URL . 'assets/js/admin/product-relations.js',
			array( 'jquery', 'jquery-ui-sortable', 'select2' ),
			MULTISTORE_PLUGIN_VERSION,
			true
		);

		// Enqueue Select2.
		wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );
		wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );

		// Localize script.
		wp_localize_script(
			'multistore-product-relations',
			'multistoreProductRelations',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'multistore_product_relations' ),
				'createGroupText' => __( 'Utwórz nową grupę', 'multistore' ),
				'selectGroupText' => __( 'Wybierz grupę', 'multistore' ),
				'addProductText'  => __( 'Dodaj produkt', 'multistore' ),
				'removeText'      => __( 'Usuń', 'multistore' ),
				'searchProducts'  => __( 'Szukaj produktów...', 'multistore' ),
				'noResultsText'   => __( 'Nie znaleziono produktów', 'multistore' ),
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
		wp_nonce_field( 'multistore_product_relations', 'multistore_product_relations_nonce' );

		// Get all groups.
		$all_groups = $this->relations_repository->get_all_groups();

		// Get product attributes.
		$attributes = $this->relations_repository->get_product_attributes();

		// Get current product relations.
		$relations = $this->relations_repository->get_product_relations( $post->ID );

		// Filter groups with relations for this product.
		$active_groups = array();
		foreach ( $all_groups as $group ) {
			if ( isset( $relations[ $group->id ] ) && ! empty( $relations[ $group->id ] ) ) {
				$active_groups[] = $group;
			}
		}

		?>
		<div class="multistore-product-relations-metabox">

			<!-- Active groups with relations -->
			<div class="multistore-active-groups-section">
				<?php if ( ! empty( $active_groups ) ) : ?>
					<?php foreach ( $active_groups as $group ) : ?>
						<div class="multistore-group-relations" data-group-id="<?php echo esc_attr( $group->id ); ?>">
							<div class="multistore-group-header">
								<h4>
									<?php echo esc_html( $group->name ); ?>
									<?php if ( $group->display_on_list ) : ?>
										<span class="multistore-group-badge"><?php esc_html_e( 'Wyświetlane na liście', 'multistore' ); ?></span>
									<?php endif; ?>
								</h4>
								<div class="multistore-group-actions">
									<button type="button"
										class="button button-secondary multistore-edit-group"
										data-group-id="<?php echo esc_attr( $group->id ); ?>"
										data-group-name="<?php echo esc_attr( $group->name ); ?>"
										data-attribute-id="<?php echo esc_attr( $group->attribute_id ?? '' ); ?>"
										data-display-on-list="<?php echo esc_attr( $group->display_on_list ); ?>"
										data-display-style-single="<?php echo esc_attr( $group->display_style_single ?? 'image_product' ); ?>"
										data-display-style-archive="<?php echo esc_attr( $group->display_style_archive ?? 'image_product' ); ?>"
										data-sort-order="<?php echo esc_attr( $group->sort_order ); ?>">
										<?php esc_html_e( 'Edytuj', 'multistore' ); ?>
									</button>
									<button type="button" class="button button-link-delete multistore-remove-group" data-group-id="<?php echo esc_attr( $group->id ); ?>">
										<?php esc_html_e( 'Usuń grupę', 'multistore' ); ?>
									</button>
								</div>
							</div>

							<div class="multistore-related-products-list" data-group-id="<?php echo esc_attr( $group->id ); ?>">
								<?php
								foreach ( $relations[ $group->id ] as $relation ) {
									$this->render_relation_item( $relation, $group->id );
								}
								?>
							</div>

							<div class="multistore-add-relation">
								<select class="multistore-product-search"
									data-group-id="<?php echo esc_attr( $group->id ); ?>"
									style="width: 100%;">
									<option value=""><?php esc_html_e( 'Szukaj produktów...', 'multistore' ); ?></option>
								</select>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<!-- Add group button -->
			<div class="multistore-add-group-section">
				<button type="button" class="button button-secondary multistore-add-group-button">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'Dodaj grupę', 'multistore' ); ?>
				</button>
			</div>

			<!-- Add group modal (hidden by default) -->
			<div class="multistore-add-group-modal" style="display: none;">
				<div class="multistore-modal-content">
					<h3><?php esc_html_e( 'Dodaj grupę relacji', 'multistore' ); ?></h3>

					<div class="multistore-tabs">
						<button type="button" class="multistore-tab-button active" data-tab="existing">
							<?php esc_html_e( 'Wybierz istniejącą', 'multistore' ); ?>
						</button>
						<button type="button" class="multistore-tab-button" data-tab="new">
							<?php esc_html_e( 'Utwórz nową', 'multistore' ); ?>
						</button>
					</div>

					<!-- Existing group tab -->
					<div class="multistore-tab-content" data-tab="existing">
						<div class="multistore-form-row">
							<label for="multistore_select_group">
								<?php esc_html_e( 'Wybierz grupę', 'multistore' ); ?>
							</label>
							<select id="multistore_select_group" class="regular-text">
								<option value=""><?php esc_html_e( '-- Wybierz grupę --', 'multistore' ); ?></option>
								<?php foreach ( $all_groups as $group ) : ?>
									<?php if ( ! in_array( $group, $active_groups, true ) ) : ?>
										<option value="<?php echo esc_attr( $group->id ); ?>"
											data-name="<?php echo esc_attr( $group->name ); ?>"
											data-display="<?php echo esc_attr( $group->display_on_list ); ?>">
											<?php echo esc_html( $group->name ); ?>
										</option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="multistore-modal-actions">
							<button type="button" class="button button-primary multistore-select-existing-group">
								<?php esc_html_e( 'Dodaj', 'multistore' ); ?>
							</button>
							<button type="button" class="button multistore-cancel-add-group">
								<?php esc_html_e( 'Anuluj', 'multistore' ); ?>
							</button>
						</div>
					</div>

					<!-- New group tab -->
					<div class="multistore-tab-content" data-tab="new" style="display: none;">
						<div class="multistore-form-row">
							<label for="multistore_new_group_name">
								<?php esc_html_e( 'Nazwa grupy', 'multistore' ); ?>
							</label>
							<input type="text"
								id="multistore_new_group_name"
								class="regular-text"
								placeholder="<?php esc_attr_e( 'np. Kolor, Rozmiar, etc.', 'multistore' ); ?>"
							>
						</div>

						<div class="multistore-form-row">
							<label for="multistore_new_group_attribute">
								<?php esc_html_e( 'Atrybut produktu', 'multistore' ); ?>
							</label>
							<select id="multistore_new_group_attribute" class="regular-text">
								<option value=""><?php esc_html_e( '-- Wybierz atrybut --', 'multistore' ); ?></option>
								<?php foreach ( $attributes as $attribute ) : ?>
									<option value="<?php echo esc_attr( $attribute->attribute_id ); ?>">
										<?php echo esc_html( $attribute->attribute_label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="multistore-form-row">
							<label>
								<input type="checkbox"
									id="multistore_new_group_display_on_list"
									value="1"
								>
								<?php esc_html_e( 'Wyświetlaj na liście produktów', 'multistore' ); ?>
							</label>
						</div>

						<div class="multistore-form-row">
							<label for="multistore_new_group_display_style_single">
								<?php esc_html_e( 'Styl wyświetlania - szczegóły produktu', 'multistore' ); ?>
							</label>
							<select id="multistore_new_group_display_style_single" class="regular-text">
								<option value="image_product"><?php esc_html_e( 'Zdjęcie produktu', 'multistore' ); ?></option>
								<option value="image_custom"><?php esc_html_e( 'Własna grafika', 'multistore' ); ?></option>
								<option value="label_only"><?php esc_html_e( 'Tylko etykieta', 'multistore' ); ?></option>
							</select>
						</div>

						<div class="multistore-form-row">
							<label for="multistore_new_group_display_style_archive">
								<?php esc_html_e( 'Styl wyświetlania - lista produktów', 'multistore' ); ?>
							</label>
							<select id="multistore_new_group_display_style_archive" class="regular-text">
								<option value="image_product"><?php esc_html_e( 'Zdjęcie produktu', 'multistore' ); ?></option>
								<option value="image_custom"><?php esc_html_e( 'Własna grafika', 'multistore' ); ?></option>
								<option value="label_only"><?php esc_html_e( 'Tylko etykieta', 'multistore' ); ?></option>
							</select>
						</div>

						<div class="multistore-form-row">
							<label for="multistore_new_group_sort_order">
								<?php esc_html_e( 'Kolejność', 'multistore' ); ?>
							</label>
							<input type="number"
								id="multistore_new_group_sort_order"
								class="small-text"
								value="0"
								min="0"
							>
						</div>

						<div class="multistore-modal-actions">
							<button type="button" class="button button-primary multistore-create-group-button">
								<?php esc_html_e( 'Utwórz i dodaj', 'multistore' ); ?>
							</button>
							<button type="button" class="button multistore-cancel-add-group">
								<?php esc_html_e( 'Anuluj', 'multistore' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Edit group modal (hidden by default) -->
			<div class="multistore-edit-group-modal" style="display: none;">
				<div class="multistore-modal-content">
					<h3><?php esc_html_e( 'Edytuj ustawienia grupy', 'multistore' ); ?></h3>

					<div class="multistore-form-row">
						<label for="multistore_edit_group_name">
							<?php esc_html_e( 'Nazwa grupy', 'multistore' ); ?>
						</label>
						<input type="text"
							id="multistore_edit_group_name"
							class="regular-text"
							placeholder="<?php esc_attr_e( 'np. Kolor, Rozmiar, etc.', 'multistore' ); ?>"
						>
					</div>

					<div class="multistore-form-row">
						<label for="multistore_edit_group_attribute">
							<?php esc_html_e( 'Atrybut produktu', 'multistore' ); ?>
						</label>
						<select id="multistore_edit_group_attribute" class="regular-text">
							<option value=""><?php esc_html_e( '-- Wybierz atrybut --', 'multistore' ); ?></option>
							<?php foreach ( $attributes as $attribute ) : ?>
								<option value="<?php echo esc_attr( $attribute->attribute_id ); ?>">
									<?php echo esc_html( $attribute->attribute_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="multistore-form-row">
						<label>
							<input type="checkbox"
								id="multistore_edit_group_display_on_list"
								value="1"
							>
							<?php esc_html_e( 'Wyświetlaj na liście produktów', 'multistore' ); ?>
						</label>
					</div>

					<div class="multistore-form-row">
						<label for="multistore_edit_group_display_style_single">
							<?php esc_html_e( 'Styl wyświetlania - szczegóły produktu', 'multistore' ); ?>
						</label>
						<select id="multistore_edit_group_display_style_single" class="regular-text">
							<option value="image_product"><?php esc_html_e( 'Zdjęcie produktu', 'multistore' ); ?></option>
							<option value="image_custom"><?php esc_html_e( 'Własna grafika', 'multistore' ); ?></option>
							<option value="label_only"><?php esc_html_e( 'Tylko etykieta', 'multistore' ); ?></option>
						</select>
					</div>

					<div class="multistore-form-row">
						<label for="multistore_edit_group_display_style_archive">
							<?php esc_html_e( 'Styl wyświetlania - lista produktów', 'multistore' ); ?>
						</label>
						<select id="multistore_edit_group_display_style_archive" class="regular-text">
							<option value="image_product"><?php esc_html_e( 'Zdjęcie produktu', 'multistore' ); ?></option>
							<option value="image_custom"><?php esc_html_e( 'Własna grafika', 'multistore' ); ?></option>
							<option value="label_only"><?php esc_html_e( 'Tylko etykieta', 'multistore' ); ?></option>
						</select>
					</div>

					<div class="multistore-form-row">
						<label for="multistore_edit_group_sort_order">
							<?php esc_html_e( 'Kolejność', 'multistore' ); ?>
						</label>
						<input type="number"
							id="multistore_edit_group_sort_order"
							class="small-text"
							value="0"
							min="0"
						>
					</div>

					<input type="hidden" id="multistore_edit_group_id" value="">

					<div class="multistore-modal-actions">
						<button type="button" class="button button-primary multistore-save-group-button">
							<?php esc_html_e( 'Zapisz zmiany', 'multistore' ); ?>
						</button>
						<button type="button" class="button multistore-cancel-edit-group">
							<?php esc_html_e( 'Anuluj', 'multistore' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Hidden group template for dynamic adding -->
			<script type="text/template" id="multistore-group-template">
				<div class="multistore-group-relations" data-group-id="{{GROUP_ID}}">
					<div class="multistore-group-header">
						<h4>
							{{GROUP_NAME}}
							{{GROUP_BADGE}}
						</h4>
						<button type="button" class="button button-link-delete multistore-remove-group" data-group-id="{{GROUP_ID}}">
							<?php esc_html_e( 'Usuń grupę', 'multistore' ); ?>
						</button>
					</div>

					<div class="multistore-related-products-list" data-group-id="{{GROUP_ID}}">
					</div>

					<div class="multistore-add-relation">
						<select class="multistore-product-search"
							data-group-id="{{GROUP_ID}}"
							style="width: 100%;">
							<option value=""><?php esc_html_e( 'Szukaj produktów...', 'multistore' ); ?></option>
						</select>
					</div>
				</div>
			</script>
		</div>
		<?php
	}

	/**
	 * Render single relation item
	 *
	 * @since 1.0.0
	 * @param object $relation Relation data.
	 * @param int    $group_id Group ID.
	 */
	private function render_relation_item( object $relation, int $group_id ): void {
		$product = wc_get_product( $relation->related_product_id );
		if ( ! $product ) {
			return;
		}

		// Get settings.
		$settings         = $this->relations_repository->get_relation_settings( $relation->settings_id ?? 0 );
		$custom_image_id  = $settings['custom_image_id'] ?? 0;
		$custom_label     = $settings['custom_label'] ?? '';
		$label_source     = $settings['label_source'] ?? 'custom';

		$custom_image_url = '';
		if ( $custom_image_id ) {
			$custom_image_url = wp_get_attachment_image_url( $custom_image_id, 'thumbnail' );
		}

		// Get group info to know which attribute to use.
		$group = $this->relations_repository->get_group( $relation->group_id );
		$attribute_values = array();
		if ( $group && $group->attribute_id ) {
			$attribute_values = $this->relations_repository->get_product_attribute_values( $relation->related_product_id, $group->attribute_id );
		}

		?>
		<div class="multistore-relation-item" data-relation-id="<?php echo esc_attr( $relation->id ); ?>">
			<input type="hidden"
				name="multistore_relations[<?php echo esc_attr( $group_id ); ?>][<?php echo esc_attr( $relation->id ); ?>][product_id]"
				value="<?php echo esc_attr( $relation->related_product_id ); ?>"
			>
			<input type="hidden"
				name="multistore_relations[<?php echo esc_attr( $group_id ); ?>][<?php echo esc_attr( $relation->id ); ?>][settings_id]"
				value="<?php echo esc_attr( $relation->settings_id ?? '' ); ?>"
			>
			<input type="hidden"
				name="multistore_relations[<?php echo esc_attr( $group_id ); ?>][<?php echo esc_attr( $relation->id ); ?>][sort_order]"
				value="<?php echo esc_attr( $relation->sort_order ); ?>"
				class="multistore-sort-order"
			>

			<div class="multistore-relation-item-handle">
				<span class="dashicons dashicons-menu"></span>
			</div>

			<div class="multistore-relation-item-details">
				<div class="multistore-relation-item-header">
					<div class="multistore-relation-item-info">
						<div class="multistore-relation-item-name">
							<?php echo esc_html( $product->get_name() ); ?>
						</div>
						<div class="multistore-relation-item-meta">
							<?php
							printf(
								/* translators: %s: product ID */
								esc_html__( 'ID: %s', 'multistore' ),
								esc_html( $relation->related_product_id )
							);
							?>
						</div>
					</div>
					<span class="multistore-toggle-icon dashicons dashicons-arrow-down-alt2"></span>
				</div>

				<div class="multistore-relation-item-custom-fields" style="display: none;">
					<div class="multistore-relation-custom-field">
						<label><?php esc_html_e( 'Źródło etykiety:', 'multistore' ); ?></label>
						<div class="multistore-label-source-options">
							<label>
								<input type="radio"
									name="multistore_relations[<?php echo esc_attr( $group_id ); ?>][<?php echo esc_attr( $relation->id ); ?>][label_source]"
									value="custom"
									<?php checked( $label_source, 'custom' ); ?>
									class="multistore-label-source-radio"
								>
								<?php esc_html_e( 'Własna etykieta', 'multistore' ); ?>
							</label>
							<?php if ( ! empty( $attribute_values ) ) : ?>
								<label>
									<input type="radio"
										name="multistore_relations[<?php echo esc_attr( $group_id ); ?>][<?php echo esc_attr( $relation->id ); ?>][label_source]"
										value="attribute"
										<?php checked( $label_source, 'attribute' ); ?>
										class="multistore-label-source-radio"
									>
									<?php esc_html_e( 'Wartość atrybutu', 'multistore' ); ?>
								</label>
							<?php endif; ?>
						</div>
					</div>

					<div class="multistore-relation-custom-field multistore-custom-label-field" style="<?php echo 'custom' !== $label_source ? 'display: none;' : ''; ?>">
						<label><?php esc_html_e( 'Własna etykieta:', 'multistore' ); ?></label>
						<input type="text"
							name="multistore_relations[<?php echo esc_attr( $group_id ); ?>][<?php echo esc_attr( $relation->id ); ?>][custom_label]"
							value="<?php echo esc_attr( $custom_label ); ?>"
							class="regular-text"
							placeholder="<?php esc_attr_e( 'Opcjonalna własna nazwa', 'multistore' ); ?>"
						>
					</div>

					<?php if ( ! empty( $attribute_values ) ) : ?>
						<div class="multistore-relation-custom-field multistore-attribute-label-field" style="<?php echo 'attribute' !== $label_source ? 'display: none;' : ''; ?>">
							<label><?php esc_html_e( 'Wartość atrybutu:', 'multistore' ); ?></label>
							<div class="multistore-attribute-values">
								<?php echo esc_html( implode( ', ', $attribute_values ) ); ?>
							</div>
							<p class="description">
								<?php esc_html_e( 'Zostanie użyta wartość atrybutu przypisanego do grupy dla tego produktu.', 'multistore' ); ?>
							</p>
						</div>
					<?php endif; ?>

					<div class="multistore-relation-custom-field">
						<label><?php esc_html_e( 'Własna grafika:', 'multistore' ); ?></label>
						<div class="multistore-custom-image-field">
							<input type="hidden"
								name="multistore_relations[<?php echo esc_attr( $group_id ); ?>][<?php echo esc_attr( $relation->id ); ?>][custom_image_id]"
								value="<?php echo esc_attr( $custom_image_id ); ?>"
								class="multistore-custom-image-id"
							>
							<button type="button" class="button multistore-select-image">
								<?php esc_html_e( 'Wybierz obraz', 'multistore' ); ?>
							</button>
							<?php if ( $custom_image_url ) : ?>
								<div class="multistore-custom-image-preview">
									<img src="<?php echo esc_url( $custom_image_url ); ?>" alt="">
									<button type="button" class="button-link multistore-remove-image">
										<span class="dashicons dashicons-no-alt"></span>
									</button>
								</div>
							<?php else : ?>
								<div class="multistore-custom-image-preview" style="display: none;">
									<img src="" alt="">
									<button type="button" class="button-link multistore-remove-image">
										<span class="dashicons dashicons-no-alt"></span>
									</button>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="multistore-relation-item-actions">
				<button type="button"
					class="button multistore-remove-relation"
					title="<?php esc_attr_e( 'Usuń relację', 'multistore' ); ?>">
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
		if ( ! isset( $_POST['multistore_product_relations_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['multistore_product_relations_nonce'] ) ), 'multistore_product_relations' ) ) {
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

		// Get current product SKU.
		$product_sku = $this->relations_repository->get_product_sku( $post_id );

		if ( empty( $product_sku ) ) {
			// Product must have SKU for relations.
			return;
		}

		// Get current relations from database by SKU.
		$current_relations = $this->relations_repository->get_current_relations_by_sku( $product_sku );

		// Track which relations to keep.
		$keep_relation_ids = array();

		// Process submitted relations.
		if ( isset( $_POST['multistore_relations'] ) && is_array( $_POST['multistore_relations'] ) ) {
			foreach ( $_POST['multistore_relations'] as $group_id => $relations ) {
				$group_id = absint( $group_id );

				foreach ( $relations as $relation_id => $relation_data ) {
					$relation_id        = absint( $relation_id );
					$related_product_id = absint( $relation_data['product_id'] ?? 0 );
					$settings_id        = absint( $relation_data['settings_id'] ?? 0 );
					$sort_order         = absint( $relation_data['sort_order'] ?? 0 );
					$custom_label       = sanitize_text_field( $relation_data['custom_label'] ?? '' );
					$custom_image_id    = absint( $relation_data['custom_image_id'] ?? 0 );
					$label_source       = sanitize_text_field( $relation_data['label_source'] ?? 'custom' );

					if ( $related_product_id > 0 ) {
						// Get related product SKU.
						$related_product_sku = $this->relations_repository->get_product_sku( $related_product_id );

						// Skip if no SKU.
						if ( empty( $related_product_sku ) ) {
							continue;
						}

						// Prepare settings data.
						$settings_data = array(
							'custom_label'    => $custom_label,
							'custom_image_id' => $custom_image_id,
							'label_source'    => $label_source,
						);

						// Create or update settings.
						if ( $settings_id > 0 ) {
							// Update existing settings.
							$this->relations_repository->update_relation_settings( $settings_id, $settings_data );
						} else {
							// Create new settings.
							$settings_id = $this->relations_repository->create_relation_settings( $settings_data );
						}

						// Update existing or insert new.
						if ( $relation_id > 0 && isset( $current_relations[ $relation_id ] ) ) {
							// Update existing relation.
							$this->relations_repository->update_relation( $relation_id, $sort_order, $settings_id );
							$keep_relation_ids[] = $relation_id;
						} else {
							// Insert new relation (both directions) using SKU.
							$this->relations_repository->create_bidirectional_relation( $product_sku, $related_product_sku, $group_id, $settings_id, $sort_order );
						}
					}
				}
			}
		}

		// Remove relations that are not in the submitted data.
		$current_relation_ids = array_keys( $current_relations );
		$remove_relation_ids  = array_diff( $current_relation_ids, $keep_relation_ids );

		if ( ! empty( $remove_relation_ids ) ) {
			foreach ( $remove_relation_ids as $remove_id ) {
				$relation = $current_relations[ $remove_id ];
				// Remove both directions using SKU.
				$this->relations_repository->remove_bidirectional_relation( $product_sku, $relation->related_product_sku, $relation->group_id );
			}
		}
	}
}
