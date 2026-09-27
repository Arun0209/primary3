# Primary Infotech — Premium Web Design & Development Agency Website

A server-rendered **PHP 8 + MySQL** website with a secure admin CMS. No React, no build step. It runs on ordinary cPanel / shared hosting.

- **Public site:** Home, AI Solutions, Case Studies (with AJAX filters), three case study pages, About, Get Started / Contact, Privacy, Terms, plus custom 404 and 500/maintenance pages
- **Admin (`/admin/`):** dashboard with charts, case study CMS, generic CRUD for 11 content types, contact inbox, site settings, SEO settings and a media library
- **Stack:** PHP 8.1+ · PDO (MySQL/MariaDB; SQLite for local preview) · HTML5 · CSS3 · vanilla JS. There are no front-end dependencies. Icons are inline SVG and visuals are generated SVG.

---

## 1. Quick start

### A. cPanel / shared hosting (MySQL)

1. Upload the folder contents to `public_html/` (or to a sub-folder).
2. In cPanel, open **MySQL® Databases**. Create a database and a user, then grant the user ALL PRIVILEGES.
3. Copy `.env.example` to `.env` and fill in `APP_URL`, `APP_KEY` (a long random string) and the `DB_*` values.
   **Recommended:** put `.env` **one level above** `public_html` (for example `/home/USER/.env`). The loader looks there first, so your credentials are never inside the web root.
4. Make `storage/` and `uploads/` writable (755 or 775).
5. Visit `https://your-domain.com/install.php` and create the admin account.
6. **Delete `install.php`.** It is already locked after installation, but deleting it is safer.
7. Sign in at `https://your-domain.com/admin/`.

> Sub-folder install: set `APP_URL=https://domain.com/sub` and `RewriteBase /sub/` in `.htaccess`.

### B. Command line

```bash
cp .env.example .env            # edit DB_* values
php database/install.php "Your Name" you@company.com 'StrongPassword123'
```

### C. Zero-config local preview (SQLite)

```bash
cp .env.example .env
# set: DB_DRIVER=sqlite  APP_URL=http://127.0.0.1:8080  APP_DEBUG=true  MAIL_DRIVER=log
php database/install.php "Admin" admin@example.com 'ChangeMe!2026'
php -S 127.0.0.1:8080 server.php
```

`server.php` reproduces the `.htaccess` routing and blocking rules for PHP's built-in server.

---

## 2. Information architecture

| URL | Page |
|---|---|
| `/` | Home: hero with an AI network animation, client marquee, services, tech ticker, metrics, featured work, CTA |
| `/ai-solutions/` | Agentic workflow diagram, 4 agent features, 6 automation cards, tech stack, sticky 4-step process |
| `/case-studies/` | Filterable list: All / AI Agents / Automation / Web Apps. Uses AJAX, with `?category=` as the no-JS fallback |
| `/case-studies/{slug}/` | Challenge → Solution → Technology → AI Architecture → Results → Testimonial → Next project |
| `/about/` | Story, founders, animated company timeline |
| `/contact/` | Enquiry form (AJAX), contact details, FAQ. **`/get-started/` redirects here with a 301** |
| `/privacy-policy/`, `/terms/` | Legal pages. Content is edited in Settings → Legal |
| `/sitemap.xml`, `/robots.txt` | Generated dynamically |
| `/api/contact`, `/api/case-studies` | JSON endpoints, reachable only through the front controller |

---

## 3. Folder structure

```
.htaccess            routing, private-folder blocking, caching, compression
index.php            front controller / router
install.php          one-time web installer (self-locking)
server.php           router for `php -S` (development only)
config/config.php    loads .env (above web root first), returns config array
includes/            bootstrap, db (PDO), helpers, security, seo, icons, visuals,
                     repository (read queries), uploads, mailer, navbar, footer
layouts/main.php     HTML shell: <head>, SEO, header, footer
components/          button, section-heading, page-hero, breadcrumb, service-card,
                     case-study-card, team-card, metric, marquee, faq, cta,
                     hero-network, workflow, process, dashboard-mock, empty-state, logo
pages/               home, ai-solutions, case-studies, case-study, about, contact,
                     legal, 404, 500, sitemap, robots
api/                 contact.php, case-studies.php
admin/               dashboard, CRUD, case study editor, messages, settings, media, profile
admin/_inc/          admin bootstrap/auth, layout, resource definitions, form builder
assets/css|js|img    main.css (design system), admin.css, main.js, admin.js, head.js
database/            schema.mysql.sql, seed.php, installer.php, install.php (CLI)
storage/             logs, installed.lock, sqlite db (web access denied)
uploads/             media (PHP/script execution disabled)
```

Every private folder has its own `.htaccess` that denies access, and the root `.htaccess` blocks them as well.

---

## 4. Database

`database/schema.mysql.sql` defines InnoDB utf8mb4 tables with foreign keys.

