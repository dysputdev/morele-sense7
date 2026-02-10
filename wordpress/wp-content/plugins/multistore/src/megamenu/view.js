document.addEventListener('DOMContentLoaded', function() {
	const megamenu_links = document.querySelectorAll('[data-megamenu]');
	
	// Global state management
	let activeTimeout = null;
	let currentActiveItem = null;
	let currentActiveMegamenu = null;
	let isKeyboardNavigation = false;

	// Function to clear all active states
	function clearAllActiveStates() {
		// Clear any pending timeout
		if (activeTimeout) {
			clearTimeout(activeTimeout);
			activeTimeout = null;
		}
		
		// Remove active classes from all navigation items and megamenus
		megamenu_links.forEach(link => {
			const navItem = link.closest('.wp-block-navigation-item');
			const megamenuId = link.getAttribute('data-megamenu');
			const megamenu = document.getElementById(megamenuId);
			
			if (navItem) {
				navItem.classList.remove('is-active');
			}
			if (megamenu) {
				megamenu.classList.remove('open');
				// Reset ARIA attributes
				megamenu.setAttribute('aria-hidden', 'true');
			}
		});
		
		currentActiveItem = null;
		currentActiveMegamenu = null;
	}

	// Function to show megamenu
	function showMegamenu(link, navigationItem, megamenu) {
		// Clear all active states first
		clearAllActiveStates();
		
		// Set new active state
		navigationItem.classList.add('is-active');
		megamenu.classList.add('open');
		megamenu.setAttribute('aria-hidden', 'false');
		
		// Update global state
		currentActiveItem = navigationItem;
		currentActiveMegamenu = megamenu;
	}

	// Function to hide megamenu
	function hideMegamenu(navigationItem, megamenu, delay = 100) {
		if (delay > 0 && !isKeyboardNavigation) {
			// Clear any existing timeout
			if (activeTimeout) {
				clearTimeout(activeTimeout);
			}

			activeTimeout = setTimeout(() => {
				// Only remove if this is still the currently active item
				if (currentActiveItem === navigationItem) {
					navigationItem.classList.remove('is-active');
					megamenu.classList.remove('open');
					megamenu.setAttribute('aria-hidden', 'true');
					currentActiveItem = null;
					currentActiveMegamenu = null;
				}
				activeTimeout = null;
			}, delay);
		} else {
			// Immediate hide for keyboard navigation
			if (currentActiveMegamenu === megamenu) {
				navigationItem.classList.remove('is-active');
				megamenu.classList.remove('open');
				megamenu.setAttribute('aria-hidden', 'true');
				currentActiveItem = null;
				currentActiveMegamenu = null;
			}
			
			// Clear any pending timeout
			if (activeTimeout) {
				clearTimeout(activeTimeout);
				activeTimeout = null;
			}
		}
	}

	// Detect keyboard navigation
	document.addEventListener('keydown', function() {
		isKeyboardNavigation = true;
	});

	document.addEventListener('mousedown', function() {
		isKeyboardNavigation = false;
	});

	megamenu_links.forEach(link => {
		const megamenu_id = link.getAttribute('data-megamenu');
		const megamenu = document.getElementById(megamenu_id);
		const navigationItem = link.closest('.wp-block-navigation-item');

		if (megamenu) {
			// Initialize ARIA attributes
			megamenu.setAttribute('aria-hidden', 'true');
			link.setAttribute('aria-expanded', 'false');
			link.setAttribute('aria-haspopup', 'true');

			// Mouse events
			link.addEventListener('mouseenter', function (event) {
				if (isKeyboardNavigation) return; // Skip mouse events during keyboard navigation
				
				event.preventDefault();
				showMegamenu(link, navigationItem, megamenu);
				link.setAttribute('aria-expanded', 'true');
			});

			link.addEventListener('mouseleave', function (event) {
				if (isKeyboardNavigation) return; // Skip mouse events during keyboard navigation
				
				event.preventDefault();
				
				// Check if mouse moved to megamenu or stayed within navigation item
				if (event.relatedTarget === megamenu || 
					event.relatedTarget === navigationItem ||
					(event.relatedTarget && megamenu.contains(event.relatedTarget)) ||
					(event.relatedTarget && navigationItem.contains(event.relatedTarget))) {
					return;
				}

				hideMegamenu(navigationItem, megamenu);
				link.setAttribute('aria-expanded', 'false');
			});

			// Keyboard events for accessibility (WCAG)
			link.addEventListener('focus', function (event) {
				showMegamenu(link, navigationItem, megamenu);
				link.setAttribute('aria-expanded', 'true');
			});

			link.addEventListener('blur', function (event) {
				// Don't hide if focus moved to megamenu or its children
				setTimeout(() => {
					const focusedElement = document.activeElement;
					if (!megamenu.contains(focusedElement) && focusedElement !== link) {
						hideMegamenu(navigationItem, megamenu, 0); // Immediate hide for keyboard
						link.setAttribute('aria-expanded', 'false');
					}
				}, 10);
			});

			link.addEventListener('keydown', function (event) {
				// Enter or Space key to toggle megamenu
				if (event.key === 'Enter' || event.key === ' ') {
					event.preventDefault();
					
					if (currentActiveMegamenu === megamenu) {
						hideMegamenu(navigationItem, megamenu, 0);
						link.setAttribute('aria-expanded', 'false');
					} else {
						showMegamenu(link, navigationItem, megamenu);
						link.setAttribute('aria-expanded', 'true');
						
						// Focus first focusable element in megamenu
						const firstFocusable = megamenu.querySelector('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
						if (firstFocusable) {
							setTimeout(() => firstFocusable.focus(), 100);
						}
					}
				}
				
				// Escape key to close megamenu
				if (event.key === 'Escape') {
					hideMegamenu(navigationItem, megamenu, 0);
					link.setAttribute('aria-expanded', 'false');
					link.focus(); // Return focus to trigger
				}
			});

			// Mouse events for megamenu
			megamenu.addEventListener('mouseleave', function (event) {
				if (isKeyboardNavigation) return; // Skip mouse events during keyboard navigation
				
				event.preventDefault();
				
				// Check if mouse moved back to the navigation item
				if (event.relatedTarget === navigationItem || 
					(event.relatedTarget && navigationItem.contains(event.relatedTarget))) {
					return;
				}
				
				hideMegamenu(navigationItem, megamenu);
				link.setAttribute('aria-expanded', 'false');
			});

			// Handle focus leaving megamenu
			megamenu.addEventListener('focusout', function (event) {
				// Use setTimeout to allow focus to settle on the new element
				setTimeout(() => {
					const focusedElement = document.activeElement;
					// If focus moved outside megamenu and trigger link, close megamenu
					if (!megamenu.contains(focusedElement) && focusedElement !== link) {
						hideMegamenu(navigationItem, megamenu, 0);
						link.setAttribute('aria-expanded', 'false');
					}
				}, 10);
			});

			// Handle Escape key within megamenu
			megamenu.addEventListener('keydown', function (event) {
				if (event.key === 'Escape') {
					hideMegamenu(navigationItem, megamenu, 0);
					link.setAttribute('aria-expanded', 'false');
					link.focus(); // Return focus to trigger
				}
			});
		}
	});
})
