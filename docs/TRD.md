# Technical Requirements Document: atomservices.org

Last updated: 2026-10-26, inner pages and logos added (launch: 2026-10-27)

## 1. Purpose and scope

atomservices.org is the company website of Atom Services (Pvt) Ltd: a homepage (products, Atomshop ecosystem,
GITEX 2026 meeting desk, leadership), a services page (client work and project enquiries), About, Contact,
FAQs, a privacy policy and terms of use.

This document lists every requirement, how it is built, and the test that proves it. **Every change or bug fix
must update this document and add or update tests** (see section 6).

## 2. Architecture

| Layer | Implementation |
|---|---|
| Server | Apache + PHP 8.2 (XAMPP locally, `/atomservices/`; live at the web root) |
| Pages | `index.php`, `services.php`, `about.php`, `contact.php`, `faqs.php`, `privacy-policy.php`, `terms.php`, `404.php`, with shared `includes/header.php`, `footer.php`, `team.php` (leadership, used on home and About), `cta_band.php` and `form_parts.php` |
| Settings | `includes/site.php` (public constants and helpers, including company details: a `null` detail such as the SECP number is left off the site, never shown as a placeholder). Secrets live in `includes/config.php`, which is gitignored |
| Forms | `forms/contact.php` (endpoint) → `includes/contact_handler.php` (pure logic) → MySQL `contact_messages` + SMTP via PHPMailer |
| Front end | `assets/css/main.css`, `assets/js/lib.js` (pure helpers), `assets/js/main.js` (DOM behaviour). No frameworks, no third-party scripts |
| Fonts | Self-hosted WOFF2 in `assets/fonts/` (one static file per weight) |
| Images | `tools/build-images.php` builds team photos (500×500 JPEG + WebP), `og-image.png` and `apple-touch-icon.png`. `tools/build-logos.php` builds product logos and symbol-only marks (PNG + WebP) from `tools/brand-sources/` into `assets/img/brands/`, shown with `brand_img()` |
| Server rules | `.htaccess`: HTTPS, private folders blocked, 404 page, redirects, caching, security headers |

## 3. Functional requirements

Status: ✅ done and tested · 🟡 done, needs a manual step at deploy · ⛔ not applicable

