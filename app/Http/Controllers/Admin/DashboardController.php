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
        return view('admin.orders.index', [
            'title' => 'Orders',
            'subtitle' => 'Control payment checks, dispatch priorities, and delivery health from one clean operational view.',
            'metrics' => [
                ['label' => 'Orders Today', 'value' => '142', 'meta' => '+19 vs yesterday'],
                ['label' => 'Gross Revenue', 'value' => 'BDT 218K', 'meta' => '+11.6% this week'],
                ['label' => 'Pending Dispatch', 'value' => '27', 'meta' => '9 need action in 2h'],
                ['label' => 'COD Exposure', 'value' => 'BDT 74K', 'meta' => '34% of active orders'],
            ],
            'pipeline' => [
                ['name' => 'Payment Verification', 'count' => 12, 'tone' => 'warning'],
                ['name' => 'Ready to Pack', 'count' => 18, 'tone' => 'primary'],
                ['name' => 'In Transit', 'count' => 43, 'tone' => 'success'],
                ['name' => 'Delivery Exceptions', 'count' => 6, 'tone' => 'danger'],
            ],
            'orders' => [
                [
                    'id' => 'ORD-90341',
                    'placed_at' => 'Today, 09:14 AM',
                    'customer' => 'Ayesha Rahman',
                    'location' => 'Dhanmondi, Dhaka',
                    'items' => 3,
                    'amount' => 'BDT 4,650',
                    'payment' => 'COD',
                    'channel' => 'Messenger',
                    'status' => 'Payment Review',
                    'progress' => 28,
                ],
                [
                    'id' => 'ORD-90339',
                    'placed_at' => 'Today, 08:42 AM',
                    'customer' => 'Mahmud Hasan',
                    'location' => 'Uttara, Dhaka',
                    'items' => 1,
                    'amount' => 'BDT 1,290',
                    'payment' => 'Paid',
                    'channel' => 'Website',
                    'status' => 'Ready to Dispatch',
                    'progress' => 64,
                ],
                [
                    'id' => 'ORD-90332',
                    'placed_at' => 'Today, 07:55 AM',
                    'customer' => 'Nusrat Jahan',
                    'location' => 'Chawkbazar, Chattogram',
                    'items' => 2,
                    'amount' => 'BDT 2,980',
                    'payment' => 'Paid',
                    'channel' => 'WhatsApp',
                    'status' => 'In Transit',
                    'progress' => 82,
                ],
                [
                    'id' => 'ORD-90318',
                    'placed_at' => 'Today, 06:28 AM',
                    'customer' => 'Riad Karim',
                    'location' => 'Rajshahi Sadar',
                    'items' => 4,
                    'amount' => 'BDT 6,120',
                    'payment' => 'COD',
                    'channel' => 'Facebook Post',
                    'status' => 'Delayed',
                    'progress' => 51,
                ],
                [
                    'id' => 'ORD-90303',
                    'placed_at' => 'Yesterday, 11:11 PM',
                    'customer' => 'Sumi Akter',
                    'location' => 'Sylhet Sadar',
                    'items' => 1,
                    'amount' => 'BDT 980',
                    'payment' => 'Paid',
                    'channel' => 'Instagram',
                    'status' => 'Delivered',
                    'progress' => 100,
                ],
            ],
            'watchlist' => [
                ['title' => '6 orders in exception queue need courier reassignment.', 'note' => 'Focus on Rajshahi and Sylhet lanes before 3:00 PM.'],
                ['title' => '4 COD orders above BDT 5,000 require call confirmation.', 'note' => 'Mark as verified before assigning riders.'],
                ['title' => 'Messenger-origin orders convert slower after 10 PM.', 'note' => 'Use instant payment link in handoff replies.'],
            ],
            'courierHealth' => [
                ['name' => 'Pathao', 'on_time' => 92],
                ['name' => 'SteadFast', 'on_time' => 84],
                ['name' => 'RedX', 'on_time' => 78],
                ['name' => 'Sundarban', 'on_time' => 69],
            ],
        ]);
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
