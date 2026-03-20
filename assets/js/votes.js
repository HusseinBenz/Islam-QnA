/**
 * Votes Module — localStorage-based voting system
 */
const Votes = (function () {
  var STORAGE_KEY = 'anti_shuboohat_votes';

  function getAll() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      return raw ? JSON.parse(raw) : {};
    } catch (e) {
      return {};
    }
  }

  function saveAll(votes) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(votes));
    } catch (e) { /* ignore */ }
  }

  /** Get vote for a specific entry: 1, -1, or 0 */
  function getVote(entryId) {
    var votes = getAll();
    return votes[entryId] || 0;
  }

  /** Cast a vote. Returns the new vote state (1, -1, or 0 if toggled off) */
  function castVote(entryId, value) {
    var votes = getAll();
    var current = votes[entryId] || 0;
    // Toggle off if same vote
    if (current === value) {
      delete votes[entryId];
      saveAll(votes);
      return 0;
    }
    votes[entryId] = value;
    saveAll(votes);
    return value;
  }

  /** Get aggregated scores for all entries */
  function getScores() {
    return getAll();
  }

  /** Calculate score for display (based on localStorage only) */
  function getScore(entryId) {
    var vote = getVote(entryId);
    return vote; // In static mode, score is just the user's own vote indicator
  }

  return {
    getVote: getVote,
    castVote: castVote,
    getScores: getScores,
    getScore: getScore
  };
})();
