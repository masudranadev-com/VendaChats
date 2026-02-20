<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminPostController extends Controller
{
    public function posts()
    {
        return view('admin.page', [
            'title' => 'Posts',
            'heading' => 'Posts',
            'subtitle' => 'Review social content, publishing status, and post-level engagement.',
        ]);
    }
}
