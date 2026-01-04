<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Config\AppConfig;
use App\Services\LanguageService;
use App\Support\Text;
use SQLite3;

final class LanguageRepository
{
    private SQLite3 $db;
    private LanguageService $languageService;

    public function __construct(SQLite3 $db, LanguageService $languageService)
    {
        $this->db = $db;
        $this->languageService = $languageService;
    }

    public function ensureDefaults(): void
    {
        $defaultLangs = [
            'en' => ['label' => 'English', 'dir' => 'ltr'],
            'ar' => ['label' => 'العربية', 'dir' => 'rtl'],
        ];

        foreach ($defaultLangs as $code => $meta) {
            $fontDefaults = $this->languageService->defaultUiFontConfig($code);
            $stmt = $this->db->prepare('INSERT INTO languages (code, label, dir, ui_font, ui_font_url)
                VALUES (:c, :l, :d, :font, :url)
                ON CONFLICT(code) DO NOTHING');
            $stmt->bindValue(':c', $code, SQLITE3_TEXT);
            $stmt->bindValue(':l', $meta['label'], SQLITE3_TEXT);
            $stmt->bindValue(':d', $meta['dir'], SQLITE3_TEXT);
            $stmt->bindValue(':font', $fontDefaults['font'], SQLITE3_TEXT);
            $stmt->bindValue(':url', $fontDefaults['url'], SQLITE3_TEXT);
            $stmt->execute();
        }

        foreach ($defaultLangs as $code => $meta) {
            $fontDefaults = $this->languageService->defaultUiFontConfig($code);
            $stmt = $this->db->prepare('SELECT label, dir, ui_font, ui_font_url FROM languages WHERE code = :code');
            $stmt->bindValue(':code', $code, SQLITE3_TEXT);
            $res = $stmt->execute();
            $row = $res !== false ? $res->fetchArray(SQLITE3_ASSOC) : false;
            if ($row === false) {
                continue;
            }
            $label = (string) ($row['label'] ?? '');
            $dir = (string) ($row['dir'] ?? '');
            $needsLabel = $label === '' || ($code === 'ar' && !preg_match('/[\x{0600}-\x{06FF}]/u', $label));
            $needsDir = $dir === '';
            $needsFont = ($row['ui_font'] ?? '') === '' || ($row['ui_font_url'] ?? '') === '';
            if ($needsLabel || $needsDir || $needsFont) {
                $update = $this->db->prepare('UPDATE languages SET label = :label, dir = :dir, ui_font = :font, ui_font_url = :url WHERE code = :code');
                $update->bindValue(':label', $needsLabel ? $meta['label'] : $label, SQLITE3_TEXT);
                $update->bindValue(':dir', $needsDir ? $meta['dir'] : $dir, SQLITE3_TEXT);
                $update->bindValue(':font', $needsFont ? $fontDefaults['font'] : $row['ui_font'], SQLITE3_TEXT);
                $update->bindValue(':url', $needsFont ? $fontDefaults['url'] : $row['ui_font_url'], SQLITE3_TEXT);
                $update->bindValue(':code', $code, SQLITE3_TEXT);
                $update->execute();
            }
        }
    }

    public function all(): array
    {
        $langs = [];
        $res = $this->db->query('SELECT code, label, dir, ui_font, ui_font_url FROM languages ORDER BY code');
        while ($res !== false && ($row = $res->fetchArray(SQLITE3_ASSOC))) {
            $defaults = $this->languageService->defaultUiFontConfig($row['code']);
            $font = $row['ui_font'] !== '' ? $row['ui_font'] : $defaults['font'];
            $fontUrl = $row['ui_font_url'] !== '' ? $row['ui_font_url'] : $defaults['url'];
            $label = Text::fixMojibake($row['label'] ?? '');
            if ($label !== (string) ($row['label'] ?? '')) {
                $update = $this->db->prepare('UPDATE languages SET label = :label WHERE code = :code');
                $update->bindValue(':label', $label, SQLITE3_TEXT);
                $update->bindValue(':code', $row['code'], SQLITE3_TEXT);
                $update->execute();
            }
            $langs[$row['code']] = [
                'label' => $label,
                'dir' => $row['dir'],
                'ui_font' => $font,
                'ui_font_url' => $fontUrl,
            ];
        }

        if (!isset($langs[AppConfig::DEFAULT_LANG])) {
            $defaults = $this->languageService->defaultUiFontConfig(AppConfig::DEFAULT_LANG);
            $langs[AppConfig::DEFAULT_LANG] = [
                'label' => 'English',
                'dir' => 'ltr',
                'ui_font' => $defaults['font'],
                'ui_font_url' => $defaults['url'],
            ];
        }

        return $langs;
    }

    public function exists(string $code): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM languages WHERE code = :code');
        $stmt->bindValue(':code', $code, SQLITE3_TEXT);
        $res = $stmt->execute();
        $row = $res !== false ? $res->fetchArray(SQLITE3_NUM) : false;
        return $row !== false && (int) $row[0] > 0;
    }

    public function save(string $code, string $label, string $dir, string $uiFont, string $uiFontUrl): void
    {
        $stmt = $this->db->prepare('INSERT INTO languages (code, label, dir, ui_font, ui_font_url)
            VALUES (:c, :l, :d, :font, :url)
            ON CONFLICT(code) DO UPDATE SET label = excluded.label, dir = excluded.dir, ui_font = excluded.ui_font, ui_font_url = excluded.ui_font_url');
        $stmt->bindValue(':c', $code, SQLITE3_TEXT);
        $stmt->bindValue(':l', $label, SQLITE3_TEXT);
        $stmt->bindValue(':d', $dir, SQLITE3_TEXT);
        $stmt->bindValue(':font', $uiFont, SQLITE3_TEXT);
        $stmt->bindValue(':url', $uiFontUrl, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function updateCodeAndMeta(string $original, string $code, string $label, string $dir, string $uiFont, string $uiFontUrl): void
    {
        $this->db->exec('BEGIN');
        $stmt = $this->db->prepare('UPDATE languages SET code = :new, label = :label, dir = :dir, ui_font = :font, ui_font_url = :url WHERE code = :old');
        $stmt->bindValue(':new', $code, SQLITE3_TEXT);
        $stmt->bindValue(':label', $label, SQLITE3_TEXT);
        $stmt->bindValue(':dir', $dir, SQLITE3_TEXT);
        $stmt->bindValue(':font', $uiFont, SQLITE3_TEXT);
        $stmt->bindValue(':url', $uiFontUrl, SQLITE3_TEXT);
        $stmt->bindValue(':old', $original, SQLITE3_TEXT);
        $stmt->execute();

        if ($code !== $original) {
            $stmt = $this->db->prepare('UPDATE qa_translations SET lang = :new WHERE lang = :old');
            $stmt->bindValue(':new', $code, SQLITE3_TEXT);
            $stmt->bindValue(':old', $original, SQLITE3_TEXT);
            $stmt->execute();

            $stmt = $this->db->prepare('UPDATE ui_translations SET lang = :new WHERE lang = :old');
            $stmt->bindValue(':new', $code, SQLITE3_TEXT);
            $stmt->bindValue(':old', $original, SQLITE3_TEXT);
            $stmt->execute();
        }

        $this->db->exec('COMMIT');
    }
}
