/* Atom Services admin — adapted from the Publishing Guru / Mark Attendance dashboard. */

// Mobile sidebar toggle.
(function () {
    var body = document.body;
    function set(open) { body.classList.toggle('sidebar-open', open); }
    document.querySelectorAll('[data-sidebar-open]').forEach(function (el) {
        el.addEventListener('click', function () { set(true); });
    });
    document.querySelectorAll('[data-sidebar-close]').forEach(function (el) {
        el.addEventListener('click', function () { set(false); });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
})();

// Confirm destructive buttons (data-confirm="Question?").
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-confirm]');
    if (btn && !confirm(btn.getAttribute('data-confirm'))) e.preventDefault();
});

// Login: show / hide the password. Hidden again before submitting, so browsers save it as a password.
(function () {
    var btn = document.querySelector('[data-password-toggle]');
    var input = document.getElementById('admin-password');
    if (!btn || !input) return;
    function set(show) {
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        btn.querySelector('.pw-show').hidden = show;
        btn.querySelector('.pw-hide').hidden = !show;
    }
    btn.addEventListener('click', function () {
        var end = input.selectionEnd;
        set(input.type === 'password');
        input.focus();
        try { input.setSelectionRange(end, end); } catch (e) {}
    });
    input.form.addEventListener('submit', function () { set(false); });
})();

// SEO editor: counters, live previews, JSON check, reset-to-default, unsaved-changes guard.
(function () {
    var form = document.querySelector('[data-dirty-form]');
    if (!form) return;

    function norm(v) { return v.replace(/\s+/g, ' ').trim(); }

    function autosize(el) {
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight + 2) + 'px';
    }

    function count(el) {
        var out = document.querySelector('[data-counter-for="' + el.id + '"]');
        if (!out) return;
        var n = norm(el.value).length, min = +out.dataset.min, max = +out.dataset.max;
        out.textContent = n + ' / ' + max;
        out.className = 'counter ' + (n === 0 ? 'len-empty' : n < min || n > max ? 'len-warn' : 'len-ok');
    }

    // Empty fields fall back to the default, exactly as on the site.
    function serp(el) {
        var text = norm(el.value) || norm(el.dataset.default || '');
        var max = el.dataset.serp === 'title' ? 60 : 160;
        document.querySelectorAll('[data-serp-out="' + el.dataset.serp + '"]').forEach(function (out) {
            out.textContent = text.length > max ? text.slice(0, max - 1).trim() + '…' : text;
        });
    }

    function ogPreview(el) {
        var img = document.querySelector('[data-og-preview]');
        if (!img) return;
        var v = norm(el.value) || el.dataset.default;
        var site = img.dataset.siteUrl || '';
        if (site && v.indexOf(site + '/') === 0) v = v.slice(site.length);   // own images: load from this server
        if (v.charAt(0) === '/') v = (window.ADMIN_BASE || '') + v;
        img.src = v;
    }

    function checkJson(el) {
        var out = form.querySelector('[data-json-state]');
        var v = el.value.trim();
        if (!v) { out.textContent = 'Default schema'; out.className = 'json-state'; return; }
        if (v === '[]') { out.textContent = 'No schema on this page'; out.className = 'json-state'; return; }
        try {
            var parsed = JSON.parse(v);
            var items = Array.isArray(parsed) ? parsed : [parsed];
            var bad = items.some(function (i) { return !i || typeof i !== 'object' || Array.isArray(i) || !(i['@type'] || i['@graph']); });
            out.textContent = bad ? 'Each item needs an "@type"' : '✓ Valid JSON · ' + items.length + (items.length === 1 ? ' block' : ' blocks');
            out.className = 'json-state ' + (bad ? 'len-warn' : 'len-ok');
        } catch (e) {
            out.textContent = 'Invalid JSON: ' + e.message;
            out.className = 'json-state len-warn';
        }
    }

    function edited(el) {
        var field = el.closest('[data-field]');
        if (field && el.dataset.default !== undefined) {
            field.classList.toggle('is-edited', norm(el.value) !== norm(el.dataset.default));
        }
    }

    function refresh(el) {
        if (el.hasAttribute('data-autosize')) autosize(el);
        if (el.dataset.serp) serp(el);
        if (el.hasAttribute('data-json')) checkJson(el);
        if (el.hasAttribute('data-og-image')) ogPreview(el);
        count(el);
    }

    form.querySelectorAll('.text-input').forEach(function (el) { refresh(el); edited(el); });
    window.addEventListener('resize', function () {
        form.querySelectorAll('[data-autosize]').forEach(autosize);
    });

    var dirty = false, label = form.querySelector('[data-dirty-label]');
    function markDirty() {
        if (dirty) return;
        dirty = true;
        document.body.classList.add('is-dirty');
        if (label) label.textContent = 'Unsaved changes';
    }
    form.addEventListener('input', function (e) {
        if (e.target.matches('.text-input')) { refresh(e.target); edited(e.target); }
        markDirty();
    });
    form.addEventListener('change', markDirty);
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    form.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-reset]');
        if (!btn) return;
        var el = btn.closest('[data-field]').querySelector('.text-input');
        el.value = el.dataset.default;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.focus();
    });
})();