| ID | Requirement | Implementation | Tests | Status |
|---|---|---|---|---|
| F1 | Works on mobile | Responsive CSS (breakpoints at 960/860/760/520/480/420 px), safe-area insets | e2e on Pixel 7 and iPhone 14: no sideways scroll, menu, forms | ✅ |
| F2 | Every link works | Relative internal links, anchors checked against IDs | `PagesTest::testEveryInternalLinkAndAssetResolves`, `testInPageAnchorsPointToExistingIds`; `npm run test:links` (external) | ✅ |
| F3 | Loads in under 3 s | Self-hosted fonts (removed 2 s+ Google Fonts blocking), compressed images, lazy team photos, deferred JS, 1-year caching with `?v=` cache busting | e2e Navigation Timing < 3000 ms; Lighthouse mobile: Performance 99, LCP 2.1 s | ✅ |
| F4 | Compressed images | Team photos 1.1 MB → 9–37 KB each (Mudassar Abbas photo replaced 2026-10-26: 1.9 MB → 30 KB JPEG / 15 KB WebP); unused old-template images removed. Team photo URLs carry `?v=<file time>` so a replaced photo beats the one-year cache | `PagesTest::testImagesAreCompressed` (every image < 120 KB) | ✅ |
| F5 | HTTPS padlock | `.htaccess` forces HTTPS (except localhost), canonical host, HSTS over HTTPS; `.well-known` left open for certificates; no mixed content | `ServerRulesTest::testWellKnownIsNotBlocked` | 🟡 SSL certificate must be active on the host |
| F6 | Works in Chrome and Safari | Standard CSS, static font files (variable fonts rendered thin in WebKit), custom `select` styling | Full e2e suite in Chromium and WebKit, desktop and phone | ✅ (WebKit engine; real iPhone check recommended) |
| F7 | No typos | Copy reviewed; British spelling used consistently (enrol, centred, catalogues) | Manual review | ✅ |
| F8 | WhatsApp button | Floating button plus footer and form links → `wa.me/16467260635` with a prefilled message | `SiteHelpersTest::testWhatsappUrl…`; e2e "WhatsApp button" | ✅ |
| F9 | Submitted to Google | `sitemap.xml`, `robots.txt`, canonical URLs, Organization JSON-LD | `ServerRulesTest::testSitemap…`, `testRobots…` | 🟡 Submit the sitemap in Google Search Console |
| F10 | Link previews | Open Graph and Twitter card tags, 1200×630 `og-image.png` | `PagesTest::testSeoAndLinkPreviewTags`, `testOgImageIsTheRightSize` | ✅ |
| F11 | Payment page | Not part of this site (payments happen on atomshop.pk / atompay.shop) | — | ⛔ |
| F12 | Cookie banner | Simple notice; dismissal stored in `localStorage` | e2e "cookie banner shows once and remembers dismissal" | ✅ |
| F13 | Site search | Header button, `/` or Ctrl+K; client-side ranked search over `includes/search_index.php` | JS unit tests (ranking, accents, AND matching); `SiteHelpersTest::testSearchIndexAnchorsMatchPageIds`; e2e search | ✅ |
| F14 | Back-to-top button | Appears after 600 px of scrolling | e2e "back-to-top and scroll progress bar" | ✅ |
| F15 | Mobile menu | Hamburger below 1100 px (six links stopped fitting beside the logo at tablet widths); closes on link tap, Escape, or resize | e2e "mobile menu…" (phones), "desktop nav…" | ✅ |
| F16 | Loading animation | Brief atom preloader removed as soon as the DOM is ready; button spinner while a form sends; hidden without JS | e2e (preloader removed), form tests | ✅ |
| F17 | Hover states | Cards, links, chips, buttons, nav underline, footer | e2e "hover states change appearance" | ✅ |
| F18 | Scroll progress bar | 3 px amber bar, `requestAnimationFrame`-throttled | JS unit test `scrollProgress`; e2e | ✅ |
| F19 | Copy button | Copies the email; falls back to `execCommand` when the Clipboard API is unavailable (http) | e2e "copy button…" (checks clipboard contents in Chrome) | ✅ |
| F20 | Skip to content | First focusable element; jumps to `<main>` | e2e "skip link…" (Chrome; Safari only tabs to links with a system setting) | ✅ |
| F21 | Password visibility toggle | The site has no password fields | — | ⛔ |
| F22 | UTM tracking | Inbound: first-touch `utm_*` kept in `sessionStorage`, sent with forms, stored in the message and email. Outbound: product links tagged `utm_source=atomservices.org` | JS unit tests `parseUtm`/`mergeUtm`; `ContactHandlerTest::testUtm…`; `ContactDatabaseTest` (stored in DB); e2e (sent with form, survives page change) | ✅ |
| F23 | Form success states | Success panel with focus, "send another" | e2e "meeting form success state…", "UTM campaign survives…" | ✅ |
| F24 | Form error states | Inline per-field errors (`aria-invalid`, `aria-describedby`), server errors mapped to fields, network/server failure messages, input kept | Unit `contact_validate`/`contact_process`; integration endpoint 422/405; e2e error tests | ✅ |
| F25 | Forms work before or without JavaScript | Plain POST is redirected (303) back to the form with a status message | Unit `contact_redirect_url`/`contact_status_message`; `ContactEndpointTest::testPlain…`; e2e "forms still work without JavaScript" | ✅ |
| F26 | Spam protection | Hidden honeypot field; bots get a fake success | Unit `testHoneypot…`, `testSpamLooks…`; integration | ✅ |
| F27 | Contact data saved even if email fails | DB insert is authoritative; `email_sent` flag | Unit `testEmailFailureStillSucceedsWhenSaved`; `ContactDatabaseTest` against real MySQL | ✅ |
| F28 | About page | Story, values, timeline with product marks, shared leadership section, company details (unconfirmed rows hidden), GITEX call to action | `PagesTest` (all checks), `testUnconfirmedCompanyDetailsAreHidden`; e2e nav and load tests | ✅ |
| F29 | Contact page | Head office and per-product contact cards with logos and WhatsApp links, office hours, general enquiry form (`form=enquiry`, topic whitelist, message required) | Unit `testEnquiry…`; `ContactEndpointTest::testEnquiry…`, `testPlainEnquiryPost…`; JS unit; e2e "contact page form…", "contact page copy button…" | ✅ |
| F30 | FAQs | Accordion (`<details>`), topic list, FAQPage structured data generated from the same list | `PagesTest::testFaqStructuredDataMatchesQuestions`; e2e "FAQ answers open and close…" | ✅ |
| F31 | Privacy policy and terms | `privacy-policy.php`, `terms.php`, linked from the footer and every form. The privacy policy describes what the site actually does (no analytics, essential storage, UTM tags, IP stored with submissions) | `PagesTest`; e2e "footer links reach the legal pages" | 🟡 Legal review before relying on them |
| F32 | Product logos | Atomshop, AtomPay, Atom Seller, AtomBrand, Mark Attendance and Publishing Guru logos on the homepage, About and Contact, always on a white tile so they read in dark mode | `SiteHelpersTest::testBrandImageHelper`, `testEveryBrandLogoHasPngAndWebp`; `PagesTest::testProductLogosAreShown`; e2e "every logo and photo on the page loads" | ✅ |
| F33 | Old design URLs | `about.html`, `contact.html`, `faqs.html`, `privacy-policy.html`, `terms.html`, `index.html` redirect (301) to the PHP pages | `ServerRulesTest::testDesignHtmlUrlsRedirectToPhpPages`; e2e | ✅ |
| F37 | Team photos shown whole | Portraits are square at every screen size instead of being cropped to 4:3 or a narrow strip | e2e "team photos are shown whole", "header stays on one line at every width" | ✅ |
| F36 | Company logo | The red dot-grid mark from the favicon, redrawn as SVG by `logo_mark()` in `includes/site.php`, in the header, footer and loading animation. `tools/build-images.php` draws the same mark into `og-image.png` and `apple-touch-icon.png` | Unit `testLogoMarkIsDecorativeSvgWithUniqueGradientIds`; `PagesTest::testCompanyLogoInHeaderAndFooter`, `testIdsAreUnique` | ✅ |
| F35 | Form emails reach both inboxes | `RECEIVING_EMAIL` in `includes/config.php` is a comma-separated list (`info@atomservices.org, abbas8156@gmail.com`); `mail_recipients()` splits it, dropping invalid or duplicate addresses. Applies to meeting requests, project enquiries and contact messages | Unit `testMailRecipients…`, `testConfiguredRecipientsIncludeBothInboxes` | 🟡 Set the same value in the live server's `config.php` |
| F34 | Copy button never gets stuck | Clipboard API with a 1 s timeout, falling back to a pinned, non-scrolling textarea (iOS) | e2e copy tests on four browser setups (80/80 repeated runs) | ✅ |

