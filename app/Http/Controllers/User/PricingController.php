<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class PricingController extends Controller
{
    public function index()
    {
        return view('user.pricing');
    }
}
