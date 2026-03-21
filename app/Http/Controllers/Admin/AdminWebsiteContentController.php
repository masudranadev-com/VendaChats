<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class AdminWebsiteContentController extends Controller
{
    public function slider(Request $request): View
    {
        $content = $this->contentRepository();
        $apiConfig = $this->backendApiConfig($request);

        return view('admin.shop-settings.website-content.slider', array_merge(
            $this->shell('slider', $content),
            [
                'quickStats' => [
                    ['key' => 'live_slides', 'label' => 'Live Slides', 'value' => '--', 'note' => 'Loading live slider count', 'tone' => 'primary'],
                    ['key' => 'draft_slides', 'label' => 'Draft Slides', 'value' => '--', 'note' => 'Loading draft slider count', 'tone' => 'warning'],
                    ['key' => 'linked_products', 'label' => 'Linked Products', 'value' => '--', 'note' => 'Loading linked product count', 'tone' => 'info'],
                    ['key' => 'queue_size', 'label' => 'Queue Size', 'value' => '--', 'note' => 'Loading total slider queue', 'tone' => 'success'],
                ],
                'slidersApiBaseUrl' => $apiConfig['publicApiBaseUrl'],
                'slidersRefreshToken' => $apiConfig['refreshToken'],
                'sliderDefaults' => [
                    'id' => null,
                    'title' => '',
                    'product_id' => 0,
                    'notes' => '',
                    'priority' => 1,
                    'status' => 'Draft',
                ],
                'sliderItems' => [],
                'sliderProducts' => [],
            ]
        ));
    }

    public function pageEditor(Request $request): View
    {
        $content = $this->contentRepository();
        $apiConfig = $this->backendApiConfig($request);
        $legalPages = $this->pageEditorPages($request, $content['legalPages']);
        $content['legalPages'] = $legalPages;

        return view('admin.shop-settings.website-content.page-editor', array_merge(
            $this->shell('page-editor', $content),
            [
                'legalPages' => $legalPages,
                'pagesApiBaseUrl' => $apiConfig['publicApiBaseUrl'],
                'pagesRefreshToken' => $apiConfig['refreshToken'],
            ]
        ));
    }

    public function contact(): View
    {
        $content = $this->contentRepository();

        return view('admin.shop-settings.website-content.contact', array_merge(
            $this->shell('contact', $content),
            [
                'contactInfo' => $content['contactInfo'],
                'socialLinks' => $content['socialLinks'],
            ]
        ));
    }

    public function footer(): View
    {
        $content = $this->contentRepository();

        return view('admin.shop-settings.website-content.footer', array_merge(
            $this->shell('footer', $content),
            [
                'trustSettings' => $content['trustSettings'],
                'seoDefaults' => $content['seoDefaults'],
            ]
        ));
    }

    private function shell(string $activeTab, array $content): array
    {
        $sliderItems = collect($content['sliderItems']);
        $legalPages = collect($content['legalPages']);
        $socialLinks = collect($content['socialLinks']);

        $statsByTab = [
            'slider' => [
                ['label' => 'Live Slides', 'value' => (string) $sliderItems->where('status', 'Live')->count(), 'note' => 'Shown on the homepage now', 'tone' => 'primary'],
                ['label' => 'Draft Slides', 'value' => (string) $sliderItems->where('status', 'Draft')->count(), 'note' => 'Ready for upcoming campaigns', 'tone' => 'warning'],
                ['label' => 'Linked Products', 'value' => (string) $sliderItems->pluck('product_id')->unique()->count(), 'note' => 'Slides tied to real catalog items', 'tone' => 'info'],
                ['label' => 'Queue Size', 'value' => (string) $sliderItems->count(), 'note' => 'Easy to reorder from one table', 'tone' => 'success'],
            ],
            'page-editor' => [
                ['label' => 'Published Pages', 'value' => (string) $legalPages->where('status', 'Published')->count(), 'note' => 'Live policy and brand pages', 'tone' => 'success'],
                ['label' => 'Footer Links', 'value' => (string) $legalPages->where('show_in_footer', true)->count(), 'note' => 'Pages exposed in the storefront footer', 'tone' => 'primary'],
                ['label' => 'Review Cycles', 'value' => (string) $legalPages->pluck('review_cycle')->unique()->count(), 'note' => 'Cadence options already defined', 'tone' => 'info'],
                ['label' => 'Editor Modes', 'value' => '2', 'note' => 'Visual editor and HTML mode', 'tone' => 'warning'],
            ],
            'contact' => [
                ['label' => 'Support Channels', 'value' => '6', 'note' => 'Phone, WhatsApp, email, address, map, notice', 'tone' => 'primary'],
                ['label' => 'Active Social Links', 'value' => (string) $socialLinks->where('status', 'Active')->count(), 'note' => 'Visible to storefront visitors', 'tone' => 'success'],
                ['label' => 'Draft Profiles', 'value' => (string) $socialLinks->where('status', '!=', 'Active')->count(), 'note' => 'Profiles not yet promoted', 'tone' => 'warning'],
                ['label' => 'Contact Cards', 'value' => '1', 'note' => 'All support details grouped together', 'tone' => 'info'],
            ],
            'footer' => [
                ['label' => 'Trust Badges', 'value' => '3', 'note' => 'Return, shipping, and payment highlights', 'tone' => 'primary'],
                ['label' => 'Announcement Bar', 'value' => '1', 'note' => 'Single global store message', 'tone' => 'success'],
                ['label' => 'Script Blocks', 'value' => '2', 'note' => 'Header and footer tracking areas', 'tone' => 'warning'],
                ['label' => 'SEO Defaults', 'value' => '5', 'note' => 'Templates, robots, OG image, and meta text', 'tone' => 'info'],
            ],
        ];

        $sectionMeta = [
            'slider' => [
                'heading' => 'Slider workspace',
                'subtitle' => 'Keep homepage hero slides fast to scan, easy to edit, and separate from page-copy work.',
            ],
            'page-editor' => [
                'heading' => 'Page editor workspace',
                'subtitle' => 'Edit policy and static pages from one editor with quick switching between storefront pages.',
            ],
            'contact' => [
                'heading' => 'Contact workspace',
                'subtitle' => 'Update support channels, address details, and storefront social profiles from one place.',
            ],
            'footer' => [
                'heading' => 'Footer workspace',
                'subtitle' => 'Manage footer trust blocks, announcement text, and default SEO or tracking content together.',
            ],
        ];

        return [
            'title' => 'Website Content',
            'subtitle' => 'Separate the storefront content tools into focused pages so admins can move faster without a crowded screen.',
            'sectionHeading' => $sectionMeta[$activeTab]['heading'],
            'sectionSubtitle' => $sectionMeta[$activeTab]['subtitle'],
            'quickStats' => $statsByTab[$activeTab],
            'contentTabs' => $this->contentTabs(),
            'activeContentTab' => $activeTab,
        ];
    }

    private function contentTabs(): array
    {
        return [
            [
                'key' => 'slider',
                'label' => 'Slider',
                'route' => 'admin.shop-settings.content',
            ],
            [
                'key' => 'page-editor',
                'label' => 'Page Editor',
                'route' => 'admin.shop-settings.content.page-editor',
            ],
            [
                'key' => 'contact',
                'label' => 'Contact',
                'route' => 'admin.shop-settings.content.contact',
            ],
            [
                'key' => 'footer',
                'label' => 'Footer',
                'route' => 'admin.shop-settings.content.footer',
            ],
        ];
    }

    private function contentRepository(): array
    {
        return [
            'sliderProducts' => [
                ['id' => 'prod_101', 'name' => 'Classic Cotton T-Shirt'],
                ['id' => 'prod_102', 'name' => 'Denim Jacket'],
                ['id' => 'prod_201', 'name' => 'Sports Sneakers'],
                ['id' => 'prod_202', 'name' => 'Leather Wallet'],
                ['id' => 'prod_203', 'name' => 'Travel Backpack'],
            ],
            'sliderItems' => [
                [
                    'id' => 'slide_hero_1',
                    'title' => 'New Season Collection',
                    'product_id' => 'prod_101',
                    'product_name' => 'Classic Cotton T-Shirt',
                    'priority' => 1,
                    'status' => 'Live',
                    'updated' => 'Today 10:12 AM',
                ],
                [
                    'id' => 'slide_hero_2',
                    'title' => 'Flash Sale Weekend',
                    'product_id' => 'prod_102',
                    'product_name' => 'Denim Jacket',
                    'priority' => 2,
                    'status' => 'Live',
                    'updated' => 'Today 08:45 AM',
                ],
                [
                    'id' => 'slide_hero_3',
                    'title' => 'Free Shipping Banner',
                    'product_id' => 'prod_201',
                    'product_name' => 'Sports Sneakers',
                    'priority' => 3,
                    'status' => 'Live',
                    'updated' => 'Yesterday 07:30 PM',
                ],
                [
                    'id' => 'slide_hero_4',
                    'title' => 'App Download Promo',
                    'product_id' => 'prod_202',
                    'product_name' => 'Leather Wallet',
                    'priority' => 4,
                    'status' => 'Draft',
                    'updated' => '2 days ago',
                ],
            ],
            'legalPages' => [
                [
                    'key' => 'terms',
                    'title' => 'Terms & Conditions',
                    'slug' => '/terms-and-conditions',
                    'status' => 'Published',
                    'review_cycle' => 'Every 90 days',
                    'last_updated' => '19 Feb 2026',
                    'seo_title' => 'Terms and Conditions | YourBrand',
                    'meta_description' => 'Review store terms, ordering rules, and payment conditions for YourBrand shoppers.',
                    'content' => "Welcome to YourBrand. By placing an order, you agree to our terms of sale, payment verification process, and order handling policy.\n\nOrders can be canceled before shipment. Once shipped, returns must follow the return and refund policy. For any dispute, contact our support team first.",
                    'show_in_footer' => true,
                ],
                [
                    'key' => 'privacy',
                    'title' => 'Privacy Policy',
                    'slug' => '/privacy-policy',
                    'status' => 'Published',
                    'review_cycle' => 'Every 90 days',
                    'last_updated' => '16 Feb 2026',
                    'seo_title' => 'Privacy Policy | YourBrand',
                    'meta_description' => 'How YourBrand collects, processes, and protects customer data across storefront and checkout.',
                    'content' => "We collect personal data required to process orders, fulfill deliveries, and provide support. Data may include name, phone number, email, and shipping address.\n\nWe do not sell customer data. You can request data export or deletion by contacting support from the contact page.",
                    'show_in_footer' => true,
                ],
                [
                    'key' => 'refund',
                    'title' => 'Return & Refund Policy',
                    'slug' => '/return-refund-policy',
                    'status' => 'Published',
                    'review_cycle' => 'Every 30 days',
                    'last_updated' => '11 Feb 2026',
                    'seo_title' => 'Return and Refund Policy | YourBrand',
                    'meta_description' => 'Return window, refund process, and eligibility conditions for YourBrand orders.',
                    'content' => "Items can be returned within 7 days of delivery if unused and in original condition. Refunds are issued after inspection.\n\nShipping charges are non-refundable unless the return is caused by a damaged or incorrect product.",
                    'show_in_footer' => true,
                ],
                [
                    'key' => 'shipping',
                    'title' => 'Shipping Policy',
                    'slug' => '/shipping-policy',
                    'status' => 'Published',
                    'review_cycle' => 'Every 60 days',
                    'last_updated' => '17 Feb 2026',
                    'seo_title' => 'Shipping Policy | YourBrand',
                    'meta_description' => 'Delivery coverage, timelines, and shipping charge policy for domestic orders.',
                    'content' => "Orders placed before 4:00 PM are usually processed on the same day. Delivery timeline depends on region and courier capacity.\n\nCustomers receive tracking updates by SMS and email once shipment is confirmed.",
                    'show_in_footer' => true,
                ],
                [
                    'key' => 'about',
                    'title' => 'About Us',
                    'slug' => '/about',
                    'status' => 'Published',
                    'review_cycle' => 'Every 180 days',
                    'last_updated' => '05 Feb 2026',
                    'seo_title' => 'About YourBrand',
                    'meta_description' => 'Brand mission, product quality commitment, and customer-first shopping promise.',
                    'content' => "YourBrand is focused on practical lifestyle products with transparent pricing and reliable support. We build long-term trust through quality and service.\n\nOur operations team monitors fulfillment quality and response times daily.",
                    'show_in_footer' => true,
                ],
                [
                    'key' => 'contact',
                    'title' => 'Contact Us',
                    'slug' => '/contact',
                    'status' => 'Published',
                    'review_cycle' => 'Every 30 days',
                    'last_updated' => 'Today',
                    'seo_title' => 'Contact YourBrand Support',
                    'meta_description' => 'Contact YourBrand support through phone, WhatsApp, email, and social channels.',
                    'content' => "Need help with an order, return, or payment issue? Reach out using phone, WhatsApp, or email during support hours.\n\nFor urgent issues, mention order number and delivery phone to speed up support response.",
                    'show_in_footer' => true,
                ],
            ],
            'contactInfo' => [
                'support_phone' => '+880 1700-000000',
                'support_whatsapp' => '+880 1700-000001',
                'support_email' => 'support@yourbrand.com',
                'business_email' => 'business@yourbrand.com',
                'store_address' => 'House 17, Road 11, Banani, Dhaka 1213',
                'support_hours' => 'Saturday - Thursday, 9:00 AM - 10:00 PM',
                'map_embed' => 'https://maps.google.com/?q=Banani+Dhaka',
                'contact_page_notice' => 'Average response time: under 15 minutes during support hours.',
            ],
            'socialLinks' => [
                ['platform' => 'Facebook', 'url' => 'https://facebook.com/yourbrand', 'status' => 'Active'],
                ['platform' => 'Instagram', 'url' => 'https://instagram.com/yourbrand', 'status' => 'Active'],
                ['platform' => 'TikTok', 'url' => 'https://tiktok.com/@yourbrand', 'status' => 'Active'],
                ['platform' => 'YouTube', 'url' => 'https://youtube.com/@yourbrand', 'status' => 'Draft'],
            ],
            'trustSettings' => [
                'copyright' => 'Copyright 2026 YourBrand. All rights reserved.',
                'store_tagline' => 'Trusted daily essentials with fast delivery.',
                'return_badge' => '7 Days Easy Return',
                'shipping_badge' => 'Nationwide Delivery',
                'payment_badge' => 'Secure Checkout Guaranteed',
                'announcement_bar' => 'Free shipping on orders above BDT 999.',
            ],
            'seoDefaults' => [
                'title_template' => '{{page_title}} | YourBrand',
                'meta_description' => 'Shop premium lifestyle products with fast shipping and trusted support from YourBrand.',
                'meta_keywords' => 'ecommerce, lifestyle, fashion, accessories, online shopping',
                'robots_meta' => 'index, follow',
                'og_image_url' => 'https://cdn.yourbrand.com/assets/store-og-default.jpg',
                'header_script' => '<script>window.dataLayer = window.dataLayer || [];</script>',
                'footer_script' => '<script src=\"https://cdn.example.com/tracking.js\" defer></script>',
            ],
        ];
    }

    private function backendApiConfig(Request $request): array
    {
        return [
            'publicApiBaseUrl' => rtrim((string) config('services.backend.public_url', 'http://localhost:8082'), '/'),
            'internalApiBaseUrl' => rtrim((string) config('services.backend.internal_url', 'http://localhost:8082'), '/'),
            'refreshToken' => (string) (
                $request->session()->get('auth.refresh_token')
                ?? $request->session()->get('refresh_token')
                ?? ''
            ),
        ];
    }

    private function pageEditorPages(Request $request, array $fallback): array
    {
        $apiConfig = $this->backendApiConfig($request);

        if ($apiConfig['refreshToken'] === '') {
            return $fallback;
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'user-refres-token' => $apiConfig['refreshToken'],
                ])
                ->timeout(12)
                ->get($apiConfig['internalApiBaseUrl'].'/api/admin/shop-settings/content/pages');

            $payload = $response->json();
            if ($response->ok() && is_array($payload) && is_array($payload['pages'] ?? null)) {
                return $payload['pages'];
            }
        } catch (Throwable) {
            return $fallback;
        }

        return $fallback;
    }
}
