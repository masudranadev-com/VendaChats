<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminPostController extends Controller
{
    public function posts(Request $request): View
    {
        return view('admin.posts.index', [
            'title' => 'Posts',
            'subtitle' => 'Review post activity and prioritize reply opportunities by time and engagement.',
        ]);
    }
}
