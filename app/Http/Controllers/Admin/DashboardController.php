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

    public function orders()
    {
        return $this->page('Orders', 'Manage order flow, approvals, and fulfillment status.');
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
                ['label' => 'Avg Conversion', 'value' => '5.8%', 'meta' => '+0.9% this week'],
            ],
            'products' => [
                [
                    'name' => 'Premium Cotton T-Shirt',
                    'sku' => 'SKU-TS-2109',
                    'category' => 'Apparel',
                    'price' => 'BDT 1,150',
                    'stock' => 84,
                    'stock_label' => 'In Stock',
                    'sales' => 146,
                    'status' => 'Active',
                ],
                [
                    'name' => 'Smart Casual Hoodie',
                    'sku' => 'SKU-HD-1231',
                    'category' => 'Apparel',
                    'price' => 'BDT 1,890',
                    'stock' => 22,
                    'stock_label' => 'Low Stock',
                    'sales' => 67,
                    'status' => 'Active',
                ],
                [
                    'name' => 'Wireless Earbuds Pro',
                    'sku' => 'SKU-EB-4412',
                    'category' => 'Electronics',
                    'price' => 'BDT 3,250',
                    'stock' => 56,
                    'stock_label' => 'In Stock',
                    'sales' => 201,
                    'status' => 'Active',
                ],
                [
                    'name' => 'Leather Office Backpack',
                    'sku' => 'SKU-BP-9920',
                    'category' => 'Accessories',
                    'price' => 'BDT 2,780',
                    'stock' => 11,
                    'stock_label' => 'Critical',
                    'sales' => 39,
                    'status' => 'Draft',
                ],
                [
                    'name' => 'AirFlex Running Shoes',
                    'sku' => 'SKU-SH-3318',
                    'category' => 'Footwear',
                    'price' => 'BDT 2,450',
                    'stock' => 73,
                    'stock_label' => 'In Stock',
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
