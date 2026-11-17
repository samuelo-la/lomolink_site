# Project Instructions: Lomolink Consulting Website

Read this file before making any change. If a request conflicts with something here, follow this file and say so.

## What this project is

A static information website for Lomolink Consulting, an education, training and recruitment consultancy. It explains what the company offers, shows the study areas it supports, and has a dedicated section for learners with refugee status. The tone is warm, respectful and professional. The audience is mostly UK-based.

It is a brochure site with one contact form. There is no admin area, no database, no user accounts and no server-side framework.

## Scope limits

- Courses are not taken online. No course delivery, no learner login, no registration, no payments and no application process on this site. Course tiles and pages are information only.
- The only form is the contact form. It collects three things: name, email and message. Do not add fields without being told to.
- The site stores nothing. Messages are emailed to the company and not saved anywhere on the server.
- No analytics, tracking pixels, chat widgets, embeds or third-party scripts. If analytics is ever added, a cookie consent banner is added with it.
- Self-host fonts and icons. No CDN links.
- There is no admin panel. Content changes are made by editing files and uploading a new build.

## Tech stack

- Astro, static output only (`output: 'static'`). No SSR adapters.
- Tailwind CSS 4 through `@tailwindcss/vite`.
- Plain JavaScript in `src/scripts/main.js`. No Alpine, React, Vue or jQuery.
- Fonts from Fontsource packages (Inter and Playfair Display), self-hosted woff2.
- `@astrojs/sitemap` for the sitemap.
- Course data in `src/data/courses.json`.
- One PHP file, `public/contact.php`, handles the form and sends email.

Do not add a package without saying what it is for. Prefer no new dependency over a small one. Commit `package-lock.json`.

## Project structure

```
src/
  layouts/BaseLayout.astro
  components/        Navbar, Footer, Hero, ServiceCard, CourseTile, Steps,
                     CtaBanner, ContactSection, SectionHeading, Button, Container
    icons/           inline SVG icon components
  pages/             index, about, education-training, study-options/[slug],
                     refugee-support, recruitment, contact, thank-you,
                     privacy, 404
  data/              courses.json, site.ts
  scripts/main.js
  styles/global.css
public/              images, robots.txt, .htaccess, contact.php,
                     contact.config.example.php
scripts/check-dist.mjs
docs/design/         page mockups (git-ignored)
docs/reference/      old Laravel code for reference only (git-ignored)
```

## Design tokens

Define these in `src/styles/global.css` inside `@theme`. Use the token names. Never hardcode hex values in components.

| Token | Hex | Use |
|---|---|---|
| navy | #0F2D4A | navbar, hero, partnership banner, headings |
| gold | #E0A93B | primary buttons on dark backgrounds, accents, step numbers |
| teal | #0E7C7B | primary buttons on light backgrounds, icons, links |
| teal-pale | #E6F3F3 | course tile backgrounds |
| cream | #FAF6EF | refugee support section background |
| white | #FFFFFF | default section background |
| dark-grey | #334155 | body text on white and cream |

Text on navy is white. Check contrast on every new colour pairing. Body text must meet WCAG AA.

## Typography

- Headings: Playfair Display (serif), weights 600 and 700.
- Body and UI: Inter (sans-serif), weights 400, 500, 600.
- `font-display: swap`.
- The logo wordmark uses the heading font with a thin gold underline.
- Section headings are about 36px on desktop and 28px on mobile, with a short gold underline (48px wide, 3px tall, centred).
- The hero headline uses a fluid size written as a Tailwind arbitrary value, for example `text-[clamp(2.25rem,3.6vw,3.5rem)]`, with `text-balance`.

## Spacing and shape

- Keep Tailwind's default spacing unit. Never override `--spacing`. Use classes that land on multiples of 8px.
- Sections use `py-12 lg:py-20`.
- Every section, the navbar and the footer use the same `<Container>` component: `max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8`. Never use `px-0` on a layout container.
- Cards and buttons use `rounded-xl`. One soft shadow style, slightly stronger on hover.
- Icons are outline style, 1.5px stroke, teal, inline SVG components. Do not mix icon styles.

## Design references

