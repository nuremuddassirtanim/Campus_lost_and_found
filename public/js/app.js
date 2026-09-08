/* AIUB Lost & Found — AJAX, cookies, small UI helpers. No dependencies. */
(function () {
  'use strict';
  var BASE = (window.LF && window.LF.base) || '';
  var CSRF = (window.LF && window.LF.csrf) || '';

  // ── cookie helpers ──────────────────────────────────────────────
  var Cookie = {
    set: function (name, value, days) {
      document.cookie = name + '=' + encodeURIComponent(value) +
        ';path=' + (BASE || '') + '/;max-age=' + (days * 86400) + ';samesite=Lax';
    },
    get: function (name) {
      var hit = document.cookie.split('; ').filter(function (c) { return c.indexOf(name + '=') === 0; })[0];
      return hit ? decodeURIComponent(hit.slice(name.length + 1)) : null;
    },
    json: function (name, fallback) {
      try { return JSON.parse(Cookie.get(name)) || fallback; } catch (e) { return fallback; }
    }
  };

  // ── fetch wrapper ───────────────────────────────────────────────
  function post(path, body) {
    var isForm = body instanceof FormData;
    if (isForm) { body.append('csrf', CSRF); }
    return fetch(BASE + path, {
      method: 'POST',
      credentials: 'same-origin',
      headers: isForm ? { 'X-CSRF-Token': CSRF }
                      : { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: isForm ? body : JSON.stringify(Object.assign({ csrf: CSRF }, body))
    }).then(function (r) { return r.json().catch(function () { return { ok: false, error: 'Server error.' }; }); });
  }

  function formData(form) {
    var out = {}, fd = new FormData(form);
    fd.forEach(function (v, k) { out[k] = v; });
    return out;
  }

  function say(form, text, ok) {
    var el = form.querySelector('[data-msg]');
    if (!el) { return; }
    el.textContent = text || '';
    el.className = 'form-msg' + (text ? (ok ? ' is-ok' : ' is-err') : '');
  }

  function busy(form, on, label) {
    var btn = form.querySelector('button[type=submit]');
    if (!btn) { return; }
    if (on) { btn.dataset.label = btn.textContent; btn.textContent = label; }
    else if (btn.dataset.label) { btn.textContent = btn.dataset.label; }
    btn.disabled = on;
  }

  function wire(id, opts) {
    var form = document.getElementById(id);
    if (!form) { return; }
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      say(form, '');
      busy(form, true, opts.busy);
      var payload = opts.multipart ? new FormData(form) : formData(form);
      post(form.getAttribute('action').replace(BASE, ''), payload).then(function (res) {
        busy(form, false);
        if (!res.ok) { say(form, res.error || 'Something went wrong.', false); return; }
        say(form, res.message || 'Done.', true);
        if (opts.reset) { form.reset(); }
        if (res.next) { setTimeout(function () { location.href = BASE + res.next; }, 500); }
      });
    });
  }

  wire('login-form',    { busy: 'Signing in\u2026' });
  wire('register-form', { busy: 'Creating account\u2026' });
  wire('report-form',   { busy: 'Submitting\u2026', multipart: true, reset: true });
  wire('claim-form',    { busy: 'Filing claim\u2026', reset: true });

  // ── feed: live AJAX filtering, prefs remembered in a cookie ─────
  var filters = document.getElementById('feed-filters');
  var feed    = document.getElementById('feed');

  if (filters && feed) {
    var saved = Cookie.json('lf_prefs', {});
    Object.keys(saved).forEach(function (k) {
      var f = filters.elements[k];
      if (f) { f.value = saved[k]; }
    });

    var timer;
    var refresh = function () {
      var q = formData(filters);
      Cookie.set('lf_prefs', JSON.stringify(q), 90);
      var qs = Object.keys(q).map(function (k) { return k + '=' + encodeURIComponent(q[k]); }).join('&');
      fetch(BASE + '/api/items?' + qs, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) { if (res.ok) { render(res.items); } });
    };

    filters.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(refresh, 220); });
    filters.addEventListener('change', refresh);
    if (Object.keys(saved).length) { refresh(); }

    var seen = Cookie.json('lf_seen', []);
    feed.addEventListener('click', function (e) {
      var link = e.target.closest('a[href*="/item/"]');
      if (!link) { return; }
      var ref = link.closest('.card').dataset.ref;
      if (ref && seen.indexOf(ref) === -1) { seen.push(ref); Cookie.set('lf_seen', JSON.stringify(seen.slice(-80)), 30); }
    });

    function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]; }); }

    function render(items) {
      if (!items.length) { feed.innerHTML = '<p class="muted">Nothing matches those filters.</p>'; return; }
      feed.innerHTML = items.map(function (i) {
        var isNew = seen.indexOf(i.ref) === -1 ? ' <span class="tag tag-outline">new to you</span>' : '';
        return '<article class="card blueprint" data-ref="' + esc(i.ref) + '">' +
          '<p class="kicker">' + esc(i.type[0].toUpperCase() + i.type.slice(1)) + ' \u00b7 ' + esc(i.category) + '</p>' +
          '<h3>' + esc(i.title) + '</h3>' +
          '<p class="muted small">' + esc(i.location) + ' \u00b7 ' + esc(i.date) + '</p>' +
          '<div class="card-foot"><span class="tag tag-' + esc(i.status) + '">' +
            esc(i.status[0].toUpperCase() + i.status.slice(1)) + '</span>' + isNew +
          '<a class="btn btn-ghost" href="' + esc(i.url) + '">View details</a></div>' +
          '<i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i></article>';
      }).join('');
    }
  }

  // ── admin: approve / reject without a page reload ───────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-item-status], [data-claim-status]');
    if (!btn) { return; }
    var isItem = btn.hasAttribute('data-item-status');
    var path   = isItem ? '/api/admin/item-status' : '/api/admin/claim-status';
    var status = btn.getAttribute(isItem ? 'data-item-status' : 'data-claim-status');
    var row    = btn.closest('[data-row]');
    btn.disabled = true;
    post(path, { id: btn.dataset.id, status: status }).then(function (res) {
      if (!res.ok) { btn.disabled = false; alert(res.error || 'Could not update.'); return; }
      row.classList.add('is-done');
      setTimeout(function () { row.remove(); }, 260);
      fetch(BASE + '/api/stats', { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (s) {
          if (!s.ok) { return; }
          var boxes = document.querySelectorAll('.stat b');
          if (boxes.length === 3) {
            boxes[0].textContent = s.stats.pending_items;
            boxes[1].textContent = s.stats.pending_claims;
            boxes[2].textContent = s.stats.returned_items;
          }
        });
    });
  });
})();
