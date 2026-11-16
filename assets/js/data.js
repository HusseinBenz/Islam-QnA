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

  /** Optional files: the site still works if one is missing. */
  function loadOptional(path, fallback) {
    return loadJSON(path).catch(function () { return fallback; });
  }

  /** Load all required data files */
  function loadAll() {
    return Promise.all([
      loadJSON('data/config.json'),
      loadJSON('data/entries.json'),
      loadJSON('data/languages.json'),
      loadJSON('data/ui-strings.json'),
      loadOptional('data/sources.json', {}),
      loadOptional('data/voices.json', [])
    ]).then(function (results) {
      return {
        config: results[0],
        entries: results[1],
        languages: results[2],
        uiStrings: results[3],
        sources: results[4],
        voices: results[5]
      };
    });
  }

  /** One entry in the reader's language, or null if it has no translation. */
  function localize(e, lang, defaultLang) {
    var src = e;
    if (lang !== defaultLang) {
      if (!e.translations || !e.translations[lang]) return null;
      src = e.translations[lang];
    }
    return {
      id: e.id,
      question: src.question,
      summary: src.summary || '',
      answer: src.answer,
      tags: e.tags || [],
      sources: e.sources || [],
      created_at: e.created_at
    };
  }

  /** Get entries for a specific language */
  function getEntries(entries, lang, defaultLang) {
    var result = [];
    entries.forEach(function (e) {
      var item = localize(e, lang, defaultLang);
      if (item) result.push(item);
    });
    return result;
  }

  /** Get a single entry by ID for a specific language */
  function getEntry(entries, id, lang, defaultLang) {
    for (var i = 0; i < entries.length; i++) {
      if (entries[i].id === id) return localize(entries[i], lang, defaultLang);
    }
    return null;
  }

  /**
   * Fold text for matching: lower case, no accents, and for Arabic no
   * vowel marks or tatweel, with the alef, ya and ta marbuta forms unified,
   * so a search typed without marks still finds a fully marked verse.
   */
  function fold(text) {
    var s = String(text || '').toLowerCase();
    try { s = s.normalize('NFKD'); } catch (e) { /* old browser */ }
    return s
      .replace(/[̀-ًͯ-ٰٟـ]/g, '')
      .replace(/[آأإٱ]/g, 'ا')
      .replace(/ى/g, 'ي')
      .replace(/ة/g, 'ه');
  }

  function index(entry) {
    if (!entry._idx) {
      entry._idx = {
        q: fold(entry.question),
        t: fold((entry.tags || []).join(' ')),
        s: fold(entry.summary),
        a: fold(entry.answer)
      };
    }
    return entry._idx;
  }

  /**
   * Search entries. Every word must appear somewhere; matches in the
   * question count most, then tags, the short answer, and the full answer.
   */
  function searchEntries(localizedEntries, query) {
    if (!query || query.trim() === '') return localizedEntries;
    var terms = fold(query).trim().split(/\s+/).filter(Boolean);
    if (!terms.length) return localizedEntries;
    var scored = [];
    localizedEntries.forEach(function (entry, position) {
      var ix = index(entry);
      var score = 0;
      for (var i = 0; i < terms.length; i++) {
        var term = terms[i];
        var s = 0;
        if (ix.q.indexOf(term) !== -1) s += 6;
        if (ix.t.indexOf(term) !== -1) s += 3;
        if (ix.s.indexOf(term) !== -1) s += 2;
        if (ix.a.indexOf(term) !== -1) s += 1;
        if (!s) return;
        score += s;
      }
      scored.push({ entry: entry, score: score, position: position });
    });
    scored.sort(function (a, b) { return b.score - a.score || a.position - b.position; });
    return scored.map(function (x) { return x.entry; });
  }

  /** Entries carrying a tag. */
  function byTag(localizedEntries, tag) {
    if (!tag) return localizedEntries;
    return localizedEntries.filter(function (e) { return (e.tags || []).indexOf(tag) !== -1; });
  }

  /** How many entries carry each tag. */
  function tagCounts(localizedEntries) {
    var counts = {};
    localizedEntries.forEach(function (e) {
      (e.tags || []).forEach(function (t) { counts[t] = (counts[t] || 0) + 1; });
    });
    return counts;
  }

  /** Other entries that share the most tags (and books) with this one. */
  function related(localizedEntries, entry, limit) {
    var scored = [];
    localizedEntries.forEach(function (e, position) {
      if (e.id === entry.id) return;
      var score = 0;
      (e.tags || []).forEach(function (t, i) {
        var at = (entry.tags || []).indexOf(t);
        if (at !== -1) score += (at === 0 && i === 0) ? 3 : 2;
      });
      (e.sources || []).forEach(function (s) { if ((entry.sources || []).indexOf(s) !== -1) score += 1; });
      if (score) scored.push({ entry: e, score: score, position: position });
    });
    scored.sort(function (a, b) { return b.score - a.score || a.position - b.position; });
    return scored.slice(0, limit || 3).map(function (x) { return x.entry; });
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
    byTag: byTag,
    tagCounts: tagCounts,
    related: related,
    fold: fold,
    paginate: paginate
  };
})();
