<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BotSettingsController extends Controller
{
    public function index()
    {
        return view('admin.bot-settings', [
            'title' => 'Bot Settings',
            'subtitle' => 'Enable and control each automation capability for Messenger and Comment workflows.',
        ]);
    }
}
