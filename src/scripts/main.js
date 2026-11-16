const header = document.querySelector('[data-navbar]');
const menu = document.querySelector('[data-mobile-menu]');
const toggle = document.querySelector('[data-menu-toggle]');

const updateScrollState = () => {
	if (header) header.toggleAttribute('data-scrolled', window.scrollY > 8);
};

const setMenu = (open) => {
	if (!menu || !toggle) return;
	menu.classList.toggle('hidden', !open);
	toggle.setAttribute('aria-expanded', String(open));
};

window.addEventListener('scroll', updateScrollState, { passive: true });
updateScrollState();

menu?.classList.add('hidden');
toggle?.addEventListener('click', () => setMenu(toggle.getAttribute('aria-expanded') !== 'true'));
menu?.querySelectorAll('[data-menu-link]').forEach((link) => link.addEventListener('click', () => setMenu(false)));
document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') setMenu(false);
});
