<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

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
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $apiUrl = config('services.backend.url');

        $response = Http::acceptJson()->post("{$apiUrl}/api/login", [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        if ($response->failed()) {
            return redirect()
                ->route('login.index')
                ->withErrors(['login' => 'Invalid email or password.'])
                ->withInput($request->only('email'));
        }

        $request->session()->put('auth.refresh_token', $response->json('refresh_token'));

        return redirect()->route('admin.dashboard');
    }

    public function signup(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'whatsapp_number' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ], [
            'whatsapp_number.regex' => 'Please enter a valid WhatsApp number (digits only, optional +).',
        ]);

        return redirect()
            ->route('login.index')
            ->with('status', 'Sign up submitted. WhatsApp verification will be required later.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $token = $request->session()->get('auth.refresh_token');

        if ($token) {
            $apiUrl = config('services.backend.url');

            Http::withHeaders(['x-refresh-token' => $token])
                ->post("{$apiUrl}/api/logout");
        }

        $request->session()->flush();

        return redirect()->route('login.index');
    }
}
