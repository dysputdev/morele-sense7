/**
 * Product Variants Preview Frontend Script
 *
 * Handles variant switching in Query Loop context.
 * Updates product images, titles, and prices on hover.
 *
 * @package MultiStore\Plugin\Block\ProductVariantsPreview
 */

(function () {

	const className = '.multistore-block-product-variants-preview';
	const clickable = false;

	class ProductPreview {
		constructor( containerElement ) {
			this.container = containerElement;
			this.productItem = this.findProductWrapper();

			this.init();
		}

		findProductWrapper() {
			// Szukaj wrappera produktu w Query Loop
			const selectors = [
				'.wp-block-post',
				'.woocommerce-loop-product',
				'li.product',
			];

			for ( const selector of selectors ) {
				const item = this.container.closest( selector );
				if ( item ) return item;
			}

			return this.container.parentElement;
		}

		init() {
			const groups = this.container.querySelectorAll( className + '__group' );
			if ( groups.length === 0 ) return;

			this.productItem.addEventListener( 'mouseleave', this.handleHoverOut.bind( this ) );

			groups.forEach( ( group, index ) => {
				const options = group.querySelectorAll( className + '__option' );
				options.forEach( ( option ) => {
					if ( ! clickable ) {
						option.addEventListener( 'mouseenter', this.handleHoverIn.bind( this ) );
					} else if ( clickable ) {
						option.addEventListener( 'click', this.handleClick.bind( this ) );
					}
				})
			})
		}

		handleHoverIn( event ) {
			// remove all .is-active classes from this group
			const group = event.target.closest( className + '__group' );
			const options = group.querySelectorAll( className + '__option' );
			options.forEach( ( option ) => option.classList.remove( 'is-active' ) );

			// add .is-active class to hovered option
			event.target.classList.add( 'is-active' );
			const productId = event.target.dataset.productId;
		}

		// restore default variant when mouse leaves product item.
		handleHoverOut( event ) {
			console.log( 'mouse leave product' );
		}

		handleClick( event ) {
			console.log( 'click option' );
		}
	}

	function initProductPreview() {
		const containers = document.querySelectorAll( className );

		containers.forEach( ( container ) => {
			if ( container.dataset.previewInit ) return;

			container.dataset.previewInit = 'true';
			new ProductPreview( container );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initProductPreview );
	} else {
		initProductPreview();
	}
})();
