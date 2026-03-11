<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPackageController extends Controller
{
    public function packages()
    {
        return view('admin.packages.index', [
            'title' => 'Billing & Packages',
            'subtitle' => 'Track subscription health, payment status, and upgrade paths from one place.',
            'billingStatus' => [
                'is_paid' => true,
                'plan_name' => 'Growth',
                'plan_label' => 'Growth Plan',
                'price' => 'BDT 4,900',
                'billing_cycle' => 'Monthly billing',
                'status_label' => 'Paid',
                'renews_on' => '28 Mar 2026',
                'last_charge' => '01 Mar 2026',
                'support_level' => 'Priority WhatsApp support',
                'seat_usage' => '3 of 5 seats active',
                'note' => 'Automation, order sync, and support tools are fully active for this workspace.',
            ],
            'packages' => [
                [
                    'name' => 'Starter',
                    'price' => 'BDT 1,900',
                    'period' => '/month',
                    'badge' => 'For new shops',
                    'description' => 'A clean starting point for single-brand stores moving off manual order handling.',
                    'accent' => 'starter',
                    'cta' => 'Choose Starter',
                    'is_current' => false,
                    'is_featured' => false,
                    'features' => [
                        'Up to 300 confirmed orders per month',
                        'Basic order dashboard and product sync',
                        'Messenger and website lead capture',
                        '1 admin seat',
                    ],
                ],
                [
                    'name' => 'Growth',
                    'price' => 'BDT 4,900',
                    'period' => '/month',
                    'badge' => 'Current plan',
                    'description' => 'Balanced package for teams that need faster confirmation flow and better conversion coverage.',
                    'accent' => 'growth',
                    'cta' => 'Current Package',
                    'is_current' => true,
                    'is_featured' => true,
                    'features' => [
                        'Up to 1,500 orders per month',
                        'Order-call automation and campaign tools',
                        'Advanced customer segments and analytics',
                        '5 admin seats with role-based access',
                    ],
                ],
                [
                    'name' => 'Scale',
                    'price' => 'BDT 9,900',
                    'period' => '/month',
                    'badge' => 'Most powerful',
                    'description' => 'Built for larger operations that want deeper automation, higher limits, and premium support.',
                    'accent' => 'scale',
                    'cta' => 'Upgrade to Scale',
                    'is_current' => false,
                    'is_featured' => false,
                    'features' => [
                        'Unlimited products and 5,000+ orders',
                        'Priority queues for team workflows',
                        'Dedicated launch support and migration help',
                        'Unlimited admin seats',
                    ],
                ],
            ],
        ]);
    }
}
