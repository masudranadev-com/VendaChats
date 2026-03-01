<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminProductsController extends Controller
{
    public function products(Request $request)
    {
        $refreshToken = (string) (
            $request->session()->get('auth.refresh_token')
            ?? $request->session()->get('refresh_token')
            ?? ''
        );

        return view('admin.products.index', [
            'title' => 'Products',
            'subtitle' => 'Manage catalog, pricing, stock risk, and product performance in one clear workspace.',
            
            'productsApiBaseUrl' => rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/'),
            'productsRefreshToken' => $refreshToken,
        ]);
    }

    public function productCreate()
    {
        $refreshToken = (string) (
            request()->session()->get('auth.refresh_token')
            ?? request()->session()->get('refresh_token')
            ?? ''
        );

        return view('admin.products.create', [
            'title' => 'Add Product',
            'subtitle' => 'Set core product details, pricing, stock, media, and channel visibility before publishing.',
            'categories' => ['Apparel', 'Electronics', 'Footwear', 'Accessories'],
            'channels' => ['Website', 'Facebook', 'Messenger', 'WhatsApp', 'Instagram'],
            'shippingProfiles' => ['Standard', 'Express', 'Fragile Item', 'Outside Dhaka'],
            'backendApiBaseUrl' => rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/'),
            'refreshToken' => $refreshToken,
            'formMode' => 'create',
            'productId' => null,
            'isLocal' => app()->environment('local'),
            'enableDevAutofill' => app()->environment('local'),
        ]);
    }

    public function productEdit(Request $request, int $productId)
    {
        $refreshToken = (string) (
            $request->session()->get('auth.refresh_token')
            ?? $request->session()->get('refresh_token')
            ?? ''
        );

        return view('admin.products.create', [
            'title' => 'Edit Product',
            'subtitle' => 'Review and update product information, pricing, inventory, and publishing settings.',
            'categories' => ['Apparel', 'Electronics', 'Footwear', 'Accessories'],
            'channels' => ['Website', 'Facebook', 'Messenger', 'WhatsApp', 'Instagram'],
            'shippingProfiles' => ['Standard', 'Express', 'Fragile Item', 'Outside Dhaka'],
            'backendApiBaseUrl' => rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/'),
            'refreshToken' => $refreshToken,
            'formMode' => 'edit',
            'productId' => $productId,
            'isLocal' => app()->environment('local'),
            'enableDevAutofill' => false,
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
}
