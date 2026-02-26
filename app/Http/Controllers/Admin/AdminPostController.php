<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPostController extends Controller
{
    public function posts(Request $request): View
    {
        $refreshToken = (string) (
            $request->session()->get('auth.refresh_token')
            ?? $request->session()->get('refresh_token')
            ?? ''
        );

        return view('admin.posts.index', [
            'title' => 'Posts',
            'subtitle' => 'Review post activity and prioritize reply opportunities by time and engagement.',
            'postsApiBaseUrl' => rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/'),
            'postsRefreshToken' => $refreshToken,
        ]);
    }
}
