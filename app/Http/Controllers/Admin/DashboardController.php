<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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

    public function products()
    {
        return view('admin.products.index', [
            'title' => 'Products',
            'subtitle' => 'Manage catalog, pricing, stock risk, and product performance in one clear workspace.',
            'metrics' => [
                ['label' => 'Total Products', 'value' => '286', 'meta' => '+14 this month'],
                ['label' => 'Live Products', 'value' => '249', 'meta' => '37 need review'],
                ['label' => 'Low Stock Alert', 'value' => '18', 'meta' => 'Restock in 24h'],
                ['label' => 'Avg Visitors', 'value' => '5.8%', 'meta' => '+0.9% this week'],
            ],
            'products' => [
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
            ],
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

    public function campaigns()
    {
        return view('admin.campaigns.index', [
            'title' => 'Campaigns',
            'subtitle' => 'Design, schedule, and monitor product-focused marketing campaigns.',
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
