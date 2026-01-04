<?php
declare(strict_types=1);

namespace App\Infrastructure;

use SQLite3;

final class Schema
{
    public static function setup(SQLite3 $db): void
    {
        $db->exec('CREATE TABLE IF NOT EXISTS admin_sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token_hash TEXT NOT NULL UNIQUE,
            ip_addr TEXT NOT NULL,
            expires_at TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');

        $db->exec('CREATE INDEX IF NOT EXISTS idx_admin_sessions_ip ON admin_sessions (ip_addr)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_admin_sessions_expires ON admin_sessions (expires_at)');

        $db->exec('CREATE TABLE IF NOT EXISTS qa (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            question TEXT NOT NULL,
            answer TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');

        $db->exec('CREATE TABLE IF NOT EXISTS qa_translations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            qa_id INTEGER NOT NULL,
            lang TEXT NOT NULL,
            question TEXT NOT NULL,
            answer TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (qa_id, lang)
        )');

        $db->exec('CREATE TABLE IF NOT EXISTS tags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL COLLATE NOCASE UNIQUE,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');

        $db->exec('CREATE TABLE IF NOT EXISTS qa_tags (
            qa_id INTEGER NOT NULL,
            tag_id INTEGER NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (qa_id, tag_id)
        )');

        $db->exec('CREATE INDEX IF NOT EXISTS idx_qa_tags_qa_id ON qa_tags (qa_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_qa_tags_tag_id ON qa_tags (tag_id)');

        $db->exec('CREATE TABLE IF NOT EXISTS languages (
            code TEXT PRIMARY KEY,
            label TEXT NOT NULL,
            dir TEXT NOT NULL DEFAULT "ltr",
            ui_font TEXT NOT NULL DEFAULT "",
            ui_font_url TEXT NOT NULL DEFAULT ""
        )');

        $db->exec('CREATE TABLE IF NOT EXISTS ui_translations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lang TEXT NOT NULL,
            key TEXT NOT NULL,
            value TEXT NOT NULL,
            UNIQUE(lang, key)
        )');

        $db->exec('CREATE TABLE IF NOT EXISTS qa_votes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            qa_id INTEGER NOT NULL,
            voter_hash TEXT NOT NULL,
            vote INTEGER NOT NULL CHECK (vote IN (1, -1)),
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');

        $db->exec('CREATE INDEX IF NOT EXISTS idx_qa_votes_qa_id ON qa_votes (qa_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_qa_votes_hash ON qa_votes (voter_hash)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_qa_votes_hash_created ON qa_votes (voter_hash, created_at)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_qa_votes_article_hash_created ON qa_votes (qa_id, voter_hash, created_at)');

        $db->exec('CREATE TABLE IF NOT EXISTS pico_palette (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            colors TEXT NOT NULL,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');

        self::ensureColumn($db, 'qa', 'updated_at');
        self::ensureColumn($db, 'qa_translations', 'updated_at');
        self::ensureColumn($db, 'languages', 'ui_font', "''");
        self::ensureColumn($db, 'languages', 'ui_font_url', "''");
    }

    private static function ensureColumn(SQLite3 $db, string $table, string $column, string $defaultExpression = 'CURRENT_TIMESTAMP'): void
    {
        $exists = false;
        $result = $db->query("PRAGMA table_info('$table')");
        while ($result !== false && ($row = $result->fetchArray(SQLITE3_ASSOC))) {
            if (strcasecmp($row['name'], $column) === 0) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $db->exec("ALTER TABLE $table ADD COLUMN $column TEXT");
            $db->exec("UPDATE $table SET $column = $defaultExpression WHERE $column IS NULL");
        }
    }

    private function __construct()
    {
    }
}
