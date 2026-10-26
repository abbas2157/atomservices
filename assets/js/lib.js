/* Pure helpers shared by main.js and the unit tests in tests/js. No DOM access here. */
(function (root, factory) {
  if (typeof module === 'object' && module.exports) module.exports = factory();
  else root.AtomLib = factory();
})(typeof self !== 'undefined' ? self : this, function () {
  "use strict";

  var UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  /** Pull utm_* values out of a query string ("?utm_source=x&..."). Returns {} when there are none. */
  function parseUtm(search) {
    var out = {};
    var params = new URLSearchParams(search || '');
    UTM_KEYS.forEach(function (k) {
      var v = (params.get(k) || '').trim().slice(0, 100);
      if (v) out[k] = v;
    });
    return out;
  }

  /** First-touch attribution: keep stored UTM values unless there are none yet. */
  function mergeUtm(stored, current) {
    return stored && Object.keys(stored).length ? stored : (current || {});
  }

  /** Whole days until a UTC timestamp; <= 0 means it has started. */
  function daysUntil(targetMs, nowMs) {
    return Math.ceil((targetMs - nowMs) / 86400000);
  }

  /**
   * Client-side checks mirroring includes/contact_handler.php, so people get instant feedback.
   * values: {form, name, email, message}. Returns {field: message} for each problem.
   */
  function validateContact(values) {
    var errors = {};
    var name = (values.name || '').trim();
    var email = (values.email || '').trim();
    if (!name) errors.name = 'Please enter your name.';
    if (!email) errors.email = 'Please enter your email address.';
    else if (!EMAIL_RE.test(email)) errors.email = 'Please enter a valid email address, like name@company.com.';
    if ((values.form === 'contact' || values.form === 'enquiry') && (values.message || '').trim().length < 10) {
      errors.message = values.form === 'contact'
        ? 'Please tell us a little about your project (at least 10 characters).'
        : 'Please write a short message (at least 10 characters).';
    }
    return errors;
  }

  function normalise(s) {
    return String(s || '').toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '');
  }

  /**
   * Rank search items ({title, text, url, keywords?}) against a query. Every query word must match
   * somewhere; title matches outrank keyword matches, which outrank body-text matches.
   */
  function search(items, query, limit) {
    var words = normalise(query).split(/\s+/).filter(Boolean);
    if (!words.length) return [];
    var scored = [];
    items.forEach(function (item) {
      var title = normalise(item.title), keys = normalise(item.keywords), text = normalise(item.text);
      var score = 0;
      for (var i = 0; i < words.length; i++) {
        var w = words[i];
        var s = (title.indexOf(w) === 0 ? 12 : 0) + (title.indexOf(w) > -1 ? 8 : 0) +
                (keys.indexOf(w) > -1 ? 5 : 0) + (text.indexOf(w) > -1 ? 2 : 0);
        if (!s) return;
        score += s;
      }
      scored.push({ item: item, score: score });
    });
    scored.sort(function (a, b) { return b.score - a.score; });
    return scored.slice(0, limit || 8).map(function (r) { return r.item; });
  }

  /** Fraction (0..1) of the page scrolled, for the progress bar. */
  function scrollProgress(scrollTop, scrollHeight, clientHeight) {
    var max = scrollHeight - clientHeight;
    if (max <= 0) return 0;
    return Math.min(1, Math.max(0, scrollTop / max));
  }

  return {
    UTM_KEYS: UTM_KEYS,
    parseUtm: parseUtm,
    mergeUtm: mergeUtm,
    daysUntil: daysUntil,
    validateContact: validateContact,
    search: search,
    scrollProgress: scrollProgress
  };
});
