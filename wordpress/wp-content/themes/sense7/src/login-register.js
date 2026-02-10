/**
 * Login/Register Tabs functionality
 */

const LoginRegisterTabs = {
	init: function() {
		const tabs = document.querySelectorAll('.login-register-tabs__tab');
		if (!tabs.length) {
			return;
		}

		this.bindEvents(tabs);
		this.initFromHash();
	},

	bindEvents: function(tabs) {
		tabs.forEach(tab => {
			tab.addEventListener('click', (e) => {
				this.handleTabClick(e.currentTarget);
			});
		});
	},

	handleTabClick: function(clickedTab) {
		const targetTab = clickedTab.dataset.tab;

		// Update tabs
		document.querySelectorAll('.login-register-tabs__tab').forEach(tab => {
			const isActive = tab.dataset.tab === targetTab;
			tab.classList.toggle('is-active', isActive);
			tab.setAttribute('aria-selected', isActive);
		});

		// Update panels
		document.querySelectorAll('.login-register').forEach(panel => {
			const panelType = panel.classList.contains('login-register--login') ? 'login' : 'register';
			const isActive = panelType === targetTab;
			panel.classList.toggle('is-active', isActive);
		});

		// Update URL hash (optional, for deep linking)
		if (history.pushState) {
			history.pushState(null, null, '#' + targetTab);
		}
	},

	initFromHash: function() {
		const hash = window.location.hash.replace('#', '');
		if (hash === 'register') {
			const registerTab = document.querySelector('[data-tab="register"]');
			if (registerTab) {
				this.handleTabClick(registerTab);
			}
		}
	}
};

// Initialize on DOM ready
(function() {
	'use strict';

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			LoginRegisterTabs.init();
		});
	} else {
		LoginRegisterTabs.init();
	}
})();
