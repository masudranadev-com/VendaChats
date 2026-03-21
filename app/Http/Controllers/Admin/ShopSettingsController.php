<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminLocaleOptions;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class ShopSettingsController extends Controller
{
    public function __construct(private readonly AdminLocaleOptions $localeOptions)
    {
    }

    public function index(Request $request): View
    {
        return view('admin.shop-settings.index', $this->generalData($request));
    }

    public function domain(Request $request): View
    {
        return view('admin.shop-settings.domain', $this->domainData($request));
    }

    public function theme(Request $request): View
    {
        return view('admin.shop-settings.theme', $this->themeData($request));
    }

    public function offers(Request $request): View
    {
        return view('admin.shop-settings.offers', $this->offersData($request));
    }

    public function content(Request $request): View
    {
        return view('admin.shop-settings.content', $this->contentData($request));
    }

    private function shell(Request $request, string $heading, string $sectionSubtitle, array $quickStats): array
    {
        return [
            'title' => 'Shop Settings',
            'subtitle' => 'Separate control centers for domain, theme, offers, and content operations.',
            'sectionHeading' => $heading,
            'sectionSubtitle' => $sectionSubtitle,
            'quickStats' => $quickStats,
            'activityLog' => $this->paginatedActivityLog($request),
        ];
    }

    private function activityLog(): array
    {
        return [
            ['time' => '10m ago', 'event' => 'Updated checkout subdomain DNS configuration.', 'actor' => 'Admin', 'status' => 'Success'],
            ['time' => '1h ago', 'event' => 'Theme color token set switched to Aurora preset.', 'actor' => 'Design Team', 'status' => 'Success'],
            ['time' => '3h ago', 'event' => 'Offer eligibility segment changed for returning customers.', 'actor' => 'Growth Team', 'status' => 'Pending'],
            ['time' => '5h ago', 'event' => 'Homepage hero content pushed to staging.', 'actor' => 'Content Team', 'status' => 'Success'],
            ['time' => 'Yesterday', 'event' => 'SEO metadata checklist completed for policy pages.', 'actor' => 'SEO Team', 'status' => 'Success'],
            ['time' => '2 days ago', 'event' => 'Offer banner audience rule synced to homepage hero.', 'actor' => 'Growth Team', 'status' => 'Success'],
            ['time' => '3 days ago', 'event' => 'Custom domain SSL renewal queued for validation.', 'actor' => 'Infra Team', 'status' => 'Pending'],
            ['time' => '4 days ago', 'event' => 'Theme spacing token rollback completed after QA.', 'actor' => 'Design Team', 'status' => 'Success'],
        ];
    }

    private function paginatedActivityLog(Request $request): LengthAwarePaginator
    {
        $pageName = 'activity_page';
        $perPage = 4;
        $currentPage = max(1, (int) $request->query($pageName, 1));
        $activityCollection = collect($this->activityLog());
        $items = $activityCollection->forPage($currentPage, $perPage)->values();

        return (new LengthAwarePaginator(
            items: $items,
            total: $activityCollection->count(),
            perPage: $perPage,
            currentPage: $currentPage,
            options: [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]
        ))->appends($request->except($pageName));
    }

    private function generalData(Request $request): array
    {
        $config = $this->latestAdminGlobalConfig($request);
        $domainContext = $this->sessionDomainContext($config);
        $localeOptions = $this->localeOptions->load($request);
        $selectedProductType = $this->normalizeProductType((string) ($config['product_type'] ?? 'physical'));
        $timezone = $this->localeOptions->normalizeTimezone(
            (string) ($config['timezone'] ?? 'Asia/Dhaka'),
            $localeOptions['timezones'] ?? []
        );
        $adminLanguage = $this->localeOptions->normalizeLanguage(
            (string) ($config['admin_panel_language'] ?? $config['admin_language'] ?? $config['language'] ?? 'en'),
            $localeOptions['admin_languages'] ?? []
        );
        $websiteLanguage = $this->localeOptions->normalizeLanguage(
            (string) ($config['website_language'] ?? $config['primary_language'] ?? $config['language'] ?? 'en'),
            $localeOptions['website_languages'] ?? []
        );
        $storefrontUsername = trim((string) ($config['subdomain'] ?? 'yourbrand')) ?: 'yourbrand';
        $subdomainBase = trim((string) ($config['subdomain_base'] ?? 'ametafy.shop')) ?: 'ametafy.shop';
        $storefrontUrl = $domainContext['domain'] ?? ($storefrontUsername.'.'.$subdomainBase);

        return array_merge($this->shell(
            request: $request,
            heading: 'First-time setup',
            sectionSubtitle: 'Change product type and locale at any time without reopening the onboarding wizard.',
            quickStats: []
        ), [
            'title' => 'Shop Settings',
            'subtitle' => 'Keep onboarding choices editable after launch so the store can switch product flow or locale later.',
            'productsApiBaseUrl' => rtrim((string) config('services.backend.public_url', 'http://localhost:8082'), '/'),
            'productsRefreshToken' => (string) (
                $request->session()->get('auth.refresh_token')
                ?? $request->session()->get('refresh_token')
                ?? ''
            ),
            'productTypeChoices' => $this->productTypeChoices(),
            'selectedProductType' => $selectedProductType,
            'selectedProductTypeLabel' => $this->productTypeLabel($selectedProductType),
            'localeOptions' => $localeOptions,
            'selectedLocale' => [
                'timezone' => $timezone,
                'admin_language' => $adminLanguage,
                'website_language' => $websiteLanguage,
            ],
            'storefrontUrl' => $storefrontUrl,
            'shopSections' => [
                [
                    'label' => 'Domain',
                    'route' => 'admin.shop-settings.domain',
                    'status' => 'Ready',
                    'badge' => 'badge-success',
                    'note' => 'Connect your subdomain or custom domain and keep DNS status in one place.',
                ],
                [
                    'label' => 'Theme',
                    'route' => 'admin.shop-settings.theme',
                    'status' => 'Depends on Domain',
                    'badge' => 'badge-info',
                    'note' => 'Theme setup unlocks after one domain is connected and verified.',
                ],
                [
                    'label' => 'Offers',
                    'route' => 'admin.shop-settings.offers',
                    'status' => 'Active',
                    'badge' => 'badge-primary',
                    'note' => 'Manage coupon codes, discount logic, and active promotions from one screen.',
                ],
                [
                    'label' => 'Website Content',
                    'route' => 'admin.shop-settings.content',
                    'status' => 'Live',
                    'badge' => 'badge-warning',
                    'note' => 'Edit sliders, policy pages, contact details, and storefront footer content.',
                ],
            ],
        ]);
    }

    private function normalizeProductType(string $value): string
    {
        return match (strtolower(trim($value))) {
            'digital', 'subscription' => 'subscription',
            'downloadable' => 'downloadable',
            default => 'physical',
        };
    }

    private function productTypeLabel(string $value): string
    {
        return match ($this->normalizeProductType($value)) {
            'subscription' => 'Subscription',
            'downloadable' => 'Downloadable',
            default => 'Physical',
        };
    }

    private function productTypeChoices(): array
    {
        return [
            [
                'value' => 'physical',
                'badge' => 'Recommended',
                'label' => 'Physical',
                'description' => 'For shipped products with inventory, courier, and confirmation flow.',
                'note' => 'Best for retail, fashion, gadgets, and cash-on-delivery stores.',
            ],
            [
                'value' => 'subscription',
                'badge' => 'Access based',
                'label' => 'Subscription',
                'description' => 'For memberships, services, licenses, or recurring access-based offers.',
                'note' => 'No shipping flow. Great for shared accounts, coaching, subscriptions, and tools.',
            ],
            [
                'value' => 'downloadable',
                'badge' => 'File delivery',
                'label' => 'Downloadable',
                'description' => 'For assets, templates, ebooks, and files delivered after purchase.',
                'note' => 'Optimized for digital goods with quick post-purchase fulfillment.',
            ],
        ];
    }

    private function domainData(Request $request): array
    {
        $config = $this->latestAdminGlobalConfig($request);
        $apiConfig = $this->backendApiConfig($request);

        return array_merge($this->shell(
            request: $request,
            heading: 'Domain Setup',
            sectionSubtitle: 'Add your A Metafy subdomain quickly, or connect a custom domain if your package supports it.',
            quickStats: []
        ), [
            'domainApiBaseUrl' => $apiConfig['publicApiBaseUrl'],
            'domainRefreshToken' => $apiConfig['refreshToken'],
            'initialSubdomainBase' => trim((string) ($config['subdomain_base'] ?? 'ametafy.shop')) ?: 'ametafy.shop',
        ]);
    }

    private function themeData(Request $request): array
    {
        $config = $this->latestAdminGlobalConfig($request);
        $apiConfig = $this->backendApiConfig($request);
        $connectedDomain = $this->sessionDomainContext($config);
        $connectedDomains = $connectedDomain ? [[
            'domain' => $connectedDomain['domain'],
            'type' => $connectedDomain['type'],
            'status' => $connectedDomain['status'],
        ]] : [];

        $primaryDomain = $connectedDomains[0] ?? null;
        $hasDomain = $primaryDomain !== null;
        $domainIsConnected = $hasDomain && ! str_contains(strtolower((string) $primaryDomain['status']), 'pending');
        $domainContext = null;

        if ($hasDomain) {
            $isCustomDomain = $primaryDomain['type'] === 'Custom Domain';

            $domainContext = [
                'value' => $primaryDomain['domain'],
                'type' => $primaryDomain['type'],
                'status' => $primaryDomain['status'],
                'is_connected' => $domainIsConnected,
                'default_theme_id' => $isCustomDomain ? 'atlas-grid-v3' : 'aurora-commerce-v4',
                'available_theme_ids' => $isCustomDomain
                    ? ['atlas-grid-v3', 'studio-luxe-v2']
                    : ['aurora-commerce-v4', 'minimal-pulse-v5', 'studio-luxe-v2'],
            ];
        }

        return array_merge($this->shell(
            request: $request,
            heading: 'Theme Setup',
            sectionSubtitle: 'If domain already exists, theme opens directly. If no domain exists, theme setup is blocked.',
            quickStats: [
                ['label' => 'Theme Source', 'value' => 'Auto', 'note' => 'System generated only', 'tone' => 'primary'],
                ['label' => 'Active Slot', 'value' => '1 Theme', 'note' => 'Only one active per domain', 'tone' => 'success'],
                ['label' => 'Domain Binding', 'value' => $hasDomain ? 'Linked' : 'Missing', 'note' => $hasDomain ? 'Theme tied to single domain' : 'Create domain first', 'tone' => 'info'],
                ['label' => 'Creation Access', 'value' => 'Locked', 'note' => 'Manual theme creation disabled', 'tone' => 'warning'],
            ]
        ), [
            'themeApiBaseUrl' => $apiConfig['publicApiBaseUrl'],
            'themeRefreshToken' => $apiConfig['refreshToken'],
            'hasDomain' => $hasDomain,
            'domainContext' => $domainContext,
            'themeCatalog' => $this->themeCatalog(),
            'themeControlGroups' => $this->themeControlGroups(),
            'themeFeatureToggles' => $this->themeFeatureToggles(),
        ]);
    }

    private function themeCatalog(): array
    {
        return [
            [
                'id' => 'aurora-commerce-v4',
                'name' => 'Aurora Commerce v4',
                'note' => 'Balanced storytelling sections with conversion-first product cards.',
                'speed' => '92',
                'conversion' => '5.9%',
                'best_for' => 'Lifestyle and fashion',
                'mode' => 'System library',
                'highlights' => ['Conversion-first hero', 'Flexible campaign blocks', 'Fast checkout'],
                'preview_from' => '#1352dc',
                'preview_to' => '#0ea271',
                'is_active' => true,
            ],
            [
                'id' => 'atlas-grid-v3',
                'name' => 'Atlas Grid v3',
                'note' => 'Dense category browsing, fast list pages, and compact checkout flow.',
                'speed' => '89',
                'conversion' => '5.1%',
                'best_for' => 'Large catalogs',
                'mode' => 'Custom-domain ready',
                'highlights' => ['Search-heavy layout', 'Large SKU support', 'Compact merchandising'],
                'preview_from' => '#0f766e',
                'preview_to' => '#111827',
                'is_active' => false,
            ],
            [
                'id' => 'minimal-pulse-v5',
                'name' => 'Minimal Pulse v5',
                'note' => 'Lightweight mobile-first layout with minimal distraction design.',
                'speed' => '96',
                'conversion' => '4.8%',
                'best_for' => 'Fast mobile storefronts',
                'mode' => 'Performance preset',
                'highlights' => ['Smallest visual footprint', 'Thumb-friendly shopping', 'Lean product cards'],
                'preview_from' => '#111827',
                'preview_to' => '#2563eb',
                'is_active' => false,
            ],
            [
                'id' => 'studio-luxe-v2',
                'name' => 'Studio Luxe v2',
                'note' => 'Premium editorial framing for curated collections and high-AOV catalogs.',
                'speed' => '90',
                'conversion' => '5.4%',
                'best_for' => 'Premium and branded drops',
                'mode' => 'Brand-rich preset',
                'highlights' => ['Editorial storytelling', 'Premium product framing', 'High-contrast CTAs'],
                'preview_from' => '#7c2d12',
                'preview_to' => '#f59e0b',
                'is_active' => false,
            ],
        ];
    }

    private function themeControlGroups(): array
    {
        return [
            [
                'title' => 'Brand Foundation',
                'subtitle' => 'Core identity decisions that define pricing, typography, and the overall brand surface.',
                'controls' => [
                    [
                        'label' => 'Store Currency',
                        'name' => 'store_currency',
                        'value' => 'BDT',
                        'options' => [
                            ['value' => 'BDT', 'label' => 'BDT (Bangladesh)'],
                            ['value' => 'INR', 'label' => 'INR (India)'],
                            ['value' => 'USD', 'label' => 'USD (United States)'],
                            ['value' => 'EUR', 'label' => 'EUR (Eurozone)'],
                            ['value' => 'GBP', 'label' => 'GBP (United Kingdom)'],
                        ],
                        'help' => 'Sets default storefront currency formatting for cards, cart totals, and checkout.',
                    ],
                    [
                        'label' => 'Color Preset',
                        'name' => 'color_preset',
                        'value' => 'ocean_blue',
                        'options' => [
                            ['value' => 'ocean_blue', 'label' => 'Ocean Blue'],
                            ['value' => 'emerald_green', 'label' => 'Emerald Green'],
                            ['value' => 'sunset_orange', 'label' => 'Sunset Orange'],
                            ['value' => 'carbon_gray', 'label' => 'Carbon Gray'],
                            ['value' => 'ruby_editorial', 'label' => 'Ruby Editorial'],
                        ],
                        'help' => 'Controls brand color, CTA emphasis, and merchandising accents.',
                    ],
                    [
                        'label' => 'Typography Pack',
                        'name' => 'typography_pack',
                        'value' => 'modern_sans',
                        'options' => [
                            ['value' => 'modern_sans', 'label' => 'Modern Sans'],
                            ['value' => 'elegant_serif', 'label' => 'Elegant Serif'],
                            ['value' => 'bold_contrast', 'label' => 'Bold Contrast'],
                            ['value' => 'humanist_grotesk', 'label' => 'Humanist Grotesk'],
                        ],
                        'help' => 'Applies the heading and body font system across the storefront.',
                    ],
                    [
                        'label' => 'CTA Style',
                        'name' => 'cta_style',
                        'value' => 'bold_fill',
                        'options' => [
                            ['value' => 'bold_fill', 'label' => 'Bold Fill'],
                            ['value' => 'soft_shadow', 'label' => 'Soft Shadow'],
                            ['value' => 'outline_minimal', 'label' => 'Outline Minimal'],
                        ],
                        'help' => 'Sets how add-to-cart, checkout, and key landing buttons appear.',
                    ],
                    [
                        'label' => 'Product Card Style',
                        'name' => 'product_card_style',
                        'value' => 'editorial',
                        'options' => [
                            ['value' => 'editorial', 'label' => 'Editorial'],
                            ['value' => 'compact', 'label' => 'Compact'],
                            ['value' => 'showcase', 'label' => 'Showcase'],
                        ],
                        'help' => 'Adjusts information density and hierarchy on catalog cards.',
                    ],
                ],
            ],
            [
                'title' => 'Layout and Navigation',
                'subtitle' => 'Tune browsing rhythm, image treatment, hero presentation, and mobile navigation.',
                'controls' => [
                    [
                        'label' => 'Section Spacing',
                        'name' => 'section_spacing',
                        'value' => 'comfortable',
                        'options' => [
                            ['value' => 'compact', 'label' => 'Compact'],
                            ['value' => 'comfortable', 'label' => 'Comfortable'],
                            ['value' => 'airy', 'label' => 'Airy'],
                        ],
                        'help' => 'Controls vertical rhythm between major sections on the homepage and catalog.',
                    ],
                    [
                        'label' => 'Corner Style',
                        'name' => 'corner_style',
                        'value' => 'soft',
                        'options' => [
                            ['value' => 'sharp', 'label' => 'Sharp'],
                            ['value' => 'soft', 'label' => 'Soft'],
                            ['value' => 'rounded', 'label' => 'Rounded'],
                        ],
                        'help' => 'Applies to cards, buttons, banners, and image placeholders.',
                    ],
                    [
                        'label' => 'Product Grid Density',
                        'name' => 'grid_density',
                        'value' => 'balanced',
                        'options' => [
                            ['value' => 'compact', 'label' => 'Compact'],
                            ['value' => 'balanced', 'label' => 'Balanced'],
                            ['value' => 'spacious', 'label' => 'Spacious'],
                        ],
                        'help' => 'Changes card count and information density for listing pages.',
                    ],
                    [
                        'label' => 'Image Ratio Mode',
                        'name' => 'image_ratio_mode',
                        'value' => 'portrait_4_5',
                        'options' => [
                            ['value' => 'square_1_1', 'label' => '1:1 Square'],
                            ['value' => 'portrait_4_5', 'label' => '4:5 Portrait'],
                            ['value' => 'landscape_16_9', 'label' => '16:9 Landscape'],
                        ],
                        'help' => 'Defines the default ratio for product thumbnails and featured blocks.',
                    ],
                    [
                        'label' => 'Hero Layout',
                        'name' => 'hero_layout',
                        'value' => 'split_spotlight',
                        'options' => [
                            ['value' => 'split_spotlight', 'label' => 'Split Spotlight'],
                            ['value' => 'editorial_stack', 'label' => 'Editorial Stack'],
                            ['value' => 'immersive_banner', 'label' => 'Immersive Banner'],
                        ],
                        'help' => 'Sets how the hero section balances copy, product imagery, and campaigns.',
                    ],
                    [
                        'label' => 'Header Style',
                        'name' => 'header_style',
                        'value' => 'glass',
                        'options' => [
                            ['value' => 'glass', 'label' => 'Glass'],
                            ['value' => 'solid_brand', 'label' => 'Solid Brand'],
                            ['value' => 'minimal_border', 'label' => 'Minimal Border'],
                        ],
                        'help' => 'Controls the visual treatment of the top navigation container.',
                    ],
                    [
                        'label' => 'Navigation Behavior',
                        'name' => 'navigation_behavior',
                        'value' => 'sticky',
                        'options' => [
                            ['value' => 'sticky', 'label' => 'Sticky'],
                            ['value' => 'static', 'label' => 'Static'],
                            ['value' => 'smart_hide', 'label' => 'Smart Hide'],
                        ],
                        'help' => 'Defines how the header behaves while users scroll the storefront.',
                    ],
                    [
                        'label' => 'Content Width',
                        'name' => 'content_width',
                        'value' => 'contained',
                        'options' => [
                            ['value' => 'contained', 'label' => 'Contained'],
                            ['value' => 'wide', 'label' => 'Wide'],
                            ['value' => 'full_bleed', 'label' => 'Full Bleed'],
                        ],
                        'help' => 'Changes max width and breathing room across catalog and landing sections.',
                    ],
                    [
                        'label' => 'Mobile Navigation',
                        'name' => 'mobile_nav_style',
                        'value' => 'bottom_bar',
                        'options' => [
                            ['value' => 'bottom_bar', 'label' => 'Bottom Bar'],
                            ['value' => 'drawer_menu', 'label' => 'Drawer Menu'],
                            ['value' => 'hybrid_tabs', 'label' => 'Hybrid Tabs'],
                        ],
                        'help' => 'Optimizes how category, search, and cart access work on smaller screens.',
                    ],
                ],
            ],
            [
                'title' => 'Conversion Rhythm',
                'subtitle' => 'Finalize checkout flow and motion to match product type, catalog size, and buyer urgency.',
                'controls' => [
                    [
                        'label' => 'Checkout Style',
                        'name' => 'checkout_style',
                        'value' => 'single_page',
                        'options' => [
                            ['value' => 'single_page', 'label' => 'Single Page'],
                            ['value' => 'step_by_step', 'label' => 'Step by Step'],
                            ['value' => 'express_focus', 'label' => 'Express Focus'],
                        ],
                        'help' => 'Controls how much friction or guidance the buyer sees before completing checkout.',
                    ],
                    [
                        'label' => 'Animation Style',
                        'name' => 'animation_style',
                        'value' => 'subtle_reveal',
                        'options' => [
                            ['value' => 'subtle_reveal', 'label' => 'Subtle Reveal'],
                            ['value' => 'snappy_hover', 'label' => 'Snappy Hover'],
                            ['value' => 'minimal_motion', 'label' => 'Minimal Motion'],
                        ],
                        'help' => 'Sets how aggressively the storefront uses hover, reveal, and transition motion.',
                    ],
                ],
            ],
        ];
    }

    private function themeFeatureToggles(): array
    {
        return [
            [
                'name' => 'announcement_bar_enabled',
                'label' => 'Announcement bar',
                'enabled' => true,
                'help' => 'Show a high-visibility strip for shipping, campaign, or urgency messages.',
            ],
            [
                'name' => 'sticky_add_to_cart_enabled',
                'label' => 'Sticky add to cart',
                'enabled' => true,
                'help' => 'Keep a compact buy action visible on product details while users scroll.',
            ],
            [
                'name' => 'quick_view_enabled',
                'label' => 'Quick view',
                'enabled' => true,
                'help' => 'Allow buyers to inspect a product without leaving the catalog grid.',
            ],
            [
                'name' => 'wishlist_enabled',
                'label' => 'Wishlist',
                'enabled' => false,
                'help' => 'Add lightweight save-for-later support for catalog discovery journeys.',
            ],
            [
                'name' => 'show_stock_badges',
                'label' => 'Stock badges',
                'enabled' => true,
                'help' => 'Surface low-stock and in-stock states directly on product cards.',
            ],
            [
                'name' => 'show_sale_badges',
                'label' => 'Sale badges',
                'enabled' => true,
                'help' => 'Highlight discounted or campaign-linked items with urgency markers.',
            ],
            [
                'name' => 'show_trust_badges',
                'label' => 'Trust badges',
                'enabled' => true,
                'help' => 'Show delivery, payment, and guarantee reassurance blocks near key CTAs.',
            ],
            [
                'name' => 'show_breadcrumbs',
                'label' => 'Breadcrumbs',
                'enabled' => true,
                'help' => 'Improve navigation clarity for large catalogs and multi-level collections.',
            ],
            [
                'name' => 'enable_product_hover',
                'label' => 'Product hover effects',
                'enabled' => true,
                'help' => 'Use hover zoom, alternate imagery, or layered metadata on desktop cards.',
            ],
            [
                'name' => 'enable_scroll_reveal',
                'label' => 'Scroll reveal',
                'enabled' => true,
                'help' => 'Apply staged section reveals to create more deliberate page progression.',
            ],
        ];
    }

    private function offersData(Request $request): array
    {
        $apiConfig = $this->backendApiConfig($request);

        return array_merge($this->shell(
            request: $request,
            heading: 'Coupon Code Manager',
            sectionSubtitle: 'Create simple coupon codes with flat or percentage discount and manage them from one place.',
            quickStats: [
                ['key' => 'active_coupons', 'label' => 'Active Coupons', 'value' => '--', 'note' => 'Loading current coupon count', 'tone' => 'success'],
                ['key' => 'total_redemptions', 'label' => 'Total Redemptions', 'value' => '--', 'note' => 'Loading coupon usage summary', 'tone' => 'primary'],
                ['key' => 'average_discount_label', 'label' => 'Avg Discount', 'value' => '--', 'note' => 'Loading active coupon average', 'tone' => 'info'],
                ['key' => 'expiring_soon', 'label' => 'Expiring Soon', 'value' => '--', 'note' => 'Loading upcoming expiries', 'tone' => 'warning'],
            ]
        ), [
            'offersApiBaseUrl' => $apiConfig['publicApiBaseUrl'],
            'offersRefreshToken' => $apiConfig['refreshToken'],
            'couponDefaults' => $this->defaultOfferCouponDefaults(),
            'coupons' => [],
            'productGroups' => [],
        ]);
    }

    private function defaultOfferCouponDefaults(): array
    {
        $startAt = now()->startOfMinute();
        $endAt = (clone $startAt)->addDays(14);

        return [
            'id' => null,
            'code' => 'WELCOME10',
            'discount_type' => 'percentage',
            'flat_value' => 120,
            'percentage_value' => 10,
            'minimum_order' => 500,
            'max_discount' => 250,
            'usage_count' => 0,
            'usage_limit' => 200,
            'per_user_limit' => 1,
            'start_at' => $startAt->format('Y-m-d\TH:i'),
            'end_at' => $endAt->format('Y-m-d\TH:i'),
            'applies_to' => 'all_products',
            'specific_product_ids' => [],
            'status' => 'Active',
        ];
    }

    private function contentData(Request $request): array
    {
        return array_merge($this->shell(
            request: $request,
            heading: 'Website Content Control',
            sectionSubtitle: 'Manage homepage sliders, policy pages, contact details, and important storefront content from one place.',
            quickStats: [
                ['label' => 'Active Slider Items', 'value' => '3', 'note' => 'All active slides are currently live', 'tone' => 'primary'],
                ['label' => 'Legal Pages Live', 'value' => '6/6', 'note' => 'All legal pages are published', 'tone' => 'success'],
                ['label' => 'Support Channels', 'value' => '6', 'note' => 'Phone, email, chat, social, and map', 'tone' => 'info'],
                ['label' => 'Pending Content Tasks', 'value' => '4', 'note' => 'Policy, hero, and footer updates', 'tone' => 'warning'],
            ]
        ), [
            'sliderProducts' => [
                ['id' => 'prod_101', 'name' => 'Classic Cotton T-Shirt'],
                ['id' => 'prod_102', 'name' => 'Denim Jacket'],
                ['id' => 'prod_201', 'name' => 'Sports Sneakers'],
                ['id' => 'prod_202', 'name' => 'Leather Wallet'],
                ['id' => 'prod_203', 'name' => 'Travel Backpack'],
            ],
            'sliderDefaults' => [
                'title' => '',
                'product_id' => '',
                'priority' => 1,
                'status' => 'Draft',
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
                    'slug' => '/about-us',
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
                    'slug' => '/contact-us',
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
        ]);
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

    private function latestAdminGlobalConfig(Request $request): array
    {
        $config = (array) $request->session()->get('admin.global_config', []);
        $apiConfig = $this->backendApiConfig($request);

        if ($apiConfig['refreshToken'] === '') {
            return $config;
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'user-refres-token' => $apiConfig['refreshToken'],
                ])
                ->timeout(12)
                ->get($apiConfig['internalApiBaseUrl'].'/api/admin/user/global/config/info');

            if ($response->ok() && is_array($response->json())) {
                $config = $response->json();
                $request->session()->put('admin.global_config', $config);
            }
        } catch (Throwable) {
            return $config;
        }

        return $config;
    }

    private function sessionDomainContext(array $config): ?array
    {
        $subdomainBase = trim((string) ($config['subdomain_base'] ?? 'ametafy.shop')) ?: 'ametafy.shop';
        $connectedDomain = trim((string) ($config['connected_domain'] ?? ''));
        $domainType = trim((string) ($config['domain_type'] ?? ''));
        $domainStatus = trim((string) ($config['domain_status'] ?? ''));
        $subdomain = trim((string) ($config['subdomain'] ?? $config['username'] ?? ''));

        if ($connectedDomain === '' && $subdomain !== '') {
            $connectedDomain = $subdomain.'.'.$subdomainBase;
            $domainType = $domainType !== '' ? $domainType : 'sub_domain';
            $domainStatus = $domainStatus !== '' ? $domainStatus : 'Connected';
        }

        if ($connectedDomain === '') {
            return null;
        }

        $typeKey = $domainType === 'domain' ? 'domain' : 'sub_domain';

        return [
            'domain' => $connectedDomain,
            'type' => $typeKey === 'domain' ? 'Custom Domain' : 'Subdomain',
            'type_key' => $typeKey,
            'status' => $domainStatus !== '' ? $domainStatus : 'Connected',
            'subdomain_base' => $subdomainBase,
        ];
    }
}
