<?php
declare(strict_types=1);

namespace App\Support;

final class Css
{
    public static function fontFamily(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 'sans-serif';
        }
        $value = str_replace(["\r", "\n"], '', $value);
        $value = preg_replace('/[^\\w\\s,"\'-]/u', '', $value);
        return $value;
    }

    private function __construct()
    {
    }
}
