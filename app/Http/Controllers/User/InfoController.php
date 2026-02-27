<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class InfoController extends Controller
{
    public function features()
    {
        return view('user.features');
    }

    public function howItWorks()
    {
        return view('user.how-it-works');
    }

    public function about()
    {
        return view('user.about');
    }

    public function contact()
    {
        return view('user.contact');
    }

    public function privacy()
    {
        return response()
            ->view('user.privacy-policy')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function terms()
    {
        return response()
            ->view('user.terms-and-conditions')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
