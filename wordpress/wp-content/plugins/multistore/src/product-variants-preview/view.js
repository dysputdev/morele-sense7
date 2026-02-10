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

			this.parsedMatrix = new Map();

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

			this.productItem.addEventListener( 'mouseleave', this.handleProductHoverOut.bind( this ) );
			this.container.addEventListener( 'mouseleave', this.handlePreviewHoverOut.bind( this ) );

			groups.forEach( ( group, index ) => {
				const options = group.querySelectorAll( className + '__option' );
				options.forEach( ( option ) => {
					if ( ! clickable ) {
						option.addEventListener( 'mouseenter', this.handleHoverIn.bind( this ) );
						option.addEventListener( 'focus', this.handleHoverIn.bind( this ) );
					} else if ( clickable ) {
						option.addEventListener( 'click', this.handleClick.bind( this ) );
					}
				})
			})

			const moreButton = this.container.querySelector( className + '__more-button' );
			if ( moreButton ) {
				moreButton.addEventListener( 'mouseenter', this.handlePreviewVisibility.bind( this ) );
				moreButton.addEventListener( 'focus', this.handlePreviewVisibility.bind( this ) );
			}
		}

		handlePreviewVisibility(event) {
			let _self = this;

			// add is-visible class
			this.container.classList.add('is-visible');
		}

		handleHoverIn( event ) {
			// remove all .is-active classes from this group
			let _self = this;

			const group = event.target.closest( className + '__group' );
			const options = group.querySelectorAll( className + '__option' );
			options.forEach( ( option ) => option.classList.remove( 'is-active' ) );

			// get matrix data
			// const matrix = event.target.dataset.related;
			// const matrixData = JSON.parse( matrix );
			// Parse matrix only once per option
			let matrixData = this.parsedMatrix.get(event.target);
			if ( ! matrixData ) {
				matrixData = JSON.parse(event.target.dataset.related);
				this.parsedMatrix.set(event.target, matrixData);
			}
			
			// get all groups.
			const groups = _self.container.querySelectorAll( className + '__group' );
			groups.forEach( ( group, index ) => {
				// get group id from dataset
				const groupId = parseInt(group.dataset.groupId);
				// hide all options other then that in matrixData[groupId]
				const options = group.querySelectorAll( className + '__option' );
				options.forEach( ( option ) => {
					const optionId = option.dataset.productId;
					if ( ! matrixData[groupId].includes( parseInt( optionId ) ) ) {
						option.classList.add( 'is-hidden' );
					} else {
						option.classList.remove( 'is-hidden' );
					}

					if ( optionId === event.target.dataset.productId ) {
						option.classList.add( 'is-active' );
						
						_self.updateData( option );
					}
				})
			})
		}

		// restore default variant when mouse leaves product item.
		handleProductHoverOut( event ) {
			// remove all .is-active classes from this group
			const groups = this.container.querySelectorAll( className + '__group' );
			groups.forEach( ( group ) => {
				const options = group.querySelectorAll( className + '__option' );
				options.forEach( ( option ) => option.classList.remove( 'is-active' ) );
			})
			// add is-active class to is-current element
			const isCurrent = this.container.querySelector( className + '__option.is-current' );
			if ( isCurrent ) {
				isCurrent.classList.add( 'is-active' );
				this.updateData( isCurrent );
			}

			this.container.classList.remove('is-visible');
		}

		handlePreviewHoverOut( event ) {
			this.container.classList.remove('is-visible');
		}

		handleClick( event ) {
			// console.log( 'click option' );
		}

		updateData( option ) {
			const productDetails = JSON.parse( option.dataset.productDetails );

			// update product image
			const productImageBlock = this.productItem.querySelector( '.wp-block-post-featured-image, .wp-block-woocommerce-product-image' );
			if ( productImageBlock && productDetails.image ) {
				const productImage = productImageBlock.querySelector( 'img' );
				const productLink = productImageBlock.querySelector( 'a' )

				if ( productImage ) {
					// productDetails.image is escaped HTML string, need to extract src from it
					const tempDiv = document.createElement( 'div' );
					tempDiv.innerHTML = productDetails.image;
					const newImage = tempDiv.querySelector( 'img' );
					if ( newImage ) {
						productImage.src = newImage.src;
						productImage.srcset = newImage.srcset;
						productImage.alt = newImage.alt;
					}
				}

				if ( productLink ) {
					productLink.href = productDetails.url;
				}
			}

			// update product title
			const titleBlock = this.productItem.querySelector( '.woocommerce-loop-product__title, .entry-title' );
			if ( titleBlock && productDetails.title ) {
				titleBlock.textContent = productDetails.title;
			}

			const simplyfiedTitleBlock = this.productItem.querySelector( '.multistore-block-simplified-product-name' );
			if ( simplyfiedTitleBlock && productDetails.simple_title ) {
				const simplyfiedTitleBlockLink = simplyfiedTitleBlock.querySelector( 'a' );
				if ( simplyfiedTitleBlockLink ) {
					simplyfiedTitleBlockLink.href = productDetails.url;
					simplyfiedTitleBlockLink.textContent = productDetails.simple_title;
				} else {
					simplyfiedTitleBlock.textContent = productDetails.simple_title;
				}
			}

			const currentPriceBlock = this.productItem.querySelector( '.multistore-block-price-current .multistore-block-price-current__value' );
			if ( currentPriceBlock && productDetails.current_price ) {
				currentPriceBlock.textContent = productDetails.current_price;
			}

			const regularPriceBlock = this.productItem.querySelector( '.multistore-block-price-regular .multistore-block-price-regular__value' );
			if ( regularPriceBlock && productDetails.regular_price ) {
				regularPriceBlock.textContent = productDetails.regular_price;
			}

			const lowestPriceBlock = this.productItem.querySelector( '.multistore-block-price-lowest .multistore-block-price-lowest__value' );
			if ( lowestPriceBlock && productDetails.lowest_price ) {
				lowestPriceBlock.textContent = productDetails.lowest_price;
			}
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
