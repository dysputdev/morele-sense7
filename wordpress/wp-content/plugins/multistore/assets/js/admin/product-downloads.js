/**
 * Product Downloads Metabox JavaScript
 *
 * @package MultiStore\Plugin
 */

(function ($) {
	'use strict';

	/**
	 * Product Downloads Manager
	 */
	const ProductDownloadsManager = {
		/**
		 * Media uploader instance
		 */
		mediaUploader: null,

		/**
		 * Initialize
		 */
		init: function () {
			this.bindEvents();
		},

		/**
		 * Bind events
		 */
		bindEvents: function () {
			const self = this;

			// Add files button click.
			$(document).on('click', '.multistore-add-files-button', function (e) {
				e.preventDefault();
				self.openMediaUploader();
			});

			// Remove file button click.
			$(document).on('click', '.multistore-remove-file', function (e) {
				e.preventDefault();
				self.removeFile($(this));
			});
		},

		/**
		 * Open media uploader
		 */
		openMediaUploader: function () {
			const self = this;

			// If the media uploader instance already exists, reopen it.
			if (self.mediaUploader) {
				self.mediaUploader.open();
				return;
			}

			// Create new media uploader instance.
			self.mediaUploader = wp.media({
				title: multistoreProductDownloads.selectFilesText,
				button: {
					text: multistoreProductDownloads.addFilesText
				},
				multiple: true
			});

			// When files are selected.
			self.mediaUploader.on('select', function () {
				const attachments = self.mediaUploader.state().get('selection').toJSON();

				attachments.forEach(function (attachment) {
					self.addFile(attachment);
				});

				self.updateNoFilesMessage();
			});

			// Open the media uploader.
			self.mediaUploader.open();
		},

		/**
		 * Add file to the list
		 *
		 * @param {Object} attachment Attachment object from media library.
		 */
		addFile: function (attachment) {
			const $list = $('.multistore-downloads-list');
			const index = Date.now();
			const fileExt = attachment.filename.split('.').pop().toUpperCase();

			const $fileItem = $('<div>', {
				class: 'multistore-file-item',
				'data-index': index
			});

			const $hiddenInput = $('<input>', {
				type: 'hidden',
				name: 'multistore_product_downloads[]',
				value: attachment.id
			});

			const $icon = $('<div>', {
				class: 'multistore-file-item-icon'
			}).append($('<img>', {
				src: attachment.icon,
				alt: ''
			}));

			const $details = $('<div>', {
				class: 'multistore-file-item-details'
			}).append(
				$('<div>', {
					class: 'multistore-file-item-name',
					text: attachment.filename
				}),
				$('<div>', {
					class: 'multistore-file-item-meta',
					text: multistoreProductDownloads.typeText ? multistoreProductDownloads.typeText.replace('%s', fileExt) : 'Typ: ' + fileExt
				})
			);

			const $actions = $('<div>', {
				class: 'multistore-file-item-actions'
			}).append(
				$('<button>', {
					type: 'button',
					class: 'button multistore-remove-file',
					title: multistoreProductDownloads.removeText
				}).append(
					$('<span>', {
						class: 'dashicons dashicons-no-alt'
					})
				)
			);

			$fileItem.append($hiddenInput, $icon, $details, $actions);
			$list.append($fileItem);
		},

		/**
		 * Remove file from the list
		 *
		 * @param {jQuery} $button Remove button element.
		 */
		removeFile: function ($button) {
			if (confirm(multistoreProductDownloads.confirmRemoveText || 'Czy na pewno chcesz usunąć ten plik?')) {
				$button.closest('.multistore-file-item').fadeOut(300, function () {
					$(this).remove();
					ProductDownloadsManager.updateNoFilesMessage();
				});
			}
		},

		/**
		 * Update "no files" message visibility
		 */
		updateNoFilesMessage: function () {
			const $list = $('.multistore-downloads-list');
			const hasFiles = $list.find('.multistore-file-item').length > 0;
			const $noFilesMsg = $list.find('.multistore-no-files');

			if (hasFiles) {
				$noFilesMsg.remove();
			} else if ($noFilesMsg.length === 0) {
				$list.append(
					$('<p>', {
						class: 'multistore-no-files',
						text: multistoreProductDownloads.noFilesText
					})
				);
			}
		}
	};

	// Initialize on document ready.
	$(document).ready(function () {
		ProductDownloadsManager.init();
	});

})(jQuery);
