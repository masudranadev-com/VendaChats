<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $refreshToken = (string) (
            $request->session()->get('auth.refresh_token')
            ?? $request->session()->get('refresh_token')
            ?? ''
        );

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'subtitle' => 'Live operational snapshot powered by order-history API.',
            'ordersApiBaseUrl' => rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/'),
            'ordersRefreshToken' => $refreshToken,
        ]);
    }

    public function analytics()
    {
        return $this->page('Analytics', 'Track revenue, conversion, and campaign performance trends.');
    }

    public function conversations()
    {
        return $this->page('Conversations', 'Monitor AI and human replies across all customer threads.');
    }

    public function customers()
    {
        return $this->page('Customers', 'View customer segments, behavior, and lifecycle insights.');
    }

    public function bargaining()
    {
        return $this->page('Bargaining Rules', 'Set floor price, negotiation steps, and approval boundaries.');
    }

    public function campaigns(Request $request)
    {
        $allCampaigns = collect([
            [
                'name' => 'Weekend Drop Recovery',
                'product' => 'Premium Cotton T-Shirt',
                'status' => 'Live',
                'budget' => 'BDT 24,000',
                'reach' => '12,450',
                'conversion' => '4.8%',
                'progress' => 72,
                'launched_at' => '21 Feb 2026, 10:00 AM',
            ],
            [
                'name' => 'Back In Stock Push',
                'product' => 'Smart Casual Hoodie',
                'status' => 'Scheduled',
                'budget' => 'BDT 16,500',
                'reach' => '8,130',
                'conversion' => '3.9%',
                'progress' => 38,
                'launched_at' => '23 Feb 2026, 01:30 PM',
            ],
            [
                'name' => 'High Intent Retarget',
                'product' => 'Wireless Earbuds Pro',
                'status' => 'Draft',
                'budget' => 'BDT 28,400',
                'reach' => '15,700',
                'conversion' => '5.2%',
                'progress' => 18,
                'launched_at' => 'Awaiting approval',
            ],
            [
                'name' => 'Eid Bundle Countdown',
                'product' => 'Leather Office Backpack',
                'status' => 'Completed',
                'budget' => 'BDT 21,200',
                'reach' => '18,320',
                'conversion' => '6.1%',
                'progress' => 100,
                'launched_at' => '14 Feb 2026, 09:00 AM',
            ],
            [
                'name' => 'Flash Friday Scale-Up',
                'product' => 'AirFlex Running Shoes',
                'status' => 'Completed',
                'budget' => 'BDT 32,000',
                'reach' => '31,580',
                'conversion' => '7.4%',
                'progress' => 100,
                'launched_at' => '07 Feb 2026, 11:15 AM',
            ],
            [
                'name' => 'Messenger Winback Sprint',
                'product' => 'Premium Cotton T-Shirt',
                'status' => 'Paused',
                'budget' => 'BDT 13,800',
                'reach' => '9,920',
                'conversion' => '2.9%',
                'progress' => 54,
                'launched_at' => '02 Feb 2026, 08:40 PM',
            ],
            [
                'name' => 'Cart Rescue - Dhaka',
                'product' => 'Wireless Earbuds Pro',
                'status' => 'Completed',
                'budget' => 'BDT 18,700',
                'reach' => '14,100',
                'conversion' => '5.8%',
                'progress' => 100,
                'launched_at' => '31 Jan 2026, 04:20 PM',
            ],
            [
                'name' => 'Sunday Story Burst',
                'product' => 'Smart Casual Hoodie',
                'status' => 'Completed',
                'budget' => 'BDT 11,400',
                'reach' => '7,450',
                'conversion' => '3.6%',
                'progress' => 100,
                'launched_at' => '26 Jan 2026, 10:35 AM',
            ],
            [
                'name' => 'Accessories Upsell Wave',
                'product' => 'Leather Office Backpack',
                'status' => 'Live',
                'budget' => 'BDT 19,300',
                'reach' => '13,670',
                'conversion' => '4.2%',
                'progress' => 66,
                'launched_at' => '20 Feb 2026, 06:25 PM',
            ],
            [
                'name' => 'Midnight Deal Teaser',
                'product' => 'AirFlex Running Shoes',
                'status' => 'Completed',
                'budget' => 'BDT 9,600',
                'reach' => '6,940',
                'conversion' => '3.1%',
                'progress' => 100,
                'launched_at' => '22 Jan 2026, 11:50 PM',
            ],
            [
                'name' => 'VIP Segment Revival',
                'product' => 'Premium Cotton T-Shirt',
                'status' => 'Scheduled',
                'budget' => 'BDT 15,200',
                'reach' => '10,820',
                'conversion' => '4.1%',
                'progress' => 24,
                'launched_at' => '24 Feb 2026, 12:00 PM',
            ],
            [
                'name' => 'New Visitor Warmup',
                'product' => 'Smart Casual Hoodie',
                'status' => 'Draft',
                'budget' => 'BDT 8,900',
                'reach' => '5,300',
                'conversion' => '2.4%',
                'progress' => 12,
                'launched_at' => 'Awaiting approval',
            ],
            [
                'name' => 'Rapid Remarketing Cycle',
                'product' => 'Wireless Earbuds Pro',
                'status' => 'Completed',
                'budget' => 'BDT 27,100',
                'reach' => '24,880',
                'conversion' => '6.7%',
                'progress' => 100,
                'launched_at' => '18 Jan 2026, 03:10 PM',
            ],
            [
                'name' => 'Festival Pre-Heat',
                'product' => 'AirFlex Running Shoes',
                'status' => 'Paused',
                'budget' => 'BDT 14,250',
                'reach' => '11,030',
                'conversion' => '3.2%',
                'progress' => 47,
                'launched_at' => '15 Jan 2026, 09:25 AM',
            ],
            [
                'name' => 'Bundle Booster Run',
                'product' => 'Leather Office Backpack',
                'status' => 'Completed',
                'budget' => 'BDT 22,600',
                'reach' => '19,910',
                'conversion' => '5.9%',
                'progress' => 100,
                'launched_at' => '11 Jan 2026, 02:45 PM',
            ],
            [
                'name' => 'Holiday Conversion Blast',
                'product' => 'Premium Cotton T-Shirt',
                'status' => 'Completed',
                'budget' => 'BDT 29,900',
                'reach' => '33,120',
                'conversion' => '7.8%',
                'progress' => 100,
                'launched_at' => '04 Jan 2026, 10:00 AM',
            ],
            [
                'name' => 'Referral Nudge Campaign',
                'product' => 'Smart Casual Hoodie',
                'status' => 'Completed',
                'budget' => 'BDT 12,300',
                'reach' => '9,680',
                'conversion' => '4.0%',
                'progress' => 100,
                'launched_at' => '29 Dec 2025, 05:30 PM',
            ],
            [
                'name' => 'Year-End Clearance Drive',
                'product' => 'Wireless Earbuds Pro',
                'status' => 'Completed',
                'budget' => 'BDT 35,000',
                'reach' => '40,250',
                'conversion' => '8.3%',
                'progress' => 100,
                'launched_at' => '22 Dec 2025, 09:15 AM',
            ],
        ])->values();

        $perPage = 6;
        $currentPage = max(1, (int) $request->query('page', 1));
        $lastPage = max(1, (int) ceil($allCampaigns->count() / $perPage));
        $currentPage = min($currentPage, $lastPage);
        $campaigns = new LengthAwarePaginator(
            $allCampaigns->forPage($currentPage, $perPage)->values()->all(),
            $allCampaigns->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.campaigns.index', [
            'title' => 'Campaigns',
            'subtitle' => 'Design, schedule, and monitor product-focused marketing campaigns.',
            'campaigns' => $campaigns,
            'campaignSummary' => [
                'total' => $allCampaigns->count(),
                'live' => $allCampaigns->where('status', 'Live')->count(),
                'scheduled' => $allCampaigns->where('status', 'Scheduled')->count(),
                'templates' => 5,
            ],
        ]);
    }

    public function settings()
    {
        return $this->page('Shop Settings', 'Update brand profile, preferences, and account controls.');
    }

    public function billing()
    {
        return view('admin.billing', [
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

    private function page(string $title, string $subtitle)
    {
        return view('admin.page', compact('title', 'subtitle'));
    }
}
