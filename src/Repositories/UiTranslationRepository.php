<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Config\UiStrings;
use App\Support\Text;
use SQLite3;

final class UiTranslationRepository
{
    private SQLite3 $db;

    public function __construct(SQLite3 $db)
    {
        $this->db = $db;
    }

    public function ensureDefaults(): void
    {
        $base = UiStrings::base();
        foreach ($base as $key => $value) {
            $stmt = $this->db->prepare('INSERT INTO ui_translations (lang, key, value) VALUES (:lang, :key, :value)
                ON CONFLICT(lang, key) DO UPDATE SET value = excluded.value');
            $stmt->bindValue(':lang', 'en', SQLITE3_TEXT);
            $stmt->bindValue(':key', $key, SQLITE3_TEXT);
            $stmt->bindValue(':value', $value, SQLITE3_TEXT);
            $stmt->execute();
        }

        $arabic = UiStrings::arabic();
        foreach ($arabic as $key => $value) {
            $stmt = $this->db->prepare('INSERT INTO ui_translations (lang, key, value) VALUES (:lang, :key, :value)
                ON CONFLICT(lang, key) DO UPDATE SET value = excluded.value');
            $stmt->bindValue(':lang', 'ar', SQLITE3_TEXT);
            $stmt->bindValue(':key', $key, SQLITE3_TEXT);
            $stmt->bindValue(':value', $value, SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    public function load(string $lang): array
    {
        $strings = UiStrings::base();
        $stmt = $this->db->prepare('SELECT key, value FROM ui_translations WHERE lang = :lang');
        $stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
        $res = $stmt->execute();
        while ($res !== false && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $value = Text::fixMojibake($row['value'] ?? '');
            $strings[$row['key']] = $value;
            if ($value !== (string) ($row['value'] ?? '')) {
                $update = $this->db->prepare('UPDATE ui_translations SET value = :value WHERE lang = :lang AND key = :key');
                $update->bindValue(':value', $value, SQLITE3_TEXT);
                $update->bindValue(':lang', $lang, SQLITE3_TEXT);
                $update->bindValue(':key', $row['key'], SQLITE3_TEXT);
                $update->execute();
            }
        }
        return $strings;
    }

    public function save(string $lang, array $values): void
    {
        foreach ($values as $key => $value) {
            $stmt = $this->db->prepare('INSERT INTO ui_translations (lang, key, value) VALUES (:lang, :key, :value)
                ON CONFLICT(lang, key) DO UPDATE SET value = excluded.value');
            $stmt->bindValue(':lang', $lang, SQLITE3_TEXT);
            $stmt->bindValue(':key', $key, SQLITE3_TEXT);
            $stmt->bindValue(':value', $value, SQLITE3_TEXT);
            $stmt->execute();
        }
    }
}
