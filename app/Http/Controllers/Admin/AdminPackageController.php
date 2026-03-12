<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class AdminPackageController extends Controller
{
    public function packages(): View
    {
        return view('admin.packages.index', [
            'title' => 'Packages',
            'subtitle' => 'Manage your current package, renewal details, and upgrade options from one place.',
            'packagesApiBaseUrl' => rtrim((string) Config::get('services.backend.url', 'http://localhost:8082'), '/'),
        ]);
    }
}
