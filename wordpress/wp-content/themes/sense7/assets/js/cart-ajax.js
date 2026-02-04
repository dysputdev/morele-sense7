/**
 * Cart AJAX functionality using WordPress AJAX
 */

(function () {
	'use strict';

	const CartAjax = {
		ajaxUrl: sense7Cart.ajaxUrl,
		nonce: sense7Cart.nonce,
		isUpdating: false,

		/**
		 * Initialize cart AJAX
		 */
		init: function () {
			this.bindQuantityButtons();
			this.bindRemoveButtons();
			this.bindCouponForm();
			this.bindCouponRemove();
			this.bindRemoveSelected();
			this.bindRemoveAll();
		},

		/**
		 * Make AJAX request
		 */
		request: function (action, data) {
			const formData = new FormData();
			formData.append('action', action);
			formData.append('nonce', this.nonce);

			for (const key in data) {
				if (data.hasOwnProperty(key)) {
					formData.append(key, data[key]);
				}
			}

			return fetch(this.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (data) {
					if (!data.success) {
						throw new Error(data.data.message || 'Request failed');
					}
					return data.data;
				})
				.catch(function (error) {
					console.error('Cart AJAX Error:', error);
					CartAjax.showNotice(
						error.message || 'Wystąpił błąd. Odśwież stronę i spróbuj ponownie.',
						'error'
					);
					throw error;
				});
		},

		/**
		 * Update cart UI with new data
		 */
		updateCartUI: function (cartData) {
			if (!cartData || !cartData.cart) {
				return;
			}

			const cart = cartData.cart;

			// Update cart totals
			this.updateTotals(cart);

			// Update item prices and subtotals
			this.updateItems(cart);

			// Update cart count
			this.updateCartCount(cart.items_count);
		},

		/**
		 * Update cart totals section
		 */
		updateTotals: function (cart) {
			const totalsSection = document.querySelector('.cart-totals');
			if (!totalsSection || !cart.totals) {
				return;
			}

			// Update subtotal
			const subtotalValue = totalsSection.querySelector(
				'.cart-totals__item--subtotal .cart-totals__value'
			);
			if (subtotalValue) {
				subtotalValue.innerHTML = this.formatPrice(cart.totals.subtotal);
			}

			// Update product count
			const productCount = totalsSection.querySelector(
				'.cart-totals__item--subtotal .cart-totals__label'
			);
			if (productCount && cart.items_count) {
				productCount.textContent =
					'Wartość produktów (' + cart.items_count + ')';
			}

			// Update shipping
			const shippingValue = totalsSection.querySelector(
				'.cart-totals__item--shipping .cart-totals__value'
			);
			if (shippingValue) {
				if (parseFloat(cart.totals.shipping_total) === 0) {
					shippingValue.innerHTML =
						'<span class="cart-totals__shipping-free">za darmo</span>';
				} else {
					shippingValue.innerHTML = this.formatPrice(
						cart.totals.shipping_total
					);
				}
			}

			// Update total
			const totalValue = totalsSection.querySelector(
				'.cart-totals__total-value'
			);
			if (totalValue) {
				totalValue.innerHTML = this.formatPrice(cart.totals.total);
			}

			// Update coupons display
			this.updateCouponsDisplay(cart);
		},

		/**
		 * Update individual cart items
		 */
		updateItems: function (cart) {
			if (!cart.items) {
				return;
			}

			cart.items.forEach(function (item) {
				const cartItem = document.querySelector(
					'[data-cart-item-key="' + item.key + '"]'
				);
				if (!cartItem) {
					return;
				}

				// Update quantity input
				const quantityInput = cartItem.querySelector(
					'.quantity-selector__input'
				);
				if (quantityInput) {
					quantityInput.value = item.quantity;
				}

				// Update item subtotal
				const priceElement = cartItem.querySelector(
					'.cart-item__current-price'
				);
				if (priceElement && item.totals) {
					priceElement.innerHTML = CartAjax.formatPrice(
						item.totals.line_total
					);
				}
			});
		},

		/**
		 * Update cart count
		 */
		updateCartCount: function (count) {
			const headerCount = document.querySelector(
				'.woocommerce-cart-form__count'
			);
			if (headerCount) {
				const text =
					count === 1 ? count + ' produkt' : count + ' produkty';
				headerCount.textContent = text;
			}
		},

		/**
		 * Update coupons display
		 */
		updateCouponsDisplay: function (cart) {
			const hasCoupons = cart.coupons && cart.coupons.length > 0;
			const couponSection = document.querySelector(
				'.cart-totals__coupon-section'
			);
			const appliedCoupons = document.querySelector(
				'.cart-totals__applied-coupons'
			);

			if (hasCoupons) {
				// Hide the form section
				if (couponSection) {
					couponSection.style.display = 'none';
				}

				// Show applied coupons
				if (!appliedCoupons) {
					this.createAppliedCouponsSection(cart.coupons);
				} else {
					this.updateAppliedCoupons(cart.coupons);
				}
			} else {
				// Show the form section
				if (couponSection) {
					couponSection.style.display = 'block';
				}

				// Hide applied coupons
				if (appliedCoupons) {
					appliedCoupons.remove();
				}
			}

			// Update discount in totals
			this.updateDiscountDisplay(cart);
		},

		/**
		 * Create applied coupons section
		 */
		createAppliedCouponsSection: function (coupons) {
			const couponSection = document.querySelector(
				'.cart-totals__coupon-section'
			);
			if (!couponSection) {
				return;
			}

			const html =
				'<div class="cart-totals__applied-coupons">' +
				'<span class="cart-totals__coupons-label">Kody rabatowe:</span>' +
				'<div class="cart-totals__coupons-list">' +
				coupons
					.map(function (coupon) {
						return (
							'<div class="cart-totals__coupon-tag">' +
							'<span class="cart-totals__coupon-code">' +
							coupon.code.toUpperCase() +
							'</span>' +
							'<a href="#" class="cart-totals__coupon-remove" data-coupon-code="' +
							coupon.code +
							'" aria-label="Usuń kupon ' +
							coupon.code +
							'">' +
							'<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">' +
							'<path d="M9 3L3 9M3 3L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
							'</svg>' +
							'</a>' +
							'</div>'
						);
					})
					.join('') +
				'</div>' +
				'</div>';

			couponSection.insertAdjacentHTML('afterend', html);
			this.bindCouponRemove();
		},

		/**
		 * Update applied coupons list
		 */
		updateAppliedCoupons: function (coupons) {
			const couponsList = document.querySelector(
				'.cart-totals__coupons-list'
			);
			if (!couponsList) {
				return;
			}

			couponsList.innerHTML = coupons
				.map(function (coupon) {
					return (
						'<div class="cart-totals__coupon-tag">' +
						'<span class="cart-totals__coupon-code">' +
						coupon.code.toUpperCase() +
						'</span>' +
						'<a href="#" class="cart-totals__coupon-remove" data-coupon-code="' +
						coupon.code +
						'" aria-label="Usuń kupon ' +
						coupon.code +
						'">' +
						'<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">' +
						'<path d="M9 3L3 9M3 3L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
						'</svg>' +
						'</a>' +
						'</div>'
					);
				})
				.join('');

			this.bindCouponRemove();
		},

		/**
		 * Update discount display in totals
		 */
		updateDiscountDisplay: function (cart) {
			// Remove old discount items
			const oldDiscounts = document.querySelectorAll(
				'.cart-totals__item--discount'
			);
			oldDiscounts.forEach(function (item) {
				item.remove();
			});

			// Add new discount items
			if (cart.coupons && cart.coupons.length > 0) {
				const subtotalItem = document.querySelector(
					'.cart-totals__item--subtotal'
				);
				if (!subtotalItem) {
					return;
				}

				cart.coupons.forEach(function (coupon) {
					const discountHtml =
						'<div class="cart-totals__item cart-totals__item--discount">' +
						'<span class="cart-totals__label">Rabat ' +
						coupon.code.toUpperCase() +
						'</span>' +
						'<span class="cart-totals__value cart-totals__value--discount">-' +
						CartAjax.formatPrice(coupon.totals.total_discount) +
						'</span>' +
						'</div>';
					subtotalItem.insertAdjacentHTML('afterend', discountHtml);
				});
			}
		},

		/**
		 * Format price
		 */
		formatPrice: function (amount) {
			const formatter = new Intl.NumberFormat('pl-PL', {
				style: 'currency',
				currency: 'PLN',
			});

			return formatter.format(parseFloat(amount));
		},

		/**
		 * Show loading state
		 */
		showLoading: function (element) {
			if (element) {
				element.classList.add('is-loading');
			}
			document.body.classList.add('cart-updating');
		},

		/**
		 * Hide loading state
		 */
		hideLoading: function (element) {
			if (element) {
				element.classList.remove('is-loading');
			}
			document.body.classList.remove('cart-updating');
		},

		/**
		 * Show notice message
		 */
		showNotice: function (message, type) {
			const noticeHtml =
				'<div class="woocommerce-message woocommerce-message--' +
				type +
				'" role="alert">' +
				message +
				'</div>';

			const container =
				document.querySelector('.woocommerce-cart-wrapper') ||
				document.querySelector('.woocommerce');

			if (container) {
				container.insertAdjacentHTML('afterbegin', noticeHtml);

				setTimeout(function () {
					const notice = container.querySelector('.woocommerce-message');
					if (notice) {
						notice.remove();
					}
				}, 5000);
			}
		},

		/**
		 * Bind quantity selector buttons
		 */
		bindQuantityButtons: function () {
			const quantitySelectors =
				document.querySelectorAll('.quantity-selector');

			quantitySelectors.forEach(function (selector) {
				const input = selector.querySelector('.quantity-selector__input');
				const decreaseBtn = selector.querySelector(
					'[data-action="decrease"]'
				);
				const increaseBtn = selector.querySelector(
					'[data-action="increase"]'
				);

				if (!input || !decreaseBtn || !increaseBtn) {
					return;
				}

				const cartItem = selector.closest(
					'.woocommerce-cart-form__cart-item'
				);
				const itemKey = cartItem
					? cartItem.getAttribute('data-cart-item-key')
					: null;

				if (!itemKey) {
					return;
				}

				const min = parseInt(input.getAttribute('min')) || 1;
				const max = parseInt(input.getAttribute('max')) || 999;

				function updateQuantity(newValue) {
					const currentValue = parseInt(input.value) || 0;
					let finalValue = newValue;

					if (finalValue < min) {
						finalValue = min;
					}
					if (max > 0 && finalValue > max) {
						finalValue = max;
					}

					if (finalValue === currentValue) {
						return;
					}

					// Update UI immediately
					input.value = finalValue;
					decreaseBtn.disabled = finalValue <= min;
					increaseBtn.disabled = max > 0 && finalValue >= max;

					// Update via AJAX
					CartAjax.updateItemQuantity(itemKey, finalValue, cartItem);
				}

				decreaseBtn.addEventListener('click', function (e) {
					e.preventDefault();
					if (CartAjax.isUpdating) {
						return;
					}
					const currentValue = parseInt(input.value) || 0;
					updateQuantity(currentValue - 1);
				});

				increaseBtn.addEventListener('click', function (e) {
					e.preventDefault();
					if (CartAjax.isUpdating) {
						return;
					}
					const currentValue = parseInt(input.value) || 0;
					updateQuantity(currentValue + 1);
				});

				// Initialize button states
				const currentValue = parseInt(input.value) || 0;
				decreaseBtn.disabled = currentValue <= min;
				increaseBtn.disabled = max > 0 && currentValue >= max;
			});
		},

		/**
		 * Update item quantity via AJAX
		 */
		updateItemQuantity: function (itemKey, quantity, cartItem) {
			if (this.isUpdating) {
				return;
			}

			this.isUpdating = true;
			this.showLoading(cartItem);

			this.request('sense7_update_cart_item', {
				cart_item_key: itemKey,
				quantity: quantity,
			})
				.then(function (response) {
					CartAjax.updateCartUI(response);
					CartAjax.isUpdating = false;
					CartAjax.hideLoading(cartItem);
				})
				.catch(function (error) {
					CartAjax.isUpdating = false;
					CartAjax.hideLoading(cartItem);
					location.reload();
				});
		},

		/**
		 * Bind remove product buttons
		 */
		bindRemoveButtons: function () {
			const removeButtons = document.querySelectorAll(
				'.cart-item__remove .remove'
			);

			removeButtons.forEach(function (button) {
				button.addEventListener('click', function (e) {
					e.preventDefault();

					if (CartAjax.isUpdating) {
						return;
					}

					const cartItem = button.closest(
						'.woocommerce-cart-form__cart-item'
					);
					const itemKey = cartItem
						? cartItem.getAttribute('data-cart-item-key')
						: null;

					if (!itemKey) {
						return;
					}

					CartAjax.removeItem(itemKey, cartItem);
				});
			});
		},

		/**
		 * Remove item from cart via AJAX
		 */
		removeItem: function (itemKey, cartItem) {
			if (this.isUpdating) {
				return;
			}

			this.isUpdating = true;
			this.showLoading(cartItem);

			this.request('sense7_remove_cart_item', {
				cart_item_key: itemKey,
			})
				.then(function (response) {
					// Remove item from DOM
					if (cartItem) {
						cartItem.style.opacity = '0';
						setTimeout(function () {
							cartItem.remove();

							// Check if cart is empty
							const remainingItems = document.querySelectorAll(
								'.woocommerce-cart-form__cart-item'
							);
							if (remainingItems.length === 0) {
								location.reload();
							}
						}, 300);
					}

					CartAjax.updateCartUI(response);
					CartAjax.showNotice(response.message || 'Produkt usunięty', 'success');
					CartAjax.isUpdating = false;
					CartAjax.hideLoading(cartItem);
				})
				.catch(function (error) {
					CartAjax.isUpdating = false;
					CartAjax.hideLoading(cartItem);
					location.reload();
				});
		},

		/**
		 * Bind coupon form
		 */
		bindCouponForm: function () {
			const couponForm = document.querySelector('.woocommerce-coupon-form');

			if (!couponForm) {
				return;
			}

			couponForm.addEventListener('submit', function (e) {
				e.preventDefault();

				if (CartAjax.isUpdating) {
					return;
				}

				const couponInput = couponForm.querySelector('#coupon_code');
				const couponCode = couponInput ? couponInput.value.trim() : '';

				if (!couponCode) {
					CartAjax.showNotice('Wprowadź kod kuponu.', 'error');
					return;
				}

				CartAjax.applyCoupon(couponCode, couponForm);
			});

			// Handle coupon toggle
			const couponToggle = document.querySelector(
				'[data-toggle="coupon-form"]'
			);
			const couponFormContainer = document.getElementById('coupon-form');

			if (couponToggle && couponFormContainer) {
				couponToggle.addEventListener('click', function () {
					if (
						couponFormContainer.style.display === 'none' ||
						couponFormContainer.style.display === ''
					) {
						couponFormContainer.style.display = 'block';
					} else {
						couponFormContainer.style.display = 'none';
					}
				});
			}
		},

		/**
		 * Apply coupon via AJAX
		 */
		applyCoupon: function (couponCode, form) {
			if (this.isUpdating) {
				return;
			}

			this.isUpdating = true;
			this.showLoading(form);

			this.request('sense7_apply_coupon', {
				coupon_code: couponCode,
			})
				.then(function (response) {
					CartAjax.updateCartUI(response);
					CartAjax.showNotice(response.message || 'Kupon zastosowany', 'success');

					// Clear input
					const input = form.querySelector('#coupon_code');
					if (input) {
						input.value = '';
					}

					CartAjax.isUpdating = false;
					CartAjax.hideLoading(form);
				})
				.catch(function (error) {
					CartAjax.isUpdating = false;
					CartAjax.hideLoading(form);
				});
		},

		/**
		 * Bind coupon remove buttons
		 */
		bindCouponRemove: function () {
			const removeButtons = document.querySelectorAll(
				'.cart-totals__coupon-remove'
			);

			removeButtons.forEach(function (button) {
				// Remove old listener
				const newButton = button.cloneNode(true);
				button.parentNode.replaceChild(newButton, button);

				newButton.addEventListener('click', function (e) {
					e.preventDefault();

					if (CartAjax.isUpdating) {
						return;
					}

					const couponCode = newButton.getAttribute('data-coupon-code');

					if (!couponCode) {
						return;
					}

					CartAjax.removeCoupon(couponCode);
				});
			});
		},

		/**
		 * Remove coupon via AJAX
		 */
		removeCoupon: function (couponCode) {
			if (this.isUpdating) {
				return;
			}

			this.isUpdating = true;
			this.showLoading();

			this.request('sense7_remove_coupon', {
				coupon_code: couponCode,
			})
				.then(function (response) {
					CartAjax.updateCartUI(response);
					CartAjax.showNotice(response.message || 'Kupon usunięty', 'success');
					CartAjax.isUpdating = false;
					CartAjax.hideLoading();
				})
				.catch(function (error) {
					CartAjax.isUpdating = false;
					CartAjax.hideLoading();
				});
		},

		/**
		 * Bind remove selected button
		 */
		bindRemoveSelected: function () {
			const removeSelectedBtn = document.querySelector(
				'[data-action="remove-selected"]'
			);

			if (!removeSelectedBtn) {
				return;
			}

			removeSelectedBtn.addEventListener('click', function (e) {
				e.preventDefault();

				if (CartAjax.isUpdating) {
					return;
				}

				const checkboxes = document.querySelectorAll(
					'.cart-item__checkbox:checked'
				);

				if (checkboxes.length === 0) {
					CartAjax.showNotice(
						'Proszę zaznaczyć produkty do usunięcia.',
						'error'
					);
					return;
				}

				if (
					!confirm(
						'Czy na pewno chcesz usunąć zaznaczone produkty z koszyka?'
					)
				) {
					return;
				}

				const itemKeys = Array.from(checkboxes).map(function (checkbox) {
					const cartItem = checkbox.closest(
						'.woocommerce-cart-form__cart-item'
					);
					return cartItem
						? cartItem.getAttribute('data-cart-item-key')
						: null;
				});

				CartAjax.removeMultipleItems(itemKeys);
			});
		},

		/**
		 * Bind remove all button
		 */
		bindRemoveAll: function () {
			const removeAllBtn = document.querySelector(
				'[data-action="remove-all"]'
			);

			if (!removeAllBtn) {
				return;
			}

			removeAllBtn.addEventListener('click', function (e) {
				e.preventDefault();

				if (CartAjax.isUpdating) {
					return;
				}

				if (
					!confirm(
						'Czy na pewno chcesz usunąć wszystkie produkty z koszyka?'
					)
				) {
					return;
				}

				const cartItems = document.querySelectorAll(
					'.woocommerce-cart-form__cart-item'
				);
				const itemKeys = Array.from(cartItems).map(function (item) {
					return item.getAttribute('data-cart-item-key');
				});

				CartAjax.removeMultipleItems(itemKeys);
			});
		},

		/**
		 * Remove multiple items
		 */
		removeMultipleItems: function (itemKeys) {
			if (this.isUpdating || !itemKeys || itemKeys.length === 0) {
				return;
			}

			this.isUpdating = true;
			this.showLoading();

			const promises = itemKeys.map(function (itemKey) {
				if (!itemKey) {
					return Promise.resolve();
				}
				return CartAjax.request('sense7_remove_cart_item', {
					cart_item_key: itemKey,
				});
			});

			Promise.all(promises)
				.then(function (responses) {
					const lastResponse = responses[responses.length - 1];
					if (lastResponse && lastResponse.cart.items_count === 0) {
						location.reload();
					} else {
						CartAjax.updateCartUI(lastResponse);
						CartAjax.showNotice('Produkty zostały usunięte.', 'success');

						// Remove items from DOM
						itemKeys.forEach(function (itemKey) {
							const cartItem = document.querySelector(
								'[data-cart-item-key="' + itemKey + '"]'
							);
							if (cartItem) {
								cartItem.remove();
							}
						});
					}

					CartAjax.isUpdating = false;
					CartAjax.hideLoading();
				})
				.catch(function (error) {
					CartAjax.isUpdating = false;
					CartAjax.hideLoading();
					location.reload();
				});
		},
	};

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			CartAjax.init();
		});
	} else {
		CartAjax.init();
	}
})();
