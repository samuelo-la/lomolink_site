const header = document.querySelector('[data-navbar]');
const menu = document.querySelector('[data-mobile-menu]');
const summary = document.querySelector('[data-menu-summary]');

const updateScrollState = () => {
	if (header) header.toggleAttribute('data-scrolled', window.scrollY > 8);
};

const closeMenu = () => {
	if (!menu || !summary || !menu.open) return;
	menu.open = false;
	summary.focus();
};

window.addEventListener('scroll', updateScrollState, { passive: true });
updateScrollState();

menu?.querySelectorAll('[data-menu-link]').forEach((link) => link.addEventListener('click', closeMenu));
document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') closeMenu();
});

// Scroll reveal: only elements that start below the fold are hidden, and only when motion is allowed.
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if ('IntersectionObserver' in window && !reduceMotion) {
	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (!entry.isIntersecting) return;
				entry.target.classList.remove('reveal-pending');
				observer.unobserve(entry.target);
			});
		},
		{ rootMargin: '0px 0px -8% 0px' },
	);

	document.querySelectorAll('[data-reveal]').forEach((element) => {
		if (element.getBoundingClientRect().top < window.innerHeight) return;
		element.classList.add('reveal-pending');
		observer.observe(element);
	});
}
