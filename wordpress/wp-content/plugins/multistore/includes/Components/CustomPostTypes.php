<?php
/**
 * Custom Post Types
 *
 * @package MultiStore\Plugin
 */

namespace MultiStore\Plugin\Components;

/**
 * Class CustomPostTypes
 *
 * Handles registration of custom post types
 *
 * @since 1.0.0
 */
class CustomPostTypes {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Register custom post types
	 *
	 * @since 1.0.0
	 */
	public function register_post_types(): void {
		// Example: Register your custom post types here.
		// $this->register_example_post_type();
	}

	/**
	 * Register example post type
	 *
	 * @since 1.0.0
	 */
	private function register_example_post_type(): void {
		$labels = array(
			'name'               => __( 'Examples', 'multistore' ),
			'singular_name'      => __( 'Example', 'multistore' ),
			'menu_name'          => __( 'Examples', 'multistore' ),
			'add_new'            => __( 'Add New', 'multistore' ),
			'add_new_item'       => __( 'Add New Example', 'multistore' ),
			'edit_item'          => __( 'Edit Example', 'multistore' ),
			'new_item'           => __( 'New Example', 'multistore' ),
			'view_item'          => __( 'View Example', 'multistore' ),
			'search_items'       => __( 'Search Examples', 'multistore' ),
			'not_found'          => __( 'No examples found', 'multistore' ),
			'not_found_in_trash' => __( 'No examples found in trash', 'multistore' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'examples' ),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-admin-post',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest'        => true, // Enable Gutenberg editor.
		);

		register_post_type( 'example', $args );
	}
}
