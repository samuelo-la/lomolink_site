import Alpine from '@alpinejs/csp';

Alpine.data('navigation', () => ({
    open: false,
    scrolled: false,

    get headerClass() {
        return this.scrolled ? 'shadow-lg' : 'shadow-none';
    },

    get navClass() {
        return this.scrolled ? 'lg:py-2' : '';
    },

    get menuClass() {
        return this.open ? 'block' : 'hidden';
    },

    handleScroll() {
        this.scrolled = window.scrollY > 8;
    },

    init() {
        this.handleScroll();
    },

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },
}));

window.Alpine = Alpine;

Alpine.start();
