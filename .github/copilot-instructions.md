# Project Instructions: Lomolink Consulting Website

Read this file before making any change. If a request conflicts with something here, follow this file and say so.

## What this project is

A static information website for Lomolink Consulting, an education, training and recruitment consultancy. It explains what the company offers, shows the study areas it supports, and has a dedicated section for learners with refugee status. The tone is warm, respectful and professional. The audience is mostly UK-based.

It is a brochure site with three forms (contact, candidate registration, employer enquiry). There is no admin area, no database, no user accounts and no server-side framework.

## Scope limits

- Courses are not taken online. No course delivery, no learner login, no registration, no payments and no application process on this site. Course tiles and pages are information only.
- There are exactly three forms, with exactly the fields listed under "Forms" below (owner decision, 18 November 2026, to collect candidate and employer details for recruitment). Do not add forms or fields without being told to. Each form has an optional phone number field (owner decision, 20 November 2026).
- The only file upload is the optional CV on the candidate form: PDF or .docx, at most 5 MB, checked on the server, attached to the email and never saved.
- The site stores nothing. Submissions (including CVs) are emailed to the company and not saved anywhere on the server.
- The live domain is proxied through Cloudflare (owner decision, 19 November 2026). Cloudflare features that inject scripts or tracking (Rocket Loader, Email Address Obfuscation, Web Analytics, Zaraz) stay off. The rate limit uses `CF-Connecting-IP` only when `behind_cloudflare` is true in the server config and the request comes from a published Cloudflare range.
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
- PHP form handlers in `public/`: `contact.php`, `candidate.php`, `employer.php`, sharing security code in `form-lib.php` (not web-accessible).

Do not add a package without saying what it is for. Prefer no new dependency over a small one. Commit `package-lock.json`.

## Project structure

