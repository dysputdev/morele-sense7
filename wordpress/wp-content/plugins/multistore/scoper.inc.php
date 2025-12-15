<?php
/**
 * PHP-Scoper configuration
 *
 * @package MultiStore\Plugin
 */

declare( strict_types=1 );

use Isolated\Symfony\Component\Finder\Finder;

return array(
	// The prefix to be used for all namespaced code.
	'prefix'                  => 'MultiStore\\Plugin\\Vendor',

	// The base output directory for the prefixed code.
	'output-dir'              => 'vendor-scoped',

	// Exclude files from processing.
	'exclude-files'           => array(
		'vendor/composer/installed.json',
	),

	// Exclude namespaces from being prefixed.
	'exclude-namespaces'      => array(
		'MultiStore\\Plugin',
	),

	// Exclude classes from being prefixed.
	'exclude-classes'         => array(),

	// Exclude functions from being prefixed.
	'exclude-functions'       => array(),

	// Exclude constants from being prefixed.
	'exclude-constants'       => array(),

	// Files to be processed.
	'finders'                 => array(
		// Process all PHP files in vendor directory.
		Finder::create()
			->files()
			->ignoreVCS( true )
			->notName( '/.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/' )
			->exclude(
				array(
					'doc',
					'test',
					'test_old',
					'tests',
					'Tests',
					'vendor-bin',
				)
			)
			->in( 'vendor' ),
	),

	// Patchers to apply custom transformations.
	'patchers'                => array(
		// Fix WordPress compatibility.
		static function ( string $file_path, string $prefix, string $content ): string {
			// Skip non-PHP files.
			if ( ! str_ends_with( $file_path, '.php' ) ) {
				return $content;
			}

			// Preserve global WordPress functions and classes.
			$content = str_replace(
				array(
					$prefix . '\\add_action',
					$prefix . '\\add_filter',
					$prefix . '\\do_action',
					$prefix . '\\apply_filters',
					$prefix . '\\get_option',
					$prefix . '\\update_option',
					$prefix . '\\delete_option',
					$prefix . '\\wp_enqueue_script',
					$prefix . '\\wp_enqueue_style',
					$prefix . '\\wp_localize_script',
					$prefix . '\\register_post_type',
					$prefix . '\\register_taxonomy',
				),
				array(
					'add_action',
					'add_filter',
					'do_action',
					'apply_filters',
					'get_option',
					'update_option',
					'delete_option',
					'wp_enqueue_script',
					'wp_enqueue_style',
					'wp_localize_script',
					'register_post_type',
					'register_taxonomy',
				),
				$content
			);

			return $content;
		},
	),

	// Whitelist global classes and functions.
	'expose-global-classes'   => true,
	'expose-global-constants' => true,
	'expose-global-functions' => true,
);
