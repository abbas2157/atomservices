// @ts-check
const { test, expect } = require('@playwright/test');

// Open a page and wait until main.js has run (it removes the preloader).
async function open(page, path) {
  await page.goto(path);
  await expect(page.locator('#preloader')).toHaveCount(0);
  // Web fonts swapping in shifts inline elements a few pixels; let layout settle before clicking.
  await page.evaluate(() => document.fonts.ready);
}

const isMobile = (testInfo) => testInfo.project.name.endsWith('mobile');

// Collect console errors and failed requests on every page.
test.beforeEach(async ({ page }, testInfo) => {
  testInfo['errors'] = [];
  page.on('pageerror', (e) => testInfo['errors'].push(e.message));
  page.on('console', (m) => { if (m.type() === 'error') testInfo['errors'].push(m.text()); });
});

test.describe('page load', () => {
  for (const path of ['./', 'services']) {
    test(`${path} loads fast, without errors or sideways scroll`, async ({ page }, testInfo) => {
      await page.goto(path, { waitUntil: 'load' });
      // Use the browser's own Navigation Timing, not wall-clock time, so browser start-up isn't counted.
      const loadMs = await page.evaluate(() => {
        const nav = performance.getEntriesByType('navigation')[0];
        return nav.loadEventEnd - nav.startTime;
      });
      expect(loadMs, `load took ${Math.round(loadMs)}ms`).toBeLessThan(3000);

      await expect(page.locator('#preloader')).toHaveCount(0, { timeout: 2000 });
      await expect(page.locator('h1')).toHaveCount(1);
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
      expect(overflow, 'page is wider than the screen').toBeLessThanOrEqual(0);
      expect(testInfo['errors']).toEqual([]);
    });
  }

  test('all images load', async ({ page }) => {
    await page.goto('./');
    await page.locator('#team').scrollIntoViewIfNeeded();
    const imgs = page.locator('img');
    for (let i = 0; i < await imgs.count(); i++) {
      await imgs.nth(i).scrollIntoViewIfNeeded();
      await expect.poll(() => imgs.nth(i).evaluate((el) => el.complete && el.naturalWidth > 0)).toBe(true);
    }
  });
});

