<?php
declare(strict_types=1);

namespace App\Services;

final class GeoService
{
    public function getClientIp(array $server): string
    {
        $ip = trim((string) ($server['REMOTE_ADDR'] ?? ''));
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    public function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    public function fetchCountryCode(string $ip): string
    {
        if (!$this->isPublicIp($ip)) {
            return '';
        }
        $url = 'https://ipapi.co/' . rawurlencode($ip) . '/country/';
        $context = stream_context_create(['http' => ['timeout' => 1.5]]);
        $code = @file_get_contents($url, false, $context);
        $code = strtoupper(trim((string) $code));
        return preg_match('/^[A-Z]{2}$/', $code) ? $code : '';
    }

    public function mapCountryToLanguage(string $country): string
    {
        $country = strtoupper(trim($country));

        return match (true) {
            in_array($country, [
                'AE', 'BH', 'DZ', 'EG', 'IQ', 'JO', 'KW', 'LB', 'LY', 'MA', 'MR',
                'OM', 'PS', 'QA', 'SA', 'SD', 'SO', 'SY', 'TN', 'YE', 'DJ', 'KM',
                'TD', 'ER',
            ], true) => 'ar',

            in_array($country, [
                'US', 'GB', 'AU', 'NZ', 'CA', 'IE', 'ZA', 'JM', 'BS', 'BZ', 'SG',
                'NG', 'PK', 'GH', 'KE', 'UG', 'SL', 'GM', 'GY', 'LR',
            ], true) => 'en',

            in_array($country, ['CN', 'TW', 'HK'], true) => 'zh',

            $country === 'IN' => 'hi',

            in_array($country, [
                'ES', 'MX', 'AR', 'CO', 'CL', 'PE', 'VE', 'EC', 'GT', 'CU', 'BO',
                'DO', 'HN', 'PY', 'SV', 'NI', 'CR', 'PR', 'PA', 'UY', 'GQ',
            ], true) => 'es',

            in_array($country, [
                'FR', 'BE', 'CH', 'LU', 'MC', 'SN', 'ML', 'NE', 'BJ', 'BF', 'CI',
                'TG', 'GA', 'GN', 'CD', 'CG', 'BI', 'RW', 'CF',
            ], true) => 'fr',

            $country === 'BD' => 'bn',

            in_array($country, ['PT', 'BR', 'AO', 'MZ', 'CV', 'GW', 'ST', 'TL'], true) => 'pt',

            in_array($country, ['RU', 'BY', 'KG', 'KZ'], true) => 'ru',

            $country === 'PK' => 'ur',

            in_array($country, ['ID', 'MY', 'BN'], true) => 'id',

            in_array($country, ['DE', 'AT', 'LI'], true) => 'de',

            $country === 'JP' => 'ja',

            in_array($country, ['TR', 'CY'], true) => 'tr',

            $country === 'VN' => 'vi',

            in_array($country, ['KR', 'KP'], true) => 'ko',

            in_array($country, ['IR', 'AF', 'TJ'], true) => 'fa',

            $country === 'UZ' => 'uz',
            $country === 'TM' => 'tk',
            $country === 'AZ' => 'az',

            $country === 'AL' => 'sq',
            $country === 'MV' => 'dv',
            $country === 'SR' => 'nl',

            default => 'en',
        };
    }
}
