<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Throwable;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('user.login');
    }

    public function signupIndex(): View
    {
        return view('user.signup');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $apiUrl = rtrim((string) config('services.backend.internal_url', 'http://localhost:8082'), '/');

        try {
            $response = Http::acceptJson()
                ->timeout(12)
                ->post("{$apiUrl}/api/login", [
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                ]);
        } catch (Throwable) {
            return redirect()
                ->route('login.index')
                ->withErrors(['login' => 'Unable to reach the authentication service right now.'])
                ->withInput($request->only('email'));
        }

        if ($response->failed()) {
            return redirect()
                ->route('login.index')
                ->withErrors(['login' => $this->resolveAuthErrorMessage($response, 'Invalid email or password.')])
                ->withInput($request->only('email'));
        }

        $this->storeAuthenticatedSession($request, $response);

        return $this->redirectAfterAuth($request);
    }

    public function signup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'whatsapp_number' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ], [
            'whatsapp_number.regex' => 'Please enter a valid WhatsApp number (digits only, optional +).',
        ]);

        $apiUrl = rtrim((string) config('services.backend.internal_url', 'http://localhost:8082'), '/');

        try {
            $response = Http::acceptJson()
                ->timeout(12)
                ->post("{$apiUrl}/api/signup", [
                    'full_name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'whatsapp_number' => $validated['whatsapp_number'],
                    'password' => $validated['password'],
                ]);
        } catch (Throwable) {
            return redirect()
                ->route('signup.index')
                ->withErrors(['signup' => 'Unable to reach the authentication service right now.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        if ($response->failed()) {
            return redirect()
                ->route('signup.index')
                ->withErrors(['signup' => $this->resolveAuthErrorMessage($response, 'Unable to create the account right now.')])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $this->storeAuthenticatedSession($request, $response);

        return $this->redirectAfterAuth($request)
            ->with('status', 'Account created successfully. Finish onboarding to launch your workspace.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $token = $request->session()->get('auth.refresh_token');

        if ($token) {
            $apiUrl = config('services.backend.internal_url');

            Http::withHeaders(['x-refresh-token' => $token])
                ->post("{$apiUrl}/api/logout");
        }

        $request->session()->flush();

        return redirect()->route('login.index');
    }

    private function storeAdminGlobalConfig(Request $request, string $refreshToken): void
    {
        if ($refreshToken === '') {
            $request->session()->forget('admin.global_config');

            return;
        }

        $apiUrl = rtrim((string) config('services.backend.internal_url', 'http://localhost:8082'), '/');

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'user-refres-token' => $refreshToken,
                ])
                ->timeout(12)
                ->get("{$apiUrl}/api/admin/user/global/config/info");

            if (! $response->ok() || ! is_array($response->json())) {
                $request->session()->forget('admin.global_config');
                return;
            }

            $request->session()->put('admin.global_config', $response->json());
        } catch (Throwable) {
            $request->session()->forget('admin.global_config');
        }
    }

    private function storeAuthenticatedSession(Request $request, ClientResponse $response): void
    {
        $refreshToken = trim((string) $response->json('refresh_token', ''));
        $rawExpiresAt = $response->json('expires_at');

        $request->session()->regenerate();
        $request->session()->put('auth.refresh_token', $refreshToken);

        try {
            $expiresAt = $rawExpiresAt ? Carbon::parse((string) $rawExpiresAt)->timestamp : null;
        } catch (Throwable) {
            $expiresAt = null;
        }

        if ($expiresAt) {
            $request->session()->put('auth.expires_at', $expiresAt);
        } else {
            $request->session()->forget('auth.expires_at');
        }

        $this->storeAdminGlobalConfig($request, $refreshToken);
    }

    private function redirectAfterAuth(Request $request): RedirectResponse
    {
        $onboarding = (string) data_get($request->session()->get('admin.global_config', []), 'onboarding', '');

        if ($onboarding !== '' && $onboarding !== 'completed') {
            return redirect()->route('admin.onboarding');
        }

        return redirect()->route('admin.dashboard');
    }

    private function resolveAuthErrorMessage(ClientResponse $response, string $fallback): string
    {
        $message = trim((string) ($response->json('error') ?? $response->json('message') ?? ''));

        return $message !== '' ? $message : $fallback;
    }
}