| Table | Purpose |
|---|---|
| `users` | Admin accounts (bcrypt/argon via `password_hash`) |
| `login_attempts` | Login throttling |
| `site_settings` | All editable copy, grouped by page |
| `services` | Home services, agent features, automation cards and process steps, selected by `section` |
| `metrics` | Counters. The `animate` flag is set only for real statistics |
| `clients` | Logo marquee |
| `case_study_categories` | Filter categories |
| `case_studies` | Case study content, SEO fields, publish/feature flags, ordering |
| `case_study_metrics` | KPIs (FK, cascade delete) |
| `case_study_images` | Gallery (FK, cascade delete) |
| `testimonials` | Quotes, optionally linked to a case study (FK, set null) |
| `team_members`, `timeline`, `technology_stack`, `social_links`, `faqs` | Page content |
| `seo_meta` | Per-page title, description, OG image and noindex overrides |
| `media` | Uploaded files with a WebP sibling, dimensions and alt text |
| `contact_submissions` | Enquiries: status (new/read/contacted), notes, IP, user agent |

All queries use PDO prepared statements. Identifiers in the generic helpers are checked against a strict whitelist pattern.

---

## 5. Admin guide

- **Dashboard:** totals, a 14-day enquiry chart, pipeline and category meters, recent enquiries.
- **Case Studies:** create, edit, delete, publish/unpublish, feature/unfeature, drag to reorder. Each case study has a metrics repeater, testimonial, gallery (multi-upload with captions), SEO title/description and OG image.
- **Services & Process / Metrics / Team / Timeline / Technologies / Client logos / Testimonials / FAQs / Social / SEO / Categories:** all use the same CRUD screens, with search, filters, visibility switches and drag-and-drop ordering.
- **Contact Messages:** filter by status, search, bulk actions, detail view with status and internal notes. Opening an unread message marks it as read.
- **Site Settings:** every piece of public copy, organised by page. In headings, a **new line** starts a new animated line and `*asterisks*` apply the gradient accent.
- **Media Library:** drag-and-drop upload, automatic WebP copies, alt text, copy path, delete.

---

## 6. Security

- PDO prepared statements everywhere. Output is escaped with `htmlspecialchars`.
- CSRF tokens on every POST, including AJAX. Logout also requires POST + CSRF.
- Sessions: `httponly`, `SameSite=Lax`, `secure` on HTTPS, strict mode, ID regeneration at login, idle timeout, user-agent fingerprint.
- Login throttling by email and by IP (configurable). Dummy hash verification avoids user enumeration.
- Uploads: MIME is detected with finfo against a whitelist, checked with `getimagesize` and dimension limits, and capped in size. Files get random names and are re-encoded with GD (which strips payloads). SVG is rejected. PHP execution is disabled in `/uploads`.
- Security headers: CSP (`script-src 'self'`, no inline JS), X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy. Admin pages also send `noindex` and `no-store`.
- Contact form spam protection: honeypot, HMAC-signed time trap (at least 3 s), per-IP rate limit (5 per hour), link limit, server-side validation.
- `.env` is loaded from above the web root. Dotfiles, `.sql`, `.log` and `.sqlite` files are blocked.
- Errors are logged to `storage/logs`. Visitors see a branded 500 page, or a 503 maintenance page if the database is down.

**Production checklist:** set `APP_DEBUG=false`, set a unique `APP_KEY`, enable HTTPS (uncomment the redirect in `.htaccess`), delete `install.php`, and configure SMTP.

---

## 7. SEO

Every page has a unique title and description (overridable per page in the admin), a canonical URL, Open Graph and Twitter/X tags, and a JSON-LD `@graph` containing:
`ProfessionalService` (organization and local business), `WebSite`, `BreadcrumbList`, `ItemList` of `Service`, `Article` for case studies, `CollectionPage`, `AboutPage` + `Person`, `ContactPage` + `FAQPage`.
Pages use semantic HTML with one `<h1>` each, clean URLs with trailing slashes (301 enforced), a dynamic sitemap and robots.txt, and a default 1200×630 OG image.

---

## 8. Performance

- No framework and no build. About 7 KB of gzipped JS in one deferred file, plus a 1-line head script. One CSS file (about 18 KB gzipped). The home page HTML is about 10 KB gzipped.
- Animations use `transform`, `opacity`, `clip-path` and `stroke-dashoffset` only. Driven by IntersectionObserver and rAF. Continuous animations pause when off-screen.
- Visuals and icons are inline SVG, so there are no image requests. Uploaded images are served through `<picture>` with WebP, `loading="lazy"`, `decoding="async"` and explicit dimensions.
- `.htaccess`: gzip/brotli, a 1-year immutable cache for assets, cache-busting `?v=` query strings.
- Queries are memoised per request, and settings load in a single query.
- Fonts: Google Fonts (Inter + Space Grotesk) with `display=swap` and preconnect. For the best score, self-host the WOFF2 files in `assets/fonts/` and update `layouts/main.php`.

## 8.5 Visual system — colour, gradient, glow

Everything visual is driven by tokens at the top of `assets/css/main.css`, so the whole site can be re-themed from one block.

**Foundation (no section is pure black twice in a row):**