Page mockups live in `docs/design/`, one image per page, named after the page (`home.webp`, `about.webp`, and so on). Build one page at a time to match its image. Treat the mockup's measurements as proportions, not literal pixels, because the mockups are small. Reuse existing components before creating new ones. If the image and these instructions disagree on colours, fonts or spacing, these instructions win.

Use the company name and wording from the mockup. Contact person, email, phone and website stay as placeholders in `src/data/site.ts` for the owner to fill in. Do not copy names, emails or phone numbers from a mockup. Mockup photos may only be used if the owner confirms the rights, so keep the placeholder until a real image file is supplied.

## Page structure

### Layout
`BaseLayout.astro` holds the head (title, description, Open Graph, canonical), a skip-to-content link, the navbar, `<main id="content">` with a slot, and the footer.

### Navbar
Navy background. Wordmark on the left. Links: Home, About Us, Education & Training, Refugee Support, Recruitment. Gold "Get in touch" button on the right, linking to the contact page. Sticky. After scrolling, `main.js` sets a `data-scrolled` attribute on the header and CSS adds a shadow and reduces vertical padding (not the other way round). Below `lg` the menu is a `<details>`-style or button-driven menu that still shows its links if JavaScript fails. Escape closes it. Links sit in a `<nav>` landmark with a label.

### Homepage sections, in order
1. **Hero.** Navy panel on the left with a small gold uppercase tagline on one line, a serif headline that sits on two lines ("Take the next step" / "towards your future.") from 1024px upward, the paragraph "Personalised guidance to help you explore study options, develop professional skills and find your career direction.", and two buttons side by side from `sm` upward (gold "Explore courses", outlined white "Book a consultation"). Photo on the right with an angled left edge using a Tailwind arbitrary `clip-path`. The text column takes about 46 to 48 percent of the container. The image extends to the right edge of the screen and is positioned relative to the container, so the text and the slanted edge never overlap at any width from 1024 to 2560px. On mobile the image stacks under the text. If the image file is missing, show a pale teal placeholder marked `aria-hidden`.
2. **How we can help.** Three cards: Education guidance, Training and qualifications (award rosette icon), Healthcare recruitment. Icon on the left of the text, title, short text, "Learn more" link with an arrow.
3. **Explore education and training.** Six tiles on teal-pale: Adult Social Care & Healthcare, English & Mathematics, Digital Skills, Business & Management, Early Childhood Education, Education & Training. Three columns on desktop, two on tablet, one on mobile. Teal "View study options" button. Tiles come from `courses.json`.
4. **Support for learners with refugee status.** Cream background, photo on the left, heading, paragraph and teal button on the right. Stacks on mobile.
5. **A clear path forward.** Four numbered steps with gold circles and a connector line on desktop: Tell us your goals, Explore your options, Plan your next steps, Apply with support. Steps stack on mobile.
6. **Partnership banner.** Navy strip, headline, one line of text, gold "Discuss a partnership" button.
7. **Contact section.** "Let's discuss your next step" with contact person, email, phone and website, plus a note that course availability, entry requirements and fees vary by programme. Email is a `mailto:` link and phone is a `tel:` link.
8. **Footer.** Quick links, contact details, privacy notice link, copyright line.

### Other pages
About Us, Education & Training (study areas with a category filter that works through `main.js` and shows everything without it), Study area detail (`/study-options/[slug]`, information only, generated with `getStaticPaths`), Refugee Support, Recruitment, Contact, Thank you (`noindex`), Privacy Notice, 404.

## Components

- `Button.astro` takes `variant` (`gold`, `teal`, `outline`) and `size` (`md`, `sm`). Padding and text size live in the size classes, never in class overrides from the caller. It uses `whitespace-nowrap`. External links get `target="_blank"`, `rel="noopener noreferrer"` and a visually hidden "(opens in a new tab)".
- `Container.astro` as described above.
- `SectionHeading.astro` centred, serif, with the gold underline.
- Components take typed props and have no business logic.
- Every enquiry button links to `enquiryUrl` from `site.ts`, which is the contact page unless an external form link is set later.

## Animation rules

