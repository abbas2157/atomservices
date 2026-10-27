// @ts-check
const { test, expect } = require('@playwright/test');

const PAGES = ['./', 'about', 'services', 'contact', 'faqs', 'privacy-policy', 'terms'];
const isMobile = (testInfo) => testInfo.project.name.endsWith('mobile');

async function open(page, path) {
  await page.goto(path);
  await expect(page.locator('#preloader')).toHaveCount(0);
}

// Phones to 8K: no page may scroll sideways or push an element past the right edge.
const SIZES = [[320, 568], [360, 800], [768, 1024], [1024, 768], [1440, 900], [1920, 1080], [2560, 1440], [3440, 1440], [3840, 2160], [7680, 4320]];

test('no sideways scroll on any page, from 320px phones to 8K', async ({ page }, testInfo) => {
  test.skip(isMobile(testInfo), 'Sizes are set explicitly; once per desktop browser is enough');
  test.setTimeout(120000);
  for (const [width, height] of SIZES) {
    await page.setViewportSize({ width, height });
    for (const path of PAGES) {
      await open(page, path);
      const over = await page.evaluate(() => {
        const vw = document.documentElement.clientWidth;
        if (document.documentElement.scrollWidth > vw) return `page is ${document.documentElement.scrollWidth}px wide`;
        const el = [...document.querySelectorAll('main *, header *, footer *')].find((e) => e.getBoundingClientRect().right > vw + 1 && !e.closest('[hidden], .sr-only'));
        return el ? `${el.tagName}.${el.className} ends at ${Math.round(el.getBoundingClientRect().right)}px` : '';
      });
      expect(over, `${path} at ${width}×${height}`).toBe('');
    }
  }
});

test('large screens fill the display instead of a thin strip', async ({ page }, testInfo) => {
  test.skip(isMobile(testInfo), 'Desktop and TV sizes only');
  // [viewport, minimum share of the screen width the content column must fill]
  for (const [width, height, share] of [[1920, 1080, 0.6], [2560, 1440, 0.65], [3440, 1440, 0.6], [3840, 2160, 0.6], [7680, 4320, 0.6]]) {
    await page.setViewportSize({ width, height });
    await open(page, './');
    const r = await page.evaluate(() => ({
      wrap: document.querySelector('main .wrap').getBoundingClientRect().width,
      body: parseFloat(getComputedStyle(document.body).fontSize) * Number(getComputedStyle(document.documentElement).zoom || 1),
    }));
    expect(r.wrap / width, `content column at ${width}×${height}`).toBeGreaterThanOrEqual(share);
    expect(r.body, `body text at ${width}×${height}`).toBeGreaterThanOrEqual(16 * (width / 1920) * 0.8);
  }
});

test('search results stay on screen on a 4K display', async ({ page }, testInfo) => {
  test.skip(isMobile(testInfo), 'Desktop and TV sizes only');
  await page.setViewportSize({ width: 3840, height: 2160 });
  await open(page, './');
  await page.keyboard.press('/');
  await page.keyboard.type('a');
  const list = page.locator('.search-results');
  await expect(list).toBeVisible();
  const box = await list.boundingBox();
  expect((box?.y ?? 0) + (box?.height ?? 0)).toBeLessThanOrEqual(2160);
});

test('small links have a tap area of at least 36px on touch screens', async ({ page }, testInfo) => {
  test.skip(!isMobile(testInfo), 'Touch screens only');
  for (const path of ['about', 'contact']) {
    await open(page, path);
    const small = await page.evaluate(() => [...document.querySelectorAll('.crumbs a, .leader-links a, .legal a, .flag-links a, .pub-card > a')]
      .filter((a) => a.getClientRects().length)
      .map((a) => {
        const r = a.getBoundingClientRect(), after = getComputedStyle(a, '::after');
        return { text: a.textContent.trim(), h: r.height - 2 * parseFloat(after.top || '0') };
      })
      .filter((t) => t.h < 36));
    expect(small, `${path}: links with a small tap area`).toEqual([]);
  }
});
