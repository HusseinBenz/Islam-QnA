/**
 * Theme Module — Dark/light toggle with localStorage persistence
 */
const Theme = (function () {
  var root = document.documentElement;
  var toggle = null;

  function getPreferred() {
    try {
      var saved = localStorage.getItem('theme');
      if (saved === 'light' || saved === 'dark') return saved;
    } catch (e) { /* ignore */ }
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function apply(theme) {
    root.setAttribute('data-theme', theme);
    if (toggle) {
      toggle.innerHTML = theme === 'dark' ? '&#x1F319;' : '&#x2600;';
    }
  }

  function init() {
    toggle = document.getElementById('theme-toggle');
    var theme = getPreferred();
    apply(theme);
    if (toggle) {
      toggle.addEventListener('click', function () {
        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        apply(next);
        try { localStorage.setItem('theme', next); } catch (e) { /* ignore */ }
      });
    }
  }

  return { init: init };
})();

// Initialize immediately to prevent flash
(function () {
  var saved;
  try { saved = localStorage.getItem('theme'); } catch (e) { saved = null; }
  var theme = (saved === 'light' || saved === 'dark') ? saved :
    (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  document.documentElement.setAttribute('data-theme', theme);
})();
