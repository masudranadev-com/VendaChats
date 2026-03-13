<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AdminOnboardingController extends Controller
{
    public function onboarding(Request $request): View
    {
        return view('admin.onboarding.index', [
            'adminGlobalConfig' => (array) $request->session()->get('admin.global_config', []),
        ]);
    }

    public function onboardingContinue(Request $request): JsonResponse
    {
        try {
            $payload = $this->validatedPayload($request);
        } catch (ValidationException $exception) {
            return response()->json([
                'error' => $exception->validator->errors()->first(),
            ], 422);
        }

        $refreshToken = trim((string) $request->session()->get('auth.refresh_token', ''));
        if ($refreshToken === '') {
            return response()->json([
                'error' => 'Missing refresh token. Please login again.',
            ], 401);
        }

        $apiUrl = rtrim((string) Config::get('services.backend.url', 'http://localhost:8082'), '/');

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'user-refres-token' => $refreshToken,
                ])
                ->timeout(12)
                ->post("{$apiUrl}/api/admin/user/global/onboarding", $payload);
        } catch (Throwable) {
            return response()->json([
                'error' => 'Could not update onboarding right now. Please try again.',
            ], 500);
        }

        $responseBody = $response->json();
        $body = is_array($responseBody) ? $responseBody : [];

        if (! $response->successful()) {
            return response()->json([
                'error' => trim((string) ($body['error'] ?? $body['message'] ?? 'Onboarding update failed.')),
            ], $response->status());
        }

        $this->syncAdminGlobalConfig($request, $payload['type'], $payload['data'], $body);

        return response()->json($body);
    }

    private function validatedPayload(Request $request): array
    {
        $payload = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:product,sub_domain,call_order,locale'],
            'data' => ['required', 'array'],
        ])->validate();

        $dataRules = match ($payload['type']) {
            'product' => [
                'product_type' => ['required', 'string'],
            ],
            'sub_domain' => [
                'sub_domain' => ['required', 'string'],
            ],
            'call_order' => [
                'recording_page_name' => ['required', 'string'],
                'recording_language' => ['required', 'string'],
                'calling_scope' => ['required', 'string', 'in:all,cod'],
            ],
            'locale' => [
                'timezone' => ['required', 'string'],
                'admin_language' => ['required', 'string'],
                'website_language' => ['required', 'string'],
            ],
        };

        $payload['data'] = Validator::make($payload['data'], $dataRules)->validate();

        return $payload;
    }

    private function syncAdminGlobalConfig(Request $request, string $type, array $submittedData, array $response): void
    {
        $config = (array) $request->session()->get('admin.global_config', []);
        $responseData = is_array($response['data'] ?? null) ? $response['data'] : [];
        $data = array_merge($submittedData, $responseData);

        if ($type === 'product') {
            $config['product_type'] = trim((string) ($data['product_type'] ?? $config['product_type'] ?? ''));
        }

        if ($type === 'sub_domain') {
            $subDomain = trim((string) ($data['sub_domain'] ?? $config['subdomain'] ?? ''));
            $config['subdomain'] = $subDomain;
            $config['username'] = $subDomain;
        }

        if ($type === 'call_order') {
            $pageName = trim((string) ($data['recording_page_name'] ?? $config['page_name'] ?? ''));
            $language = strtolower(trim((string) ($data['recording_language'] ?? $config['primary_language'] ?? '')));
            $scope = strtolower(trim((string) ($data['calling_scope'] ?? $config['call_buyer_scope'] ?? 'cod')));

            $config['page_name'] = $pageName;
            $config['primary_language'] = $language;
            $config['language'] = $language;
            $config['call_buyer_scope'] = $scope;
        }

        if ($type === 'locale') {
            $config['timezone'] = trim((string) ($data['timezone'] ?? $config['timezone'] ?? ''));
            $config['admin_panel_language'] = strtolower(trim((string) ($data['admin_language'] ?? $config['admin_panel_language'] ?? '')));
            $config['website_language'] = strtolower(trim((string) ($data['website_language'] ?? $config['website_language'] ?? '')));
        }

        $onboardingState = trim((string) ($response['onboarding'] ?? ''));
        if ($onboardingState !== '') {
            $config['onboarding'] = $onboardingState;
        }

        $nextStep = trim((string) ($response['next_step'] ?? ''));
        if ($nextStep !== '') {
            $config['onboarding_next_step'] = $nextStep;
        } else {
            unset($config['onboarding_next_step']);
        }

        $request->session()->put('admin.global_config', $config);
    }
}
