/**
 * i18n Module — Handles language detection, string translation, and RTL support
 */
const I18n = (function () {
  let currentLang = 'en';
  let strings = {};
  let languages = {};

  /** Get language from URL param or localStorage */
  function detectLang(defaultLang) {
    const params = new URLSearchParams(window.location.search);
    const paramLang = params.get('lang');
    if (paramLang && paramLang.match(/^[a-z]{2,8}$/)) {
      return paramLang;
    }
    try {
      const stored = localStorage.getItem('preferred_lang');
      if (stored && stored.match(/^[a-z]{2,8}$/)) return stored;
    } catch (e) { /* ignore */ }
    return defaultLang || 'en';
  }

  /** Initialize i18n with loaded data */
  function init(uiStrings, langs, defaultLang) {
    languages = langs || {};
    currentLang = detectLang(defaultLang);
    // Fall back to default if language not available
    if (!uiStrings[currentLang]) {
      currentLang = defaultLang || 'en';
    }
    strings = uiStrings[currentLang] || uiStrings['en'] || {};
    try {
      localStorage.setItem('preferred_lang', currentLang);
    } catch (e) { /* ignore */ }
    applyDirection();
    applyFont();
    return currentLang;
  }

  /** Translate a key */
  function t(key) {
    return strings[key] || key;
  }

  /** Translate with sprintf-style %d replacements */
  function tf(key) {
    var tpl = strings[key] || key;
    var args = Array.prototype.slice.call(arguments, 1);
    var i = 0;
    return tpl.replace(/%d/g, function () {
      return args[i++] !== undefined ? args[i - 1] : '%d';
    });
  }

  /** Apply text direction to <html> */
  function applyDirection() {
    var dir = (languages[currentLang] && languages[currentLang].dir) || 'ltr';
    document.documentElement.setAttribute('dir', dir);
    document.documentElement.setAttribute('lang', currentLang);
  }

  /** Apply language-specific font */
  function applyFont() {
    var langData = languages[currentLang];
    if (!langData) return;
    // Load font URL if present
    if (langData.fontUrl) {
      var existing = document.querySelector('link[data-lang-font]');
      if (existing) existing.remove();
      var link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = langData.fontUrl;
      link.setAttribute('data-lang-font', currentLang);
      document.head.appendChild(link);
    }
    // Apply font family
    if (langData.font) {
      document.body.style.fontFamily = langData.font;
    }
  }

  /** Get current language code */
  function getLang() {
    return currentLang;
  }

  /** Get all languages */
  function getLanguages() {
    return languages;
  }

  /** Get direction for current language */
  function getDir() {
    return (languages[currentLang] && languages[currentLang].dir) || 'ltr';
  }

  /** Build a URL with lang param preserved */
  function langUrl(page, extraParams) {
    var params = new URLSearchParams(extraParams || {});
    params.set('lang', currentLang);
    return page + '?' + params.toString();
  }

  return {
    init: init,
    t: t,
    tf: tf,
    getLang: getLang,
    getDir: getDir,
    getLanguages: getLanguages,
    langUrl: langUrl,
    detectLang: detectLang
  };
})();
