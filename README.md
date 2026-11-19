# Lomolink Consulting website

Static brochure website for Lomolink Consulting (education guidance, training support and health and social care recruitment, UK audience), built with [Astro](https://astro.build) and Tailwind CSS 4. It has three forms (contact, candidate registration, employer enquiry), handled by small PHP files. There is no database, admin area or user accounts, and the site stores nothing.

Project rules (stack, design tokens, security, content) live in [.github/copilot-instructions.md](.github/copilot-instructions.md). Read it before making changes.

## Requirements

- Node.js 22.23.4 or newer (see `engines` in `package.json`)
- For the forms on the server: PHP 8.1 or newer (use the newest your host offers) with the `mbstring` and `zip` extensions, working `mail()`, and these settings (cPanel → Select PHP Version → Options): `file_uploads = On`, `upload_max_filesize` at least `8M`, `post_max_size` at least `10M`

## Commands

| Command | What it does |
| --- | --- |
| `npm install` | Install dependencies (install scripts are disabled in `.npmrc`) |
| `npm run dev` | Local development server at http://localhost:4321 |
| `npm run build` | Build the site into `dist/` |
| `npm run check` | Build, then run `scripts/check-dist.mjs`, which fails on inline scripts or styles, event handler attributes, `javascript:` URLs or resources from other hosts |
| `npm run preview` | Serve the built `dist/` locally (static pages only; PHP does not run here) |

Run `npm run build` and `npm run check` before every commit and every upload.

## Editing content

| What | Where |
| --- | --- |
| Contact person, email, phone, website | `src/data/site.ts` (used on every page) |
| Course areas (cards, details, qualifications) | `src/data/courses.json` |
| Navigation and page addresses | `src/data/routes.ts` |
| Page text | `src/pages/*.astro` |
| Photos | `src/assets/images/` (optimised to WebP automatically at build time) |
| Colours and fonts | `src/styles/global.css` (`@theme` tokens) |
| Privacy Notice | `src/pages/privacy.astro` (update `lastUpdated` when you change it) |

Forms: `/contact/` (contact.php), `/recruitment/register/` (candidate.php, with optional CV upload) and `/recruitment/employers/` (employer.php). All three share the security code in `public/form-lib.php` and email the submission; nothing is stored. CVs must be PDF or .docx up to 5 MB and are checked by content (macros, scripts and embedded objects are rejected), then attached to the email as `CV.pdf` or `CV.docx`.

Contact form topics: buttons can link to `/contact/?topic=consultation`, `partnership`, `referral`, `candidate` or `employer` to prefill the start of the message. Other values are ignored.

## Deployment (Apache or LiteSpeed shared hosting)

1. Run `npm run build` and then `npm run check`.
2. Upload the **contents** of `dist/` to the web root (for example `public_html/`) by SFTP. This includes `.htaccess` and `contact.php`. Never upload `node_modules`, `.git`, `docs`, `scripts` or `src`.
3. On the server, copy `contact.config.example.php` to `contact.config.php` in the same folder and fill in:
   - `recipient`: the mailbox that receives enquiries (`peter@lomolinkconsulting.com`)
   - `sender`: a fixed address on the site's own domain, for example `website@lomolinkconsulting.com`
   - `rate_limit_secret`: a long random string, for example from `php -r "echo bin2hex(random_bytes(32));"`
   - `rate_limit_dir`: a writable folder **outside** the web root, for example `/home/ACCOUNT/contact-rate-limit`
4. Email delivery:
   - Add SPF, DKIM and DMARC records for `lomolinkconsulting.com`.
   - If the mailbox is hosted elsewhere (for example Google Workspace or Microsoft 365), set the host's email routing to "Remote Mail Exchanger" (cPanel: Email Routing). Otherwise the server may try to deliver locally and enquiries will not arrive.
5. After uploading:
   - Send a test through each of the three forms, including a candidate registration with a small PDF CV, and check they arrive (and are not marked as spam).
   - Check `https://lomolinkconsulting.com`, `http://` and `www.` all end up on `https://lomolinkconsulting.com`.
   - Check response headers with an online header checker (Content-Security-Policy, X-Frame-Options and the others in `.htaccess`).
   - Check that `/contact.config.php` and `/form-lib.php` return 403.
6. Once HTTPS is confirmed working, raise `Strict-Transport-Security` in `public/.htaccess` from `max-age=300` to `max-age=31536000`, rebuild and upload `.htaccess` again.

### Cloudflare

The live domain is proxied through Cloudflare. Required settings:

- `contact.config.php`: `'behind_cloudflare' => true` (otherwise all visitors share one rate limit). Leave it `false` on any domain not behind Cloudflare.
- SSL/TLS mode **Full (strict)**, with a valid certificate on GO54 (AutoSSL or a Cloudflare Origin Certificate).
- DNS: website records proxied (orange cloud); mail records (`mail`, MX, SPF, DKIM, DMARC) **DNS only** (grey cloud).
- Turn **off** features that inject scripts or change pages, which the strict Content Security Policy blocks or which add tracking: Rocket Loader, Email Address Obfuscation, Web Analytics / Real User Monitoring automatic setup, Zaraz.
- Do not let Cloudflare cache HTML (the default is fine) or override the security headers.
- Cloudflare's address ranges are listed in `public/form-lib.php` (`FORM_CLOUDFLARE_RANGES`). Check https://www.cloudflare.com/ips/ once a year and update if they change.

## Security notes

- Strict Content Security Policy (`default-src 'self'`, no inline scripts or styles). Do not add `unsafe-inline`, hashes or nonces; fix the cause instead.
- No secrets in the repository. `contact.config.php` is git-ignored and blocked from web access by `.htaccess`.
- `contact.php` accepts POST only from this site, validates and length-limits input, uses a honeypot field, rate-limits to 5 messages per hour per visitor using a keyed hash of the IP address (no raw IPs or message content are stored), never puts visitor input in email headers except a validated `Reply-To`, and logs only generic failure codes.
- `npm audit` currently reports 10 "high" findings, all from `picomatch` (ReDoS, GHSA-fc5q-39xc-vjpf), a file-matching library used only at build time by Astro, Vite and Tailwind. No fixed version exists yet, and npm's suggested fix downgrades Astro to 5.x, which would break the site. It does not ship in `dist/` and does not affect visitors. Run `npm audit` before each release and update when a fixed version is published.
