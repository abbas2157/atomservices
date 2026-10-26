// Unit tests for assets/js/lib.js. Run with: npm run test:js
const test = require('node:test');
const assert = require('node:assert/strict');
const L = require('../../assets/js/lib.js');

test('parseUtm reads utm_* keys and ignores others', () => {
  assert.deepEqual(L.parseUtm('?utm_source=linkedin&utm_medium=social&ref=x&utm_campaign=gitex+2026'),
    { utm_source: 'linkedin', utm_medium: 'social', utm_campaign: 'gitex 2026' });
});

test('parseUtm returns {} for empty or missing query', () => {
  assert.deepEqual(L.parseUtm(''), {});
  assert.deepEqual(L.parseUtm(undefined), {});
  assert.deepEqual(L.parseUtm('?utm_source='), {});
});

test('parseUtm trims and caps values at 100 chars', () => {
  const r = L.parseUtm('?utm_term=' + 'a'.repeat(300));
  assert.equal(r.utm_term.length, 100);
  assert.equal(L.parseUtm('?utm_source=%20fb%20').utm_source, 'fb');
});

test('mergeUtm keeps first-touch attribution', () => {
  assert.deepEqual(L.mergeUtm({ utm_source: 'first' }, { utm_source: 'second' }), { utm_source: 'first' });
  assert.deepEqual(L.mergeUtm({}, { utm_source: 'second' }), { utm_source: 'second' });
  assert.deepEqual(L.mergeUtm(null, undefined), {});
});

test('daysUntil rounds up partial days', () => {
  const day = 86400000;
  assert.equal(L.daysUntil(10 * day, 0), 10);
  assert.equal(L.daysUntil(10 * day, 1), 10);
  assert.equal(L.daysUntil(10 * day, 9 * day + 1), 1);
  assert.equal(L.daysUntil(10 * day, 10 * day), 0);
  assert.ok(L.daysUntil(0, day) <= 0);
});

test('validateContact flags missing name and email', () => {
  assert.deepEqual(Object.keys(L.validateContact({ form: 'meeting', name: ' ', email: '' })).sort(), ['email', 'name']);
});

test('validateContact flags malformed emails', () => {
  for (const email of ['a', 'a@', 'a@b', 'a b@c.com', '@c.com']) {
    assert.ok(L.validateContact({ form: 'meeting', name: 'A', email }).email, email);
  }
  for (const email of ['a@b.co', 'first.last+tag@company.com.pk']) {
    assert.equal(L.validateContact({ form: 'meeting', name: 'A', email }).email, undefined, email);
  }
});

test('validateContact requires a message only on the contact form', () => {
  assert.equal(L.validateContact({ form: 'meeting', name: 'A', email: 'a@b.co', message: '' }).message, undefined);
  assert.ok(L.validateContact({ form: 'contact', name: 'A', email: 'a@b.co', message: 'short' }).message);
  assert.deepEqual(L.validateContact({ form: 'contact', name: 'A', email: 'a@b.co', message: 'A long enough message' }), {});
});

const items = [
  { title: 'Mark Attendance', keywords: 'face recognition hr', text: 'Attendance for offices.', url: '#products' },
  { title: 'Atomshop.pk', keywords: 'installments bnpl', text: 'Installment shopping.', url: '#products' },
  { title: 'Services', keywords: 'mobile apps', text: 'We also track attendance projects.', url: 'services.php' },
  { title: 'Café Menu', keywords: '', text: 'Accent test', url: '#x' },
];

test('search ranks title matches above body matches', () => {
  const r = L.search(items, 'attendance');
  assert.equal(r[0].title, 'Mark Attendance');
  assert.equal(r[1].title, 'Services');
});

test('search requires every word to match', () => {
  assert.deepEqual(L.search(items, 'face recognition').map(i => i.title), ['Mark Attendance']);
  assert.deepEqual(L.search(items, 'face installments'), []);
});

test('search is case- and accent-insensitive', () => {
  assert.equal(L.search(items, 'BNPL')[0].title, 'Atomshop.pk');
  assert.equal(L.search(items, 'cafe')[0].title, 'Café Menu');
});

test('search returns nothing for blank queries and respects limit', () => {
  assert.deepEqual(L.search(items, '   '), []);
  assert.equal(L.search(items, 'a', 2).length, 2);
});

test('scrollProgress is clamped between 0 and 1', () => {
  assert.equal(L.scrollProgress(0, 2000, 1000), 0);
  assert.equal(L.scrollProgress(500, 2000, 1000), 0.5);
  assert.equal(L.scrollProgress(5000, 2000, 1000), 1);
  assert.equal(L.scrollProgress(-50, 2000, 1000), 0);
  assert.equal(L.scrollProgress(100, 800, 1000), 0);
});

test('validateContact requires a message on the contact-page enquiry form', () => {
  assert.equal(L.validateContact({ form: 'enquiry', name: 'A', email: 'a@b.co', message: 'hi' }).message,
    'Please write a short message (at least 10 characters).');
  assert.deepEqual(L.validateContact({ form: 'enquiry', name: 'A', email: 'a@b.co', message: 'A proper message here' }), {});
});

test('client and server agree on the contact rules', () => {
  // Mirrors tests/Unit/ContactHandlerTest: the 10-character minimum and the error wording.
  const e = L.validateContact({ form: 'contact', name: '', email: 'bad', message: '123456789' });
  assert.equal(e.name, 'Please enter your name.');
  assert.equal(e.email, 'Please enter a valid email address, like name@company.com.');
  assert.match(e.message, /at least 10 characters/);
});
