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
        $request->session()->put('auth.expires_at', $response->json('expires_at'));

        return redirect()->route('admin.dashboard');
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