## 4. SEO requirements

| ID | Requirement | Implementation | Tests | Status |
|---|---|---|---|---|
| S1 | Clear H1 | Exactly one H1 per page; services H1 states the offer | `PagesTest::testExactlyOneH1` | ✅ |
| S2 | Strong CTAs | "Book a meeting" (header), "Meet us at GITEX", "Tell us about your project", WhatsApp | e2e form/CTA flows | ✅ |
| S3 | Service page | `services.php`: four services with anchors, process, enquiry form, breadcrumbs | `PagesTest` (all checks run on it) | ✅ |
| S4 | Trust signals | Real traction figures, live product links, named leadership with LinkedIn, registered entities, Organization schema | `PagesTest::testStructuredDataIsValidJson` | ✅ |
| S5 | Internal linking | Nav, footer "Company" links, capability → service anchors, "see also" links, 404 page links | `PagesTest` link tests | ✅ |
| S6 | Image alt text | Descriptive alt on every image; decorative SVGs `aria-hidden` | `PagesTest::testEveryImageHasAltText` | ✅ |
| S7 | XML sitemap | `sitemap.xml` (all seven public pages), referenced from `robots.txt` | `ServerRulesTest::testSitemapIsValidAndEveryUrlIsLive` | ✅ |

## 5. Non-functional requirements

