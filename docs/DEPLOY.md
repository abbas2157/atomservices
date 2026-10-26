# Deploying atomservices.org

How to put this site live, check it works, and roll back if it doesn't. Allow about an hour the first time.

Contents:
1. [What the server needs](#1-what-the-server-needs)
2. [Before you start](#2-before-you-start)
3. [Back up the current live site](#3-back-up-the-current-live-site)
4. [Turn on SSL first](#4-turn-on-ssl-first)
5. [Set up the database](#5-set-up-the-database)
6. [Upload the code](#6-upload-the-code)
7. [Create the config file](#7-create-the-config-file)
8. [Check the live site](#8-check-the-live-site)
9. [Tell Google](#9-tell-google)
10. [Roll back](#10-roll-back)
11. [Common problems](#11-common-problems)
12. [Routine updates after launch](#12-routine-updates-after-launch)

---

## 1. What the server needs

| Need | Notes |
|---|---|
| PHP 8.1 or newer | Tested on 8.2. Extensions: `pdo_mysql`, `mbstring`, `openssl` (standard on most hosts) |
| Apache with `.htaccess` allowed | Modules: `mod_rewrite` (required), `mod_headers`, `mod_expires`, `mod_deflate`, `mod_alias` |
| MySQL or MariaDB | One database, two tables |
| SMTP account | To send form notifications (host, port, username, password) |
| SSL certificate | Let's Encrypt / cPanel AutoSSL is fine |
| Composer | On the server, or on your PC (see 6.2) |

The site must live at the **web root** of `atomservices.org` (e.g. `public_html/`), not in a subfolder.
`.htaccess` points the 404 page at `/404.php`.

---

## 2. Before you start

On your PC, in `C:\xampp\htdocs\atomservices`:

1. **Get the final code onto `main`.** The launch work is on the `launch-redesign` branch:
   ```bash
   git checkout main
   git pull
   git merge launch-redesign
   git push
   ```
2. **Run the tests** (XAMPP must be running):
   ```bash
   npm run test:js
   php vendor/bin/phpunit
   npm run test:links
   npm run test:e2e        # about 6 minutes
   ```
   Don't deploy if anything fails.
3. **Check company details** in `includes/site.php`. Any value left as `null` is simply hidden on the site:
   - `COMPANY_SECP_NO`: SECP registration number
   - `COMPANY_NTN`: National Tax Number
   - `COMPANY_PK_PHONE`: Pakistan office number
   - `COMPANY_OFFICE`: office address (currently the Atomshop.pk office address; confirm it)
4. **Have these to hand:** hosting login (cPanel or SSH), database name/user/password, SMTP details.

---

## 3. Back up the current live site

Do this before touching anything; it's your way back (section 10).

- **Files:** in cPanel → File Manager, select everything in `public_html`, then **Compress** and download the zip.
  Over SSH: `tar -czf ~/backup-$(date +%F).tar.gz -C ~/public_html .`
- **Database:** cPanel → phpMyAdmin, select the database → **Export** → Quick → SQL → Go.
  Over SSH: `mysqldump -u USER -p DBNAME > ~/backup-$(date +%F).sql`
- **Old config:** download the current `includes/config.php`. It has the working DB and SMTP passwords.

---

## 4. Turn on SSL first

The new `.htaccess` sends every visitor to `https://`. **If the certificate isn't working yet, the site will show a
security error.** So:

1. cPanel → **SSL/TLS Status** (or Let's Encrypt) → issue a certificate for `atomservices.org` **and**
   `www.atomservices.org`.
2. Open `https://atomservices.org` in a browser and confirm the padlock shows (even on the old site).

Only continue once the padlock shows.

---

## 5. Set up the database

Skip this if the live database already has the `contact_messages` table. (The old site used the same table.)

1. cPanel → **MySQL Databases**: create a database and a user, and give the user **All privileges** on it.
   cPanel adds a prefix, e.g. `atomuser_atomservices`. Note the full names.
2. cPanel → **phpMyAdmin** → select that database → **SQL** tab.
3. Paste **only the two `CREATE TABLE` statements** from `database/schema.sql` and click **Go**.
   Leave out the first lines (`CREATE DATABASE` and `USE`): shared hosting won't allow them, and the
   database name differs.
4. You should now see `contact_messages` and `newsletter_subscribers`.

---

## 6. Upload the code

### 6.1 What goes to the server

| Upload | Don't upload |
|---|---|
| `*.php` in the root (index, services, about, contact, faqs, privacy-policy, terms, 404) | `node_modules/` |
| `.htaccess`, `robots.txt`, `sitemap.xml` | `tests/`, `test-results/`, `playwright-report/` |
| `assets/` (css, js, fonts, img) | `tools/` |
| `forms/` | `docs/` |
| `includes/` **except** your local `config.php` | `.git/`, `.phpunit.cache/` |
| `database/` (optional; blocked from the web anyway) | `package.json`, `package-lock.json`, `phpunit.xml`, `playwright.config.js` |
| `composer.json`, `composer.lock`, `vendor/` (see 6.2) | |

Even if some of the right-hand column gets uploaded by mistake, `.htaccess` blocks it from the web (403). Still,
keep it off the server.

### 6.2 The `vendor/` folder (PHPMailer)

The form handler needs PHPMailer from `vendor/`. Pick one:

- **Composer on the server (best):** after uploading, in the site folder run
  ```bash
  composer install --no-dev --optimize-autoloader
  ```
- **No Composer on the server:** build it on your PC without the test tools, upload `vendor/`, then restore:
  ```bash
  composer install --no-dev --optimize-autoloader   # removes PHPUnit locally
  # ...upload the vendor/ folder...
  composer install                                   # puts the test tools back on your PC
  ```

### 6.3 Option A: Git over SSH

```bash
cd ~
git clone https://github.com/abbas2157/atomservices.git atomservices-new
cd atomservices-new && composer install --no-dev --optimize-autoloader
# move the old site aside and the new one in (keep your backup from section 3!)
mv ~/public_html ~/public_html-old
mv ~/atomservices-new ~/public_html
```
For later updates: `cd ~/public_html && git pull && composer install --no-dev`.
`.htaccess` blocks `.git/`, `tests/` and `tools/` from the web.

### 6.4 Option B: cPanel File Manager or FTP

1. On your PC, make a zip of the files in the left column of 6.1.
2. File Manager → `public_html` → delete the old site's files. **Keep** the old `includes/config.php` if you want
   to copy values from it; you have a backup either way.
3. **Upload** the zip → **Extract** it into `public_html`.
4. Make sure `.htaccess` arrived: hidden files are easy to miss. In File Manager, use Settings → **Show Hidden Files**.

---

## 7. Create the config file

`includes/config.php` is not in Git because it holds passwords. Create it on the server:

1. Copy `includes/config.sample.php` to `includes/config.php`.
2. Fill in the real values:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'atomuser_atomservices');   // full name from section 5
   define('DB_USER', 'atomuser_web');
   define('DB_PASS', '********');

   define('SMTP_HOST', 'mail.atomservices.org');  // from your email provider
   define('SMTP_PORT', 587);
   define('SMTP_ENCRYPTION', 'tls');              // 'ssl' if the port is 465
   define('SMTP_USERNAME', 'info@atomservices.org');
   define('SMTP_PASSWORD', '********');
   define('SMTP_FROM_EMAIL', 'info@atomservices.org');
   define('SMTP_FROM_NAME', 'Atom Services');

   // Every form submission is emailed to all of these (comma-separated).
   define('RECEIVING_EMAIL', 'info@atomservices.org, abbas8156@gmail.com');
   ```
3. If you use a Gmail account for SMTP, use a Google **App Password**, not the normal password.
4. Set file permission to `640` (or `644` if the site errors with 640).

---

## 8. Check the live site

Work through this list; it takes about 15 minutes.

### 8.1 In a browser

- [ ] `https://atomservices.org` loads with the **padlock**, new design, no error text.
- [ ] `http://atomservices.org` and `https://www.atomservices.org` both end up at `https://atomservices.org`.
- [ ] Each page opens: Services, About, FAQs, Contact, Privacy policy, Terms (header and footer links).
- [ ] `https://atomservices.org/about.html` redirects to `/about.php`; `/service-details.php` redirects to `/services.php`.
- [ ] `https://atomservices.org/this-does-not-exist` shows the styled "We can't find that page" page.
- [ ] Team photos and product logos show; the GITEX countdown shows a number.
- [ ] On a **phone**: the menu button opens the menu, the WhatsApp button opens a chat with +1 646 726 0635.
- [ ] On an **iPhone in Safari**: open the homepage and contact page once and scroll through.

### 8.2 Private files are blocked

Each of these must show **403 Forbidden**:
```
https://atomservices.org/includes/config.php
https://atomservices.org/vendor/autoload.php
https://atomservices.org/composer.json
https://atomservices.org/.git/config
```
If any of them shows content, stop and fix `.htaccess` (section 11).

### 8.3 Forms send email and save to the database

1. On the **Contact** page, send a message with your own email and the topic "Something else".
2. You should see the green "Message sent" panel.
3. **Both inboxes** (info@atomservices.org and abbas8156@gmail.com) receive the email within a minute or two.
   Check spam the first time.
4. phpMyAdmin → `contact_messages`: a new row, with `email_sent = 1`.
   - Row there but `email_sent = 0` → the save worked but email failed; check the SMTP settings (section 11).
5. Delete your test row afterwards if you like.

### 8.4 Automated checks against the live site

From your PC:
```bash
BASE_URL=https://atomservices.org/ npm run test:links   # every link and asset on all pages
BASE_URL=https://atomservices.org/ npm run test:e2e     # browser tests in Chrome + Safari engine
```
The browser tests only submit incomplete forms to the live site, so they never send email or write to the
database. On Windows PowerShell, set the variable first: `$env:BASE_URL="https://atomservices.org/"`.

### 8.5 Link previews

Paste `https://atomservices.org` into:
- LinkedIn Post Inspector: https://www.linkedin.com/post-inspector/
- Facebook Sharing Debugger: https://developers.facebook.com/tools/debug/

You should see the navy "Shop now. Pay in steps." image, title and description. Click **Scrape again** if an old
preview appears.

---

## 9. Tell Google

1. Open [Google Search Console](https://search.google.com/search-console) → **Add property** → *Domain* →
   `atomservices.org` → add the TXT record it gives you at your DNS provider → **Verify**.
2. **Sitemaps** → enter `sitemap.xml` → **Submit**.
3. **URL Inspection** → `https://atomservices.org/` → **Request indexing**. Repeat for `/services.php` and `/about.php`.

New pages usually appear in search within a few days to two weeks.

---

## 10. Roll back

If the new site has a serious problem and you can't fix it quickly:

- **Git (option A):** `mv ~/public_html ~/public_html-broken && mv ~/public_html-old ~/public_html`
- **File Manager (option B):** empty `public_html`, upload the backup zip from section 3, extract it.
- **Database:** usually nothing to undo; the new site uses the same table. If needed, phpMyAdmin → Import the
  `.sql` backup.

Then work out what went wrong on your PC (XAMPP) before trying again.

---

## 11. Common problems

| Symptom | Likely cause | Fix |
|---|---|---|
| White page or "500 Internal Server Error" | `includes/config.php` missing, `vendor/` missing, or PHP older than 8.1 | Create the config (7); run Composer (6.2); cPanel → *Select PHP Version* → 8.2 |
| "Too many redirects" | A proxy or CDN (e.g. Cloudflare "Flexible" SSL) sends plain http to the server | Cloudflare: set SSL mode to **Full (strict)** |
| Browser security warning | Certificate not issued or doesn't cover `www` | Section 4 |
| Pages work but the 404 page has no styling | Site not at the web root | Put it in `public_html` itself, or change `ErrorDocument 404 /404.php` in `.htaccess` |
| Form shows "We could not send your message" | Both the database and the email failed | Check DB and SMTP values in `config.php`; check the host's PHP error log |
| Form succeeds, row has `email_sent = 0`, no email | SMTP login wrong, wrong port/encryption, or the host blocks port 587 | Try port 465 with `'ssl'`; Gmail needs an App Password; ask the host if outgoing SMTP is allowed |
| Email arrives only at one address | `RECEIVING_EMAIL` on the server still has one address | Section 7, step 2 |
| `/includes/config.php` shows a page instead of 403 | `.htaccess` missing or the host ignores it | Upload `.htaccess`; ask the host to enable `AllowOverride All` and `mod_rewrite` |
| Old CSS or photos after an update | Browser cache | Shouldn't happen (files are version-stamped); hard refresh with Ctrl+F5 |

---

## 12. Routine updates after launch

| Task | How |
|---|---|
| Add or replace a team photo | Put the original in `assets/img/team/` named `team-N.png` or `.jpg` (Ahmed is `team-4`, Gulshair is `team-5`), run `php tools/build-images.php`, upload the new `team-N.jpg` and `team-N.webp` |
| Add a product logo (e.g. KDP Score) | Put the source in `tools/brand-sources/`, add it to the list in `tools/build-logos.php`, run `php tools/build-logos.php`, upload `assets/img/brands/` |
| Add SECP number, NTN or PK phone | Set the constant in `includes/site.php` and upload that file |
| GITEX hall and stand number | Edit the "Where we are" row in `index.php` |
| Change who gets form emails | Edit `RECEIVING_EMAIL` in the **server's** `includes/config.php` |
| Read enquiries | phpMyAdmin → `contact_messages`, newest first (`created_at`) |
| Any code change | Update `docs/TRD.md`, add tests, run them, then deploy as in section 6 |
