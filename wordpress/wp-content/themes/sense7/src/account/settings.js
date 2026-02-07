export const AccountSettings = {
	init: function() {
		const forms = document.querySelectorAll('.edit-account--display-name, .edit-account--email');
		if (!forms.length) {
			return;
		}

		this.bindEvents(forms);
	},

	bindEvents: function(forms) {
		forms.forEach(form => {
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
				this.hideSuccess(form);
				this.hideError(form);
			});

			// Handle form submit
			form.addEventListener('submit', (e) => {
				e.preventDefault();
				this.handleSubmit(form);
			});
		});
	},

	handleSubmit: function(form) {
		const input = form.querySelector('input[type="text"], input[type="email"]');
		const button = form.querySelector('.inline-input__button');
		const fieldName = input.name;
		const fieldValue = input.value.trim();

		// Basic validation
		if (!fieldValue) {
			this.showError(form, 'To pole jest wymagane');
			return;
		}

		// Email validation
		if (input.type === 'email' && !this.isValidEmail(fieldValue)) {
			this.showError(form, 'Podaj poprawny adres e-mail');
			return;
		}

		// Show loading state
		button.classList.add('is-loading');
		this.hideError(form);

		// Get nonce
		const nonce = form.querySelector('[name="save-account-details-nonce"]').value;

		// AJAX request
		fetch(sense7MyAccount.ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'save_account_field',
				nonce: nonce,
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
				this.showSuccess(form);
				// Hide success icon after 3 seconds
				setTimeout(() => {
					this.hideSuccess(form);
				}, 3000);
			} else {
				// Show error message
				this.showError(form, data.data.message || 'Wystąpił błąd podczas zapisywania');
			}
		})
		.catch(error => {
			button.classList.remove('is-loading');
			this.showError(form, 'Wystąpił błąd podczas zapisywania');
			console.error('Error:', error);
		});
	},

	isValidEmail: function(email) {
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return emailRegex.test(email);
	},

	showSuccess: function(form) {
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

	hideSuccess: function(form) {
		const successIcon = form.querySelector('.inline-input__success');
		if (successIcon) {
			successIcon.classList.remove('is-visible');
		}
	},

	showError: function(form, message) {
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

	hideError: function(form) {
		const accountField = form.querySelector('.inline-input');
		const errorDiv = accountField.querySelector('.inline-input__error');

		if (errorDiv) {
			errorDiv.classList.remove('is-visible');
		}
		accountField.classList.remove('has-error');
	}
};

export default AccountSettings;