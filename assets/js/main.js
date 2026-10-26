(function () {
  "use strict";
  var L = window.AtomLib;
  var doc = document.documentElement;

  // Loading animation: fade the preloader out as soon as the DOM is ready.
  var preloader = document.getElementById('preloader');
  if (preloader) {
    preloader.classList.add('done');
    setTimeout(function () { preloader.remove(); }, 400);
  }

  // GITEX countdown. The server renders the initial value; this keeps it right for cached pages.
  var days = document.getElementById('days');
  if (days) {
    var d = L.daysUntil(Date.parse(days.getAttribute('data-opens')), Date.now());
    if (d > 0) {
      days.textContent = d;
      days.nextElementSibling.textContent = (d === 1 ? 'day' : 'days') + ' until the expo opens';
    } else {
      days.textContent = 'Now';
      days.nextElementSibling.textContent = 'GITEX Global is open. Come and find us.';
    }
  }

  // Mobile menu
  var menuBtn = document.querySelector('.menu-toggle');
  var nav = document.getElementById('site-nav');
  function setMenu(open) {
    menuBtn.setAttribute('aria-expanded', String(open));
    menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    nav.classList.toggle('open', open);
    document.body.classList.toggle('menu-open', open);
  }
  if (menuBtn && nav) {
    menuBtn.addEventListener('click', function () { setMenu(menuBtn.getAttribute('aria-expanded') !== 'true'); });
    nav.addEventListener('click', function (e) { if (e.target.closest('a')) setMenu(false); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('open')) { setMenu(false); menuBtn.focus(); }
    });
    window.matchMedia('(min-width: 1101px)').addEventListener('change', function (m) { if (m.matches) setMenu(false); });
  }

  // Scroll progress bar + back-to-top button
  var bar = document.getElementById('progress');
  var toTop = document.getElementById('to-top');
  var ticking = false;
  function onScroll() {
    ticking = false;
    var y = window.scrollY || doc.scrollTop;
    if (bar) bar.style.transform = 'scaleX(' + L.scrollProgress(y, doc.scrollHeight, window.innerHeight) + ')';
    if (toTop) toTop.classList.toggle('show', y > 600);
  }
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
  onScroll();
  if (toTop) toTop.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    var skipTarget = document.getElementById('top');
    if (skipTarget) skipTarget.focus({ preventScroll: true });
  });

  // Copy buttons. navigator.clipboard needs HTTPS, so fall back to execCommand elsewhere.
  // Also used when the Clipboard API refuses (some iOS versions). The textarea is pinned on screen and
  // focused without scrolling, otherwise iOS jumps to it.
  function copyFallback(t) {
    return new Promise(function (resolve, reject) {
      var ta = document.createElement('textarea');
      ta.value = t;
      ta.setAttribute('readonly', '');
      ta.style.cssText = 'position:fixed;top:0;left:0;width:1px;height:1px;opacity:0;font-size:16px';
      document.body.appendChild(ta);
      ta.focus({ preventScroll: true });
      ta.setSelectionRange(0, t.length);
      var ok = false;
      try { ok = document.execCommand('copy'); } catch (e) {}
      ta.remove();
      ok ? resolve() : reject();
    });
  }
  function copyText(t) {
    if (navigator.clipboard && window.isSecureContext) {
      // WebKit can leave writeText pending indefinitely; don't leave the button stuck on "Copy".
      var timeout = new Promise(function (_, reject) { setTimeout(reject, 1000); });
      var write;
      try { write = navigator.clipboard.writeText(t); } catch (e) { return copyFallback(t); }
      return Promise.race([write, timeout]).catch(function () { return copyFallback(t); });
    }
    return copyFallback(t);
  }
  document.querySelectorAll('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var label = b.textContent;
      copyText(b.getAttribute('data-copy')).then(function () {
        b.textContent = 'Copied'; b.classList.add('copied');
      }, function () {
        b.textContent = 'Press Ctrl+C';
      }).then(function () {
        setTimeout(function () { b.textContent = label; b.classList.remove('copied'); }, 1800);
      });
    });
  });

  // Cookie banner. The site only uses essential browser storage, so this is a notice with one button.
  var banner = document.getElementById('cookie-banner');
  function stored(key) { try { return localStorage.getItem(key); } catch (e) { return null; } }
  if (banner && stored('atom-cookies') !== 'ok') {
    banner.hidden = false;
    banner.querySelector('button').addEventListener('click', function () {
      try { localStorage.setItem('atom-cookies', 'ok'); } catch (e) {}
      banner.hidden = true;
    });
  }

  // UTM tracking: remember the campaign that first brought this visitor in (this tab only),
  // and send it with any form they submit.
  var utm = {};
  try {
    utm = L.mergeUtm(JSON.parse(sessionStorage.getItem('atom-utm') || '{}'), L.parseUtm(location.search));
    sessionStorage.setItem('atom-utm', JSON.stringify(utm));
  } catch (e) { utm = L.parseUtm(location.search); }

  // Site search
  var dialog = document.getElementById('search');
  var searchData = document.getElementById('search-index');
  if (dialog && searchData) {
    var index = JSON.parse(searchData.textContent);
    var input = dialog.querySelector('input');
    var list = dialog.querySelector('.search-results');
    var openers = document.querySelectorAll('[data-open-search]');
    var lastFocus = null;

    var render = function () {
      var q = input.value;
      var hits = L.search(index, q, 8);
      list.innerHTML = '';
      if (!q.trim()) { list.hidden = true; return; }
      list.hidden = false;
      if (!hits.length) {
        var li = document.createElement('li');
        li.className = 'search-empty';
        li.textContent = 'No results for "' + q.trim() + '". Try "installments", "attendance" or "services".';
        list.appendChild(li);
        return;
      }
      hits.forEach(function (h) {
        var li = document.createElement('li');
        var a = document.createElement('a');
        a.href = h.url;
        var b = document.createElement('b'); b.textContent = h.title;
        var s = document.createElement('span'); s.textContent = h.text;
        a.appendChild(b); a.appendChild(s);
        if (/^https?:/.test(h.url)) { a.target = '_blank'; a.rel = 'noopener'; }
        li.appendChild(a);
        list.appendChild(li);
      });
    };
    var openSearch = function () {
      lastFocus = document.activeElement;
      if (nav && nav.classList.contains('open')) setMenu(false);
      dialog.hidden = false;
      document.body.classList.add('search-open');
      input.focus();
      render();
    };
    var closeSearch = function () {
      dialog.hidden = true;
      document.body.classList.remove('search-open');
      if (lastFocus) lastFocus.focus();
    };
    openers.forEach(function (o) { o.addEventListener('click', openSearch); });
    input.addEventListener('input', render);
    dialog.addEventListener('click', function (e) {
      if (e.target === dialog || e.target.closest('[data-close-search]') || e.target.closest('.search-results a')) closeSearch();
    });
    document.addEventListener('keydown', function (e) {
      var typing = /INPUT|TEXTAREA|SELECT/.test((e.target.tagName || '')) || e.target.isContentEditable;
      if (e.key === 'Escape' && !dialog.hidden) closeSearch();
      else if (dialog.hidden && ((e.key === '/' && !typing) || (e.key.toLowerCase() === 'k' && (e.ctrlKey || e.metaKey)))) {
        e.preventDefault(); openSearch();
      } else if (!dialog.hidden && e.key === 'Enter' && document.activeElement === input) {
        var first = list.querySelector('a');
        if (first) { e.preventDefault(); first.click(); }
      }
    });
  }

  // Forms: client-side checks, inline error states, JSON submit, success state.
  document.querySelectorAll('form[data-ajax]').forEach(function (form) {
    var msg = form.querySelector('.form-msg');
    var button = form.querySelector('button[type="submit"]');
    var success = form.parentElement.querySelector('.form-success');

    function setStatus(text, kind) {
      msg.hidden = !text;
      msg.textContent = text || '';
      msg.className = 'form-msg' + (kind ? ' ' + kind : '');
    }
    function clearErrors() {
      form.querySelectorAll('.field.has-error').forEach(function (f) { f.classList.remove('has-error'); });
      form.querySelectorAll('[aria-invalid]').forEach(function (i) { i.removeAttribute('aria-invalid'); });
      form.querySelectorAll('.field-error').forEach(function (p) { p.remove(); });
    }
    function showErrors(errors) {
      var first = null;
      Object.keys(errors).forEach(function (name) {
        var input = form.elements[name];
        if (!input) return;
        var field = input.closest('.field');
        var p = document.createElement('p');
        p.className = 'field-error';
        p.id = input.id + '-error';
        p.textContent = errors[name];
        field.classList.add('has-error');
        field.appendChild(p);
        input.setAttribute('aria-invalid', 'true');
        input.setAttribute('aria-describedby', p.id);
        if (!first) first = input;
      });
      if (first) first.focus();
    }
    // Clear a field's error as soon as the person edits it.
    form.addEventListener('input', function (e) {
      var field = e.target.closest('.field.has-error');
      if (!field) return;
      field.classList.remove('has-error');
      e.target.removeAttribute('aria-invalid');
      var p = field.querySelector('.field-error');
      if (p) p.remove();
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearErrors();
      var f = form.elements;
      var values = { form: f.form.value, name: f.name.value, email: f.email.value, message: f.message ? f.message.value : '' };
      var errors = L.validateContact(values);
      if (Object.keys(errors).length) {
        setStatus('Please fix the highlighted fields.', 'error');
        showErrors(errors);
        return;
      }

      var data = new FormData(form);
      Object.keys(utm).forEach(function (k) { data.set(k, utm[k]); });

      button.disabled = true;
      button.classList.add('loading');
      setStatus('Sending…');

      fetch(form.getAttribute('action'), { method: 'POST', body: data, headers: { 'Accept': 'application/json' } })
        .then(function (r) {
          return r.json().catch(function () { return { ok: false, message: 'Unexpected response from the server (' + r.status + ').' }; });
        })
        .then(function (res) {
          if (res.ok) {
            form.reset();
            setStatus('');
            if (success) {
              success.querySelector('.form-success-text').textContent = res.message;
              form.hidden = true;
              success.hidden = false;
              success.focus();
            } else {
              setStatus(res.message, 'ok');
            }
          } else {
            setStatus(res.message || 'Something went wrong.', 'error');
            if (res.errors) showErrors(res.errors);
          }
        })
        .catch(function () {
          setStatus('We could not reach the server. Check your connection and try again, or email info@atomservices.org.', 'error');
        })
        .then(function () { button.disabled = false; button.classList.remove('loading'); });
    });

    if (success) {
      var again = success.querySelector('[data-send-another]');
      if (again) again.addEventListener('click', function () {
        success.hidden = true;
        form.hidden = false;
        form.elements.name.focus();
      });
    }
  });
})();
