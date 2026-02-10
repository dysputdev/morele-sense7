import './tooltip.scss';

( function () {
	'use strict';

	/**
	 * Initialize tooltip functionality.
	 */
	function initTooltips() {
		const tooltips = document.querySelectorAll( '.multistore-tooltip' );

		if ( ! tooltips.length ) {
			return;
		}

		tooltips.forEach( function ( tooltip ) {
			const icon = tooltip.querySelector( '.multistore-tooltip__icon' );
			const content = tooltip.querySelector( '.multistore-tooltip__content' );

			if ( ! icon || ! content ) {
				return;
			}

			// Add ARIA attributes for accessibility
			icon.setAttribute( 'role', 'button' );
			icon.setAttribute( 'tabindex', '0' );
			icon.setAttribute( 'aria-expanded', 'false' );
			icon.setAttribute( 'aria-label', 'Pokaż więcej informacji' );

			// Show tooltip on hover
			icon.addEventListener( 'mouseenter', function () {
				showTooltip( tooltip, icon, content );
			} );

			// Hide tooltip on mouse leave
			tooltip.addEventListener( 'mouseleave', function () {
				hideTooltip( tooltip, icon, content );
			} );

			// Toggle tooltip on click for touch devices
			icon.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				event.stopPropagation();
				toggleTooltip( tooltip, icon, content );
			} );

			// Keyboard accessibility
			icon.addEventListener( 'keydown', function ( event ) {
				if ( event.key === 'Enter' || event.key === ' ' ) {
					event.preventDefault();
					toggleTooltip( tooltip, icon, content );
				} else if ( event.key === 'Escape' ) {
					hideTooltip( tooltip, icon, content );
				}
			} );
		} );

		// Close tooltip when clicking outside
		document.addEventListener( 'click', function ( event ) {
			tooltips.forEach( function ( tooltip ) {
				const icon = tooltip.querySelector( '.multistore-tooltip__icon' );
				const content = tooltip.querySelector( '.multistore-tooltip__content' );

				if ( ! tooltip.contains( event.target ) ) {
					hideTooltip( tooltip, icon, content );
				}
			} );
		} );
	}

	/**
	 * Show tooltip.
	 *
	 * @param {Element} tooltip - Tooltip container element.
	 * @param {Element} icon    - Tooltip icon element.
	 * @param {Element} content - Tooltip content element.
	 */
	function showTooltip( tooltip, icon, content ) {
		tooltip.classList.add( 'multistore-tooltip--active' );
		content.classList.add( 'multistore-tooltip__content--visible' );
		icon.setAttribute( 'aria-expanded', 'true' );
		positionTooltip( tooltip, content );
	}

	/**
	 * Hide tooltip.
	 *
	 * @param {Element} tooltip - Tooltip container element.
	 * @param {Element} icon    - Tooltip icon element.
	 * @param {Element} content - Tooltip content element.
	 */
	function hideTooltip( tooltip, icon, content ) {
		tooltip.classList.remove( 'multistore-tooltip--active' );
		content.classList.remove( 'multistore-tooltip__content--visible' );
		icon.setAttribute( 'aria-expanded', 'false' );
	}

	/**
	 * Toggle tooltip visibility.
	 *
	 * @param {Element} tooltip - Tooltip container element.
	 * @param {Element} icon    - Tooltip icon element.
	 * @param {Element} content - Tooltip content element.
	 */
	function toggleTooltip( tooltip, icon, content ) {
		const isActive = tooltip.classList.contains( 'multistore-tooltip--active' );

		if ( isActive ) {
			hideTooltip( tooltip, icon, content );
		} else {
			showTooltip( tooltip, icon, content );
		}
	}

	/**
	 * Position tooltip to prevent overflow.
	 *
	 * @param {Element} tooltip - Tooltip container element.
	 * @param {Element} content - Tooltip content element.
	 */
	function positionTooltip( tooltip, content ) {
		const contentRect = content.getBoundingClientRect();
		const viewportWidth = window.innerWidth;
		const viewportHeight = window.innerHeight;

		// Reset position classes
		tooltip.classList.remove( 'multistore-tooltip--left', 'multistore-tooltip--right', 'multistore-tooltip--top' );

		// Check if tooltip overflows right edge
		if ( contentRect.right > viewportWidth ) {
			tooltip.classList.add( 'multistore-tooltip--left' );
		}

		// Check if tooltip overflows left edge
		if ( contentRect.left < 0 ) {
			tooltip.classList.add( 'multistore-tooltip--right' );
		}

		// Check if tooltip overflows bottom edge
		if ( contentRect.bottom > viewportHeight ) {
			tooltip.classList.add( 'multistore-tooltip--top' );
		}
	}

	// Initialize on DOM ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initTooltips );
	} else {
		initTooltips();
	}
} )();
