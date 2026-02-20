<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CoachController extends Controller
{
    public function index(): View
    {
        return view('admin.coach.index', [
            'title' => 'Performance Coach',
            'subtitle' => 'AI-driven action center for growth, recovery, and campaign optimization.',
        ]);
    }
}
