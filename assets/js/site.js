/**
 * Site — pieces shared by every Islam Q&A page: header and footer text,
 * language links, excerpts, and the live search suggestions.
 */
var Site = (function () {
  'use strict';

  var ARROW = '<svg class="sk-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';

  function escapeHtml(str) {
    var d = document.createElement('div');
    d.textContent = str == null ? '' : String(str);
    return d.innerHTML;
  }

  function plain(md) {
    return String(md || '')
      .replace(/^#+\s.*$/gm, ' ')
      .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1')
      .replace(/[*_>`#|]/g, '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function excerpt(md, max) {
    var text = plain(md);
    var m = text.match(/^(.{20,}?[.!?؟])\s/);
    var s = m ? m[1] : text;
    if (s.length > max) s = s.slice(0, max - 1).replace(/\s+\S*$/, '') + '…';
    return s;
  }

  function minutes(md) {
    return Math.max(1, Math.round(plain(md).split(' ').length / 200));
  }

  function pageName() {
    var last = location.pathname.split('/').pop();
    return last || 'index.html';
  }

  function langUrl(code) {
    var params = new URLSearchParams(location.search);
    params.set('lang', code);
    return pageName() + '?' + params.toString();
  }

  function tagsHtml(tags) {
    return (tags || []).map(function (t) { return '<span class="sk-tag">' + escapeHtml(t) + '</span>'; }).join('');
  }

  /** Header labels, language link and footer — the same on every page. */
  function initChrome(lang) {
    var langs = I18n.getLanguages();
    var home = document.getElementById('nav-home');
    var browse = document.getElementById('nav-browse');
    home.textContent = I18n.t('back_home');
    home.href = 'index.html?lang=' + lang;
    browse.textContent = I18n.t('browse');
    browse.href = 'browse.html?lang=' + lang + '&browse=1';
    document.getElementById('brand-link').href = 'index.html?lang=' + lang;

    var other = Object.keys(langs).filter(function (c) { return c !== lang; })[0];
    var switcher = document.getElementById('nav-lang');
    if (other) {
      switcher.textContent = langs[other].label;
      switcher.href = langUrl(other);
      switcher.setAttribute('lang', other);
      switcher.setAttribute('hreflang', other);
    } else {
      switcher.hidden = true;
    }

    document.getElementById('footer-lang-label').textContent = I18n.t('languages');
    document.getElementById('footer-copyright').textContent = I18n.t('footer_no_copyright');
    var box = document.getElementById('lang-switcher');
    box.innerHTML = '';
    Object.keys(langs).forEach(function (code) {
      var a = document.createElement('a');
      a.href = langUrl(code);
      a.textContent = langs[code].label;
      a.setAttribute('lang', code);
      if (code === lang) { a.classList.add('active'); a.setAttribute('aria-current', 'true'); }
      box.appendChild(a);
    });
  }

  /**
   * Live suggestions under a search field.
   * Arrow keys move through them; Enter opens one or runs a full search.
   */
  function suggest(input, box, entries, lang, submit) {
    var active = -1;
    var items = [];

    function close() {
      box.hidden = true;
      input.setAttribute('aria-expanded', 'false');
      active = -1;
    }

    function highlight() {
      items.forEach(function (el, i) {
        el.classList.toggle('is-active', i === active);
        el.setAttribute('aria-selected', i === active ? 'true' : 'false');
      });
    }

    function render() {
      var q = input.value.trim();
      if (q.length < 2) { close(); return; }
      var hits = DataStore.searchEntries(entries, q).slice(0, 5);
      var html = '';
      hits.forEach(function (e, i) {
        html += '<a role="option" id="sg-' + i + '" href="article.html?id=' + e.id + '&lang=' + lang + '"><span><strong>' +
          escapeHtml(e.question) + '</strong><small>' + escapeHtml(excerpt(e.answer, 110)) + '</small></span>' + ARROW + '</a>';
      });
      if (!hits.length) html += '<div class="suggest__empty">' + escapeHtml(I18n.t('no_results')) + '</div>';
      html += '<a role="option" id="sg-all" class="suggest__all" href="browse.html?lang=' + lang + '&q=' + encodeURIComponent(q) + '">' +
        escapeHtml(I18n.t('search')) + ': “' + escapeHtml(q) + '”</a>';
      box.innerHTML = html;
      items = Array.prototype.slice.call(box.querySelectorAll('a'));
      active = -1;
      box.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    }

    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-controls', box.id);
    box.setAttribute('role', 'listbox');

    var timer;
    input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(render, 120); });
    input.addEventListener('focus', function () { if (input.value.trim().length >= 2) render(); });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown' && !box.hidden) { e.preventDefault(); active = Math.min(items.length - 1, active + 1); highlight(); }
      else if (e.key === 'ArrowUp' && !box.hidden) { e.preventDefault(); active = Math.max(-1, active - 1); highlight(); }
      else if (e.key === 'Escape') { close(); }
      else if (e.key === 'Enter') {
        e.preventDefault();
        if (active >= 0 && items[active]) window.location.href = items[active].href;
        else { close(); submit(input.value.trim()); }
      }
    });
    document.addEventListener('click', function (e) {
      if (!box.contains(e.target) && e.target !== input) close();
    });
    // "/" jumps to the search field, like most search-first sites
    document.addEventListener('keydown', function (e) {
      if (e.key === '/' && document.activeElement !== input && !/^(INPUT|TEXTAREA|SELECT)$/.test((document.activeElement || {}).tagName || '')) {
        e.preventDefault();
        input.focus();
      }
    });
  }

  return {
    ARROW: ARROW,
    escapeHtml: escapeHtml,
    excerpt: excerpt,
    minutes: minutes,
    tagsHtml: tagsHtml,
    initChrome: initChrome,
    suggest: suggest
  };
})();
