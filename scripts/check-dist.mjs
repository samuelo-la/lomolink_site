import { readFile, readdir } from 'node:fs/promises';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';

const dist = fileURLToPath(new URL('../dist/', import.meta.url));
const violations = [];

async function visit(directory) {
	for (const entry of await readdir(directory, { withFileTypes: true })) {
		const path = join(directory, entry.name);
		if (entry.isDirectory()) await visit(path);
		else if (entry.name.endsWith('.html')) await checkHtml(path);
		else if (entry.name.endsWith('.css')) await checkCss(path);
	}
}

async function checkHtml(path) {
	const html = await readFile(path, 'utf8');
	const tags = html.match(/<([a-z][\w:-]*)(?:\s[^<>]*?)?\s*\/?>/gi) ?? [];

	for (const tag of tags) {
		const name = tag.match(/^<([a-z][\w:-]*)/i)?.[1].toLowerCase();
		const attributes = tag.slice(name.length + 1);
		if (name === 'script' && !/\bsrc\s*=/i.test(attributes)) violations.push(`${path}: inline script`);
		if (/<style\b/i.test(tag)) violations.push(`${path}: style tag`);
		if (/\bstyle\s*=/i.test(attributes)) violations.push(`${path}: style attribute`);
		if (/\bon[a-z]+\s*=/i.test(attributes)) violations.push(`${path}: inline event handler`);
		if (/=\s*["']?\s*javascript:/i.test(attributes)) violations.push(`${path}: javascript: URL`);

		const allowedExternalHref =
			name === 'a' ||
			(name === 'link' && /\brel\s*=\s*["'][^"']*\b(?:canonical|alternate)\b/i.test(attributes)) ||
			(name === 'meta' && /\b(?:property|name)\s*=\s*["']og:/i.test(attributes));
		const externalAttributes = attributes.match(/\b(?:src|href|action|poster)\s*=\s*["'](https?:\/\/|\/\/)/gi) ?? [];
		if (externalAttributes.length && !allowedExternalHref) {
			violations.push(`${path}: external resource URL in <${name}>`);
		}
	}
}

async function checkCss(path) {
	const css = await readFile(path, 'utf8');
	if (/url\(\s*["']?(?:https?:)?\/\//i.test(css)) violations.push(`${path}: external CSS URL`);
	if (/@import\s+(?:url\(\s*)?["']?(?:https?:)?\/\//i.test(css)) violations.push(`${path}: external CSS import`);
}

for (const required of ['.htaccess', '404.html']) {
	try {
		await readFile(join(dist, required));
	} catch {
		violations.push(`dist/${required}: required file is missing`);
	}
}

await visit(dist);
if (violations.length) {
	console.error(['dist check failed:', ...violations].join('\n'));
	process.exit(1);
}
console.log('dist check passed');