- Scroll reveal uses `IntersectionObserver` in `main.js`. Elements marked `data-reveal` that are already on screen are left alone. Elements below the fold are hidden by a class that `main.js` adds, then revealed as they enter. Fade up by 16px over 500ms, cards staggered by 100ms. Without JavaScript everything is simply visible.
- The connector line in the steps section draws as it enters the viewport.
- Cards and tiles lift 4px with a stronger shadow on hover. Buttons have clear hover, focus and active states.
- Hero text fades up on load and the hero image slides in, using CSS keyframes.
- Durations between 200ms and 600ms, ease-out. Animate only `opacity` and `transform`.
- Everything is disabled under `prefers-reduced-motion: reduce`.
- No auto-playing carousels, no parallax, no scroll hijacking.

## Contact form

Front end on `/contact/`:
- Fields: name, email, message. Visible labels, inline errors linked with `aria-describedby`, a visible focus state, and a line under the button: "We use your details only to reply to your enquiry." with a link to the Privacy Notice.
- The form is a normal HTML form that posts to `/contact.php`. It works without JavaScript.
- `main.js` upgrades it: it sends the form with `fetch` (header `Accept: application/json`), shows the result in an `aria-live` region, and does not reload the page.
- Buttons elsewhere can link to `/contact/?topic=consultation` or `?topic=partnership`. Allow only those two values and use them to prefill the start of the message box with a short sentence. Ignore any other value.

Back end in `public/contact.php` (PHP 8.1+, `declare(strict_types=1)`):
- Accept POST only, otherwise return 405.
- Settings come from `contact.config.php` on the server (site host, recipient, sender address, rate-limit secret). Only `contact.config.example.php` with placeholder values is committed. `contact.config.php` is git-ignored and blocked from direct web access in `.htaccess`.
- Check that the `Origin` or `Referer` host matches the site host.
- Validate: name required, max 100; email required, valid, max 150; message required, 10 to 2000 characters. Trim all values and strip control characters.
- Honeypot field named `website`. If it is filled, pretend success and send nothing.
- Rate limit: at most 5 submissions per hour per visitor. Count by a keyed hash of the IP address, held in a small file in a directory outside the web root, expiring after an hour. Never store a raw IP address and never store message content.
- Email: fixed subject line, plain-text body, `From` is a fixed address on the site's own domain, `Reply-To` is the visitor's address only after it passes validation with every CR and LF removed. Never put visitor input into a subject or any other header.
- Responses: JSON when `Accept: application/json` is present, otherwise a 303 redirect to `/thank-you/` on success or `/contact/?error=1` on failure. Use fixed messages only. Never echo submitted data back. Send `Cache-Control: no-store` and `X-Content-Type-Options: nosniff`.
- Do not log names, emails or messages. Log only generic failure codes.

## Company details and course data

- Company details live in `src/data/site.ts`: name (Lomolink Consulting), contact person, email, phone, website, `enquiryUrl`. Use placeholders for everything except the name until the owner supplies real values. Never hardcode these in components.
- `src/data/courses.json` is an array of study areas. Each has `slug`, `title`, `category`, `icon`, `summary`, `description`. Every course page carries a note that availability, entry requirements and fees vary by programme and should be confirmed through an enquiry. Do not list prices, start dates, accreditation or awarding bodies unless supplied.

## Content and imagery

- Do not invent testimonials, statistics, accreditation claims, partner logos or fee amounts.
- Do not make promises about visas, admissions or job outcomes. Do not imply courses are delivered online.
- Wording about refugee learners is respectful and practical, with no pity language.
- Use only licensed or original photos, through `astro:assets` so they are optimised to WebP, lazy-loaded below the hero, with meaningful `alt` text.
- UK English spelling (personalised, programme, organisation).

## Privacy notice

A short, plain page. It says who runs the site and how to contact them; that the contact form collects name, email and message; that these are used only to reply to the enquiry; that the site does not store them and they arrive by email at the company mailbox, where the company's own retention applies; that nothing is sold or shared for marketing; that a short-lived, hashed record of the visitor's address is used only to limit spam; that the site sets no cookies and uses no tracking; and how to ask for a copy or deletion of data. Mark it as needing review by the business owner before launch. Do not present it as legal advice.

## Security

Every item is required.

**Build output**
- `astro.config.mjs` sets `build.inlineStylesheets: 'never'` and `vite.build.assetsInlineLimit: 0`, so the built pages contain no inline styles and no inline scripts.
- `scripts/check-dist.mjs` runs after the build (`npm run check`) and fails if any HTML file in `dist/` contains an inline `<script>` without `src`, a `<style>` tag, a `style=` attribute, an `on...=` event attribute, a `javascript:` URL, or any resource URL (script, stylesheet, image, font, form action) pointing to another host.
- Never edit `dist/` by hand.

