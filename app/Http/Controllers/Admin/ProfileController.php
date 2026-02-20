<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        return view('admin.profile.index', [
            'title' => 'My Profile',
            'subtitle' => 'Manage your personal profile and account details used across the admin panel.',
            'profile' => [
                'name' => 'Rahim Ahmed',
                'email' => 'rahim.ahmed@example.com',
                'phone' => '+880 1712-345678',
                'role' => 'Owner',
                'department' => 'Operations',
                'timezone' => 'Asia/Dhaka',
                'language' => 'English',
                'joined_at' => 'January 12, 2024',
                'last_login' => 'Today, 10:32 AM',
            ],
            'recentActivities' => [
                ['time' => '15m ago', 'text' => 'Updated courier assignment rules.'],
                ['time' => '2h ago', 'text' => 'Approved 4 high-value COD orders.'],
                ['time' => 'Yesterday', 'text' => 'Changed store timezone preference.'],
            ],
        ]);
    }

    public function settings(): View
    {
        return view('admin.profile.settings', [
            'title' => 'My Settings',
            'subtitle' => 'Set notification preferences, login security, and interface defaults for your account.',
            'preferences' => [
                'email_notifications' => true,
                'sms_notifications' => false,
                'browser_notifications' => true,
                'weekly_summary' => true,
                'dark_mode' => false,
            ],
            'security' => [
                'two_factor_enabled' => false,
                'last_password_change' => '42 days ago',
                'active_sessions' => 2,
            ],
        ]);
    }
}