```
src/
  layouts/BaseLayout.astro
  components/        Navbar, Footer, Hero, ServiceCard, CourseTile, Steps,
                     CtaBanner, ContactSection, SectionHeading, Button, Container
    icons/           inline SVG icon components
  pages/             index, about, education-training,
                     refugee-education-support, student-recruitment, recruitment,
                     recruitment/register, recruitment/employers,
                     contact, thank-you, privacy, 404
  data/              courses.json, site.ts, routes.ts
  scripts/main.js
  styles/global.css
public/              robots.txt, .htaccess, contact.php, candidate.php,
                     employer.php, form-lib.php, contact.config.example.php
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
Navy background. Wordmark on the left. Links: Home, About Us, Education & Training, Refugee Education Support, and a "Recruitment" group that opens two links: Student Recruitment (`/student-recruitment/`) and Care Recruitment (`/recruitment/`) (owner decision, 20 November 2026). The group is a `<details>` dropdown that works without JavaScript; `main.js` closes it on outside click, Escape or a link tap. The mobile menu and footer list both links without grouping (`navLinks` in `routes.ts`). Gold "Get in touch" button on the right, linking to the contact page. Sticky. After scrolling, `main.js` sets a `data-scrolled` attribute on the header and CSS adds a shadow and reduces vertical padding (not the other way round). Below `xl` (1280px) the menu is a `<details>`-style or button-driven menu (owner chose the label "Refugee Education Support", which does not fit beside the logo at 1024px) that still shows its links if JavaScript fails. Escape closes it. Links sit in a `<nav>` landmark with a label.

### Homepage sections, in order
1. **Hero.** Navy panel on the left with a small gold uppercase tagline ("Education, training and health and social care recruitment", owner-approved 20 November 2026; it may wrap onto two lines), a serif headline that sits on two lines ("Take the next step" / "towards your future.") from 1024px upward, the paragraph "Personalised guidance to help you explore study options, develop professional skills and find your career direction.", and two buttons side by side from `sm` upward (gold "Explore courses", outlined white "Book a consultation"). Photo on the right with an angled left edge using a Tailwind arbitrary `clip-path`. The text column takes about 46 to 48 percent of the container. The image extends to the right edge of the screen and is positioned relative to the container, so the text and the slanted edge never overlap at any width from 1024 to 2560px. On mobile the image stacks under the text. If the image file is missing, show a pale teal placeholder marked `aria-hidden`.
2. **How we can help.** Three cards: Education guidance, Training and qualifications (award rosette icon), Health and social care recruitment. Icon on the left of the text, title, short text, "Learn more" link with an arrow.
3. **Explore education and training.** Six tiles on teal-pale: Adult Social Care & Healthcare, English & Mathematics, Digital Skills, Business & Management, Early Childhood Education, Education & Training. Three columns on desktop, two on tablet, one on mobile. Teal "View study options" button. Tiles come from `courses.json`.
4. **Support for learners with refugee status.** Cream background, photo on the left, heading, paragraph and teal button on the right. Stacks on mobile.
5. **A clear path forward.** Four numbered steps with gold circles and a connector line on desktop (inner pages use teal circles, as their mockups show): Tell us your goals, Explore your options, Plan your next steps, Apply with support. Steps stack on mobile.
6. **Partnership banner.** Navy strip, headline, one line of text, gold "Discuss a partnership" button.
7. **Contact section.** "Let's discuss your next step" with contact person, email, phone and website (owner removed the "fees vary by programme" note here and on the Contact page). Email is a `mailto:` link and phone is a `tel:` link.
8. **Footer.** Quick links, contact details, privacy notice link, copyright line.

### Other pages
Student Recruitment (`/student-recruitment/`: the owner's brief copy word for word, including "We act as an education agent for most UK universities", which the owner confirmed; "Discuss your study options" buttons use topic `study`; hero photo `Student Recruitment.jpeg`, owner-supplied), About Us, Education & Training (the six course areas as cards, no category filter, each linking to its own details section further down the same page at `#<slug>`: owner decision, replacing separate `/study-options/[slug]` pages; homepage tiles link to `/education-training/#<slug>`), Refugee Education Support (at `/refugee-education-support/`, menu label "Refugee Education Support"; owner-approved mix of both copy briefs: updated-brief hero text with both buttons, six titled "How we can help" cards (first-brief headings, updated-brief text, sixth card from the first brief), "A welcoming and respectful service" (first brief), "What happens when you get in touch" with "Discuss your study options" (topic `study`), charities banner with "Discuss referrals" (topic `referral`) and the consent note, and the "Take your next step" contact strip (first brief)), Recruitment, Contact, Thank you (`noindex`), Privacy Notice, 404.

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

## Forms

Three forms, each a normal HTML form that works without JavaScript, with visible labels, inline errors linked with `aria-describedby`, a visible focus state, a honeypot field named `website`, and a line under the button linking to the Privacy Notice. `main.js` upgrades them: client-side checks, `fetch` submission (header `Accept: application/json`), result in an `aria-live` region, no page reload.

