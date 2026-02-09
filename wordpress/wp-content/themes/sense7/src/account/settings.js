export const AccountSettings = {
	init: function() {
		const inlineForms = document.querySelectorAll('.edit-account--display-name, .edit-account--email');
		if (!inlineForms.length) {
			return;
		}

		this.bindEvents(inlineForms);
	},

	bindEvents: function(inlineForms) {
		inlineForms.forEach(form => {
			const input = form.querySelector('input[type="text"], input[type="email"]');
			const button = form.querySelector('.inline-input__button');

			if (!input || !button) {
				return;
			}

			// Store original value
			input.dataset.originalValue = input.value;

			// Show/hide save button on input change
			input.addEventListener('input', () => {
				const hasChanged = input.value !== input.dataset.originalValue;
				if (hasChanged) {
					button.classList.add('is-visible');
				} else {
					button.classList.remove('is-visible');
				}
				// Hide success icon when typing
				this.hideInlineSuccess(form);
				this.hideInlineError(form);
			});

			// Handle form submit
			form.addEventListener('submit', (e) => {
				e.preventDefault();
				this.handleInlineSubmit(form);
			});
		});
	},

	handleInlineSubmit: function(form) {
		const input = form.querySelector('input[type="text"], input[type="email"]');
		const button = form.querySelector('.inline-input__button');
		const fieldName = input.name;
		const fieldValue = input.value.trim();

		// Basic validation
		if (!fieldValue) {
			this.showInlineError(form, 'To pole jest wymagane');
			return;
		}

		// Email validation
		if (input.type === 'email' && !this.isValidEmail(fieldValue)) {
			this.showInlineError(form, 'Podaj poprawny adres e-mail');
			return;
		}

		// Show loading state
		button.classList.add('is-loading');
		this.hideInlineError(form);

		// Get nonce
		const nonce = form.querySelector('[name="save-account-details-nonce"]').value;

		// AJAX request
		fetch(sense7Account.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'save_account_field',
				nonce: sense7Account.save_account_nonce,
				field_name: fieldName,
				field_value: fieldValue,
			})
		})
		.then(response => response.json())
		.then(data => {
			button.classList.remove('is-loading');

			if (data.success) {
				// Update original value
				input.dataset.originalValue = fieldValue;
				// Hide button and show success icon
				button.classList.remove('is-visible');
				this.showInlineSuccess(form);
				// Hide success icon after 3 seconds
				setTimeout(() => {
					this.hideInlineSuccess(form);
				}, 3000);
			} else {
				// Show error message
				this.showInlineError(form, data.data.message || 'Wystąpił błąd podczas zapisywania');
			}
		})
		.catch(error => {
			button.classList.remove('is-loading');
			this.showInlineError(form, 'Wystąpił błąd podczas zapisywania');
			console.error('Error:', error);
		});
	},

	isValidEmail: function(email) {
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return emailRegex.test(email);
	},

	showInlineSuccess: function(form) {
		const accountField = form.querySelector('.inline-input');
		let successIcon = accountField.querySelector('.inline-input__success');

		if (!successIcon) {
			successIcon = document.createElement('div');
			successIcon.className = 'inline-input__success';
			successIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>';
			accountField.appendChild(successIcon);
		}

		successIcon.classList.add('is-visible');
	},

	hideInlineSuccess: function(form) {
		const successIcon = form.querySelector('.inline-input__success');
		if (successIcon) {
			successIcon.classList.remove('is-visible');
		}
	},

	showInlineError: function(form, message) {
		const accountField = form.querySelector('.inline-input');
		let errorDiv = accountField.querySelector('.inline-input__error');

		if (!errorDiv) {
			errorDiv = document.createElement('div');
			errorDiv.className = 'inline-input__error';
			accountField.appendChild(errorDiv);
		}

		errorDiv.textContent = message;
		errorDiv.classList.add('is-visible');
		accountField.classList.add('has-error');
	},

	hideInlineError: function(form) {
		const accountField = form.querySelector('.inline-input');
		const errorDiv = accountField.querySelector('.inline-input__error');

		if (errorDiv) {
			errorDiv.classList.remove('is-visible');
		}
		accountField.classList.remove('has-error');
	}
};

export const AddressSettings = {

	editElementsClassName: 'address-action--edit',
	deleteClassName: 'address-action--delete',
	setDefaultClassName: 'address-action--set-default',

	init: function() {
		this.bindEvents();
	},

	bindEvents: function() {
		const checkboxes = document.querySelectorAll('.' + this.setDefaultClassName);

		if (!checkboxes.length) {
			return;
		}

		checkboxes.forEach(checkbox => {
			checkbox.addEventListener('change', (e) => {
				this.handleSetDefault(e.target);
			});
		});
	},

	handleSetDefault: function(checkbox) {
		const addressName = checkbox.value;
		const addressType = checkbox.dataset.addressType;
		const isDefault   = checkbox.dataset.isDefault === 'true';

		// If already default, prevent unchecking
		if (isDefault) {
			checkbox.checked = true;
			return;
		}

		// Disable checkbox during request
		checkbox.disabled = true;

		// Show loading state on the address item
		const addressItem = checkbox.closest('.woocommerce-Address__item');
		if (addressItem) {
			addressItem.classList.add('is-loading');
		}

		// Make AJAX call to wc_address_book_make_primary
		fetch(sense7Account.wc_ajax_url.replace('%%endpoint%%', 'wc_address_book_make_primary'), {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				name: addressName,
				nonce: sense7Account.primary_nonce,
			})
		})
		.then(response => {
			// WooCommerce AJAX endpoints don't return JSON by default
			// The endpoint just dies after updating, so any response means success
			this.handleSuccess(addressName, addressType);
		})
		.catch(error => {
			console.error('Error setting default address:', error);
			this.handleError(checkbox, addressItem);
		});
	},

	handleSuccess: function(addressName, addressType) {
		// Find all checkboxes for this address type
		const checkboxes = document.querySelectorAll('.' + this.setDefaultClassName + '[data-address-type="' + addressType + '"]');

		checkboxes.forEach(cb => {
			const wasDefault = cb.dataset.isDefault === 'true';
			const isNowDefault = cb.value === addressName;

			// Update data attribute
			cb.dataset.isDefault = isNowDefault ? 'true' : 'false';

			// Update checked state
			cb.checked = isNowDefault;

			// Re-enable checkbox
			cb.disabled = false;

			// Remove loading state
			const addressItem = cb.closest('.woocommerce-Address__item');
			if (addressItem) {
				addressItem.classList.remove('is-loading');
			}
		});
	},

	handleError: function(checkbox, addressItem) {
		// Revert checkbox state
		checkbox.checked = false;
		checkbox.disabled = false;

		// Remove loading state
		if (addressItem) {
			addressItem.classList.remove('is-loading');
		}

		// Could show error message to user here
		alert('Wystąpił błąd podczas zmiany domyślnego adresu. Spróbuj ponownie.');
	}
}


export default AccountSettings;