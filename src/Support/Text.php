<?php
declare(strict_types=1);

namespace App\Support;

final class Text
{
    public static function displayDate(?string $value): string
    {
        $value = trim((string) $value);
        return $value === '' ? 'invalid blabla..' : $value;
    }

    public static function truncate(string $value, int $limit = 80): string
    {
        $value = trim($value);
        if (mb_strlen($value) <= $limit) {
            return $value;
        }
        return mb_substr($value, 0, $limit - 3) . '...';
    }

    public static function sanitizePlainText(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[\\x00-\\x1F\\x7F]/u', '', $value) ?? '';
        return $value;
    }

    public static function sanitizeMarkdown(string $value): string
    {
        $value = trim($value);
        $value = str_replace("\0", '', $value);
        return $value;
    }

    public static function fixMojibake(?string $value): string
    {
        $value = (string) $value;
        if ($value === '') {
            return $value;
        }
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $value)) {
            return $value;
        }
        if (!preg_match('/[AŸ’\'AŸƒ?sAŸA?AŸƒ?~AŸƒ?TAŸƒ,›AŸEo]/u', $value)
            && !preg_match('/[\x{00C2}-\x{00F4}]/u', $value)) {
            return $value;
        }
        $fixed = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        if ($fixed !== $value && preg_match('/[\x{0600}-\x{06FF}]/u', $fixed)) {
            return $fixed;
        }
        return $value;
    }

    private function __construct()
    {
    }
}