1. **Contact** (`/contact/` → `/contact.php`): name (required, max 100), email (required, max 150), phone (optional), message (required, 10 to 2000). Line under the button: "We use your details only to reply to your enquiry." Buttons elsewhere can link to `/contact/?topic=<value>`. Allowed values (owner-approved): `consultation`, `study`, `guidance`, `refugee-education`, `partnership`, `referral`, `candidate`, `employer`, and `course-<slug>` for each course area (used by "Enquire about this area"). The list and sentences live in `topicSentences` in `src/data/routes.ts` and reach the form as a `data-topics` attribute. Use them only to prefill the start of the message box with a fixed short sentence. Ignore any other value. Topics never add form fields.
2. **Candidate registration** (`/recruitment/register/` → `/candidate.php`; owner's "Lomolink Care Recruitment Form" brief, 20 November 2026; page title "Register your interest in care work", submit button "Register my interest"): full name (required, max 100), email (required, max 150), phone (optional), town or postcode (required, max 100), areas they can travel to (optional, max 200), preferred role (required, one of: care assistant, support worker, domiciliary care worker, other, open to opportunities), full UK driving licence and access to a car (two separate yes/no questions, required only for domiciliary care; `main.js` shows them only for that role, and without JavaScript they stay visible and optional; the server ignores them for other roles), care experience (required, one of: new to care, has care experience), experience and relevant training (optional, max 1000), full-time or part-time (required, one of: full-time, part-time, either), preferred shifts (at least one of: day shifts, night shifts, weekends, flexible), earliest start date (optional; today up to two years ahead; blank means as soon as possible), CV (optional), consent to share details and CV with employers about suitable roles (required checkbox). Confirmation: "Thank you for registering your interest with Lomolink Consulting. We've received your details. If we identify a suitable opportunity, we'll contact you to discuss the role and next steps." The page asks candidates not to include date of birth, health information or ID documents; identity documents and references are requested later for a specific vacancy.
3. **Employer enquiry** (`/recruitment/employers/` → `/employer.php`): organisation (required, max 150), contact name (required, max 100), email (required, max 150), phone (optional), role (required, max 150), location (required, max 100), shift pattern (required, max 200), essential requirements (required, 10 to 2000).

CV upload rules (`candidate.php`):
- Optional, one file, at most 5 MB. Only PDF and .docx. The type is decided from the file contents, never from the name or the browser's MIME type: PDFs must start with `%PDF-`; .docx must be a real ZIP Office document with `word/document.xml`, no macros (`vbaProject.bin`, `macroEnabled`), no embedded objects or ActiveX, and a sane uncompressed size. PDFs containing `/JavaScript`, `/JS`, `/Launch`, `/EmbeddedFile` or `/RichMedia` are rejected (a basic check; the mailbox's own virus scanning is the main malware defence).
- The file is read from PHP's temporary upload location (checked with `is_uploaded_file`), attached to the email as `CV.pdf` or `CV.docx`, and never written anywhere. The visitor's file name is never used.

Back end, shared by all three handlers through `public/form-lib.php` (PHP 8.1+, `declare(strict_types=1)`; `form-lib.php` refuses direct requests and is blocked in `.htaccess`):
- Accept POST only, otherwise return 405.
- Settings come from `contact.config.php` on the server (site host, recipient, sender address, rate-limit secret). Only `contact.config.example.php` with placeholder values is committed (exception, owner decision: its `recipient` is the public contact address `peter@lomolinkconsulting.com`, which also appears on every page; the rate-limit secret stays a placeholder). `contact.config.php` is git-ignored and blocked from direct web access in `.htaccess`.
- Check that the `Origin` or `Referer` host matches the site host.
- Validate every field against the rules above; choice fields only accept their listed values. Phone (optional, max 30): digits, spaces, `+`, brackets, dots and hyphens only, 7 to 15 digits; shown as "not given" in the email when empty. Trim all values and strip control characters.
- Honeypot field named `website`. If it is filled, pretend success and send nothing.
- Rate limit: at most 5 submissions per hour per visitor, counted across all three forms. Count by a keyed hash of the IP address, held in a small file in a directory outside the web root, expiring after an hour. Never store a raw IP address and never store message content.
- Email: fixed subject line, plain-text body, `From` is a fixed address on the site's own domain, `Reply-To` is the visitor's address only after it passes validation with every CR and LF removed. Never put visitor input into a subject or any other header.
- Responses: JSON when `Accept: application/json` is present, otherwise a 303 redirect to `/thank-you/` on success or back to the form's page with `?error=1#form-error` on failure. Use fixed messages only. Never echo submitted data back. Send `Cache-Control: no-store` and `X-Content-Type-Options: nosniff`.
- Do not log names, emails or messages. Log only generic failure codes.

## Company details and course data

