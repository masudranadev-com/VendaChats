<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class OrderCallController extends Controller
{
    public function index(): View
    {
        $pageName = 'A Metafy';
        $defaultLanguage = 'Bangla';
        $defaultCallScope = 'cash_on_delivery';
        $packageLabel = 'Main Package';
        $totalCalls = 100;
        $remainingCalls = 64;
        $usedCalls = max(0, $totalCalls - $remainingCalls);
        $remainingCallPercent = (int) round(($remainingCalls / max(1, $totalCalls)) * 100);

        return view('admin.order-call.index', [
            'title' => 'Order Call Confirmation',
            'subtitle' => 'Manage live AI-powered voice call confirmation settings and review the remaining call balance included with your main package.',
            'packageLabel' => $packageLabel,
            'totalCalls' => $totalCalls,
            'remainingCalls' => $remainingCalls,
            'usedCalls' => $usedCalls,
            'remainingCallPercent' => $remainingCallPercent,
            'pageName' => $pageName,
            'defaultLanguage' => $defaultLanguage,
            'defaultCallScope' => $defaultCallScope,
            'supportedLanguages' => [
                'English',
                'Hindi',
                'Spanish',
                'Arabic',
                'Bangla',
                'Portuguese',
                'Indonesian',
                'Urdu',
                'Filipino',
                'Vietnamese',
                'Thai',
                'French',
            ],
        ]);
    }
}
