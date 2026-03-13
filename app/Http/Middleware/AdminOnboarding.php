<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnboarding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->session()->get('auth.refresh_token');

        if (! $token) {
            return redirect()->route('login.index');
        }

        $expiresAt = $request->session()->get('auth.expires_at');

        if ($expiresAt && now()->timestamp > $expiresAt) {
            $request->session()->forget(['auth.refresh_token', 'auth.expires_at']);

            return redirect()->route('login.index')
                ->withErrors(['login' => 'Your session has expired. Please log in again.']);
        }

        // onboarding
        if (isset($request->session()->get("admin.global_config")["onboarding"]) && $request->session()->get("admin.global_config")["onboarding"] == "completed") {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
