/**
 * Product Variants Frontend Script
 *
 * @package MultiStore\Plugin\Block\ProductVariants
 */

(function () {
	'use strict';

	/**
	 * Initialize product variants
	 */
	function initProductVariants() {
		const blocks = document.querySelectorAll('.multistore-block-product-variants');

		blocks.forEach((block) => {
			const layout = block.dataset.layout;

			if (layout === 'dropdown') {
				initDropdownVariants(block);
			} else {
				initButtonVariants(block);
			}
		});
	}

	/**
	 * Initialize dropdown variants
	 */
	function initDropdownVariants(block) {
		const selects = block.querySelectorAll('[data-variant-select]');

		selects.forEach((select) => {
			select.addEventListener('change', function () {
				const url = this.value;
				if (url) {
					window.location.href = url;
				}
			});
		});
	}

	/**
	 * Initialize button/swatch variants
	 */
	function initButtonVariants(block) {
		const options = block.querySelectorAll('.multistore-block-product-variants__option');

		options.forEach((option) => {
			option.addEventListener('click', function (e) {
				// Let the browser handle the navigation
				// The href attribute already contains the correct URL
			});
		});
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initProductVariants);
	} else {
		initProductVariants();
	}
})();