| Token | Value | Used for |
|---|---|---|
| `--bg-primary` | `#070707` | Hero, default sections |
| `--bg-secondary` | `#0D0D0F` | Alternating sections (`.section--alt`) |
| `--bg-tertiary` | `#121216` | Raised blocks |
| `--bg-tint` | `#08090E` | Blue-tinted sections (`.section--tint`, ticker band) |
| `--surface-primary` / `--surface-secondary` | `#111114` / `#17171B` | Cards and panels |
| `--text-primary` / `--text-secondary` | `#F5F5F5` / `#A7A7AE` | Body copy |
| `--text-soft` | `#8D8D96` | Small labels — passes AA on black |
| `--text-muted` | `#6F6F78` | Decorative only (large numerals, ghost text) |
| `--border-subtle` / `--border-hover` | `rgba(255,255,255,.10)` / `.20` | Card and input borders |

**Accent (used sparingly — CTAs, active nav, numbers, icons, links, hover states):**
`--accent-primary #8B5CF6` · `--accent-secondary #6366F1` · `--accent-bright #A78BFA` · `--accent-cyan #22D3EE`.
To rebrand the accent, change these four values (and `--accent-rgb`); every gradient, glow and highlight follows.

**Atmospheric light** — soft radial gradients that read as light coming from behind the content, never as a coloured background:
`--atmos-hero`, `--atmos-section`, `--atmos-left`, `--atmos-right`, `--atmos-cta`, `--atmos-footer`.
The hero and inner-page light sources drift slowly between roughly 56% and 71% horizontally over 16 seconds (`@keyframes light-drift`, driven by the registered `--gx` / `--gy` custom properties), so the glow breathes instead of sitting still.

**Glow** — `--glow-xs` through `--glow-xl` plus `--glow-ring`: wide, low-opacity shadows on CTAs, the hero visual, active cards and the CTA block. No neon borders anywhere.

**Section rhythm** — tonal steps between sections, each seam marked by a one-pixel gradient hairline. Cards, feature blocks, tech columns and info cards carry a pointer-tracked spotlight; where the browser supports `:has()`, the whole section's light lifts while one of its cards is hovered.

## 9. Motion & accessibility

Motion includes: cinematic hero load sequence, masked line reveals, fade/blur-up reveals, clip-path image reveals, counters, marquee and ticker, a sticky process with an active step, an animated timeline fill, workflow data pulses, card spotlight, hover glow, magnetic CTAs, custom cursor ("View Project →"), a scroll progress bar, page transitions and an animated FAQ.

- **`prefers-reduced-motion`** turns off parallax, cursor, magnetic effects, floating elements and marquees. Content appears instantly.
- **Tablet and mobile:** the cursor and magnetic effects are off, parallax is off and floating is reduced. The workflow diagram switches to a vertical layout.
- Accessibility: skip link, visible focus rings, keyboard-trapped mobile menu (Esc closes it), ARIA labels, labelled form fields with announced errors, and live regions for filters and forms. Status is never shown by colour alone. Contrast meets WCAG AA.

## 10. Email

Set `MAIL_DRIVER=smtp` together with the `SMTP_*` values (for example cPanel mail, Google Workspace or SES). The built-in client supports STARTTLS/SSL and AUTH LOGIN. `MAIL_DRIVER=mail` uses PHP `mail()`. `log` writes messages to `storage/logs` for development. If a notification fails, the enquiry is still saved and the failure is logged.

## 11. Nginx (instead of Apache)

```nginx
root /var/www/site;
index index.php;
location ~ ^/(config|includes|components|layouts|pages|database|storage|admin/_inc)/ { deny all; }
location ~ /\.(?!well-known) { deny all; }
location ~* \.(sql|log|lock|sqlite|env)$ { deny all; }
location ~* ^/uploads/.*\.(php|phtml|phar|html?|svg|js)$ { deny all; }
location ~* \.(css|js|svg|png|jpe?g|webp|avif|gif|woff2?)$ { expires 1y; add_header Cache-Control "public, immutable"; }
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass unix:/run/php/php8.3-fpm.sock; }
```

---

## 12. QA performed

Tests were run on PHP 8.4 using the SQLite driver:

- Crawled every internal link and asset: no broken URLs, one `<h1>` per page, no console errors.
- Checked for horizontal overflow at 360, 390, 430, 768, 1024, 1440 and 1920 px: none.
- Contact form: client and server validation, CSRF rejection (419), forged or too-fast time trap (422), honeypot, rate limit, DB storage, non-JS fallback, success state.
- Admin: 42 screens render without warnings. Tested login, bad password, bad CSRF, throttling lockout, create/validate/toggle/reorder/delete, case study save, unpublish (the page returns 404), settings save, upload with WebP generation, rejection of a disguised PHP file, rejection of path traversal, message status and notes.
- Tested the installer on a fresh copy.

**Not verified in the build environment:** a live MySQL server was not available there. The schema and all queries are written for MySQL 5.7+/MariaDB 10.3+ and were reviewed for strict mode and `ONLY_FULL_GROUP_BY`. Run the installer on your MySQL host and click through once. Lighthouse was not run in the sandbox either, so run it against the deployed site.