| ID | Requirement | How it's met | Test |
|---|---|---|---|
| N1 | Security | Header-injection safe fields, length caps, whitelisted selects, prepared SQL, private folders 403, `nosniff`, `SAMEORIGIN`, Permissions-Policy | `ServerRulesTest::testPrivateFilesAreBlocked`, `testSecurityHeaders`; unit field cleaning |
| N2 | Accessibility | Labels on every field, focus management, `aria-live` status, reduced motion respected, contrast ≥ 4.5:1 | `PagesTest::testFormFieldsHaveLabels`; Lighthouse Accessibility 100 |
| N3 | Unicode | Names and messages in Urdu or with emoji stored intact (utf8mb4) | `ContactDatabaseTest::testUnicodeIsStoredIntact` |
| N4 | Friendly 404 | `404.php`, `noindex`, works at any depth | `ServerRulesTest::testUnknownPageGetsFriendly404`, `testNested404StillLoadsStyles`; e2e |

## 6. Testing

```bash
composer install && npm install && npx playwright install chromium webkit   # once

npm run test:js      # JavaScript unit tests (node:test)               tests/js/
npm run test:php     # PHP unit + integration tests (PHPUnit)          tests/Unit, tests/Integration
npm run test:e2e     # browser tests: Chrome + Safari engine, desktop + phone   tests/e2e/
npm run test:links   # every internal and external link
npm test             # js + php + e2e
```

- Integration and e2e tests run against `http://localhost/atomservices` (XAMPP must be running). Point them
  at the live site with `BASE_URL=https://atomservices.org/`.
- `ContactDatabaseTest` creates and drops a throwaway `atomservices_test` database; it never touches real data.
- No automated test sends a real email or writes to the real `contact_messages` table.
- Rule: every new feature or bug fix adds a row or test reference here, plus unit and integration tests.

## 7. Deployment checklist

1. Upload the files. Do not upload `node_modules/`, `tests/`, `tools/originals/` or `.git/` (they are blocked
   by `.htaccess` anyway).
2. On the server, run `composer install --no-dev` and create `includes/config.php` from `config.sample.php`.
3. Enable SSL on the host (Let's Encrypt / cPanel AutoSSL) **before** relying on the HTTPS redirect.
4. Run `BASE_URL=https://atomservices.org/ npm run test:links` and `npm run test:e2e` against live.
5. Send one real test enquiry and confirm it arrives by email and in the database.
6. Google Search Console: verify the domain and submit `https://atomservices.org/sitemap.xml`.
7. Check link previews with the LinkedIn Post Inspector and the Facebook Sharing Debugger.
8. Open the site on a real iPhone in Safari.

## 8. Known gaps and open items

- Company details not yet confirmed: SECP registration number, NTN and a Pakistan office phone. Set
  `COMPANY_SECP_NO`, `COMPANY_NTN` and `COMPANY_PK_PHONE` in `includes/site.php`; they then appear on the
  About page and in the footer. The office address shown is the one used on atomshop.pk.
- No logo file exists yet for KDP Score; it uses a text mark. Add a source to `tools/brand-sources/` and run
  `php tools/build-logos.php`.
- Photos for Muhammad Ahmed Chaudhary and Gulshair Murtaza are missing. Add `team-4.*` and `team-5.*` to
  `assets/img/team/` and run `php tools/build-images.php`; initials show until then.
- GITEX hall and stand number: update in `index.php` when confirmed.
- LinkedIn profile links can't be checked automatically (LinkedIn blocks bots); verify by hand.
- Safari is tested with Playwright's WebKit on Windows, which is close to but not identical to Safari on iOS/macOS.
