<?php
declare(strict_types=1);

namespace App;

use App\Services\Translator;
use App\Support\Html;

final class View
{
    public static function render(string $template, array $data = []): void
    {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $template . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        extract($data, EXTR_SKIP);

        $translator = $data['translator'] ?? null;
        if ($translator instanceof Translator) {
            $t = static fn(string $key): string => $translator->t($key);
            $tf = static fn(string $key, mixed ...$args): string => $translator->tf($key, ...$args);
        } else {
            $t = static fn(string $key): string => $key;
            $tf = static fn(string $key, mixed ...$args): string => $key;
        }
        $e = static fn(?string $value): string => Html::escape($value);

        require $path;
    }

    private function __construct()
    {
    }
}
