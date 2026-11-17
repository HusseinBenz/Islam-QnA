/**
 * Site — pieces shared by every Islam Q&A page: header and footer text,
 * language links, topic labels and icons, question cards, book citations,
 * and the live search suggestions.
 */
var Site = (function () {
  'use strict';

  var ARROW = '<svg class="sk-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';
  var STAR_PATH = 'M11 0L7.78 3.22L7.78 7.78L3.22 7.78L0 11L-3.22 7.78L-7.78 7.78L-7.78 3.22L-11 0L-7.78 -3.22L-7.78 -7.78L-3.22 -7.78L0 -11L3.22 -7.78L7.78 -7.78L7.78 -3.22Z';

  /* One line icon per topic, drawn on a 24px grid. */
  var TOPIC_ICONS = {
    'god': '<circle cx="12" cy="12" r="3.6"/><path d="M12 2.5v3M12 18.5v3M2.5 12h3M18.5 12h3M5.3 5.3l2.1 2.1M16.6 16.6l2.1 2.1M5.3 18.7l2.1-2.1M16.6 7.4l2.1-2.1"/>',
    'quran': '<path d="M12 7.5v12.5"/><path d="M3 5.5h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5v12.5h-6a3 3 0 0 0-3 2 3 3 0 0 0-3-2H3z"/>',
    'prophet': '<path d="M3.5 20.5h17"/><path d="M6 20.5v-7a6 6 0 0 1 12 0v7"/><path d="M12 3.5v4"/><path d="M10.5 20.5v-3.5a1.5 1.5 0 0 1 3 0v3.5"/>',
    'misconceptions': '<circle cx="12" cy="12" r="9"/><path d="M9.6 9.3a2.5 2.5 0 0 1 4.8.9c0 1.7-2.4 2.1-2.4 3.6"/><path d="M12 17h.01"/>',
    'women': '<path d="M12 3.5v17"/><path d="M8 20.5h8"/><path d="M5 7h14"/><path d="M5 7l-2.6 6.5a2.8 2.8 0 0 0 5.2 0z"/><path d="M19 7l-2.6 6.5a2.8 2.8 0 0 0 5.2 0z"/>',
    'other-faiths': '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a13.5 13.5 0 0 1 0 18"/><path d="M12 3a13.5 13.5 0 0 0 0 18"/>',
    'history': '<path d="M6 3h12"/><path d="M6 21h12"/><path d="M7.5 3v3.5a4.5 4.5 0 0 0 9 0V3"/><path d="M7.5 21v-3.500a4.5 4.5 0 0 1 9 0V21"/>',
    'science': '<path d="M9 3h6"/><path d="M10 3v6.2l-5 8.600A2 2 0 0 0 6.700 21h10.600a2 2 0 0 0 1.700-3.200l-5-8.600V3"/><path d="M7.600 15.500h8.800"/>',
    'worship': '<path d="M5 21V10.500a7 7 0 0 1 14 0V21"/><path d="M3.500 21h17"/><path d="M9.500 21v-5.500a2.500 2.500 0 0 1 5 0V21"/>',
    'heart': '<path d="M12 20.500s-7.500-4.600-7.500-10.300A4.200 4.200 0 0 1 12 7.600a4.200 4.200 0 0 1 7.500 2.600c0 5.700-7.500 10.300-7.500 10.300z"/>'
  };

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

  /** The line shown under a question: its short answer, or the first sentence. */
  function teaser(entry, max) {
    var s = entry.summary || '';
    if (!s) return excerpt(entry.answer, max);
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

  function articleUrl(id, lang) { return 'article.html?id=' + id + '&lang=' + lang; }
  function tagUrl(tag, lang) { return 'browse.html?lang=' + lang + '&tag=' + encodeURIComponent(tag); }

  /** Readable, translated name of a tag. */
  function tagLabel(tag) {
    var map = I18n.t('tags');
    if (map && typeof map === 'object' && map[tag]) return map[tag];
    var s = String(tag).replace(/-/g, ' ');
    return s.charAt(0).toUpperCase() + s.slice(1);
  }

  /** Tags as small labels; pass a language to make them links to that topic. */
  function tagsHtml(tags, lang) {
    return (tags || []).map(function (t) {
      var label = escapeHtml(tagLabel(t));
      return lang
        ? '<a class="sk-tag" href="' + tagUrl(t, lang) + '">' + label + '</a>'
        : '<span class="sk-tag">' + label + '</span>';
    }).join('');
  }

  function topicIcon(tag, size) {
    var s = size || 22;
    var inner = TOPIC_ICONS[tag];
    if (!inner) return '<svg width="' + s + '" height="' + s + '" viewBox="-12 -12 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" d="' + STAR_PATH + '"/></svg>';
    return '<svg width="' + s + '" height="' + s + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + inner + '</svg>';
  }

  /** A question card, used on the home page and under articles. */
  function cardHtml(entry, lang, extraClass) {
    var first = entry.tags && entry.tags.length ? '<span class="sk-tag sk-tag--accent">' + escapeHtml(tagLabel(entry.tags[0])) + '</span>' : '';
    var second = entry.tags && entry.tags.length > 1 ? '<span class="sk-tag">' + escapeHtml(tagLabel(entry.tags[1])) + '</span>' : '';
    return '<a class="qcard sk-card sk-lift ' + (extraClass || '') + '" href="' + articleUrl(entry.id, lang) + '">' +
      '<div class="qcard__tags">' + first + second + '</div>' +
      '<h3 class="qcard__q">' + escapeHtml(entry.question) + '</h3>' +
      '<p class="qcard__excerpt">' + escapeHtml(teaser(entry, 150)) + '</p>' +
      '<div class="qcard__foot"><span>' + minutes(entry.answer) + ' ' + escapeHtml(I18n.t('min_read')) + '</span>' + ARROW + '</div>' +
      '</a>';
  }

  /** Author and title of a registered book in the reader's language. */
  function sourceText(source, lang) {
    function pick(field) { return (field && (field[lang] || field.en)) || ''; }
    var year = source.year && typeof source.year === 'object' ? pick(source.year) : (source.year || '');
    return { author: pick(source.author), title: pick(source.title), note: pick(source.note), year: year };
  }

  /** A title short enough for the spine of a book on the shelf. */
  function spineLabel(source, lang) {
    if (source.short && (source.short[lang] || source.short.en)) return source.short[lang] || source.short.en;
    var label = sourceText(source, lang).title.replace(/\s*\(.*$/, '').replace(/[:,].*$/, '');
    if (label.length > 26) label = label.slice(0, 25).replace(/\s+\S*$/, '') + '…';
    return label;
  }

  /** Header labels, language link and footer — the same on every page. */
  function initChrome(lang) {
    var langs = I18n.getLanguages();
    var home = document.getElementById('nav-home');
    var browse = document.getElementById('nav-browse');
    var library = document.getElementById('nav-library');
    home.textContent = I18n.t('back_home');
    home.href = 'index.html?lang=' + lang;
    browse.textContent = I18n.t('browse');
    browse.href = 'browse.html?lang=' + lang;
    if (library) {
      library.textContent = I18n.t('nav_library');
      library.href = 'library.html?lang=' + lang;
    }
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
    var links = document.getElementById('footer-links');
    if (links) {
      links.innerHTML =
        '<a href="index.html?lang=' + lang + '">' + escapeHtml(I18n.t('back_home')) + '</a>' +
        '<a href="browse.html?lang=' + lang + '">' + escapeHtml(I18n.t('browse')) + '</a>' +
        '<a href="library.html?lang=' + lang + '">' + escapeHtml(I18n.t('nav_library')) + '</a>';
    }
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
        html += '<a role="option" id="sg-' + i + '" href="' + articleUrl(e.id, lang) + '"><span><strong>' +
          escapeHtml(e.question) + '</strong><small>' + escapeHtml(teaser(e, 110)) + '</small></span>' + ARROW + '</a>';
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
    slashFocus(input);
  }

  /** "/" jumps to the search field, like most search-first sites. */
  function slashFocus(input) {
    document.addEventListener('keydown', function (e) {
      if (e.key === '/' && document.activeElement !== input && !/^(INPUT|TEXTAREA|SELECT)$/.test((document.activeElement || {}).tagName || '')) {
        e.preventDefault();
        input.focus();
      }
    });
  }

  return {
    ARROW: ARROW,
    STAR_PATH: STAR_PATH,
    escapeHtml: escapeHtml,
    excerpt: excerpt,
    teaser: teaser,
    minutes: minutes,
    articleUrl: articleUrl,
    tagUrl: tagUrl,
    tagLabel: tagLabel,
    tagsHtml: tagsHtml,
    topicIcon: topicIcon,
    cardHtml: cardHtml,
    sourceText: sourceText,
    spineLabel: spineLabel,
    initChrome: initChrome,
    suggest: suggest,
    slashFocus: slashFocus
  };
})();
