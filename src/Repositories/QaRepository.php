<?php
declare(strict_types=1);

namespace App\Repositories;

use SQLite3;

final class QaRepository
{
    private SQLite3 $db;

    public function __construct(SQLite3 $db)
    {
        $this->db = $db;
    }

    public function fetchPublicEntries(string $lang, string $searchQuery, int $limit, int $offset, int &$total): array
    {
        $total = 0;
        $rows = [];
        if ($lang === \App\Config\AppConfig::DEFAULT_LANG) {
            $countSql = 'SELECT COUNT(*) FROM qa';
            $dataSql = 'SELECT q.id, q.question, q.answer, q.created_at, COALESCE(SUM(v.vote), 0) AS score
                        FROM qa q
                        LEFT JOIN qa_votes v ON v.qa_id = q.id ';
            if ($searchQuery !== '') {
                $countSql .= ' WHERE question LIKE :needle OR answer LIKE :needle';
                $dataSql .= 'WHERE q.question LIKE :needle OR q.answer LIKE :needle ';
            }
            $dataSql .= 'GROUP BY q.id, q.question, q.answer, q.created_at
                         ORDER BY q.created_at DESC LIMIT :limit OFFSET :offset';

            $countStmt = $this->db->prepare($countSql);
            $dataStmt = $this->db->prepare($dataSql);
            if ($searchQuery !== '') {
                $needle = '%' . $searchQuery . '%';
                $countStmt->bindValue(':needle', $needle, SQLITE3_TEXT);
                $dataStmt->bindValue(':needle', $needle, SQLITE3_TEXT);
            }
        } else {
            $countSql = 'SELECT COUNT(*) FROM qa q INNER JOIN qa_translations t ON t.qa_id = q.id AND t.lang = :lang';
            $dataSql = 'SELECT q.id, t.question, t.answer, q.created_at, COALESCE(SUM(v.vote), 0) AS score
                        FROM qa q
                        INNER JOIN qa_translations t ON t.qa_id = q.id AND t.lang = :lang
                        LEFT JOIN qa_votes v ON v.qa_id = q.id ';
            if ($searchQuery !== '') {
                $countSql .= ' WHERE t.question LIKE :needle OR t.answer LIKE :needle';
                $dataSql .= 'WHERE t.question LIKE :needle OR t.answer LIKE :needle ';
            }
            $dataSql .= 'GROUP BY q.id, t.question, t.answer, q.created_at
                         ORDER BY q.created_at DESC LIMIT :limit OFFSET :offset';

            $countStmt = $this->db->prepare($countSql);
            $dataStmt = $this->db->prepare($dataSql);
            $countStmt->bindValue(':lang', $lang, SQLITE3_TEXT);
            $dataStmt->bindValue(':lang', $lang, SQLITE3_TEXT);
            if ($searchQuery !== '') {
                $needle = '%' . $searchQuery . '%';
                $countStmt->bindValue(':needle', $needle, SQLITE3_TEXT);
                $dataStmt->bindValue(':needle', $needle, SQLITE3_TEXT);
            }
        }

        $dataStmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $dataStmt->bindValue(':offset', $offset, SQLITE3_INTEGER);

        $countRes = $countStmt->execute();
        if ($countRes !== false) {
            $countRow = $countRes->fetchArray(SQLITE3_NUM);
            if ($countRow !== false) {
                $total = (int) $countRow[0];
            }
        }

        $res = $dataStmt->execute();
        while ($res !== false && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function fetchEntryByLang(int $id, string $lang): ?array
    {
        if ($id <= 0) {
            return null;
        }
        if ($lang === \App\Config\AppConfig::DEFAULT_LANG) {
            $stmt = $this->db->prepare('SELECT q.id, q.question, q.answer, q.created_at,
                COALESCE(SUM(v.vote), 0) AS score,
                COALESCE(SUM(CASE WHEN v.vote = 1 THEN 1 ELSE 0 END), 0) AS upvotes,
                COALESCE(SUM(CASE WHEN v.vote = -1 THEN 1 ELSE 0 END), 0) AS downvotes
                FROM qa q
                LEFT JOIN qa_votes v ON v.qa_id = q.id
                WHERE q.id = :id
                GROUP BY q.id, q.question, q.answer, q.created_at');
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        } else {
            $stmt = $this->db->prepare('SELECT q.id, t.question, t.answer, q.created_at,
                COALESCE(SUM(v.vote), 0) AS score,
                COALESCE(SUM(CASE WHEN v.vote = 1 THEN 1 ELSE 0 END), 0) AS upvotes,
                COALESCE(SUM(CASE WHEN v.vote = -1 THEN 1 ELSE 0 END), 0) AS downvotes
                FROM qa q
                INNER JOIN qa_translations t ON t.qa_id = q.id AND t.lang = :lang
                LEFT JOIN qa_votes v ON v.qa_id = q.id
                WHERE q.id = :id
                GROUP BY q.id, t.question, t.answer, q.created_at');
            $stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        }
        $res = $stmt->execute();
        $row = $res !== false ? $res->fetchArray(SQLITE3_ASSOC) : false;
        return $row ?: null;
    }

    public function fetchAdminEntries(string $keyword, string $sort, string $direction, int $limit, int $offset, int &$total): array
    {
        $sortMap = [
            'id' => 'q.id',
            'created_at' => 'q.created_at',
            'updated_at' => 'q.updated_at',
            'question' => 'q.question',
        ];
        $orderBy = $sortMap[$sort] ?? $sortMap['updated_at'];
        $dir = $direction === 'ASC' ? 'ASC' : 'DESC';

        $countSql = 'SELECT COUNT(DISTINCT q.id)
            FROM qa q
            LEFT JOIN qa_translations t ON t.qa_id = q.id
            WHERE 1=1';
        $sql = 'SELECT q.id, q.question, q.answer, q.created_at, q.updated_at, IFNULL(GROUP_CONCAT(DISTINCT t.lang), \'\') AS langs
            FROM qa q
            LEFT JOIN qa_translations t ON t.qa_id = q.id
            WHERE 1=1';
        if ($keyword !== '') {
            $countSql .= ' AND (q.question LIKE :kw OR q.answer LIKE :kw)';
            $sql .= ' AND (q.question LIKE :kw OR q.answer LIKE :kw)';
        }
        $sql .= " GROUP BY q.id ORDER BY $orderBy $dir LIMIT :limit OFFSET :offset";

        $countStmt = $this->db->prepare($countSql);
        if ($keyword !== '') {
            $countStmt->bindValue(':kw', '%' . $keyword . '%', SQLITE3_TEXT);
        }
        $countRes = $countStmt->execute();
        $total = 0;
        if ($countRes !== false) {
            $countRow = $countRes->fetchArray(SQLITE3_NUM);
            if ($countRow !== false) {
                $total = (int) $countRow[0];
            }
        }

        $stmt = $this->db->prepare($sql);
        if ($keyword !== '') {
            $stmt->bindValue(':kw', '%' . $keyword . '%', SQLITE3_TEXT);
        }
        $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
        $rows = [];
        $res = $stmt->execute();
        while ($res !== false && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $langs = array_filter(array_map('trim', explode(',', (string) $row['langs'])));
            $row['langs_array'] = $langs;
            $rows[] = $row;
        }
        return $rows;
    }

    public function getEntry(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT id, question, answer, created_at, updated_at FROM qa WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $res = $stmt->execute();
        $row = $res !== false ? $res->fetchArray(SQLITE3_ASSOC) : false;
        return $row ?: null;
    }

    public function exists(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        $count = (int) $this->db->querySingle('SELECT COUNT(*) FROM qa WHERE id = ' . $id);
        return $count > 0;
    }

    public function addEntry(string $question, string $answer): int
    {
        $stmt = $this->db->prepare('INSERT INTO qa (question, answer, updated_at) VALUES (:q, :a, CURRENT_TIMESTAMP)');
        $stmt->bindValue(':q', $question, SQLITE3_TEXT);
        $stmt->bindValue(':a', $answer, SQLITE3_TEXT);
        $stmt->execute();
        return (int) $this->db->lastInsertRowID();
    }

    public function updateEntry(int $id, string $question, string $answer): void
    {
        $stmt = $this->db->prepare('UPDATE qa SET question = :q, answer = :a, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':q', $question, SQLITE3_TEXT);
        $stmt->bindValue(':a', $answer, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    public function deleteEntry(int $id): void
    {
        $this->db->exec('BEGIN');
        $delTranslations = $this->db->prepare('DELETE FROM qa_translations WHERE qa_id = :id');
        $delTranslations->bindValue(':id', $id, SQLITE3_INTEGER);
        $delTranslations->execute();

        $delTags = $this->db->prepare('DELETE FROM qa_tags WHERE qa_id = :id');
        $delTags->bindValue(':id', $id, SQLITE3_INTEGER);
        $delTags->execute();

        $delEntry = $this->db->prepare('DELETE FROM qa WHERE id = :id');
        $delEntry->bindValue(':id', $id, SQLITE3_INTEGER);
        $delEntry->execute();
        $this->cleanupUnusedTags();
        $this->db->exec('COMMIT');
    }

    public function getTranslation(int $qaId, string $lang): ?array
    {
        $stmt = $this->db->prepare('SELECT qa_id, lang, question, answer, created_at, updated_at FROM qa_translations WHERE qa_id = :id AND lang = :lang');
        $stmt->bindValue(':id', $qaId, SQLITE3_INTEGER);
        $stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
        $res = $stmt->execute();
        $row = $res !== false ? $res->fetchArray(SQLITE3_ASSOC) : false;
        return $row ?: null;
    }

    public function getTranslationLangs(int $qaId): array
    {
        $langs = [];
        $stmt = $this->db->prepare('SELECT lang FROM qa_translations WHERE qa_id = :id ORDER BY lang');
        $stmt->bindValue(':id', $qaId, SQLITE3_INTEGER);
        $res = $stmt->execute();
        while ($res !== false && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $langs[] = $row['lang'];
        }
        return $langs;
    }

    public function addTranslation(int $qaId, string $lang, string $question, string $answer): void
    {
        $stmt = $this->db->prepare('INSERT INTO qa_translations (qa_id, lang, question, answer, updated_at)
            VALUES (:qa_id, :lang, :q, :a, CURRENT_TIMESTAMP)');
        $stmt->bindValue(':qa_id', $qaId, SQLITE3_INTEGER);
        $stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
        $stmt->bindValue(':q', $question, SQLITE3_TEXT);
        $stmt->bindValue(':a', $answer, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function updateTranslation(int $qaId, string $lang, string $question, string $answer): void
    {
        $stmt = $this->db->prepare('UPDATE qa_translations SET question = :q, answer = :a, updated_at = CURRENT_TIMESTAMP WHERE qa_id = :id AND lang = :lang');
        $stmt->bindValue(':q', $question, SQLITE3_TEXT);
        $stmt->bindValue(':a', $answer, SQLITE3_TEXT);
        $stmt->bindValue(':id', $qaId, SQLITE3_INTEGER);
        $stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function getAllTags(): array
    {
        $tags = [];
        $res = $this->db->query('SELECT name FROM tags ORDER BY name');
        while ($res !== false && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $tags[] = $row['name'];
        }
        return $tags;
    }

    public function getTagsForEntries(array $entryIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $entryIds), static fn(int $id): bool => $id > 0));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            'SELECT qt.qa_id, t.name
            FROM qa_tags qt
            INNER JOIN tags t ON t.id = qt.tag_id
            WHERE qt.qa_id IN (' . $placeholders . ')
            ORDER BY t.name'
        );
        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, SQLITE3_INTEGER);
        }
        $map = [];
        $res = $stmt->execute();
        while ($res !== false && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $qaId = (int) $row['qa_id'];
            $map[$qaId][] = $row['name'];
        }
        return $map;
    }

    public function getTagsForEntry(int $qaId): array
    {
        $map = $this->getTagsForEntries([$qaId]);
        return $map[$qaId] ?? [];
    }

    public function setTags(int $qaId, array $tags): void
    {
        if ($qaId <= 0) {
            return;
        }
        $tags = $this->normalizeTags($tags);

        $this->db->exec('BEGIN');
        $delStmt = $this->db->prepare('DELETE FROM qa_tags WHERE qa_id = :id');
        $delStmt->bindValue(':id', $qaId, SQLITE3_INTEGER);
        $delStmt->execute();

        if ($tags !== []) {
            $insertTag = $this->db->prepare('INSERT OR IGNORE INTO tags (name) VALUES (:name)');
            foreach ($tags as $tag) {
                $insertTag->bindValue(':name', $tag, SQLITE3_TEXT);
                $insertTag->execute();
            }

            $tagIds = $this->fetchTagIds($tags);
            $linkStmt = $this->db->prepare('INSERT OR IGNORE INTO qa_tags (qa_id, tag_id) VALUES (:qa_id, :tag_id)');
            foreach ($tagIds as $tagId) {
                $linkStmt->bindValue(':qa_id', $qaId, SQLITE3_INTEGER);
                $linkStmt->bindValue(':tag_id', $tagId, SQLITE3_INTEGER);
                $linkStmt->execute();
            }
        }

        $this->cleanupUnusedTags();
        $this->db->exec('COMMIT');
    }

    private function normalizeTags(array $tags): array
    {
        $unique = [];
        foreach ($tags as $tag) {
            $tag = trim((string) $tag);
            if ($tag === '') {
                continue;
            }
            $key = $this->tagKey($tag);
            if (!isset($unique[$key])) {
                $unique[$key] = $tag;
            }
        }
        return array_values($unique);
    }

    private function fetchTagIds(array $tags): array
    {
        if ($tags === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($tags), '?'));
        $stmt = $this->db->prepare('SELECT id FROM tags WHERE name IN (' . $placeholders . ')');
        foreach ($tags as $index => $tag) {
            $stmt->bindValue($index + 1, $tag, SQLITE3_TEXT);
        }
        $ids = [];
        $res = $stmt->execute();
        while ($res !== false && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $ids[] = (int) $row['id'];
        }
        return $ids;
    }

    private function cleanupUnusedTags(): void
    {
        $this->db->exec('DELETE FROM tags WHERE id NOT IN (SELECT DISTINCT tag_id FROM qa_tags)');
    }

    private function tagKey(string $tag): string
    {
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($tag);
        }
        return strtolower($tag);
    }
}
