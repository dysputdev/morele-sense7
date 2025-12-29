/**
 * Product Relations Metabox JavaScript
 *
 * @package MultiStore\Plugin
 */

(function($) {
	'use strict';

	/**
	 * Product Relations Manager
	 */
	const ProductRelations = {

		/**
		 * Initialize
		 */
		init: function() {
			console.log('ProductRelations init', multistoreProductRelations);
			this.setupModal();
			this.setupTabs();
			this.setupAddGroup();
			this.setupCreateGroup();
			this.setupSelectExistingGroup();
			this.setupEditGroup();
			this.setupRemoveGroup();
			this.setupProductSearch();
			this.setupRemoveRelation();
			this.setupSortable();
			this.setupToggleFields();
			this.setupLabelSourceToggle();
		},

		/**
		 * Setup modal
		 */
		setupModal: function() {
			// Open modal.
			$('.multistore-add-group-button').on('click', function(e) {
				e.preventDefault();
				$('.multistore-add-group-modal').fadeIn(200);
			});

			// Close modal.
			$('.multistore-cancel-add-group').on('click', function(e) {
				e.preventDefault();
				ProductRelations.closeModal();
			});

			// Close on overlay click.
			$('.multistore-add-group-modal').on('click', function(e) {
				if ($(e.target).hasClass('multistore-add-group-modal')) {
					ProductRelations.closeModal();
				}
			});

			// Close on ESC key.
			$(document).on('keyup', function(e) {
				if (e.key === 'Escape') {
					ProductRelations.closeModal();
				}
			});
		},

		/**
		 * Close modal
		 */
		closeModal: function() {
			$('.multistore-add-group-modal').fadeOut(200);
			// Reset form.
			$('#multistore_new_group_name').val('');
			$('#multistore_new_group_attribute').val('');
			$('#multistore_new_group_display_on_list').prop('checked', false);
			$('#multistore_new_group_display_style_single').val('image_product');
			$('#multistore_new_group_display_style_archive').val('image_product');
			$('#multistore_new_group_sort_order').val('0');
			$('#multistore_select_group').val('');
		},

		/**
		 * Setup tabs
		 */
		setupTabs: function() {
			$('.multistore-tab-button').on('click', function() {
				const tab = $(this).data('tab');

				// Update active tab.
				$('.multistore-tab-button').removeClass('active');
				$(this).addClass('active');

				// Show/hide content.
				$('.multistore-tab-content').hide();
				$('.multistore-tab-content[data-tab="' + tab + '"]').show();
			});
		},

		/**
		 * Setup add group functionality
		 */
		setupAddGroup: function() {
			// This is handled by setupModal.
		},

		/**
		 * Setup select existing group
		 */
		setupSelectExistingGroup: function() {
			$('.multistore-select-existing-group').on('click', function(e) {
				e.preventDefault();

				const $select = $('#multistore_select_group');
				const $option = $select.find('option:selected');
				const groupId = $option.val();
				const groupName = $option.data('name');
				const displayOnList = $option.data('display');

				if (!groupId) {
					alert('Wybierz grupę z listy');
					return;
				}

				// Add group section.
				ProductRelations.addGroupSection(groupId, groupName, displayOnList);

				// Close modal.
				ProductRelations.closeModal();
			});
		},

		/**
		 * Setup edit group functionality
		 */
		setupEditGroup: function() {
			// Open edit modal.
			$(document).on('click', '.multistore-edit-group', function(e) {
				e.preventDefault();

				const $button = $(this);
				const groupId = $button.data('group-id');
				const groupName = $button.data('group-name');
				const attributeId = $button.data('attribute-id');
				const displayOnList = $button.data('display-on-list');
				const displayStyleSingle = $button.data('display-style-single') || 'image_product';
				const displayStyleArchive = $button.data('display-style-archive') || 'image_product';
				const sortOrder = $button.data('sort-order');

				// Fill form.
				$('#multistore_edit_group_id').val(groupId);
				$('#multistore_edit_group_name').val(groupName);
				$('#multistore_edit_group_attribute').val(attributeId || '');
				$('#multistore_edit_group_display_on_list').prop('checked', displayOnList == 1);
				$('#multistore_edit_group_display_style_single').val(displayStyleSingle);
				$('#multistore_edit_group_display_style_archive').val(displayStyleArchive);
				$('#multistore_edit_group_sort_order').val(sortOrder);

				// Open modal.
				$('.multistore-edit-group-modal').fadeIn(200);
			});

			// Close edit modal.
			$('.multistore-cancel-edit-group').on('click', function(e) {
				e.preventDefault();
				ProductRelations.closeEditModal();
			});

			// Close on overlay click.
			$('.multistore-edit-group-modal').on('click', function(e) {
				if ($(e.target).hasClass('multistore-edit-group-modal')) {
					ProductRelations.closeEditModal();
				}
			});

			// Save changes.
			$('.multistore-save-group-button').on('click', function(e) {
				e.preventDefault();

				const $button = $(this);
				const groupId = $('#multistore_edit_group_id').val();
				const name = $('#multistore_edit_group_name').val().trim();
				const attributeId = $('#multistore_edit_group_attribute').val();
				const displayOnList = $('#multistore_edit_group_display_on_list').is(':checked') ? 1 : 0;
				const displayStyleSingle = $('#multistore_edit_group_display_style_single').val();
				const displayStyleArchive = $('#multistore_edit_group_display_style_archive').val();
				const sortOrder = $('#multistore_edit_group_sort_order').val();

				if (!name) {
					alert('Nazwa grupy jest wymagana');
					return;
				}

				$button.prop('disabled', true).text('Zapisywanie...');

				$.ajax({
					url: multistoreProductRelations.ajaxUrl,
					type: 'POST',
					data: {
						action: 'multistore_update_relation_group',
						nonce: multistoreProductRelations.nonce,
						group_id: groupId,
						name: name,
						attribute_id: attributeId,
						display_on_list: displayOnList,
						display_style_single: displayStyleSingle,
						display_style_archive: displayStyleArchive,
						sort_order: sortOrder
					},
					success: function(response) {
						if (response.success) {
							// Update group header in DOM.
							const $group = $('.multistore-group-relations[data-group-id="' + groupId + '"]');
							const $header = $group.find('h4');
							const badge = displayOnList ? '<span class="multistore-group-badge">Wyświetlane na liście</span>' : '';

							$header.html(name + ' ' + badge);

							// Update button data attributes.
							const $editButton = $group.find('.multistore-edit-group');
							$editButton.data('group-name', name);
							$editButton.data('attribute-id', attributeId);
							$editButton.data('display-on-list', displayOnList);
							$editButton.data('display-style-single', displayStyleSingle);
							$editButton.data('display-style-archive', displayStyleArchive);
							$editButton.data('sort-order', sortOrder);
							$editButton.attr('data-group-name', name);
							$editButton.attr('data-attribute-id', attributeId);
							$editButton.attr('data-display-on-list', displayOnList);
							$editButton.attr('data-display-style-single', displayStyleSingle);
							$editButton.attr('data-display-style-archive', displayStyleArchive);
							$editButton.attr('data-sort-order', sortOrder);

							// Close modal.
							ProductRelations.closeEditModal();

							$button.prop('disabled', false).text('Zapisz zmiany');
						} else {
							let errorMsg = 'Błąd podczas aktualizacji grupy';
							if (response.data && response.data.message) {
								errorMsg = response.data.message;
							}
							if (response.data && response.data.error) {
								errorMsg += '\n\nSzczegóły: ' + response.data.error;
							}
							alert(errorMsg);
							$button.prop('disabled', false).text('Zapisz zmiany');
						}
					},
					error: function(xhr, status, error) {
						console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
						alert('Błąd komunikacji z serwerem\n\nSzczegóły: ' + error);
						$button.prop('disabled', false).text('Zapisz zmiany');
					}
				});
			});
		},

		/**
		 * Close edit modal
		 */
		closeEditModal: function() {
			$('.multistore-edit-group-modal').fadeOut(200);
			// Reset form.
			$('#multistore_edit_group_id').val('');
			$('#multistore_edit_group_name').val('');
			$('#multistore_edit_group_attribute').val('');
			$('#multistore_edit_group_display_on_list').prop('checked', false);
			$('#multistore_edit_group_display_style_single').val('image_product');
			$('#multistore_edit_group_display_style_archive').val('image_product');
			$('#multistore_edit_group_sort_order').val('0');
		},

		/**
		 * Setup create group functionality
		 */
		setupCreateGroup: function() {
			$('.multistore-create-group-button').on('click', function(e) {
				e.preventDefault();

				const $button = $(this);
				const name = $('#multistore_new_group_name').val().trim();
				const attributeId = $('#multistore_new_group_attribute').val();
				const displayOnList = $('#multistore_new_group_display_on_list').is(':checked') ? 1 : 0;
				const displayStyleSingle = $('#multistore_new_group_display_style_single').val();
				const displayStyleArchive = $('#multistore_new_group_display_style_archive').val();
				const sortOrder = $('#multistore_new_group_sort_order').val();

				if (!name) {
					alert('Nazwa grupy jest wymagana');
					return;
				}

				$button.prop('disabled', true).text('Tworzenie...');

				$.ajax({
					url: multistoreProductRelations.ajaxUrl,
					type: 'POST',
					data: {
						action: 'multistore_create_relation_group',
						nonce: multistoreProductRelations.nonce,
						name: name,
						attribute_id: attributeId,
						display_on_list: displayOnList,
						display_style_single: displayStyleSingle,
						display_style_archive: displayStyleArchive,
						sort_order: sortOrder
					},
					success: function(response) {
						if (response.success) {
							// Add group section.
							ProductRelations.addGroupSection(response.data.group_id, name, displayOnList);

							// Close modal.
							ProductRelations.closeModal();

							$button.prop('disabled', false).text('Utwórz i dodaj');
						} else {
							let errorMsg = 'Błąd podczas tworzenia grupy';
							if (response.data && response.data.message) {
								errorMsg = response.data.message;
							}
							if (response.data && response.data.error) {
								errorMsg += '\n\nSzczegóły: ' + response.data.error;
							}
							alert(errorMsg);
							$button.prop('disabled', false).text('Utwórz i dodaj');
						}
					},
					error: function(xhr, status, error) {
						console.error('AJAX Error:', {xhr: xhr, status: status, error: error});
						alert('Błąd komunikacji z serwerem\n\nSzczegóły: ' + error);
						$button.prop('disabled', false).text('Utwórz i dodaj');
					}
				});
			});
		},

		/**
		 * Add group section to the page
		 */
		addGroupSection: function(groupId, groupName, displayOnList) {
			const template = $('#multistore-group-template').html();
			const badge = displayOnList ? '<span class="multistore-group-badge">Wyświetlane na liście</span>' : '';

			const html = template
				.replace(/\{\{GROUP_ID\}\}/g, groupId)
				.replace(/\{\{GROUP_NAME\}\}/g, groupName)
				.replace(/\{\{GROUP_BADGE\}\}/g, badge);

			const $group = $(html);

			// Add to page.
			$('.multistore-active-groups-section').append($group);

			// Setup Select2 for this group.
			const $select = $group.find('.multistore-product-search');
			ProductRelations.setupProductSearchForSelect($select);

			// Setup remove button.
			$group.find('.multistore-remove-group').on('click', function(e) {
				e.preventDefault();
				ProductRelations.removeGroup($(this));
			});

			// Setup sortable.
			$group.find('.multistore-related-products-list').sortable({
				handle: '.multistore-relation-item-handle',
				placeholder: 'ui-sortable-placeholder',
				forcePlaceholderSize: true,
				update: function(event, ui) {
					ProductRelations.updateSortOrder();
				}
			});
		},

		/**
		 * Setup remove group
		 */
		setupRemoveGroup: function() {
			$(document).on('click', '.multistore-remove-group', function(e) {
				e.preventDefault();
				ProductRelations.removeGroup($(this));
			});
		},

		/**
		 * Remove group
		 */
		removeGroup: function($button) {
			if (confirm('Czy na pewno chcesz usunąć tę grupę relacji z tego produktu?')) {
				$button.closest('.multistore-group-relations').fadeOut(300, function() {
					$(this).remove();
				});
			}
		},

		/**
		 * Setup product search with Select2
		 */
		setupProductSearch: function() {
			$('.multistore-product-search').each(function() {
				ProductRelations.setupProductSearchForSelect($(this));
			});
		},

		/**
		 * Setup product search for a specific select element
		 */
		setupProductSearchForSelect: function($select) {
			const groupId = $select.data('group-id');

			$select.select2({
				ajax: {
					url: multistoreProductRelations.ajaxUrl,
					type: 'POST',
					dataType: 'json',
					delay: 250,
					data: function(params) {
						return {
							action: 'multistore_search_products',
							nonce: multistoreProductRelations.nonce,
							search: params.term
						};
					},
					processResults: function(response) {
						if (!response.success || !response.data || !response.data.products) {
							return { results: [] };
						}

						// Convert products to Select2 format.
						const results = response.data.products.map(function(product) {
							return {
								id: product.id,
								text: product.name,
								name: product.name,
								sku: product.sku,
								image_url: product.image_url
							};
						});

						return { results: results };
					},
					cache: true
				},
				minimumInputLength: 2,
				placeholder: multistoreProductRelations.searchProducts,
				templateResult: ProductRelations.formatProductResult,
				templateSelection: ProductRelations.formatProductSelection,
				language: {
					inputTooShort: function() {
						return 'Wpisz co najmniej 2 znaki';
					},
					searching: function() {
						return 'Szukam...';
					},
					noResults: function() {
						return multistoreProductRelations.noResultsText;
					}
				}
			});

			// Add product on select.
			$select.on('select2:select', function(e) {
				const data = e.params.data;
				ProductRelations.addRelation(groupId, data.id, data.text);

				// Clear selection.
				$select.val(null).trigger('change');
			});
		},

		/**
		 * Format product result in dropdown
		 */
		formatProductResult: function(product) {
			if (product.loading) {
				return product.text;
			}

			const $result = $(
				'<div class="multistore-product-result">' +
					'<div class="multistore-product-result-image">' +
						(product.image_url ? '<img src="' + product.image_url + '" />' : '<span class="dashicons dashicons-camera"></span>') +
					'</div>' +
					'<div class="multistore-product-result-details">' +
						'<div class="multistore-product-result-name">' + product.text + '</div>' +
						'<div class="multistore-product-result-meta">' +
							'#' + product.id +
							(product.sku ? ' - SKU: ' + product.sku : '') +
						'</div>' +
					'</div>' +
				'</div>'
			);

			return $result;
		},

		/**
		 * Format selected product
		 */
		formatProductSelection: function(product) {
			return product.text || product.name;
		},

		/**
		 * Add relation to list
		 */
		addRelation: function(groupId, productId, productName) {
			const $list = $('.multistore-related-products-list[data-group-id="' + groupId + '"]');

			// Check if already exists.
			const exists = $list.find('input[value="' + productId + '"]').length > 0;
			if (exists) {
				alert('Ten produkt jest już dodany do tej grupy');
				return;
			}

			// Get next sort order.
			const sortOrder = $list.find('.multistore-relation-item').length;

			// Create relation item.
			const relationId = 'new_' + Date.now();
			const $item = $('<div class="multistore-relation-item" data-relation-id="' + relationId + '">')
				.append(
					'<input type="hidden" name="multistore_relations[' + groupId + '][' + relationId + '][product_id]" value="' + productId + '">' +
					'<input type="hidden" name="multistore_relations[' + groupId + '][' + relationId + '][settings_id]" value="0">' +
					'<input type="hidden" name="multistore_relations[' + groupId + '][' + relationId + '][sort_order]" value="' + sortOrder + '" class="multistore-sort-order">' +
					'<div class="multistore-relation-item-handle">' +
						'<span class="dashicons dashicons-menu"></span>' +
					'</div>' +
					'<div class="multistore-relation-item-details">' +
						'<div class="multistore-relation-item-name">' + productName + '</div>' +
						'<div class="multistore-relation-item-meta">ID: ' + productId + '</div>' +
					'</div>' +
					'<div class="multistore-relation-item-actions">' +
						'<button type="button" class="button multistore-remove-relation" title="' + multistoreProductRelations.removeText + '">' +
							'<span class="dashicons dashicons-no-alt"></span>' +
						'</button>' +
					'</div>'
				);

			$list.append($item);

			// Setup remove button.
			$item.find('.multistore-remove-relation').on('click', function(e) {
				e.preventDefault();
				ProductRelations.removeRelation($(this));
			});

			// Refresh sortable.
			$list.sortable('refresh');
		},

		/**
		 * Setup remove relation buttons
		 */
		setupRemoveRelation: function() {
			$(document).on('click', '.multistore-remove-relation', function(e) {
				e.preventDefault();
				ProductRelations.removeRelation($(this));
			});
		},

		/**
		 * Remove relation
		 */
		removeRelation: function($button) {
			if (confirm('Czy na pewno chcesz usunąć tę relację?')) {
				const $item = $button.closest('.multistore-relation-item');
				$item.fadeOut(300, function() {
					$(this).remove();
					ProductRelations.updateSortOrder();
				});
			}
		},

		/**
		 * Setup sortable lists
		 */
		setupSortable: function() {
			$('.multistore-related-products-list').sortable({
				handle: '.multistore-relation-item-handle',
				placeholder: 'ui-sortable-placeholder',
				forcePlaceholderSize: true,
				update: function(event, ui) {
					ProductRelations.updateSortOrder();
				}
			});
		},

		/**
		 * Update sort order after reordering
		 */
		updateSortOrder: function() {
			$('.multistore-related-products-list').each(function() {
				$(this).find('.multistore-relation-item').each(function(index) {
					$(this).find('.multistore-sort-order').val(index);
				});
			});
		},

		/**
		 * Setup toggle fields functionality
		 */
		setupToggleFields: function() {
			$(document).on('click', '.multistore-relation-item-header', function(e) {
				e.preventDefault();

				const $header = $(this);
				const $item = $header.closest('.multistore-relation-item');
				const $fields = $item.find('.multistore-relation-item-custom-fields');
				const $icon = $header.find('.multistore-toggle-icon');

				if ($fields.is(':visible')) {
					$fields.slideUp(200);
					$icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
				} else {
					$fields.slideDown(200);
					$icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
				}
			});
		},

		/**
		 * Setup label source toggle
		 */
		setupLabelSourceToggle: function() {
			$(document).on('change', '.multistore-label-source-radio', function() {
				const $radio = $(this);
				const $container = $radio.closest('.multistore-relation-item-custom-fields');
				const value = $radio.val();

				// Toggle visibility of fields.
				if (value === 'custom') {
					$container.find('.multistore-custom-label-field').show();
					$container.find('.multistore-attribute-label-field').hide();
				} else if (value === 'attribute') {
					$container.find('.multistore-custom-label-field').hide();
					$container.find('.multistore-attribute-label-field').show();
				}
			});
		},

		/**
		 * Setup image uploader
		 */
		setupImageUploader: function() {
			let mediaUploader;

			// Select image button.
			$(document).on('click', '.multistore-select-image', function(e) {
				e.preventDefault();

				const $button = $(this);
				const $container = $button.closest('.multistore-custom-image-field');
				const $input = $container.find('.multistore-custom-image-id');
				const $preview = $container.find('.multistore-custom-image-preview');

				// Open media uploader.
				if (mediaUploader) {
					mediaUploader.open();
					return;
				}

				mediaUploader = wp.media({
					title: 'Wybierz obraz dla relacji',
					button: {
						text: 'Użyj tego obrazu'
					},
					multiple: false
				});

				mediaUploader.on('select', function() {
					const attachment = mediaUploader.state().get('selection').first().toJSON();

					// Set image ID.
					$input.val(attachment.id);

					// Show preview.
					$preview.find('img').attr('src', attachment.url);
					$preview.show();
				});

				mediaUploader.open();
			});

			// Remove image button.
			$(document).on('click', '.multistore-remove-image', function(e) {
				e.preventDefault();

				const $button = $(this);
				const $preview = $button.closest('.multistore-custom-image-preview');
				const $container = $button.closest('.multistore-custom-image-field');
				const $input = $container.find('.multistore-custom-image-id');

				// Clear values and hide preview.
				$input.val('');
				$preview.find('img').attr('src', '');
				$preview.hide();
			});
		}
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		ProductRelations.init();
		ProductRelations.setupImageUploader();
	});

})(jQuery);
