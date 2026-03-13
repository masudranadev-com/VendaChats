<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminOnboardingController extends Controller
{
    // onboarding
    public function onboarding(Request $request)
    {
        return view('admin.onboarding.index');
    }
}
