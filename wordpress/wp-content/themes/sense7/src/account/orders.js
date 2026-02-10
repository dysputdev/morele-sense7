import DataTable from 'datatables.net-dt';

export const Orders = {
	table: null,

	init: function() {
		const ordersTable = document.querySelector('.myaccount-orders__table');

		if (!ordersTable) {
			return;
		}

		// Inicjalizuj DataTable
		this.table = new DataTable(ordersTable, {
			// Wyłącz dodatkowe elementy UI - tylko sortowanie w nagłówkach
			paging: false,
			searching: false,
			info: false,
			ordering: true,
			// Nie zmieniaj struktury DOM - bez wrapperów
			dom: 't',
			// Język dla komunikatów (jeśli będą)
			// language: {
			// 	emptyTable: 'Brak zamówień',
			// 	zeroRecords: 'Nie znaleziono zamówień'
			// }
		});

		// Inicjalizuj filtry
		this.initFilters();
	},

	initFilters: function() {
		const filterButtons = document.querySelectorAll('.myaccount-orders__filter');

		filterButtons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();

				// Pobierz status z atrybutu data-filter-status
				const status = button.dataset.filterStatus;

				// Aktualizuj aktywny filtr
				filterButtons.forEach(btn => btn.classList.remove('is-active'));
				button.classList.add('is-active');

				// Filtruj wiersze
				this.filterByStatus(status);
			});
		});
	},

	filterByStatus: function(status) {
		const rows = document.querySelectorAll('.myaccount-orders__table tbody tr');

		rows.forEach(row => {
			const rowStatus = row.dataset.orderStatus;

			if (status && rowStatus !== status) {
				row.style.display = 'none';
			} else {
				row.style.display = '';
			}
		});
	}
};

export default Orders;
