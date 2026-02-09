/**
 * My Account page functionality
 * Handles order table sorting and password modal
 */

import { AccountSettings, AddressSettings } from './account/settings';
import { Modal } from './account/modal';
import { Orders } from './account/orders';

(function() {
	'use strict';

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			if (document.querySelector('.myaccount-orders__table')) {
				Orders.init();
			}
			Modal.init();
			AccountSettings.init();
			AddressSettings.init();
		});
	} else {
		if (document.querySelector('.myaccount-orders__table')) {
			Orders.init();
		}
		Modal.init();
		AccountSettings.init();
		AddressSettings.init();
	}
})();
