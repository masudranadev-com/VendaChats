<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class AdminCampaignController extends Controller
{
    public function index(): View
    {
        return view('admin.campaigns.index', [
            'title' => 'Campaigns',
            'subtitle' => 'Build, schedule, and control product campaigns from one clear workspace.',
            'campaignsApiBaseUrl' => rtrim((string) Config::get('services.backend.url', 'http://localhost:8082'), '/'),
        ]);
    }
}
