// Universal Modal Handler
export const Modal = {
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
			input.type = 'text';
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

export default Modal
