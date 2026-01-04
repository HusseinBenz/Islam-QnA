<?php
declare(strict_types=1);

namespace App\Support;

final class Html
{
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function __construct()
    {
    }
}
