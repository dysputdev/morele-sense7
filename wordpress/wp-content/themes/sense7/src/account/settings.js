import { Modal } from './modal';

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
		this.initAddressModal();
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

		// Store old default address name for swapping
		let oldDefaultName = addressType;

		checkboxes.forEach(cb => {
			const wasDefault = cb.dataset.isDefault === 'true';
			const isNowDefault = cb.value === addressName;

			// Store old default name
			if (wasDefault) {
				oldDefaultName = cb.value;
			}

			// Update data attribute
			cb.dataset.isDefault = isNowDefault ? 'true' : 'false';

			// Update checked state
			cb.checked = isNowDefault;

			// Update checkbox value - woo-address-book swaps the names
			if (wasDefault && !isNowDefault) {
				// Old default gets the name of the new default
				cb.value = addressName;
			} else if (isNowDefault && !wasDefault) {
				// New default becomes the address type (e.g., "billing")
				cb.value = addressType;
			}

			// Re-enable checkbox
			cb.disabled = false;

			// Remove loading state
			const addressItem = cb.closest('.woocommerce-Address__item');
			if (addressItem) {
				addressItem.classList.remove('is-loading');

				// Update data-address-id in edit and delete links
				const editLink = addressItem.querySelector('.address-action--edit');
				const deleteLink = addressItem.querySelector('.address-action--delete');

				if (wasDefault && !isNowDefault) {
					// Old default address now has the new default's name
					if (editLink) editLink.dataset.addressId = addressName;
					if (deleteLink) deleteLink.dataset.addressId = addressName;
				} else if (isNowDefault && !wasDefault) {
					// New default address now has the address type name
					if (editLink) editLink.dataset.addressId = addressType;
					if (deleteLink) deleteLink.dataset.addressId = addressType;
				}
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
	},

	initAddressModal: function() {
		const form = document.getElementById('address-modal-form');
		if (!form) {
			return;
		}

		// Listen for modal open events
		const addressLinks = document.querySelectorAll('[data-modal-id="address-modal"]');
		addressLinks.forEach(link => {
			link.addEventListener('click', (e) => {
				this.handleAddressModalOpen(e.currentTarget);
			});
		});

		// Handle form submit
		form.addEventListener('submit', (e) => {
			e.preventDefault();
			this.handleAddressSubmit(form);
		});
	},

	handleAddressModalOpen: function(trigger) {
		const addressType = trigger.dataset.addressType;
		const addressId = trigger.dataset.addressId || '';

		// Update modal title
		const modal = document.getElementById('address-modal');
		const modalTitle = modal.querySelector('.multistore-modal__title');

		if (addressId && addressId !== addressType) {
			modalTitle.textContent = addressType === 'billing'
				? 'Edytuj adres rozliczeniowy'
				: 'Edytuj adres dostawy';
		} else {
			modalTitle.textContent = addressType === 'billing'
				? 'Dodaj adres rozliczeniowy'
				: 'Dodaj adres dostawy';
		}

		// Set hidden field values
		document.getElementById('address_type').value = addressType;
		document.getElementById('address_name').value = addressId || addressType;

		// Load form fields
		this.loadAddressFormFields(addressType, addressId);
	},

	loadAddressFormFields: function(addressType, addressId) {
		const modal = document.getElementById('address-modal');
		const fieldsContainer = modal.querySelector('.address-modal__fields');
		const loadingDiv = modal.querySelector('.address-modal__loading');

		// Show loading
		fieldsContainer.style.display = 'none';
		loadingDiv.style.display = 'block';

		// AJAX request
		fetch(sense7Account.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'get_address_form_fields',
				nonce: sense7Account.save_address_nonce,
				address_type: addressType,
				address_name: addressId || '',
			})
		})
		.then(response => response.json())
		.then(data => {
			loadingDiv.style.display = 'none';
			fieldsContainer.style.display = 'block';

			if (data.success) {
				fieldsContainer.innerHTML = data.data.html;

				// Initialize select2 if available
				if (typeof jQuery !== 'undefined' && jQuery.fn.selectWoo) {
					jQuery(fieldsContainer).find('select').selectWoo();
				}
			} else {
				this.showModalError(data.data.message || 'Error loading form');
			}
		})
		.catch(error => {
			loadingDiv.style.display = 'none';
			fieldsContainer.style.display = 'block';
			this.showModalError('Error loading form');
			console.error('Error:', error);
		});
	},

	handleAddressSubmit: function(form) {
		const submitButton = form.closest('.multistore-modal').querySelector('button[type="submit"]');

		// Show loading
		if (submitButton) {
			submitButton.classList.add('is-loading');
			submitButton.disabled = true;
		}

		this.hideModalError();

		// Prepare form data
		const formData = new FormData(form);
		formData.append('action', 'save_address');
		formData.append('nonce', sense7Account.save_address_nonce);

		// AJAX request
		fetch(sense7Account.ajax_url, {
			method: 'POST',
			body: new URLSearchParams(formData)
		})
		.then(response => response.json())
		.then(data => {
			if (submitButton) {
				submitButton.classList.remove('is-loading');
				submitButton.disabled = false;
			}

			if (data.success) {
				// Close modal
				const modal = document.getElementById('address-modal');
				if (modal) {
					Modal.hideModal(modal);
				}

				// Reload page
				window.location.reload();
			} else {
				this.showModalError(data.data.message || 'Error saving address');
			}
		})
		.catch(error => {
			if (submitButton) {
				submitButton.classList.remove('is-loading');
				submitButton.disabled = false;
			}
			this.showModalError('Error saving address');
			console.error('Error:', error);
		});
	},

	showModalError: function(message) {
		const errorDiv = document.getElementById('address-modal-error');
		if (errorDiv) {
			errorDiv.textContent = message;
			errorDiv.style.display = 'block';
		}
	},

	hideModalError: function() {
		const errorDiv = document.getElementById('address-modal-error');
		if (errorDiv) {
			errorDiv.style.display = 'none';
		}
	}
}


export default AccountSettings;