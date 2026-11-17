// @ts-check
import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
	output: 'static',
	site: 'https://lomolinkconsulting.com',
	integrations: [sitemap()],
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
