// @ts-check
// About, Contact, FAQs and legal pages.
const { test, expect } = require('@playwright/test');

const isMobile = (testInfo) => testInfo.project.name.endsWith('mobile');

async function open(page, path) {
  await page.goto(path);
  await expect(page.locator('#preloader')).toHaveCount(0);
  // Web fonts swapping in shifts inline elements a few pixels; let layout settle before clicking.
  await page.evaluate(() => document.fonts.ready);
}

const PAGES = ['about', 'contact', 'faqs', 'privacy-policy', 'terms'];

for (const path of PAGES) {
  test(`${path} loads fast, without errors, and fits the screen`, async ({ page }) => {
    const errors = [];
    page.on('pageerror', (e) => errors.push(e.message));
    page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
    await open(page, path);
    const loadMs = await page.evaluate(() => {
      const nav = performance.getEntriesByType('navigation')[0];
      return nav.loadEventEnd - nav.startTime;
    });
    expect(loadMs, `load took ${Math.round(loadMs)}ms`).toBeLessThan(3000);
    await expect(page.locator('h1')).toHaveCount(1);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    expect(overflow, 'page is wider than the screen').toBeLessThanOrEqual(0);
    expect(errors).toEqual([]);
  });
}

test('every logo and photo on the page loads', async ({ page }) => {
  for (const path of ['./', 'about', 'contact']) {
    await open(page, path);
    const imgs = page.locator('img');
    const n = await imgs.count();
    expect(n).toBeGreaterThan(0);
    for (let i = 0; i < n; i++) {
      await imgs.nth(i).scrollIntoViewIfNeeded();
      await expect.poll(() => imgs.nth(i).evaluate((el) => el.complete && el.naturalWidth > 0), { message: `${path} img ${i}` }).toBe(true);
    }
  }
});

test('main navigation reaches About, FAQs and Contact', async ({ page }, testInfo) => {
  await open(page, './');
  for (const [label, url, heading] of [['About', /about$/, 'Smarter finance'], ['FAQs', /faqs$/, 'Frequently asked'], ['Contact', /contact$/, 'Talk to the right team']]) {
    if (isMobile(testInfo)) await page.locator('.menu-toggle').click();
    await page.locator('#site-nav').getByRole('link', { name: label, exact: true }).click();
    await expect(page).toHaveURL(url);
    await expect(page.locator('h1')).toContainText(heading);
    // (the phone menu is closed by now, so match the hidden link by attribute rather than role)
    await expect(page.locator('#site-nav a[aria-current="page"]')).toHaveText(label);
  }
});

test('footer links reach the legal pages', async ({ page }) => {
  await open(page, './');
  await page.locator('#cookie-banner button').click();
  await page.locator('footer').getByRole('link', { name: 'Privacy policy' }).click();
  await expect(page.locator('h1')).toHaveText('Privacy policy.');
  await page.locator('footer').getByRole('link', { name: 'Terms of use' }).click();
  await expect(page.locator('h1')).toHaveText('Terms of use.');
});

test('FAQ answers open and close, and topic links jump to sections', async ({ page }, testInfo) => {
  await open(page, 'faqs');
  const q = page.locator('details').filter({ hasText: 'What is AtomPay?' });
  const answer = q.locator('p');
  await expect(answer).toBeHidden();
  await q.locator('summary').click();
  await expect(answer).toBeVisible();
  await expect(answer).toContainText('0% markup');
  await q.locator('summary').click();
  await expect(answer).toBeHidden();
  if (!isMobile(testInfo)) { // the topic list is hidden on phones
    await page.locator('.toc').getByRole('link', { name: 'GITEX Global 2026' }).click();
    await expect(page).toHaveURL(/faqs#gitex$/);
    await expect(page.locator('#gitex h2')).toBeInViewport();
  }
});

test('contact page form shows errors, then a success state', async ({ page }) => {
  let posted = '';
  await page.route('**/forms/contact.php', async (route) => {
    posted = route.request().postData() || '';
    await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ ok: true, message: 'Thanks. We have your message and will reply by email.' }) });
  });
  await open(page, 'contact');
  const form = page.locator('#message form');
  await page.locator('#e-name').fill('Zara');
  await page.locator('#e-email').fill('zara@example.com');
  await form.getByRole('button', { name: 'Send message' }).click();
  await expect(page.locator('#e-message-error')).toHaveText('Please write a short message (at least 10 characters).');
  expect(posted).toBe('');

  await page.locator('#e-topic').selectOption('Careers');
  await page.locator('#e-message').fill('I would like to apply for a design role.');
  await form.getByRole('button', { name: 'Send message' }).click();
  await expect(page.locator('#message .form-success')).toBeVisible();
  expect(posted).toContain('name="form"\r\n\r\nenquiry');
  expect(posted).toContain('Careers');
});

test('contact page copy button and WhatsApp links work', async ({ page, context, browserName }) => {
  if (browserName === 'chromium') await context.grantPermissions(['clipboard-read', 'clipboard-write']);
  await open(page, 'contact');
  const copy = page.locator('.ccard [data-copy]');
  // Record every label change: "Copied" only shows for 1.8 s, which a busy test machine can miss when polling.
  await copy.evaluate((btn) => {
    window.__labels = [];
    new MutationObserver(() => window.__labels.push(btn.textContent)).observe(btn, { childList: true, characterData: true, subtree: true });
    document.addEventListener('click', (e) => window.__labels.push('click on ' + (e.target.className || e.target.tagName)), true);
  });
  // Dispatch the click on the element itself: on a CPU-starved test machine WebKit's coordinate-based
  // clicks occasionally landed beside this inline button (it does not move; measured). Real pointer
  // clicks on a copy button are covered by the homepage test in site.spec.js.
  await copy.dispatchEvent('click');
  await expect.poll(() => page.evaluate(() => window.__labels)).toContain('Copied');
  for (const href of await page.locator('.ccard a[href^="https://wa.me/"]').evaluateAll((as) => as.map((a) => a.getAttribute('href')))) {
    expect(href).toMatch(/^https:\/\/wa\.me\/\d{10,15}(\?text=.+)?$/);
  }
});

test('old .html links from the design redirect to the live pages', async ({ page }) => {
  await page.goto('about.html');
  await expect(page).toHaveURL(/about$/);
  await expect(page.locator('h1')).toContainText('Smarter finance');
});

test('cookie notice stays dismissed across pages', async ({ page }) => {
  await open(page, 'about');
  await page.locator('#cookie-banner button').click();
  await open(page, 'faqs');
  await expect(page.locator('#cookie-banner')).toBeHidden();
});

test('team photos are shown whole (square, not cropped)', async ({ page }) => {
  await open(page, 'about');
  const portraits = page.locator('.leader .portrait');
  await expect(portraits).toHaveCount(5);
  for (let i = 0; i < 5; i++) {
    const box = await portraits.nth(i).boundingBox();
    expect(Math.abs(box.width - box.height), `portrait ${i} is ${Math.round(box.width)}×${Math.round(box.height)}`).toBeLessThanOrEqual(2);
  }
});

test('header stays on one line at every width', async ({ page }) => {
  for (const width of [1366, 1120, 1100, 900, 390]) {
    await page.setViewportSize({ width, height: 800 });
    await open(page, './');
    const h = await page.locator('header.top').evaluate((el) => el.getBoundingClientRect().height);
    expect(h, `header is ${Math.round(h)}px tall at ${width}px`).toBeLessThanOrEqual(80);
  }
});
