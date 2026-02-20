<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShopSettingsController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.shop-settings.domain');
    }

    public function domain(): View
    {
        return $this->renderSection('domain');
    }

    public function theme(): View
    {
        return $this->renderSection('theme');
    }

    public function category(): View
    {
        return $this->renderSection('category');
    }

    public function offers(): View
    {
        return $this->renderSection('offers');
    }

    public function content(): View
    {
        return $this->renderSection('content');
    }

    private function renderSection(string $activeSection): View
    {
        $sectionMeta = match ($activeSection) {
            'domain' => [
                'heading' => 'Domain Settings',
                'subtitle' => 'Configure domain mapping, SSL, DNS propagation, and redirect controls.',
            ],
            'theme' => [
                'heading' => 'Theme Settings',
                'subtitle' => 'Choose storefront theme and optimize for conversion and speed.',
            ],
            'category' => [
                'heading' => 'Category Settings',
                'subtitle' => 'Balance product categories and keep catalog discovery clear.',
            ],
            'offers' => [
                'heading' => 'Offer Settings',
                'subtitle' => 'Control discount logic, targeting audience, and trigger rules.',
            ],
            default => [
                'heading' => 'Website Content Settings',
                'subtitle' => 'Manage homepage banners, CMS pages, FAQs, and blog content.',
            ],
        };

        return view('admin.shop-settings.index', array_merge($this->baseData(), [
            'activeSection' => $activeSection,
            'sectionHeading' => $sectionMeta['heading'],
            'sectionSubtitle' => $sectionMeta['subtitle'],
        ]));
    }

    private function baseData(): array
    {
        return [
            'title' => 'Shop Settings',
            'subtitle' => 'Control domain, theme, category, offers, and website content data from one place.',
            'headerTabs' => [
                ['key' => 'domain', 'label' => 'Domain', 'route' => 'admin.shop-settings.domain'],
                ['key' => 'theme', 'label' => 'Theme', 'route' => 'admin.shop-settings.theme'],
                ['key' => 'category', 'label' => 'Category', 'route' => 'admin.shop-settings.category'],
                ['key' => 'offers', 'label' => 'Offers', 'route' => 'admin.shop-settings.offers'],
                ['key' => 'content', 'label' => 'Website Content Data', 'route' => 'admin.shop-settings.content'],
            ],
            'quickStats' => [
                ['label' => 'Configuration Score', 'value' => '92%', 'note' => '2 items need review', 'tone' => 'primary'],
                ['label' => 'Domain Health', 'value' => '2/3', 'note' => '1 SSL pending', 'tone' => 'warning'],
                ['label' => 'Active Offers', 'value' => '3', 'note' => '1 starts tomorrow', 'tone' => 'success'],
                ['label' => 'Content Freshness', 'value' => '87%', 'note' => 'Policy page outdated', 'tone' => 'info'],
            ],
            'domains' => [
                ['label' => 'Primary Domain', 'value' => 'shop.example.com', 'status' => 'Connected', 'ssl' => 'Valid', 'dns' => 'Healthy', 'traffic' => '74%'],
                ['label' => 'Staging Domain', 'value' => 'staging.shop.example.com', 'status' => 'Connected', 'ssl' => 'Valid', 'dns' => 'Healthy', 'traffic' => '18%'],
                ['label' => 'Custom Checkout URL', 'value' => 'checkout.shop.example.com', 'status' => 'Pending SSL', 'ssl' => 'Pending', 'dns' => 'Propagating', 'traffic' => '8%'],
            ],
            'themes' => [
                ['name' => 'Aurora Commerce', 'status' => 'Active', 'note' => 'Optimized for product storytelling and conversion.', 'conversion' => '5.9%', 'speed' => '92'],
                ['name' => 'Atlas Grid', 'status' => 'Draft', 'note' => 'Clean catalog layout with category-first browsing.', 'conversion' => '5.1%', 'speed' => '89'],
                ['name' => 'Minimal Pulse', 'status' => 'Available', 'note' => 'Fast and light for mobile-first stores.', 'conversion' => '4.8%', 'speed' => '96'],
            ],
            'categories' => [
                ['name' => 'Apparel', 'products' => 84, 'share' => 46],
                ['name' => 'Electronics', 'products' => 63, 'share' => 28],
                ['name' => 'Footwear', 'products' => 41, 'share' => 16],
                ['name' => 'Accessories', 'products' => 52, 'share' => 10],
            ],
            'offers' => [
                ['name' => 'New Visitor 10% Off', 'type' => 'Coupon', 'audience' => 'First-time visitors', 'trigger' => 'First session', 'status' => 'Live', 'expires' => 'In 12 days'],
                ['name' => 'Buy 2 Get 1 Free', 'type' => 'Bundle', 'audience' => 'High-intent buyers', 'trigger' => '3+ items in cart', 'status' => 'Scheduled', 'expires' => 'Starts tomorrow'],
                ['name' => 'Free Delivery Friday', 'type' => 'Shipping', 'audience' => 'All customers', 'trigger' => 'Friday orders', 'status' => 'Live', 'expires' => 'Every Friday'],
            ],
            'contentData' => [
                ['title' => 'Homepage banners', 'status' => 'Healthy', 'note' => '3 active hero banners for current campaign.', 'meta' => 'Updated 3h ago'],
                ['title' => 'CMS pages (About, Privacy Policy, Terms)', 'status' => 'Review Needed', 'note' => 'Policy and terms should be revalidated this month.', 'meta' => 'Last review 24 days ago'],
                ['title' => 'FAQs', 'status' => 'Healthy', 'note' => '18 published answers aligned with latest support logs.', 'meta' => 'Updated yesterday'],
                ['title' => 'Blog posts', 'status' => 'Draft Pending', 'note' => '2 scheduled posts waiting for product image assets.', 'meta' => 'Next publish Monday'],
            ],
            'activityLog' => [
                ['time' => '10m ago', 'event' => 'Updated checkout subdomain DNS configuration.', 'actor' => 'Admin', 'status' => 'Success'],
                ['time' => '1h ago', 'event' => 'Theme color palette switched to Aurora preset.', 'actor' => 'Design Team', 'status' => 'Success'],
                ['time' => '4h ago', 'event' => 'CMS Terms page submitted for legal review.', 'actor' => 'Content Team', 'status' => 'Pending'],
                ['time' => 'Yesterday', 'event' => 'Offer rule validation for Friday delivery campaign.', 'actor' => 'Marketing', 'status' => 'Success'],
            ],
        ];
    }
}
