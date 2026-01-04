<?php
declare(strict_types=1);

namespace App\Services;

final class Translator
{
    private array $strings;

    public function __construct(array $strings)
    {
        $this->strings = $strings;
    }

    public function t(string $key): string
    {
        return $this->strings[$key] ?? $key;
    }

    public function tf(string $key, mixed ...$args): string
    {
        $value = $this->t($key);
        if ($args === [] || strpos($value, '%') === false) {
            return $value;
        }
        return vsprintf($value, $args);
    }
}
