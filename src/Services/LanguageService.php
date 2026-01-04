<?php
declare(strict_types=1);

namespace App\Services;

final class LanguageService
{
    private GeoService $geo;

    public function __construct(GeoService $geo)
    {
        $this->geo = $geo;
    }

    public function resolveUiLang(string $requested, array $languages, string $defaultLang, array $server): string
    {
        $requested = strtolower(trim($requested));
        if ($requested !== '' && array_key_exists($requested, $languages)) {
            return $requested;
        }

        $ip = $this->geo->getClientIp($server);
        if ($ip !== '') {
            $country = $this->geo->fetchCountryCode($ip);
            if ($country !== '') {
                $candidate = $this->geo->mapCountryToLanguage($country);
                if ($candidate !== '' && array_key_exists($candidate, $languages)) {
                    return $candidate;
                }
            }
        }

        if (array_key_exists($defaultLang, $languages)) {
            return $defaultLang;
        }

        return array_key_first($languages) ?: $defaultLang;
    }

    public function defaultUiFontConfig(string $code): array
    {
        if ($code === 'ar') {
            return [
                'font' => "'Noto Naskh Arabic', serif",
                'url' => 'https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400;600&display=swap',
            ];
        }

        return [
            'font' => "'Noto Sans', sans-serif",
            'url' => 'https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;600&display=swap',
        ];
    }
}
