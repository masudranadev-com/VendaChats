<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class AdminLocaleOptions
{
    public function load(Request $request): array
    {
        $fallback = $this->fallback();
        $refreshToken = trim((string) $request->session()->get('auth.refresh_token', ''));

        if ($refreshToken === '') {
            return $fallback;
        }

        $apiUrl = rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/');

        try {
            $response = $this->request($refreshToken)
                ->get("{$apiUrl}/api/admin/user/language/all");
            $timezonesResponse = $this->request($refreshToken)
                ->get("{$apiUrl}/api/admin/user/timezone/all");

            $languages = $this->parseLanguages($response->json(), $fallback['languages']);
            $timezones = $this->parseTimezones($timezonesResponse->json(), $fallback['timezones']);

            return [
                'languages' => $languages,
                'admin_languages' => $languages,
                'website_languages' => $languages,
                'timezones' => $timezones,
            ];
        } catch (Throwable) {
            return $fallback;
        }
    }

    public function normalizeLanguage(?string $value, array $languages, string $fallback = 'en'): string
    {
        $needle = strtolower(trim((string) $value));
        if ($needle === '') {
            return array_key_exists($fallback, $languages)
                ? $fallback
                : (array_key_first($languages) ?? $fallback);
        }

        foreach ($languages as $code => $label) {
            if (strtolower(trim((string) $code)) === $needle || strtolower(trim((string) $label)) === $needle) {
                return (string) $code;
            }
        }

        return array_key_exists($fallback, $languages)
            ? $fallback
            : (array_key_first($languages) ?? $fallback);
    }

    public function normalizeTimezone(?string $value, array $timezones, string $fallback = 'Asia/Dhaka'): string
    {
        $needle = trim((string) $value);
        if ($needle !== '' && array_key_exists($needle, $timezones)) {
            return $needle;
        }

        return array_key_exists($fallback, $timezones)
            ? $fallback
            : (array_key_first($timezones) ?? $fallback);
    }

    private function request(string $refreshToken): PendingRequest
    {
        return Http::acceptJson()
            ->withHeaders([
                'user-refres-token' => $refreshToken,
            ])
            ->timeout(12);
    }

    private function parseLanguages(mixed $payload, array $fallback): array
    {
        if (! is_array($payload)) {
            return $fallback;
        }

        $languages = [];
        foreach ($payload as $item) {
            if (! is_array($item)) {
                continue;
            }

            $code = strtolower(trim((string) ($item['code'] ?? '')));
            $name = trim((string) ($item['name'] ?? ''));

            if ($code === '' || $name === '') {
                continue;
            }

            $languages[$code] = $name;
        }

        return $languages !== [] ? $languages : $fallback;
    }

    private function parseTimezones(mixed $payload, array $fallback): array
    {
        if (! is_array($payload)) {
            return $fallback;
        }

        $timezones = [];
        foreach ($payload as $item) {
            if (! is_array($item)) {
                continue;
            }

            $timezone = trim((string) ($item['timezone'] ?? ''));
            $label = trim((string) ($item['label'] ?? ''));

            if ($timezone === '' || $label === '') {
                continue;
            }

            $timezones[$timezone] = $label;
        }

        return $timezones !== [] ? $timezones : $fallback;
    }

    private function fallback(): array
    {
        $languages = [
            'pt' => 'Portuguese',
            'bn' => 'Bangla',
            'ar' => 'Arabic',
            'fr' => 'French',
            'hi' => 'Hindi',
            'id' => 'Indonesian',
            'es' => 'Spanish',
            'ur' => 'Urdu',
            'tl' => 'Filipino',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'en' => 'English',
            'vi' => 'Vietnamese',
        ];

        return [
            'languages' => $languages,
            'admin_languages' => $languages,
            'website_languages' => $languages,
            'timezones' => [
                'America/Sao_Paulo' => 'America/Sao_Paulo (UTC -03:00)',
                'Asia/Dhaka' => 'Asia/Dhaka (UTC +06:00)',
                'Africa/Cairo' => 'Africa/Cairo (UTC +02:00)',
                'Europe/Paris' => 'Europe/Paris (UTC +01:00)',
                'Asia/Kolkata' => 'Asia/Kolkata (UTC +05:30)',
                'Asia/Jakarta' => 'Asia/Jakarta (UTC +07:00)',
                'America/Mexico_City' => 'America/Mexico_City (UTC -06:00)',
                'Asia/Karachi' => 'Asia/Karachi (UTC +05:00)',
                'Asia/Manila' => 'Asia/Manila (UTC +08:00)',
                'Asia/Bangkok' => 'Asia/Bangkok (UTC +07:00)',
                'Europe/Istanbul' => 'Europe/Istanbul (UTC +03:00)',
                'America/New_York' => 'America/New_York (UTC -05:00)',
                'Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh (UTC +07:00)',
            ],
        ];
    }
}
