<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CoachController extends Controller
{
    public function index(): View
    {
        return view('admin.coach.index', [
            'title' => 'Execution Coach',
            'subtitle' => 'Practical action board based on modules currently implemented in this build.',
        ]);
    }
}
