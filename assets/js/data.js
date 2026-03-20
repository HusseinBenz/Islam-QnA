/**
 * Data Module — Loads JSON data files and provides access
 */
const DataStore = (function () {
  var cache = {};

  function loadJSON(path) {
    if (cache[path]) return Promise.resolve(cache[path]);
    return fetch(path)
      .then(function (res) {
        if (!res.ok) throw new Error('Failed to load ' + path);
        return res.json();
      })
      .then(function (data) {
        cache[path] = data;
        return data;
      });
  }

  /** Load all required data files */
  function loadAll() {
    return Promise.all([
      loadJSON('data/config.json'),
      loadJSON('data/entries.json'),
      loadJSON('data/languages.json'),
      loadJSON('data/ui-strings.json')
    ]).then(function (results) {
      return {
        config: results[0],
        entries: results[1],
        languages: results[2],
        uiStrings: results[3]
      };
    });
  }

  /** Get entries for a specific language */
  function getEntries(entries, lang, defaultLang) {
    if (lang === defaultLang) {
      return entries.map(function (e) {
        return {
          id: e.id,
          question: e.question,
          answer: e.answer,
          tags: e.tags || [],
          created_at: e.created_at
        };
      });
    }
    // For non-default language, only return entries that have translations
    var result = [];
    entries.forEach(function (e) {
      if (e.translations && e.translations[lang]) {
        result.push({
          id: e.id,
          question: e.translations[lang].question,
          answer: e.translations[lang].answer,
          tags: e.tags || [],
          created_at: e.created_at
        });
      }
    });
    return result;
  }

  /** Get a single entry by ID for a specific language */
  function getEntry(entries, id, lang, defaultLang) {
    var entry = null;
    for (var i = 0; i < entries.length; i++) {
      if (entries[i].id === id) {
        entry = entries[i];
        break;
      }
    }
    if (!entry) return null;
    if (lang === defaultLang) {
      return {
        id: entry.id,
        question: entry.question,
        answer: entry.answer,
        tags: entry.tags || [],
        created_at: entry.created_at
      };
    }
    if (entry.translations && entry.translations[lang]) {
      return {
        id: entry.id,
        question: entry.translations[lang].question,
        answer: entry.translations[lang].answer,
        tags: entry.tags || [],
        created_at: entry.created_at
      };
    }
    return null;
  }

  /** Search entries by query string */
  function searchEntries(localizedEntries, query) {
    if (!query || query.trim() === '') return localizedEntries;
    var q = query.toLowerCase().trim();
    var terms = q.split(/\s+/);
    return localizedEntries.filter(function (entry) {
      var text = (entry.question + ' ' + entry.answer + ' ' + (entry.tags || []).join(' ')).toLowerCase();
      return terms.every(function (term) {
        return text.indexOf(term) !== -1;
      });
    });
  }

  /** Paginate an array */
  function paginate(arr, page, perPage) {
    var total = arr.length;
    var totalPages = Math.max(1, Math.ceil(total / perPage));
    page = Math.max(1, Math.min(page, totalPages));
    var start = (page - 1) * perPage;
    return {
      items: arr.slice(start, start + perPage),
      page: page,
      perPage: perPage,
      total: total,
      totalPages: totalPages
    };
  }

  return {
    loadAll: loadAll,
    getEntries: getEntries,
    getEntry: getEntry,
    searchEntries: searchEntries,
    paginate: paginate
  };
})();
