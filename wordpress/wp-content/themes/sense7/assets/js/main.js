document.addEventListener('DOMContentLoaded', function () {

	// category dropdown
	const categorySelects = document.querySelectorAll('.wc-block-product-categories.is-dropdown select');

	categorySelects.forEach((select) => {
		select.addEventListener('change', function () {
			const url = this.value;
			if (url) {
				window.location.href = url;
			}
		});
	});
});