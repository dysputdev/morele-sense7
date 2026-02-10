/**
 * Product Grouping Toggle Frontend Script
 *
 * @package MultiStore\Plugin\Block\ProductGroupingToggle
 */

(function () {
	'use strict';

	/**
	 * Cookie helpers
	 */
	const Cookie = {
		/**
		 * Set cookie
		 *
		 * @param {string} name Cookie name.
		 * @param {string} value Cookie value.
		 * @param {number} days Days until expiration.
		 */
		set: function (name, value, days) {
			let expires = '';
			if (days) {
				const date = new Date();
				date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
				expires = '; expires=' + date.toUTCString();
			}
			document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax';
		},

		/**
		 * Get cookie
		 *
		 * @param {string} name Cookie name.
		 * @return {string|null} Cookie value or null.
		 */
		get: function (name) {
			const nameEQ = name + '=';
			const ca = document.cookie.split(';');
			for (let i = 0; i < ca.length; i++) {
				let c = ca[i];
				while (c.charAt(0) === ' ') {
					c = c.substring(1, c.length);
				}
				if (c.indexOf(nameEQ) === 0) {
					return c.substring(nameEQ.length, c.length);
				}
			}
			return null;
		},
	};

	/**
	 * Initialize product grouping toggle
	 */
	function initGroupingToggle() {
		const blocks = document.querySelectorAll('.multistore-block-product-grouping-toggle');

		blocks.forEach((block) => {
			const button = block.querySelector('.multistore-block-product-grouping-toggle__button');

			if (!button) {
				return;
			}

			button.addEventListener('click', function () {
				toggleGrouping(button, block);
			});

			// Dispatch custom event for analytics tracking.
			button.addEventListener('click', function () {
				const isGrouped = button.getAttribute('aria-pressed') === 'true';
				const event = new CustomEvent('multistore:grouping:toggle', {
					detail: {
						state: isGrouped ? 'on' : 'off',
						timestamp: Date.now(),
					},
					bubbles: true,
				});
				button.dispatchEvent(event);
			});
		});
	}

	/**
	 * SVG icons
	 */
	const ICONS = {
		grouped:
			'<svg class="multistore-block-product-grouping-toggle__icon multistore-block-product-grouping-toggle__icon--grouped" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="2" y="2" width="7" height="7" rx="1" fill="currentColor"/><rect x="11" y="2" width="7" height="7" rx="1" fill="currentColor"/><rect x="2" y="11" width="7" height="7" rx="1" fill="currentColor"/><rect x="11" y="11" width="7" height="7" rx="1" fill="currentColor"/></svg>',
		ungrouped:
			'<svg class="multistore-block-product-grouping-toggle__icon multistore-block-product-grouping-toggle__icon--ungrouped" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="2" y="2" width="16" height="3" rx="1" fill="currentColor"/><rect x="2" y="8.5" width="16" height="3" rx="1" fill="currentColor"/><rect x="2" y="15" width="16" height="3" rx="1" fill="currentColor"/></svg>',
	};

	/**
	 * Toggle grouping state
	 *
	 * @param {HTMLElement} button Button element.
	 * @param {HTMLElement} block Block element.
	 */
	function toggleGrouping(button, block) {
		const currentState = button.getAttribute('aria-pressed') === 'true';
		const newState = !currentState;

		// Update button state.
		button.setAttribute('aria-pressed', newState ? 'true' : 'false');
		block.setAttribute('data-grouped', newState ? 'true' : 'false');

		// Update button classes.
		if (newState) {
			button.classList.remove('multistore-block-product-grouping-toggle__button--off');
			button.classList.add('multistore-block-product-grouping-toggle__button--on');
		} else {
			button.classList.remove('multistore-block-product-grouping-toggle__button--on');
			button.classList.add('multistore-block-product-grouping-toggle__button--off');
		}

		// Update icon.
		const oldIcon = button.querySelector('.multistore-block-product-grouping-toggle__icon');
		if (oldIcon) {
			const label = button.querySelector('.multistore-block-product-grouping-toggle__label');
			const newIconHTML = newState ? ICONS.grouped : ICONS.ungrouped;
			oldIcon.outerHTML = newIconHTML;

			// Re-append label if it exists (to maintain order).
			if (label) {
				button.appendChild(label);
			}
		}

		// Update label if exists.
		const label = button.querySelector('.multistore-block-product-grouping-toggle__label');
		if (label) {
			const labelOn = button.getAttribute('data-label-on');
			const labelOff = button.getAttribute('data-label-off');
			label.textContent = newState ? labelOn : labelOff;
		}

		// Save state to cookie.
		Cookie.set('multistore_product_grouping', newState ? 'on' : 'off', 365);

		// Reload page to apply new grouping state.
		window.location.reload();
	}

	// Initialize on DOM ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initGroupingToggle);
	} else {
		initGroupingToggle();
	}
})();