test.describe('navigation', () => {
  test('skip link is the first focusable element and jumps to content', async ({ page, browserName }) => {
    test.skip(browserName === 'webkit', 'Safari only tabs to links when "Press Tab to highlight each item" is on');
    await page.goto('./');
    await page.keyboard.press('Tab');
    const skip = page.locator('.skip-link');
    await expect(skip).toBeFocused();
    await expect(skip).toBeInViewport();
    await skip.press('Enter');
    await expect(page).toHaveURL(/#top$/);
  });

  test('mobile menu opens, links work and it closes', async ({ page }, testInfo) => {
    test.skip(!isMobile(testInfo), 'phone-only menu');
    await page.goto('./');
    const toggle = page.locator('.menu-toggle');
    const nav = page.locator('#site-nav');
    await expect(toggle).toBeVisible();
    await expect(nav.getByRole('link', { name: 'Products' })).toBeHidden();
    await toggle.click();
    await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    await nav.getByRole('link', { name: 'Products' }).click();
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
    await expect(page).toHaveURL(/#products$/);
    await toggle.click();
    await page.keyboard.press('Escape');
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  });

  test('desktop nav shows all links', async ({ page }, testInfo) => {
    test.skip(isMobile(testInfo), 'desktop-only layout');
    await page.goto('./');
    await expect(page.locator('.menu-toggle')).toBeHidden();
    for (const name of ['Products', 'Services', 'GITEX 2026', 'About', 'FAQs', 'Contact']) {
      await expect(page.locator('#site-nav').getByRole('link', { name })).toBeVisible();
    }
  });

  test('services link goes to the services page and back home', async ({ page }) => {
    await page.goto('./');
    await page.locator('.svc').getByRole('link', { name: 'Mobile apps' }).click();
    await expect(page).toHaveURL(/services#mobile-apps$/);
    await expect(page.locator('h1')).toContainText('Software development services');
    await page.locator('.crumbs').getByRole('link', { name: 'Home' }).click();
    await expect(page.locator('h1')).toContainText('Pay in steps');
  });

  test('back-to-top and scroll progress bar', async ({ page }) => {
    await page.goto('./');
    const toTop = page.locator('#to-top');
    await expect(toTop).not.toHaveClass(/show/);
    await page.evaluate(() => window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'instant' }));
    await expect(toTop).toHaveClass(/show/);
    await expect.poll(() => page.locator('#progress').evaluate((el) => new DOMMatrix(getComputedStyle(el).transform).a)).toBeGreaterThan(0.95);
    await page.locator('#cookie-banner button').click();
    await toTop.click();
    await expect.poll(() => page.evaluate(() => window.scrollY), { timeout: 5000 }).toBe(0);
  });
});

test.describe('search', () => {
  test('finds products and services and navigates', async ({ page }) => {
    await page.goto('./');
    await page.locator('[data-open-search]').first().click();
    const input = page.locator('#search-input');
    await expect(input).toBeFocused();
    await input.fill('attendance');
    const results = page.locator('.search-results a');
    await expect(results.first()).toContainText('Mark Attendance');
    await input.fill('mobile app');
    await expect(results.first()).toContainText('Mobile app development');
    await input.press('Enter');
    await expect(page).toHaveURL(/services#mobile-apps$/);
  });

  test('shows a helpful empty state and closes with Escape', async ({ page }) => {
    await page.goto('services');
    await page.locator('[data-open-search]').first().click();
    await page.locator('#search-input').fill('zzzz');
    await expect(page.locator('.search-empty')).toContainText('No results');
    await page.keyboard.press('Escape');
    await expect(page.locator('#search')).toBeHidden();
  });

  test('opens with the / shortcut', async ({ page }, testInfo) => {
    test.skip(isMobile(testInfo), 'no physical keyboard on phones');
    await page.goto('./');
    await page.keyboard.press('/');
    await expect(page.locator('#search')).toBeVisible();
  });
});

test.describe('widgets', () => {
  test('cookie banner shows once and remembers dismissal', async ({ page }) => {
    await page.goto('./');
    const banner = page.locator('#cookie-banner');
    await expect(banner).toBeVisible();
    await banner.getByRole('button').click();
    await expect(banner).toBeHidden();
    await page.reload();
    await expect(banner).toBeHidden();
  });

  test('WhatsApp button opens a chat with the right number', async ({ page }) => {
    await page.goto('./');
    const wa = page.locator('.wa-float');
    await expect(wa).toBeVisible();
    const href = await wa.getAttribute('href');
    expect(href).toMatch(/^https:\/\/wa\.me\/923041129252\?text=.+/);
    await expect(wa).toHaveAttribute('target', '_blank');
  });

  test('copy button copies the email address', async ({ page, context, browserName }) => {
    if (browserName === 'chromium') await context.grantPermissions(['clipboard-read', 'clipboard-write']);
    await page.goto('./');
    const btn = page.locator('[data-copy]');
    await btn.scrollIntoViewIfNeeded();
    await page.locator('#cookie-banner button').click();
    await btn.click();
    await expect(btn).toHaveText('Copied');
    if (browserName === 'chromium') {
      expect(await page.evaluate(() => navigator.clipboard.readText())).toBe('info@atomservices.org');
    }
    await expect(btn).toHaveText('Copy', { timeout: 4000 });
  });

  test('hover states change appearance', async ({ page }, testInfo) => {
    test.skip(isMobile(testInfo), 'no hover on touch screens');
    await page.goto('./');
    const card = page.locator('.app').first();
    const before = await card.evaluate((el) => getComputedStyle(el).borderColor);
    await card.hover();
    await expect.poll(() => card.evaluate((el) => getComputedStyle(el).borderColor)).not.toBe(before);
  });

  test('GITEX countdown shows a number of days', async ({ page }) => {
    await page.goto('./');
    await expect(page.locator('#days')).toHaveText(/^(\d+|Now)$/);
  });
});

test.describe('forms', () => {
  test('meeting form shows inline errors, then clears them as you type', async ({ page }) => {
    await open(page, './#meet');
    const form = page.locator('#meet form');
    await form.getByRole('button', { name: 'Request meeting' }).click();
    await expect(form.locator('.form-msg')).toHaveText('Please fix the highlighted fields.');
    await expect(page.locator('#m-name')).toHaveAttribute('aria-invalid', 'true');
    await expect(page.locator('#m-name')).toBeFocused();
    await expect(page.locator('#m-email-error')).toHaveText('Please enter your email address.');
    await page.locator('#m-name').fill('Sara');
    await expect(page.locator('#m-name')).not.toHaveAttribute('aria-invalid');
    await page.locator('#m-email').fill('nope');
    await form.getByRole('button', { name: 'Request meeting' }).click();
    await expect(page.locator('#m-email-error')).toContainText('valid email');
  });

  test('meeting form success state, with UTM campaign sent along', async ({ page }) => {
    let posted = '';
    await page.route('**/forms/contact.php', async (route) => {
      posted = route.request().postData() || '';
      await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ ok: true, message: 'Thanks. We have your request and will confirm a time and place by email.' }) });
    });
    await open(page, './?utm_source=linkedin&utm_campaign=gitex2026#meet');
    await page.locator('#m-name').fill('Sara Khan');
    await page.locator('#m-email').fill('sara@example.com');
    await page.locator('#m-topic').selectOption('Mark Attendance');
    await page.locator('#meet form').getByRole('button', { name: 'Request meeting' }).click();

    const success = page.locator('#meet .form-success');
    await expect(success).toBeVisible();
    await expect(success).toContainText('Request received');
    await expect(page.locator('#meet form')).toBeHidden();
    expect(posted).toContain('name="utm_source"\r\n\r\nlinkedin');
    expect(posted).toContain('name="utm_campaign"\r\n\r\ngitex2026');
    expect(posted).toContain('name="form"\r\n\r\nmeeting');
    expect(posted).toContain('Mark Attendance');

    await success.getByRole('button', { name: 'Send another request' }).click();
    await expect(page.locator('#meet form')).toBeVisible();
    await expect(page.locator('#m-name')).toHaveValue('');
  });

  test('UTM campaign survives navigating to another page', async ({ page }) => {
    let posted = '';
    await page.route('**/forms/contact.php', async (route) => {
      posted = route.request().postData() || '';
      await route.fulfill({ status: 200, contentType: 'application/json', body: '{"ok":true,"message":"Thanks."}' });
    });
    await open(page, './?utm_source=newsletter');
    await open(page, 'services#contact');
    await page.locator('#c-name').fill('Ali');
    await page.locator('#c-email').fill('ali@example.com');
    await page.locator('#c-message').fill('We need an Android app for our drivers.');
    await page.locator('#contact form').getByRole('button', { name: 'Send enquiry' }).click();
    await expect(page.locator('#contact .form-success')).toBeVisible();
    expect(posted).toContain('name="utm_source"\r\n\r\nnewsletter');
  });

  test('server validation errors are shown on the right fields', async ({ page }) => {
    await page.route('**/forms/contact.php', (route) => route.fulfill({
      status: 422, contentType: 'application/json',
      body: JSON.stringify({ ok: false, message: 'Please fix the highlighted fields.', errors: { email: 'That address bounced.' } }),
    }));
    await open(page, 'services#contact');
    await page.locator('#c-name').fill('Ali');
    await page.locator('#c-email').fill('ali@example.com');
    await page.locator('#c-message').fill('A project description.');
    await page.locator('#contact form').getByRole('button', { name: 'Send enquiry' }).click();
    await expect(page.locator('#c-email-error')).toHaveText('That address bounced.');
    await expect(page.locator('#contact form .form-msg')).toHaveClass(/error/);
  });

  test('server and network failures show an error and keep the input', async ({ page }) => {
    await page.route('**/forms/contact.php', (route) => route.fulfill({
      status: 500, contentType: 'application/json',
      body: JSON.stringify({ ok: false, message: 'We could not send your message just now. Please try again, or email info@atomservices.org.' }),
    }));
    await open(page, './#meet');
    await page.locator('#m-name').fill('Sara');
    await page.locator('#m-email').fill('sara@example.com');
    const button = page.locator('#meet form').getByRole('button', { name: 'Request meeting' });
    await button.click();
    await expect(page.locator('#meet .form-msg')).toContainText('could not send');
    await expect(page.locator('#m-name')).toHaveValue('Sara');
    await expect(button).toBeEnabled();

    await page.unroute('**/forms/contact.php');
    await page.route('**/forms/contact.php', (route) => route.abort('failed'));
    await button.click();
    await expect(page.locator('#meet .form-msg')).toContainText('could not reach the server');
  });

  test('real endpoint rejects an invalid submission with field errors', async ({ page }) => {
    // Hits the real PHP handler; invalid data is rejected before anything is saved or emailed.
    await open(page, './#meet');
    await page.locator('#m-name').fill('Sara');
    await page.locator('#m-email').fill('sara@example.com');
    await page.evaluate(() => { document.querySelector('#m-email').value = 'sara@'; });
    // Bypass the client check to prove the server enforces it too.
    const res = await page.request.post('forms/contact.php', { form: { form: 'meeting', name: 'Sara', email: 'sara@' }, headers: { Accept: 'application/json' } });
    expect(res.status()).toBe(422);
    expect((await res.json()).errors.email).toContain('valid email');
  });

  test('forms still work without JavaScript', async ({ browser }) => {
    // Covers slow phones where someone taps Send before main.js has loaded. Uses the real endpoint
    // with an incomplete submission, so nothing is saved or emailed.
    const ctx = await browser.newContext({ javaScriptEnabled: false, baseURL: test.info().project.use.baseURL });
    const page = await ctx.newPage();
    await page.goto('./#meet');
    await page.locator('#m-name').fill('Sara');
    // Enter submits the form natively. (Clicking can fail here: with JS off, Chromium smooth-scrolls
    // while Playwright waits for the button to stop moving.)
    await page.locator('#m-name').press('Enter');
    await expect(page).toHaveURL(/form_status=invalid#meet$/);
    await expect(page.locator('#meet .form-msg')).toHaveText(/valid email address/);
    await expect(page.locator('#meet .form-msg')).toBeVisible();
    await ctx.close();
  });
});

test('404 page helps people find their way', async ({ page }) => {
  const res = await page.goto('no-such-page.php');
  expect(res?.status()).toBe(404);
  await expect(page.locator('h1')).toHaveText("We can't find that page.");
  await page.getByRole('link', { name: 'Go to the homepage' }).click();
  await expect(page.locator('h1')).toContainText('Pay in steps');
});
