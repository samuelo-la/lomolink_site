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
