// End-to-end tests in real browser engines: Chromium (Chrome) and WebKit (Safari), desktop and phone.
// Run: npm run test:e2e        Against live: BASE_URL=https://atomservices.org/ npm run test:e2e
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: 'tests/e2e',
  timeout: 30000,
  retries: 0,
  // WebKit on Windows is CPU-hungry; more parallel workers make timing checks measure the laptop, not the site.
  workers: 2,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost/atomservices/',
    // Reduced motion turns off smooth scrolling (the site honours it), so clicks never land mid-scroll.
    reducedMotion: 'reduce',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'chrome-desktop', use: { ...devices['Desktop Chrome'] } },
    { name: 'safari-desktop', use: { ...devices['Desktop Safari'] } },
    { name: 'chrome-mobile', use: { ...devices['Pixel 7'] } },
    { name: 'safari-mobile', use: { ...devices['iPhone 14'] } },
  ],
});
