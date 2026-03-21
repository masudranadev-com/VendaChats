<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class OrderCallController extends Controller
{
    public function index(): View
    {
        $pageName = 'A Metafy';
        $defaultLanguage = 'english';
        $defaultCallScope = 'cod';
        $packageLabel = 'Main Package';

        return view('admin.order-call.index', [
            'title' => 'Order Call Confirmation',
            'subtitle' => 'Manage live AI-powered voice call confirmation settings and review the remaining minute balance included with your main package.',
            'orderCallApiBaseUrl' => rtrim((string) Config::get('services.backend.public_url', 'http://localhost:8082'), '/'),
            'packageLabel' => $packageLabel,
            'pageName' => $pageName,
            'defaultLanguage' => $defaultLanguage,
            'defaultCallScope' => $defaultCallScope,
            'supportedLanguages' => [
                'english' => 'English',
                'hindi' => 'Hindi',
                'spanish' => 'Spanish',
                'arabic' => 'Arabic',
                'bangla' => 'Bangla',
                'portuguese' => 'Portuguese',
                'indonesian' => 'Indonesian',
                'urdu' => 'Urdu',
                'filipino' => 'Filipino',
                'vietnamese' => 'Vietnamese',
                'thai' => 'Thai',
                'french' => 'French',
            ],
        ]);
    }
}
