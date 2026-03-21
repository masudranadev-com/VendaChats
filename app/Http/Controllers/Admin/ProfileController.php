<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminLocaleOptions;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function __construct(private readonly AdminLocaleOptions $localeOptions)
    {
    }

    public function index(Request $request): View
    {
        $profile = $this->fetchProfile($request);
        $localeOptions = $this->localeOptions->load($request);

        return view('admin.profile.index', [
            'title' => 'My Profile',
            'subtitle' => 'Update your contact details, profile image, email, and password from one place.',
            'profileApiBaseUrl' => rtrim((string) config('services.backend.public_url', 'http://localhost:8082'), '/'),
            'profileRefreshToken' => (string) (
                $request->session()->get('auth.refresh_token')
                ?? $request->session()->get('refresh_token')
                ?? ''
            ),
            'localeOptions' => $localeOptions,
            'profile' => [
                'id' => (int) ($profile['id'] ?? 0),
                'username' => (string) ($profile['username'] ?? 'admin'),
                'full_name' => (string) ($profile['full_name'] ?? 'Admin'),
                'email' => (string) ($profile['email'] ?? ''),
                'phone_number' => (string) ($profile['phone_number'] ?? ''),
                'address' => (string) ($profile['address'] ?? ''),
                'profile_image' => (string) ($profile['profile_image'] ?? ''),
                'onboarding' => (string) ($profile['onboarding'] ?? 'completed'),
                'product_type' => (string) ($profile['product_type'] ?? 'physical'),
                'timezone' => $this->localeOptions->normalizeTimezone(
                    (string) ($profile['timezone'] ?? 'Asia/Dhaka'),
                    $localeOptions['timezones'] ?? []
                ),
                'admin_language' => $this->localeOptions->normalizeLanguage(
                    (string) ($profile['admin_language'] ?? 'en'),
                    $localeOptions['admin_languages'] ?? []
                ),
                'website_language' => $this->localeOptions->normalizeLanguage(
                    (string) ($profile['website_language'] ?? 'en'),
                    $localeOptions['website_languages'] ?? []
                ),
                'joined_at' => $this->formatDate($profile['created_at'] ?? null, 'Account not available'),
                'last_login_at' => $this->formatDateTime($profile['last_login_at'] ?? null, 'No login recorded yet'),
                'password_changed_at' => $this->formatDateTime($profile['password_changed_at'] ?? null, 'No password change yet'),
            ],
        ]);
    }

    public function settings(Request $request): View
    {
        return $this->index($request);
    }

    private function fetchProfile(Request $request): array
    {
        $refreshToken = trim((string) $request->session()->get('auth.refresh_token', ''));
        if ($refreshToken === '') {
            return [];
        }

        $apiUrl = rtrim((string) config('services.backend.internal_url', 'http://localhost:8082'), '/');

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'user-refres-token' => $refreshToken,
                ])
                ->timeout(12)
                ->get("{$apiUrl}/api/admin/profile");

            if (! $response->ok() || ! is_array($response->json())) {
                return [];
            }

            return $response->json();
        } catch (Throwable) {
            return [];
        }
    }

    private function formatDate(mixed $value, string $fallback): string
    {
        $date = $this->parseDate($value);

        return $date?->format('F j, Y') ?? $fallback;
    }

    private function formatDateTime(mixed $value, string $fallback): string
    {
        $date = $this->parseDate($value);

        return $date?->format('F j, Y g:i A') ?? $fallback;
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($text);
        } catch (Throwable) {
            return null;
        }
    }
}
