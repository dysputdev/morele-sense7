document.addEventListener('DOMContentLoaded', function() {
	const megamenu_links = document.querySelectorAll('[data-megamenu]');

	megamenu_links.forEach(link => {
		const megamenu_id = link.getAttribute('data-megamenu');
		const megamenu = document.getElementById(megamenu_id);
		const navigation = link.closest('.wp-block-navigation');
		let closeTimeout;

		if (megamenu) {

			const openMegamenu = () => {
				clearTimeout(closeTimeout);
				megamenu.classList.add('open');
				navigation.classList.add('open');
			};

			const closeMegamenu = () => {
				closeTimeout = setTimeout(() => {
					megamenu.classList.remove('open');
					navigation.classList.remove('open');
				}, 50);
			};

			link.addEventListener('mouseenter', openMegamenu);
			link.addEventListener('mouseleave', closeMegamenu);

			// Events dla megamenu
			megamenu.addEventListener('mouseenter', openMegamenu);
			megamenu.addEventListener('mouseleave', closeMegamenu);
		}
	});
})