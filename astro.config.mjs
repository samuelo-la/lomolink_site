// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
	output: 'static',
	site: 'https://lomolinkconsulting.com',
	// thank-you and 404 are noindex, so keep them out of the sitemap.
	integrations: [sitemap({ filter: (page) => !/\/(thank-you|404)\/?$/.test(new URL(page).pathname) })],
	build: {
		inlineStylesheets: 'never',
	},
	vite: {
		plugins: [tailwindcss()],
		build: {
			assetsInlineLimit: 0,
		},
	},
});
