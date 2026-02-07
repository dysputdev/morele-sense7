export const Orders = {
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

export default Orders;
