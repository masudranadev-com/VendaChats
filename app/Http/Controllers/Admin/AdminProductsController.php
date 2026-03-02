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

    public function categories(Request $request)
    {
        $categoriesRefreshToken = (string) (
            $request->session()->get('auth.refresh_token')
            ?? $request->session()->get('refresh_token')
            ?? ''
        );

        return view('admin.categories.index', [
            'title' => 'Categories',
            'subtitle' => 'Create and organize product categories in one place.',
            'categoriesApiBaseUrl' => rtrim((string) config('services.backend.url', 'http://localhost:8082'), '/'),
            'categoriesRefreshToken' => $categoriesRefreshToken,
        ]);
    }
}
