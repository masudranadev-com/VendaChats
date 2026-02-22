<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
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

    public function products(Request $request)
    {
        $allProducts = [
            [
                'name' => 'Premium Cotton T-Shirt',
                'sku' => 'SKU-TS-2109',
                'image' => asset('assets/images/products/premium-cotton-tshirt.svg'),
                'category' => 'Apparel',
                'price' => 'BDT 1,150',
                'stock' => 84,
                'stock_label' => 'In Stock',
                'visitors' => 2340,
                'sales' => 146,
                'status' => 'Active',
            ],
            [
                'name' => 'Smart Casual Hoodie',
                'sku' => 'SKU-HD-1231',
                'image' => asset('assets/images/products/smart-casual-hoodie.svg'),
                'category' => 'Apparel',
                'price' => 'BDT 1,890',
                'stock' => 22,
                'stock_label' => 'Low Stock',
                'visitors' => 1288,
                'sales' => 67,
                'status' => 'Active',
            ],
            [
                'name' => 'Wireless Earbuds Pro',
                'sku' => 'SKU-EB-4412',
                'image' => asset('assets/images/products/wireless-earbuds-pro.svg'),
                'category' => 'Electronics',
                'price' => 'BDT 3,250',
                'stock' => 56,
                'stock_label' => 'In Stock',
                'visitors' => 3610,
                'sales' => 201,
                'status' => 'Active',
            ],
            [
                'name' => 'Leather Office Backpack',
                'sku' => 'SKU-BP-9920',
                'image' => asset('assets/images/products/leather-office-backpack.svg'),
                'category' => 'Accessories',
                'price' => 'BDT 2,780',
                'stock' => 11,
                'stock_label' => 'Critical',
                'visitors' => 910,
                'sales' => 39,
                'status' => 'Draft',
            ],
            [
                'name' => 'AirFlex Running Shoes',
                'sku' => 'SKU-SH-3318',
                'image' => asset('assets/images/products/airflex-running-shoes.svg'),
                'category' => 'Footwear',
                'price' => 'BDT 2,450',
                'stock' => 73,
                'stock_label' => 'In Stock',
                'visitors' => 2062,
                'sales' => 128,
                'status' => 'Active',
            ],
        ];

        $perPage = 3;
        $currentPage = max(1, (int) $request->query('page', 1));
        $products = new LengthAwarePaginator(
            array_slice($allProducts, ($currentPage - 1) * $perPage, $perPage),
            count($allProducts),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.products.index', [
            'title' => 'Products',
            'subtitle' => 'Manage catalog, pricing, stock risk, and product performance in one clear workspace.',
            'metrics' => [
                ['label' => 'Total Products', 'value' => '286', 'meta' => '+14 this month'],
                ['label' => 'Live Products', 'value' => '249', 'meta' => '37 need review'],
                ['label' => 'Low Stock Alert', 'value' => '18', 'meta' => 'Restock in 24h'],
                ['label' => 'Avg Visitors', 'value' => '5.8%', 'meta' => '+0.9% this week'],
            ],
            'products' => $products,
            'attentionItems' => [
                ['title' => '11 units left on Leather Office Backpack', 'note' => 'Top wishlist item. Create urgent restock request.'],
                ['title' => 'Hoodie return rate increased to 6.2%', 'note' => 'Check size chart and fabric details on product page.'],
                ['title' => '3 products missing size variation', 'note' => 'Publish size options to reduce drop-off on checkout.'],
            ],
            'categoryHealth' => [
                ['name' => 'Apparel', 'share' => 46],
                ['name' => 'Electronics', 'share' => 28],
                ['name' => 'Footwear', 'share' => 16],
                ['name' => 'Accessories', 'share' => 10],
            ],
        ]);
    }

    public function productCreate()
    {
        return view('admin.products.create', [
            'title' => 'Add Product',
            'subtitle' => 'Set core product details, pricing, stock, media, and channel visibility before publishing.',
            'categories' => ['Apparel', 'Electronics', 'Footwear', 'Accessories'],
            'channels' => ['Website', 'Facebook', 'Messenger', 'WhatsApp', 'Instagram'],
            'shippingProfiles' => ['Standard', 'Express', 'Fragile Item', 'Outside Dhaka'],
        ]);
    }

    public function categories()
    {
        $categories = [
            [
                'name' => 'Apparel',
                'slug' => 'apparel',
                'parent' => null,
                'description' => 'Everyday fashion essentials and seasonal wear with quality-first sourcing.',
                'products' => 84,
                'share' => 46,
                'status' => 'Active',
                'updated_at' => '2h ago',
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'parent' => null,
                'description' => 'Smart gadgets and accessories curated for performance and reliability.',
                'products' => 63,
                'share' => 28,
                'status' => 'Active',
                'updated_at' => '5h ago',
            ],
            [
                'name' => 'Footwear',
                'slug' => 'footwear',
                'parent' => 'Apparel',
                'description' => 'Comfort-driven shoe collection for casual, office, and active lifestyles.',
                'products' => 41,
                'share' => 16,
                'status' => 'Active',
                'updated_at' => '1d ago',
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'parent' => 'Apparel',
                'description' => 'Bags, belts, and daily carry items to complete each look.',
                'products' => 52,
                'share' => 10,
                'status' => 'Draft',
                'updated_at' => '3d ago',
            ],
            [
                'name' => 'Flash Deals',
                'slug' => 'flash-deals',
                'parent' => null,
                'description' => 'Limited-time category for high-intent offers and short campaign pushes.',
                'products' => 0,
                'share' => 0,
                'status' => 'Draft',
                'updated_at' => 'Just now',
            ],
        ];

        return view('admin.categories.index', [
            'title' => 'Categories',
            'subtitle' => 'Create and organize product categories in one place.',
            'metrics' => [
                ['label' => 'Total Categories', 'value' => (string) count($categories), 'meta' => '1 draft pending approval'],
                ['label' => 'Catalog Coverage', 'value' => '96%', 'meta' => '4% products uncategorized'],
                ['label' => 'Top Category', 'value' => 'Apparel', 'meta' => '46% share of catalog'],
                ['label' => 'Updated Today', 'value' => '3', 'meta' => 'Last sync 2h ago'],
            ],
            'categories' => $categories,
            'suggestionSchedule' => [
                'next_reset_in' => '2h 40m',
                'next_reset_at' => 'Today, 11:30 PM',
            ],
            'suggestions' => [
                [
                    'title' => 'Launch New Arrival Category',
                    'note' => 'Create a dedicated "New Arrival" category for faster product discovery.',
                    'next_reset_in' => '2h 40m',
                    'next_reset_at' => 'Today, 11:30 PM',
                ],
                [
                    'title' => 'Merge Low-Volume Buckets',
                    'note' => 'Merge overlapping low-volume categories to reduce navigation clutter.',
                    'next_reset_in' => '8h 10m',
                    'next_reset_at' => 'Tomorrow, 5:00 AM',
                ],
                [
                    'title' => 'Review Draft Category Status',
                    'note' => 'Review draft categories before the next campaign launch.',
                    'next_reset_in' => '17h 55m',
                    'next_reset_at' => 'Tomorrow, 2:45 PM',
                ],
            ],
        ]);
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
        return $this->page('Billing', 'Manage subscriptions, invoices, and payment methods.');
    }

    private function page(string $title, string $subtitle)
    {
        return view('admin.page', compact('title', 'subtitle'));
    }
}
