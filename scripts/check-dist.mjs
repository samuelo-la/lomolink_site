import { readFile, readdir } from 'node:fs/promises';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';

const dist = fileURLToPath(new URL('../dist/', import.meta.url));
const violations = [];

async function visit(directory) {
	for (const entry of await readdir(directory, { withFileTypes: true })) {
		const path = join(directory, entry.name);
		if (entry.isDirectory()) await visit(path);
		else if (entry.name.endsWith('.html')) {
			const html = await readFile(path, 'utf8');
			const checks = [
				[/<script(?![^>]*\bsrc=)[^>]*>/i, 'inline script'],
				[/<style\b/i, 'style tag'],
				[/\bstyle\s*=/i, 'style attribute'],
				[/\bon[a-z]+\s*=/i, 'event attribute'],
				[/\bjavascript:/i, 'javascript URL'],
				[/<script\b[^>]*\bsrc\s*=\s*["']https?:\/\//i, 'external script URL'],
				[/<link\b[^>]*\brel\s*=\s*["']stylesheet["'][^>]*\bhref\s*=\s*["']https?:\/\//i, 'external stylesheet URL'],
				[/<(?:img|source|video|audio|iframe)\b[^>]*\bsrc\s*=\s*["']https?:\/\//i, 'external media URL'],
				[/<form\b[^>]*\baction\s*=\s*["']https?:\/\//i, 'external form action'],
			];
			for (const [pattern, label] of checks) {
				if (pattern.test(html)) violations.push(`${path}: ${label}`);
			}
		}
	}
}

await visit(dist);
if (violations.length) {
	console.error(violations.join('\n'));
	process.exit(1);
}
console.log('dist check passed');
