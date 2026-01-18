<?php

namespace MultiStore\Plugin\CLI;

use MultiStore\Plugin\Database\Product_Relation_Groups_Table;
use MultiStore\Plugin\Database\Product_Relation_Settings_Table;
use MultiStore\Plugin\Database\Product_Relations_Table;
use MultiStore\Plugin\Repository\Relations_Repository;
use WP_CLI;

class Import_Relations {
	public function __invoke() {
		global $wpdb;

		$upload_dir = wp_upload_dir();
		$csv_file   = $upload_dir['basedir'] . '/multistore-import-data/relations.csv';

		if ( ! file_exists( $csv_file ) ) {
			WP_CLI::error( sprintf( 'File not found: %s', $csv_file ) );
			return;
		}

		// read each line
		$data = file( $csv_file );
		if ( empty( $data ) ) {
			WP_CLI::error( 'Data file is empty.' );
			return;
		}

		$relations_repository = new Relations_Repository();

		$product_sku     = '';
		$attribute_ids   = wc_get_attribute_taxonomy_ids();
		$groups          = $relations_repository->get_all_groups();
		$group_table     = Product_Relation_Groups_Table::get_table_name();
		$relations_table = Product_Relations_Table::get_table_name();
		$setting_table   = Product_Relation_Settings_Table::get_table_name();

		foreach ( $attribute_ids as $attribute_name => $attribute_id ) {

			$group_existst = array_filter(
				$groups,
				function ( $group ) use ( $attribute_id ) {
					return (int) $group->attribute_id === (int) $attribute_id;
				}
			);

			if ( ! empty( $group_existst ) ) {
				continue;
			}
			$data    = array(
				'attribute_id'          => $attribute_id,
				'name'                  => $attribute_name,
				'display_on_list'       => 0,
				'display_style_single'  => 'image_product',
				'display_style_archive' => 'image_product',
				'sort_order'            => 0,
			);
			$formats = array( '%d', '%s', '%d', '%s', '%s', '%d' );

			// Add attribute_id if provided.
			$result = $wpdb->insert( $group_table, $data, $formats );
		}

		// get all groups after adding.
		$group_map = array();
		$groups    = $relations_repository->get_all_groups();
		foreach ( $groups as $group ) {
			$group_map[ $group->name ] = $group->id;
		}

		// clear database before import.
		$wpdb->query( "TRUNCATE TABLE {$relations_table}" );
		$wpdb->query( "TRUNCATE TABLE {$setting_table}" );

		foreach ( $data as $line ) {
			// skip separators.
			if ( empty( trim( $line ) ) ) {
				continue;
			}
			if ( preg_match( '/-+$/', $line ) ) {
				continue;
			}

			// check if is product line.
			if ( preg_match( '/^(?<sku>\d+):/', $line, $product_match ) ) {
				$product_sku = $product_match['sku'];
				continue;
			}

			if ( empty( $product_sku ) ) {
				continue;
			}

			// if ( ! in_array( $product_sku, array( '9115366', '9115422' ), true ) ) {
			// 	continue;
			// }

			// parse attribute line.
			preg_match( '/-\s(?<attr_name>[\w-]+):\s(?<rel_sku>.+)/', $line, $attribute_match );
			
			$group_id = null;
			if ( isset( $attribute_match['attr_name'] ) && isset( $group_map[ $attribute_match['attr_name'] ] ) ) {
				$group_id = $group_map[ $attribute_match['attr_name'] ];
			}

			if ( empty( $group_id ) ) {
				error_log( 'Group not found: ' . $attribute_match['attr_name'] );
				continue;
			}

			// explode skus.
			$related_skus = explode( ',', $attribute_match['rel_sku'] );

			foreach ( $related_skus as $index_order => $related_product_sku ) {
				$related_product_sku = trim( $related_product_sku );
				if ( empty( $related_product_sku ) ) {
					continue;
				}

				$label_value = 'Label ' . $related_product_sku;
				if ( str_contains( $related_product_sku, '@' ) ) {
					list( $related_product_sku, $label_value ) = explode( '@', $related_product_sku );
					$related_product_sku                       = trim( $related_product_sku );
				}

				$current_relations = $relations_repository->get_relations_by_sku( $product_sku );

				// sprwadzamy czy istnieje juz relacja.
				$existing_relations = array_filter(
					$current_relations,
					function ( $relation ) use ( $related_product_sku, $group_id ) {
						return $relation->related_product_sku === $related_product_sku && $relation->group_id === $group_id;
					}
				);

				$existing_relations = ! empty( $existing_relations ) ? current( $existing_relations ) : false;
				if ( ! $existing_relations ) {

					// sprawdzamy czy jakiś inny produkt ma relacje z tym samym sku, żeby pobrać ustawienia (settings).
					$existing_related_relations = $relations_repository->get_relations_by_related_sku( $related_product_sku, $group_id );
					if ( empty( $existing_related_relations ) ) {

						$default_settings = array(
							'custom_label'        => trim( $label_value ),
							'custom_lable_single' => '',
							'custom_image_id'     => 0,
							'label_source'        => 'custom',
						);
						$settings_id = $relations_repository->create_relation_settings( $default_settings );
					} else {
						// wszystkie relacje z tym produktem powinny miec ten sam settings.
						$related_relation = current( $existing_related_relations );
						$settings_id      = $related_relation->settings_id;
					}

					$relations_repository->create_relation( $product_sku, $related_product_sku, $group_id, $settings_id, $index_order );
				} else {
					error_log( 'Relationship already exists: ' . $product_sku . ' -> ' . $related_product_sku );
				}
			}
		}
	}
}
