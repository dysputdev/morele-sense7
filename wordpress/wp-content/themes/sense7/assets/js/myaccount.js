/**
 * My Account page functionality
 * Handles order table sorting and password modal
 */

(function() {
	'use strict';

	const MyAccountOrders = {
		sortOrder: {
			number: 'none',
			date: 'none',
			total: 'none'
		},

		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			const sortButtons = document.querySelectorAll('.myaccount-orders__sort');
			sortButtons.forEach(button => {
				button.addEventListener('click', this.handleSort.bind(this));
			});
		},

		handleSort: function(e) {
			const button = e.currentTarget;
			const sortType = button.dataset.sort;
			const table = button.closest('table');
			const tbody = table.querySelector('tbody');
			const rows = Array.from(tbody.querySelectorAll('tr'));

			// Update sort order
			if (this.sortOrder[sortType] === 'none' || this.sortOrder[sortType] === 'desc') {
				this.sortOrder[sortType] = 'asc';
			} else {
				this.sortOrder[sortType] = 'desc';
			}

			// Reset other sort orders
			Object.keys(this.sortOrder).forEach(key => {
				if (key !== sortType) {
					this.sortOrder[key] = 'none';
				}
			});

			// Update button states
			this.updateSortButtons(sortType);

			// Sort rows
			rows.sort((a, b) => {
				let aValue, bValue;

				switch (sortType) {
					case 'number':
						aValue = parseInt(a.dataset.orderNumber);
						bValue = parseInt(b.dataset.orderNumber);
						break;
					case 'date':
						aValue = parseInt(a.dataset.orderDate);
						bValue = parseInt(b.dataset.orderDate);
						break;
					case 'total':
						aValue = parseFloat(a.dataset.orderTotal);
						bValue = parseFloat(b.dataset.orderTotal);
						break;
				}

				if (this.sortOrder[sortType] === 'asc') {
					return aValue - bValue;
				} else {
					return bValue - aValue;
				}
			});

			// Clear tbody and append sorted rows
			tbody.innerHTML = '';
			rows.forEach(row => tbody.appendChild(row));
		},

		updateSortButtons: function(activeType) {
			const buttons = document.querySelectorAll('.myaccount-orders__sort');
			buttons.forEach(button => {
				const type = button.dataset.sort;
				const svg = button.querySelector('svg');

				button.classList.remove('is-active', 'is-asc', 'is-desc');

				if (type === activeType) {
					button.classList.add('is-active');
					if (this.sortOrder[type] === 'asc') {
						button.classList.add('is-asc');
						svg.style.transform = 'rotate(0deg)';
					} else {
						button.classList.add('is-desc');
						svg.style.transform = 'rotate(180deg)';
					}
				} else {
					svg.style.transform = 'rotate(0deg)';
				}
			});
		}
	};

	// Universal Modal Handler
	const UniversalModal = {
		modals: {},

		init: function() {
			this.bindEvents();
			this.initPasswordModal();
		},

		bindEvents: function() {
			// Open modal buttons
			const openButtons = document.querySelectorAll('[data-action="open-modal"]');
			openButtons.forEach(button => {
				button.addEventListener('click', this.openModal.bind(this));
			});

			// Close modal buttons
			const closeButtons = document.querySelectorAll('[data-action="close-modal"]');
			closeButtons.forEach(button => {
				button.addEventListener('click', this.closeModal.bind(this));
			});

			// Close on overlay click
			const overlays = document.querySelectorAll('.multistore-modal__overlay');
			overlays.forEach(overlay => {
				overlay.addEventListener('click', this.closeModal.bind(this));
			});

			// Close on ESC key
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape') {
					this.closeOpenModals();
				}
			});

			// Password toggle buttons
			const toggleButtons = document.querySelectorAll('.password-toggle');
			toggleButtons.forEach(button => {
				button.addEventListener('click', this.togglePasswordVisibility.bind(this));
			});
		},

		openModal: function(e) {
			e.preventDefault();
			const modalId = e.currentTarget.getAttribute('href').replace('#', '') || e.currentTarget.dataset.modalId;
			const modal = document.getElementById(modalId);

			if (!modal) {
				return;
			}

			modal.style.display = 'flex';
			document.body.style.overflow = 'hidden';

			// Focus first input
			const firstInput = modal.querySelector('input:not([type="hidden"])');
			if (firstInput) {
				setTimeout(() => firstInput.focus(), 100);
			}
		},

		closeModal: function(e) {
			if (e) {
				e.preventDefault();
			}

			const modalId = e?.currentTarget?.dataset?.modalId;

			if (modalId) {
				const modal = document.getElementById(modalId);
				if (modal) {
					this.hideModal(modal);
				}
			} else {
				this.closeOpenModals();
			}
		},

		closeOpenModals: function() {
			const openModals = document.querySelectorAll('.multistore-modal[style*="display: flex"]');
			openModals.forEach(modal => {
				this.hideModal(modal);
			});
		},

		hideModal: function(modal) {
			modal.style.display = 'none';
			document.body.style.overflow = '';

			// Clear forms
			const forms = modal.querySelectorAll('form');
			forms.forEach(form => form.reset());

			// Hide errors
			const errors = modal.querySelectorAll('.multistore-modal__error');
			errors.forEach(error => error.style.display = 'none');
		},

		togglePasswordVisibility: function(e) {
			const button = e.currentTarget;
			const targetId = button.dataset.target;
			const input = document.getElementById(targetId);
			const iconShow = button.querySelector('.icon-show');
			const iconHide = button.querySelector('.icon-hide');

			if (!input) return;

			if (input.type === 'password') {
				input.type = 'text';const confirmPassword = document.getElementById('password_2_modal').value; 
				iconShow.style.display = 'none';
				iconHide.style.display = 'block';
			} else {
				input.type = 'password';
				iconShow.style.display = 'block';
				iconHide.style.display = 'none';
			}
		},

		// Password modal specific logic
		initPasswordModal: function() {
			const form = document.getElementById('password-change-form');
			if (!form) {
				return;
			}

			form.addEventListener('submit', this.handlePasswordSubmit.bind(this));
		},

		validatePassword: function(password) {
			// At least 8 characters
			if (password.length < 8) {
				return 'Hasło musi mieć minimum 8 znaków';
			}

			// At least one uppercase letter
			if (!/[A-Z]/.test(password)) {
				return 'Hasło musi zawierać przynajmniej jedną wielką literę';
			}

			// At least one lowercase letter
			if (!/[a-z]/.test(password)) {
				return 'Hasło musi zawierać przynajmniej jedną małą literę';
			}

			// At least one digit
			if (!/[0-9]/.test(password)) {
				return 'Hasło musi zawierać przynajmniej jedną cyfrę';
			}

			// At least one special character
			if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
				return 'Hasło musi zawierać przynajmniej jeden znak specjalny';
			}

			return null;
		},

		showError: function(message, errorId) {
			const errorDiv = document.getElementById(errorId);
			if (errorDiv) {
				errorDiv.textContent = message;
				errorDiv.style.display = 'block';
			}
		},

		handlePasswordSubmit: function(e) {
			e.preventDefault();

			const currentPassword = document.getElementById('password_current_modal').value;
			const newPassword = document.getElementById('password_1_modal').value;
			const confirmPassword = document.getElementById('password_2_modal').value; 

			// Validate current password
			if (!currentPassword) {
				this.showError('Proszę podać obecne hasło', 'password-error');
				return;
			}

			// Check if passwords match.
			if (newPassword !== confirmPassword) {
				this.showError('Nowe hasła nie są zgodne', 'password-error');
				return;
			}
			
			// Validate new password
			const passwordError = this.validatePassword(newPassword);
			if (passwordError) {
				this.showError(passwordError, 'password-error');
				return;
			}

			// Submit form
			e.target.submit();
		}
	};

	// Account Fields AJAX Handler
	const AccountFields = {
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
				const button = form.querySelector('.account-field__button');

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
			const button = form.querySelector('.account-field__button');
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
			const accountField = form.querySelector('.account-field');
			let successIcon = accountField.querySelector('.account-field__success');

			if (!successIcon) {
				successIcon = document.createElement('div');
				successIcon.className = 'account-field__success';
				successIcon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>';
				accountField.appendChild(successIcon);
			}

			successIcon.classList.add('is-visible');
		},

		hideSuccess: function(form) {
			const successIcon = form.querySelector('.account-field__success');
			if (successIcon) {
				successIcon.classList.remove('is-visible');
			}
		},

		showError: function(form, message) {
			const accountField = form.querySelector('.account-field');
			let errorDiv = accountField.querySelector('.account-field__error');

			if (!errorDiv) {
				errorDiv = document.createElement('div');
				errorDiv.className = 'account-field__error';
				accountField.appendChild(errorDiv);
			}

			errorDiv.textContent = message;
			errorDiv.classList.add('is-visible');
			accountField.classList.add('has-error');
		},

		hideError: function(form) {
			const accountField = form.querySelector('.account-field');
			const errorDiv = accountField.querySelector('.account-field__error');

			if (errorDiv) {
				errorDiv.classList.remove('is-visible');
			}
			accountField.classList.remove('has-error');
		}
	};

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			if (document.querySelector('.myaccount-orders__table')) {
				MyAccountOrders.init();
			}
			UniversalModal.init();
			AccountFields.init();
		});
	} else {
		if (document.querySelector('.myaccount-orders__table')) {
			MyAccountOrders.init();
		}
		UniversalModal.init();
		AccountFields.init();
	}
})();
