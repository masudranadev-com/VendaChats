<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShopSettingsController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.shop-settings.domain');
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

    private function shell(Request $request, string $activeTab, string $heading, string $sectionSubtitle, array $quickStats): array
    {
        return [
            'title' => 'Shop Settings',
            'subtitle' => 'Separate control centers for domain, theme, offers, and content operations.',
            'activeTab' => $activeTab,
            'sectionHeading' => $heading,
            'sectionSubtitle' => $sectionSubtitle,
            'shopTabs' => $this->tabs(),
            'quickStats' => $quickStats,
            'activityLog' => $this->paginatedActivityLog($request),
        ];
    }

    private function tabs(): array
    {
        return [
            ['key' => 'domain', 'label' => 'Domain', 'route' => 'admin.shop-settings.domain'],
            ['key' => 'theme', 'label' => 'Theme', 'route' => 'admin.shop-settings.theme'],
            ['key' => 'offers', 'label' => 'Offers', 'route' => 'admin.shop-settings.offers'],
            ['key' => 'content', 'label' => 'Website Content', 'route' => 'admin.shop-settings.content'],
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

    private function domainData(Request $request): array
    {
        $connectedDomains = [
            [
                'domain' => 'yourbrand.ametafy.shop',
                'type' => 'Subdomain',
                'dns_required' => 'No',
                'status' => 'Connected',
                'ssl' => 'Auto SSL Active',
            ],
        ];

        $canAddDomain = count($connectedDomains) === 0;

        return array_merge($this->shell(
            request: $request,
            activeTab: 'domain',
            heading: 'Domain Setup',
            sectionSubtitle: 'Add your A Metafy subdomain quickly, or connect a custom domain if your package supports it.',
            quickStats: [
                ['label' => 'Current Package', 'value' => 'Starter', 'note' => 'Subdomain only', 'tone' => 'primary'],
                ['label' => 'Connected Domains', 'value' => '1', 'note' => 'Single-domain mode enabled', 'tone' => 'success'],
                ['label' => 'Custom Domain Access', 'value' => 'Locked', 'note' => 'Upgrade to Pro or higher', 'tone' => 'warning'],
                ['label' => 'DNS Tasks', 'value' => '0', 'note' => 'No DNS pending on active domain', 'tone' => 'info'],
            ]
        ), [
            'plan' => [
                'name' => 'Starter',
                'custom_domain_allowed' => false,
                'subdomain_limit' => 1,
                'custom_domain_limit' => 1,
                'single_domain_mode' => true,
                'upgrade_plan' => 'Pro',
                'subdomain_base' => 'ametafy.shop',
            ],
            'canAddDomain' => $canAddDomain,
            'accessRules' => [
                [
                    'name' => 'A Metafy Subdomain',
                    'availability' => 'Available on all plans',
                    'dns_rule' => 'No DNS setup needed',
                    'status' => 'Open',
                ],
                [
                    'name' => 'Custom Domain',
                    'availability' => 'Pro and higher only',
                    'dns_rule' => 'DNS setup required',
                    'status' => 'Locked on Starter',
                ],
            ],
            'connectedDomains' => $connectedDomains,
            'dnsRecords' => [
                ['host' => '@', 'type' => 'A', 'value' => '103.22.18.44', 'ttl' => '300s', 'notes' => 'Point root domain to server IP'],
                ['host' => 'www', 'type' => 'CNAME', 'value' => 'yourbrand.com', 'ttl' => '300s', 'notes' => 'Alias www to root domain'],
                ['host' => '_verify', 'type' => 'TXT', 'value' => 'ametafy-domain-verify=ab12cd34', 'ttl' => '3600s', 'notes' => 'Required for ownership verification'],
            ],
            'checklist' => [
                'Step 1: Choose domain type (Subdomain or Custom Domain).',
                'Step 2: Add domain name and submit.',
                'Step 3: If custom domain, add DNS records exactly as shown.',
                'Step 4: Click verify after DNS propagation completes.',
                'Step 5: To use another domain, remove current domain first.',
            ],
        ]);
    }

    private function themeData(Request $request): array
    {
        $connectedDomains = [
            [
                'domain' => 'yourbrand.ametafy.shop',
                'type' => 'Subdomain',
                'status' => 'Connected',
            ],
        ];

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
                    ? ['atlas-grid-v3']
                    : ['aurora-commerce-v4', 'minimal-pulse-v5'],
            ];
        }

        return array_merge($this->shell(
            request: $request,
            activeTab: 'theme',
            heading: 'Theme Setup',
            sectionSubtitle: 'If domain already exists, theme opens directly. If no domain exists, theme setup is blocked.',
            quickStats: [
                ['label' => 'Theme Source', 'value' => 'Auto', 'note' => 'System generated only', 'tone' => 'primary'],
                ['label' => 'Active Slot', 'value' => '1 Theme', 'note' => 'Only one active per domain', 'tone' => 'success'],
                ['label' => 'Domain Binding', 'value' => $hasDomain ? 'Linked' : 'Missing', 'note' => $hasDomain ? 'Theme tied to single domain' : 'Create domain first', 'tone' => 'info'],
                ['label' => 'Creation Access', 'value' => 'Locked', 'note' => 'Manual theme creation disabled', 'tone' => 'warning'],
            ]
        ), [
            'hasDomain' => $hasDomain,
            'domainContext' => $domainContext,
            'themeCatalog' => [
                [
                    'id' => 'aurora-commerce-v4',
                    'name' => 'Aurora Commerce v4',
                    'note' => 'Balanced storytelling sections with conversion-first product cards.',
                    'speed' => '92',
                    'conversion' => '5.9%',
                    'best_for' => 'Lifestyle and fashion',
                    'generated_at' => 'Today 08:10 AM',
                    'is_active' => true,
                ],
                [
                    'id' => 'atlas-grid-v3',
                    'name' => 'Atlas Grid v3',
                    'note' => 'Dense category browsing, fast list pages, and compact checkout flow.',
                    'speed' => '89',
                    'conversion' => '5.1%',
                    'best_for' => 'Large catalogs',
                    'generated_at' => 'Today 08:10 AM',
                    'is_active' => false,
                ],
                [
                    'id' => 'minimal-pulse-v5',
                    'name' => 'Minimal Pulse v5',
                    'note' => 'Lightweight mobile-first layout with minimal distraction design.',
                    'speed' => '96',
                    'conversion' => '4.8%',
                    'best_for' => 'Fast mobile storefronts',
                    'generated_at' => 'Today 08:10 AM',
                    'is_active' => false,
                ],
            ],
            'advancedControls' => [
                [
                    'label' => 'Color Preset',
                    'name' => 'color_preset',
                    'value' => 'Ocean Blue',
                    'options' => ['Ocean Blue', 'Emerald Green', 'Sunset Orange', 'Carbon Gray'],
                    'help' => 'Controls brand color, CTA emphasis, and highlight chips.',
                ],
                [
                    'label' => 'Typography Pack',
                    'name' => 'typography_pack',
                    'value' => 'Modern Sans',
                    'options' => ['Modern Sans', 'Elegant Serif', 'Bold Contrast'],
                    'help' => 'Applies heading + body font system across storefront pages.',
                ],
                [
                    'label' => 'Section Spacing',
                    'name' => 'section_spacing',
                    'value' => 'Comfortable',
                    'options' => ['Compact', 'Comfortable', 'Airy'],
                    'help' => 'Controls vertical rhythm between major homepage sections.',
                ],
                [
                    'label' => 'Corner Style',
                    'name' => 'corner_style',
                    'value' => 'Soft',
                    'options' => ['Sharp', 'Soft', 'Rounded'],
                    'help' => 'Applies to cards, buttons, and image placeholders.',
                ],
                [
                    'label' => 'Product Grid Density',
                    'name' => 'grid_density',
                    'value' => 'Balanced',
                    'options' => ['Compact', 'Balanced', 'Spacious'],
                    'help' => 'Adjusts card count and information density in listing pages.',
                ],
                [
                    'label' => 'Image Ratio Mode',
                    'name' => 'image_ratio_mode',
                    'value' => '4:5 Portrait',
                    'options' => ['1:1 Square', '4:5 Portrait', '16:9 Landscape'],
                    'help' => 'Defines default ratio for product thumbnails and featured banners.',
                ],
            ],
            'behaviorSettings' => [
                [
                    'id' => 'sticky_buy_mobile',
                    'label' => 'Sticky Buy Button (Mobile)',
                    'description' => 'Keep purchase CTA visible while user scrolls product details.',
                    'enabled' => true,
                ],
                [
                    'id' => 'quick_view_cards',
                    'label' => 'Quick View from Product Grid',
                    'description' => 'Allow product quick-view modal directly from listing cards.',
                    'enabled' => true,
                ],
                [
                    'id' => 'trust_badges_checkout',
                    'label' => 'Trust Badges at Checkout',
                    'description' => 'Show secure payment and delivery badges near checkout CTA.',
                    'enabled' => true,
                ],
                [
                    'id' => 'auto_dark_sections',
                    'label' => 'Auto Contrast for Hero Sections',
                    'description' => 'Auto adjust text and overlay for readable hero content.',
                    'enabled' => false,
                ],
                [
                    'id' => 'floating_chat_icon',
                    'label' => 'Floating Chat Shortcut',
                    'description' => 'Enable sticky messenger-style support icon on storefront pages.',
                    'enabled' => false,
                ],
            ],
            'checkoutControls' => [
                [
                    'label' => 'Cart Drawer Style',
                    'name' => 'cart_drawer_style',
                    'value' => 'Right Slide',
                    'options' => ['Right Slide', 'Bottom Sheet', 'Inline Cart Page'],
                    'help' => 'Defines default cart interaction before checkout.',
                ],
                [
                    'label' => 'Checkout Layout',
                    'name' => 'checkout_layout',
                    'value' => 'Two Column',
                    'options' => ['Single Column', 'Two Column', 'Progressive Step'],
                    'help' => 'Select how checkout information and summary are shown.',
                ],
                [
                    'label' => 'Primary CTA Style',
                    'name' => 'primary_cta_style',
                    'value' => 'Solid',
                    'options' => ['Solid', 'Soft Gradient', 'Outline + Fill Hover'],
                    'help' => 'Controls styling of add-to-cart and checkout buttons.',
                ],
                [
                    'label' => 'Announcement Bar',
                    'name' => 'announcement_bar',
                    'value' => 'Compact',
                    'options' => ['Off', 'Compact', 'Full Width'],
                    'help' => 'Theme-level top bar for campaign message visibility.',
                ],
            ],
        ]);
    }

    private function offersData(Request $request): array
    {
        return array_merge($this->shell(
            request: $request,
            activeTab: 'offers',
            heading: 'Coupon Code Manager',
            sectionSubtitle: 'Create simple coupon codes with flat or percentage discount and manage them from one place.',
            quickStats: [
                ['label' => 'Active Coupons', 'value' => '3', 'note' => 'Currently usable at checkout', 'tone' => 'success'],
                ['label' => 'Used This Week', 'value' => '428', 'note' => 'Total redemptions', 'tone' => 'primary'],
                ['label' => 'Avg Discount', 'value' => 'BDT 142', 'note' => 'Per successful order', 'tone' => 'info'],
                ['label' => 'Expired Soon', 'value' => '1', 'note' => 'Ends within 48 hours', 'tone' => 'warning'],
            ]
        ), [
            'couponDefaults' => [
                'code' => 'WELCOME10',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'minimum_order' => 500,
                'max_discount' => 250,
                'usage_limit' => 200,
                'per_user_limit' => 1,
                'start_at' => '2026-02-23T00:00',
                'end_at' => '2026-03-31T23:59',
                'applies_to' => 'All Products',
                'status' => 'Active',
            ],
            'coupons' => [
                [
                    'code' => 'WELCOME10',
                    'discount_type' => 'Percentage',
                    'discount_value' => '10%',
                    'minimum_order' => 'BDT 500',
                    'usage' => '134 / 200',
                    'status' => 'Active',
                    'validity' => '23 Feb 2026 - 31 Mar 2026',
                ],
                [
                    'code' => 'FLAT120',
                    'discount_type' => 'Flat',
                    'discount_value' => 'BDT 120',
                    'minimum_order' => 'BDT 800',
                    'usage' => '68 / 150',
                    'status' => 'Active',
                    'validity' => '15 Feb 2026 - 28 Feb 2026',
                ],
                [
                    'code' => 'RAMADAN20',
                    'discount_type' => 'Percentage',
                    'discount_value' => '20%',
                    'minimum_order' => 'BDT 1200',
                    'usage' => '0 / 300',
                    'status' => 'Scheduled',
                    'validity' => '01 Mar 2026 - 20 Mar 2026',
                ],
            ],
            'couponGuides' => [
                [
                    'title' => 'Flat Discount',
                    'note' => 'Use for fixed amount off like BDT 100 discount.',
                    'example' => 'Example: BDT 120 off on orders above BDT 800.',
                ],
                [
                    'title' => 'Percentage Discount',
                    'note' => 'Use for flexible discount by order value.',
                    'example' => 'Example: 10% off with max BDT 250 discount cap.',
                ],
                [
                    'title' => 'Safe Limits',
                    'note' => 'Set usage limit and per-user limit to control abuse.',
                    'example' => 'Example: Total 200 uses, max 1 use per customer.',
                ],
            ],
            'couponChecklist' => [
                'Pick discount type: Flat or Percentage.',
                'Set minimum order amount before enabling coupon.',
                'For percentage coupon, always set max discount cap.',
                'Set start/end date so coupon auto starts and stops.',
                'Set usage limits to avoid unlimited abuse.',
            ],
            'categoryOptions' => [
                'Fashion',
                'Beauty',
                'Electronics',
                'Home & Living',
                'Sports',
                'Kids',
            ],
            'productOptions' => [
                'Classic Cotton T-Shirt',
                'Premium Wireless Earbuds',
                'Hydrating Face Serum',
                'Running Shoes - Sprint X',
                'Ceramic Coffee Mug Set',
                'Kids Learning Puzzle Board',
            ],
        ]);
    }

    private function contentData(Request $request): array
    {
        return array_merge($this->shell(
            request: $request,
            activeTab: 'content',
            heading: 'Website Content Operations',
            sectionSubtitle: 'Manage website content health, publishing pipeline, and SEO compliance from a dedicated page.',
            quickStats: [
                ['label' => 'Content Freshness', 'value' => '87%', 'note' => '3 assets need refresh', 'tone' => 'primary'],
                ['label' => 'Published This Week', 'value' => '14', 'note' => 'Pages, banners, and FAQs', 'tone' => 'success'],
                ['label' => 'SEO Score', 'value' => '91', 'note' => 'Average of key landing pages', 'tone' => 'info'],
                ['label' => 'Pending Reviews', 'value' => '5', 'note' => 'Waiting for legal or brand approval', 'tone' => 'warning'],
            ]
        ), [
            'contentData' => [
                ['title' => 'Homepage Banners', 'status' => 'Healthy', 'note' => '3 active hero banners mapped to current campaign.', 'meta' => 'Updated 3h ago'],
                ['title' => 'CMS Pages (About, Privacy, Terms)', 'status' => 'Review Needed', 'note' => 'Policy and terms require legal review this month.', 'meta' => 'Last review 24 days ago'],
                ['title' => 'FAQs', 'status' => 'Healthy', 'note' => '18 published answers aligned with support tickets.', 'meta' => 'Updated yesterday'],
                ['title' => 'Blog Posts', 'status' => 'Draft Pending', 'note' => '2 posts waiting on product image assets.', 'meta' => 'Next publish Monday'],
            ],
            'pipeline' => [
                ['asset' => 'Ramadan Hero Banner', 'owner' => 'Design Team', 'stage' => 'Ready for QA', 'publish' => '23 Feb 2026', 'channel' => 'Homepage'],
                ['asset' => 'Shipping Policy Update', 'owner' => 'Legal Team', 'stage' => 'In Review', 'publish' => '24 Feb 2026', 'channel' => 'Policy Page'],
                ['asset' => 'Earbuds Comparison Blog', 'owner' => 'Content Team', 'stage' => 'Draft', 'publish' => '26 Feb 2026', 'channel' => 'Blog'],
                ['asset' => 'Offer Explainer FAQ', 'owner' => 'Support Team', 'stage' => 'Ready', 'publish' => 'Today', 'channel' => 'FAQ'],
            ],
            'seoChecks' => [
                ['page' => 'Homepage', 'meta' => 'Complete', 'schema' => 'Valid', 'last_audit' => 'Today 09:15 AM'],
                ['page' => 'Category Landing', 'meta' => 'Missing 2 titles', 'schema' => 'Valid', 'last_audit' => 'Today 08:05 AM'],
                ['page' => 'Product Detail Template', 'meta' => 'Complete', 'schema' => 'Needs Product schema patch', 'last_audit' => 'Yesterday 06:22 PM'],
            ],
            'checklist' => [
                'Review legal pages at least once every 30 days.',
                'Keep banner copy synced with active offer rules.',
                'Audit metadata before every campaign launch.',
                'Confirm FAQ responses match latest support policies.',
            ],
        ]);
    }
}
