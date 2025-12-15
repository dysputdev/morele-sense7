/**
 * Store Selector Block Frontend Script
 *
 * @package MultiStore\Plugin\Block\StoreSelector
 */

document.addEventListener( 'DOMContentLoaded', () => {
	const selectors = document.querySelectorAll(
		'.multistore-block-store-selector'
	);

	selectors.forEach( ( selector ) => {
		const button = selector.querySelector(
			'.multistore-block-store-selector__button'
		);
		const container = selector.querySelector(
			'.multistore-block-store-selector__container'
		);

		if ( ! button || ! container ) {
			return;
		}

		let isOpen = false;

		// Toggle on button click.
		button.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			e.stopPropagation();

			isOpen = ! isOpen;

			if ( isOpen ) {
				container.classList.add( 'is-visible' );
				button.setAttribute( 'aria-expanded', 'true' );
			} else {
				container.classList.remove( 'is-visible' );
				button.setAttribute( 'aria-expanded', 'false' );
			}
		} );

		// Show on button hover.
		button.addEventListener( 'mouseenter', () => {
			if ( ! isOpen ) {
				container.classList.add( 'is-visible' );
			}
		} );

		// Hide on mouse leave from both button and container.
		const hideContainer = () => {
			if ( ! isOpen ) {
				container.classList.remove( 'is-visible' );
			}
		};

		selector.addEventListener( 'mouseleave', hideContainer );

		// Close when clicking outside.
		document.addEventListener( 'click', ( e ) => {
			if ( isOpen && ! selector.contains( e.target ) ) {
				isOpen = false;
				container.classList.remove( 'is-visible' );
				button.setAttribute( 'aria-expanded', 'false' );
			}
		} );

		// Close on escape key.
		document.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'Escape' && isOpen ) {
				isOpen = false;
				container.classList.remove( 'is-visible' );
				button.setAttribute( 'aria-expanded', 'false' );
				button.focus();
			}
		} );
	} );
} );