**Server headers in `public/.htaccess`** (Apache or LiteSpeed)
- Redirect HTTP to HTTPS.
- `Options -Indexes`. Deny access to dotfiles, `*.map`, `contact.config.php` and anything else not meant to be public.
- `Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; object-src 'none'; base-uri 'self'; form-action 'self'`
- `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, a `Permissions-Policy` that disables camera, microphone, geolocation, payment and usb.
- `Strict-Transport-Security` with a short max-age while testing, raised to one year later. No `includeSubDomains` unless every subdomain has a valid certificate.
- Long cache lifetimes for hashed files in `/_astro/` only.
- If the host is not Apache or LiteSpeed, write the equivalent rules in a `docs/server-config.md` file.
- Do not add `unsafe-inline`, `unsafe-eval`, a hash or a nonce to the CSP to make something work. Fix the cause.

**Dependencies**
- `npm audit` before each release, fix high and critical findings.
- Commit the lockfile. Use `ignore-scripts=true` in `.npmrc` if the build still works with it.

**Secrets and data**
- No secrets in the repo. Real settings exist only on the server.
- `.gitignore` covers `node_modules`, `dist`, `docs/design`, `docs/reference`, `contact.config.php` and `review.*`.

**Email domain (deployment note)**
- The sending domain needs SPF, DKIM and DMARC records so messages arrive and the domain is harder to spoof.

## Accessibility

- One `h1` per page, headings in order, `nav`, `main` and `footer` landmarks.
- Everything works from the keyboard with a visible `focus-visible` ring.
- Colour is never the only signal.
- Skip-to-content link.
- Respect `prefers-reduced-motion`.

## SEO

- Unique `<title>` and meta description per page. Open Graph tags. Canonical URL.
- Sitemap through `@astrojs/sitemap` and a `robots.txt`.
- `thank-you` and `404` are `noindex`.

## Code conventions

- TypeScript strict mode for Astro files and data.
- Format with Prettier and the Astro plugin if installed; otherwise keep the existing style.
- Internal links are built from a small route helper or constants, never retyped strings.
- Keep pages thin and put repeated markup in components.
- `npm run build` and `npm run check` must both pass before every commit.
- Prefer small, focused changes. Do not rewrite unrelated files.

## Things not to do

- Do not add form fields, accounts, uploads, a database, an admin area or any server-side framework.
- Do not change the colour tokens, fonts or section order without being told to.
- Do not add pages, packages or integrations that are not listed here without asking.
- Do not leave `console.log` or commented-out code behind.
- Do not weaken the CSP or any security header to get something working. Ask first.
- Do not commit real email addresses, passwords or the server config file.

## Deployment notes

- Build locally with `npm run build`, then `npm run check`. Upload the contents of `dist/` by SFTP. `contact.php` is already in `dist/` because it lives in `public/`.
- Create `contact.config.php` on the server by hand from the example file.
- Never upload `node_modules`, `.git`, `docs`, `scripts` or `src`.
- After deploying, test the contact form, test the response headers with an online header checker, and send a test email to confirm it is not marked as spam.

## Build order and progress

The old Laravel project is reference only. Tick each step when it is done and tested.

- [ ] 1. Project setup: Astro, Tailwind, fonts, tokens, `BaseLayout`, `Container`, `Button`, `SectionHeading`, `Navbar`, `Footer`, `main.js`, `site.ts`, `.htaccess`, `check-dist.mjs`, `.gitignore`, `.npmrc`, 404 page
- [ ] 2. Homepage sections as components, fully responsive
- [ ] 3. Scroll animations and hover states with reduced-motion support
- [ ] 4. `courses.json`, study options page with category filter, study area detail pages
- [ ] 5. Contact page, `contact.php`, example config, thank-you page, form upgrade in `main.js`
- [ ] 6. Remaining pages: About, Refugee Support, Recruitment, Privacy Notice
- [ ] 7. SEO, sitemap, robots, accessibility pass, performance pass
- [ ] 8. Security review against the Security section, `npm audit`, `npm run check`, final review

When you finish a task, update this checklist and note anything left unfinished.
