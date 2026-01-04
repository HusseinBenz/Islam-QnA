<?php
declare(strict_types=1);

namespace App\Http;

final class Response
{
    public static function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }

    public static function redirectWithParams(string $path, array $params): void
    {
        $query = http_build_query($params);
        $location = $path . ($query !== '' ? '?' . $query : '');
        self::redirect($location);
    }

    private function __construct()
    {
    }
}