- Company details live in `src/data/site.ts`: name (Lomolink Consulting), contact person, email, phone, website, `enquiryUrl`. The owner supplied real values on 17 November 2026. Never hardcode these in components.
- The domain is `lomolinkconsulting.com` (set as `site` in `astro.config.mjs`).
- `src/data/courses.json` is an array of course areas. Each has `slug` (also the details-section anchor), `title`, `category`, `icon`, `summary` (owner-supplied card text), `description` and `qualifications` (example qualification names taken from the owner's Education & Training mockup, approved by the owner). Course details sections carry no "fees vary by programme" note (owner decision, 18 November 2026; fee notes were removed site-wide). Do not list prices, start dates, accreditation or awarding bodies unless supplied.

## Content and imagery

- Do not invent testimonials, statistics, accreditation claims, partner logos or fee amounts.
- Do not make promises about visas, admissions or job outcomes. Do not imply courses are delivered online.
- Wording about refugee learners is respectful and practical, with no pity language.
- Use only licensed or original photos, through `astro:assets` so they are optimised to WebP, lazy-loaded below the hero, with meaningful `alt` text.
- UK English spelling (personalised, programme, organisation).

## Privacy notice

A short, plain page. It says who runs the site and how to contact them; what each of the three forms collects; that contact and employer details are used only to deal with the enquiry; that candidate details and CVs are used to find suitable roles and shared with employers only with the candidate's consent, which can be withdrawn; that uploaded CVs are checked and emailed, not stored on the site; that the site does not store them and they arrive by email at the company mailbox, where the company's own retention applies; that nothing is sold or shared for marketing; that a short-lived, hashed record of the visitor's address is used only to limit spam; that the site sets no cookies and uses no tracking; and how to ask for a copy or deletion of data. Mark it as needing review by the business owner before launch. Do not present it as legal advice.

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

- Do not add form fields, accounts, uploads (other than the candidate CV described under "Forms"), a database, an admin area or any server-side framework.
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

- [x] 1. Project setup, including `.htaccess` (long cache only for `/_astro/`, pages revalidated, matched by request address so `contact.php` keeps `no-store`), project README and a site favicon. Canonical host is `https://lomolinkconsulting.com` (no www); `.htaccess` redirects HTTP and www there.
- [x] 2. Homepage sections as components, fully responsive (checked at 390, 768, 1024, 1440, 1920). Owner-supplied photos in `src/assets/images/`.
- [x] 3. Scroll animations and hover states with reduced-motion support, used by the shared components on every page (reveal, stagger, steps line, hero keyframes, card lift only on linked cards). Checked with reduced motion emulated.
- [x] 4. `courses.json` and the Education & Training page with course-area cards and same-page details sections (checked at 390, 768, 1024, 1440, 1920). Owner-supplied hero and section photos.
- [x] 5. Contact page, `contact.php`, example config, thank-you page, form upgrade in `main.js`. Front end tested in the browser (prefill, validation, ARIA, no-JS error box). `contact.php` tested on PHP 8.1 with a local SMTP sink: 43 checks (method, origin, honeypot, validation, header injection, line length, JSON/redirect responses, rate limit, hashed records, missing config, generic-only logs) plus an end-to-end browser send. Still to verify on the live host: real mail delivery and SPF/DKIM/DMARC. Error colours use Tailwind's built-in red-700/red-50/red-800 (no project token exists for errors).
- [x] 6. Remaining pages: About, Refugee Support (owner's copy brief text in the mockup layout), Recruitment, Privacy Notice. All checked at the five widths. Privacy Notice shows a visible draft note until the owner reviews it and sets `reviewed = true` in `src/pages/privacy.astro`.
- [ ] 7. SEO, sitemap, robots, accessibility pass, performance pass
- [ ] 8. Security review against the Security section, `npm audit`, `npm run check`, final review

When you finish a task, update this checklist and note anything left unfinished.
