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
            'callVoiceFeature' => $this->callVoiceFeature(),
        ]);
    }

    public function productCreate()
    {
        $refreshToken = (string) (
            request()->session()->get('auth.refresh_token')
            ?? request()->session()->get('refresh_token')
            ?? ''
        );
        $configuredProductType = $this->configuredProductType();

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
            'configuredProductType' => $configuredProductType,
            'configuredProductTypeLabel' => $this->productTypeLabel($configuredProductType),
            'isLocal' => app()->environment('local'),
            'enableDevAutofill' => app()->environment('local'),
            'callVoiceFeature' => $this->callVoiceFeature(),
        ]);
    }

    public function productEdit(Request $request, int $productId)
    {
        $refreshToken = (string) (
            $request->session()->get('auth.refresh_token')
            ?? $request->session()->get('refresh_token')
            ?? ''
        );
        $configuredProductType = $this->configuredProductType($request);

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
            'configuredProductType' => $configuredProductType,
            'configuredProductTypeLabel' => $this->productTypeLabel($configuredProductType),
            'isLocal' => app()->environment('local'),
            'enableDevAutofill' => false,
            'callVoiceFeature' => $this->callVoiceFeature(),
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

    private function callVoiceFeature(): array
    {
        $pageName = 'A Metafy';
        $defaultProductTitle = 'Premium Cotton T-Shirt';

        return [
            'enabled' => false,
            'price_bdt' => 500,
            'page_name' => $pageName,
            'default_product_title' => $defaultProductTitle,
            'default_duration' => '00:18',
            'default_script_bn' => "Assalamu alaikum. {$pageName}-e apnake shagotom. Apni amader {$pageName} theke {$defaultProductTitle} order korechen. Apnar order ti confirm korte 1 chapun, cancel korte 2 chapun.",
            'default_script_en' => "Assalamu alaikum. Welcome to {$pageName}. You placed an order for {$defaultProductTitle} from {$pageName}. Press 1 to confirm your order, or press 2 to cancel it.",
            'supported_languages' => ['All languages', 'Auto-detect', 'Bangla + English samples'],
            'voice_wave' => [24, 38, 52, 31, 64, 43, 68, 34, 60, 47, 58, 29],
            'recent_voices' => [
                [
                    'title' => 'Premium Cotton T-Shirt',
                    'duration' => '00:18',
                    'language' => 'Bangla + English',
                    'status' => 'Demo preview ready',
                ],
                [
                    'title' => 'AirFlex Running Shoes',
                    'duration' => '00:17',
                    'language' => 'Bangla + English',
                    'status' => 'Waiting for subscription',
                ],
                [
                    'title' => 'Leather Office Backpack',
                    'duration' => '00:19',
                    'language' => 'Bangla + English',
                    'status' => 'Will auto-regenerate on title edit',
                ],
            ],
        ];
    }

    private function configuredProductType(?Request $request = null): string
    {
        $session = ($request ?? request())->session();
        $value = strtolower(trim((string) data_get($session->get('admin.global_config', []), 'product_type', 'physical')));

        return in_array($value, ['physical', 'downloadable', 'subscription'], true)
            ? $value
            : 'physical';
    }

    private function productTypeLabel(string $type): string
    {
        return match ($type) {
            'downloadable' => 'Downloadable',
            'subscription' => 'Subscription',
            default => 'Physical',
        };
    }
}
