<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
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

        // now check on boarding 

        return $next($request);
    }
}
