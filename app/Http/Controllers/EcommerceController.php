<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class EcommerceController extends Controller
{
    public function index(): View
    {
        return $this->renderPage('index');
    }

    public function shop(): View
    {
        return $this->renderPage('shop');
    }

    public function bestseller(): View
    {
        return $this->renderPage('bestseller');
    }

    public function cart(): View
    {
        return $this->renderPage('cart');
    }

    public function cheackout(): View
    {
        return $this->renderPage('cheackout');
    }

    public function contact(): View
    {
        return $this->renderPage('contact');
    }

    public function login(): View
    {
        return $this->renderPage('login');
    }

    public function logout()
    {
        return "done";
    }

    public function signup(): View
    {
        return $this->renderPage('signup');
    }

    public function returnsWarranty(): View
    {
        return $this->renderPage('returns-warranty');
    }

    public function termsConditions(): View
    {
        return $this->renderPage('terms-conditions');
    }

    public function privacyPolicy(): View
    {
        return $this->renderPage('privacy-policy');
    }

    public function aboutUs(): View
    {
        return $this->renderPage('about-us');
    }

    public function faq(): View
    {
        return $this->renderPage('faq');
    }

    public function giftVouchers(): View
    {
        return $this->renderPage('gift-vouchers');
    }

    public function wishlist(): View
    {
        return $this->renderPage('wishlist');
    }

    public function trackOrder(): View
    {
        return $this->renderPage('track-order', [
            'trackingOrders' => $this->trackingOrders(),
        ]);
    }

    public function publicTrackOrder(string $orderId): View
    {
        $normalizedOrderId = $this->normalizeTrackingOrderId($orderId);

        return $this->renderPage('track-order-public', [
            'requestedTrackingOrderId' => $normalizedOrderId,
            'selectedTrackingOrder' => $this->findTrackingOrder($orderId),
        ]);
    }

    public function orderHistory(): View
    {
        return $this->renderPage('order-history');
    }

    public function myAccount(): View
    {
        return $this->renderPage('my-account');
    }

    public function notifications(): View
    {
        return $this->renderPage('notifications');
    }

    public function notFound(): View
    {
        return $this->renderPage('404');
    }

    public function fallback(): Response
    {
        return response()
            ->view('ecommerce.theme1.404', $this->sharedThemeData(), 404);
    }

    public function productDetails(string $slug): View
    {
        return $this->renderPage('product-details', [
            'slug' => $slug,
            'selectedProduct' => $this->findProduct($slug) ?? ($this->catalogProducts()[0] ?? null),
        ]);
    }

    public function category(string $slug): View
    {
        $category = $this->findCategory($slug);

        abort_unless(is_array($category), 404);

        $categoryProducts = array_values(array_filter(
            $this->catalogProducts(),
            static fn (array $product): bool => $product['category_slug'] === $slug
        ));

        return $this->renderPage('category-view', [
            'currentCategory' => $category,
            'categoryProducts' => $categoryProducts,
        ]);
    }

    private function renderPage(string $view, array $data = []): View
    {
        return view("ecommerce.theme1.{$view}", array_merge($this->sharedThemeData(), $data));
    }

    private function sharedThemeData(): array
    {
        $products = $this->catalogProducts();
        $categories = $this->themeCategories();

        return [
            'themeCategories' => $categories,
            'themeProducts' => $products,
            'themeFeaturedProducts' => array_slice($products, 0, 4),
            'themeBestSellerProducts' => array_slice($products, 4, 4),
            'themeServices' => [
                [
                    'icon' => 'fa fa-sync-alt',
                    'title' => 'Easy Returns',
                    'description' => '7-day returns on eligible devices and accessories.',
                ],
                [
                    'icon' => 'fab fa-telegram-plane',
                    'title' => 'Fast Delivery',
                    'description' => 'Express shipping in major cities and live tracking.',
                ],
                [
                    'icon' => 'fas fa-life-ring',
                    'title' => 'Support 24/7',
                    'description' => 'Store experts available every day for setup and help.',
                ],
                [
                    'icon' => 'fas fa-credit-card',
                    'title' => 'Flexible Payment',
                    'description' => 'Card, COD, wallet, and installment-ready checkout.',
                ],
                [
                    'icon' => 'fas fa-shield-alt',
                    'title' => 'Warranty Ready',
                    'description' => 'Official coverage and quick replacement guidance.',
                ],
                [
                    'icon' => 'fas fa-gift',
                    'title' => 'Gift Options',
                    'description' => 'Gift vouchers, wrapping, and custom message support.',
                ],
            ],
            'themePrimaryProduct' => $products[0] ?? null,
        ];
    }

    private function themeCategories(): array
    {
        $products = $this->catalogProducts();
        $productCounts = [];

        foreach ($products as $product) {
            $slug = $product['category_slug'];
            $productCounts[$slug] = ($productCounts[$slug] ?? 0) + 1;
        }

        $categories = [
            [
                'slug' => 'accessories',
                'name' => 'Accessories',
                'icon' => 'fas fa-headphones',
                'description' => 'Audio gear, chargers, wearables, and travel-ready add-ons.',
            ],
            [
                'slug' => 'electronics-computers',
                'name' => 'Electronics & Computers',
                'icon' => 'fas fa-keyboard',
                'description' => 'Daily productivity tools, creators’ gear, and smart peripherals.',
            ],
            [
                'slug' => 'laptops-desktops',
                'name' => 'Laptops & Desktops',
                'icon' => 'fas fa-laptop',
                'description' => 'Portable workstations, gaming rigs, and display setups.',
            ],
            [
                'slug' => 'mobiles-tablets',
                'name' => 'Mobiles & Tablets',
                'icon' => 'fas fa-mobile-alt',
                'description' => 'Phones and tablets built for work, play, and travel.',
            ],
            [
                'slug' => 'smart-tv-entertainment',
                'name' => 'Smartphone & Smart TV',
                'icon' => 'fas fa-tv',
                'description' => 'Entertainment hubs, streaming devices, and connected displays.',
            ],
        ];

        return array_map(
            static fn (array $category): array => $category + ['count' => $productCounts[$category['slug']] ?? 0],
            $categories
        );
    }

    private function catalogProducts(): array
    {
        return [
            [
                'slug' => 'apple-ipad-mini-g2356',
                'name' => 'Apple iPad Mini G2356',
                'short_name' => 'Apple iPad Mini',
                'category_slug' => 'mobiles-tablets',
                'category_name' => 'Mobiles & Tablets',
                'image' => 'product-3.png',
                'price' => '$1,050.00',
                'old_price' => '$1,250.00',
                'badge' => 'New',
                'rating' => 4,
                'stock' => 'In Stock',
                'status' => 'Ready to ship',
                'excerpt' => 'Compact tablet with a bright display, long battery life, and smooth multitasking.',
            ],
            [
                'slug' => 'vortex-probook-15',
                'name' => 'Vortex ProBook 15',
                'short_name' => 'Vortex ProBook 15',
                'category_slug' => 'laptops-desktops',
                'category_name' => 'Laptops & Desktops',
                'image' => 'product-4.png',
                'price' => '$1,329.00',
                'old_price' => '$1,499.00',
                'badge' => 'Sale',
                'rating' => 5,
                'stock' => 'Limited Stock',
                'status' => 'Best for creators',
                'excerpt' => 'A slim performance laptop with dedicated graphics and all-day battery backup.',
            ],
            [
                'slug' => 'aurora-watch-s2',
                'name' => 'Aurora Watch S2',
                'short_name' => 'Aurora Watch S2',
                'category_slug' => 'accessories',
                'category_name' => 'Accessories',
                'image' => 'product-5.png',
                'price' => '$249.00',
                'old_price' => '$299.00',
                'badge' => 'New',
                'rating' => 4,
                'stock' => 'In Stock',
                'status' => 'Fitness ready',
                'excerpt' => 'Smart fitness tracking, call sync, and premium strap options for everyday wear.',
            ],
            [
                'slug' => 'nova-cam-4k',
                'name' => 'Nova Cam 4K',
                'short_name' => 'Nova Cam 4K',
                'category_slug' => 'electronics-computers',
                'category_name' => 'Electronics & Computers',
                'image' => 'product-6.png',
                'price' => '$699.00',
                'old_price' => '$779.00',
                'badge' => 'Sale',
                'rating' => 4,
                'stock' => 'In Stock',
                'status' => 'Creator favorite',
                'excerpt' => 'A content-first mirrorless camera for studio shoots, vlogging, and travel reels.',
            ],
            [
                'slug' => 'orbit-earbuds-x',
                'name' => 'Orbit Earbuds X',
                'short_name' => 'Orbit Earbuds X',
                'category_slug' => 'accessories',
                'category_name' => 'Accessories',
                'image' => 'product-7.png',
                'price' => '$129.00',
                'old_price' => '$169.00',
                'badge' => 'Sale',
                'rating' => 5,
                'stock' => 'In Stock',
                'status' => 'Noise cancelling',
                'excerpt' => 'Wireless earbuds with strong bass, ANC, and a charging case for long commutes.',
            ],
            [
                'slug' => 'pixel-tab-11',
                'name' => 'Pixel Tab 11',
                'short_name' => 'Pixel Tab 11',
                'category_slug' => 'mobiles-tablets',
                'category_name' => 'Mobiles & Tablets',
                'image' => 'product-8.png',
                'price' => '$889.00',
                'old_price' => '$999.00',
                'badge' => 'New',
                'rating' => 4,
                'stock' => 'Pre-order',
                'status' => 'Launching soon',
                'excerpt' => 'A premium Android tablet designed for streaming, sketching, and hybrid work.',
            ],
            [
                'slug' => 'ultrawide-monitor-q27',
                'name' => 'UltraWide Monitor Q27',
                'short_name' => 'UltraWide Q27',
                'category_slug' => 'laptops-desktops',
                'category_name' => 'Laptops & Desktops',
                'image' => 'product-9.png',
                'price' => '$459.00',
                'old_price' => '$539.00',
                'badge' => 'Sale',
                'rating' => 4,
                'stock' => 'In Stock',
                'status' => 'Desk upgrade',
                'excerpt' => 'Curved high-resolution monitor with strong color accuracy for work and gaming.',
            ],
            [
                'slug' => 'neo-smart-tv-55',
                'name' => 'Neo Smart TV 55"',
                'short_name' => 'Neo Smart TV 55"',
                'category_slug' => 'smart-tv-entertainment',
                'category_name' => 'Smartphone & Smart TV',
                'image' => 'product-10.png',
                'price' => '$1,199.00',
                'old_price' => '$1,399.00',
                'badge' => 'Sale',
                'rating' => 5,
                'stock' => 'In Stock',
                'status' => 'Family pick',
                'excerpt' => 'A bright 4K smart TV with voice remote, streaming apps, and gaming mode.',
            ],
            [
                'slug' => 'streambox-mini-hub',
                'name' => 'StreamBox Mini Hub',
                'short_name' => 'StreamBox Mini',
                'category_slug' => 'smart-tv-entertainment',
                'category_name' => 'Smartphone & Smart TV',
                'image' => 'product-11.png',
                'price' => '$89.00',
                'old_price' => '$109.00',
                'badge' => 'New',
                'rating' => 4,
                'stock' => 'In Stock',
                'status' => 'Quick setup',
                'excerpt' => 'Turns any display into a streaming and casting-ready entertainment screen.',
            ],
            [
                'slug' => 'mechanical-keyboard-k8',
                'name' => 'Mechanical Keyboard K8',
                'short_name' => 'Keyboard K8',
                'category_slug' => 'electronics-computers',
                'category_name' => 'Electronics & Computers',
                'image' => 'product-12.png',
                'price' => '$139.00',
                'old_price' => '$169.00',
                'badge' => 'Sale',
                'rating' => 4,
                'stock' => 'In Stock',
                'status' => 'Workstation essential',
                'excerpt' => 'Tactile wireless keyboard with multi-device pairing and durable keycaps.',
            ],
            [
                'slug' => 'home-security-cam-a1',
                'name' => 'Home Security Cam A1',
                'short_name' => 'Security Cam A1',
                'category_slug' => 'electronics-computers',
                'category_name' => 'Electronics & Computers',
                'image' => 'product-13.png',
                'price' => '$179.00',
                'old_price' => '$219.00',
                'badge' => 'New',
                'rating' => 5,
                'stock' => 'In Stock',
                'status' => 'Indoor protection',
                'excerpt' => 'Smart home camera with night vision, motion alerts, and app-based controls.',
            ],
            [
                'slug' => 'fold-pro-phone-x',
                'name' => 'Fold Pro Phone X',
                'short_name' => 'Fold Pro Phone X',
                'category_slug' => 'smart-tv-entertainment',
                'category_name' => 'Smartphone & Smart TV',
                'image' => 'product-14.png',
                'price' => '$1,499.00',
                'old_price' => '$1,699.00',
                'badge' => 'New',
                'rating' => 5,
                'stock' => 'Limited Stock',
                'status' => 'Flagship foldable',
                'excerpt' => 'A foldable premium phone with an immersive screen and advanced camera stack.',
            ],
        ];
    }

    private function findCategory(string $slug): ?array
    {
        foreach ($this->themeCategories() as $category) {
            if ($category['slug'] === $slug) {
                return $category;
            }
        }

        return null;
    }

    private function findProduct(string $slug): ?array
    {
        foreach ($this->catalogProducts() as $product) {
            if ($product['slug'] === $slug) {
                return $product;
            }
        }

        return null;
    }

    private function trackingOrders(): array
    {
        return [
            [
                'id' => '#ELX-20510',
                'route_id' => 'ELX-20510',
                'items' => 'Home Security Cam A1',
                'status' => 'waiting_for_call',
                'status_class' => 'is-warning',
                'summary' => 'Your order is waiting for the first verification call before confirmation can move forward.',
                'eta' => 'Pending confirmation',
                'courier' => 'Not assigned yet',
                'shipment' => 'Awaiting dispatch',
                'address' => 'Mirpur DOHS, Dhaka',
                'last_update' => '20 Mar 2026, 10:05 AM',
                'timeline' => [
                    ['title' => 'waiting_for_call', 'body' => 'Our team is preparing the first verification call for this order.', 'time' => '20 Mar 2026, 10:05 AM', 'state' => 'current'],
                    ['title' => 'waiting_for_confirmation', 'body' => 'This stage will begin after the verification call is answered.', 'time' => 'Pending', 'state' => 'pending'],
                    ['title' => 'ready_to_dispatch', 'body' => 'Packing and dispatch preparation will start after confirmation.', 'time' => 'Pending', 'state' => 'pending'],
                    ['title' => 'in_transit', 'body' => 'Courier movement will begin after dispatch handoff.', 'time' => 'Pending', 'state' => 'pending'],
                    ['title' => 'success', 'body' => 'Delivery will be marked complete after successful handoff.', 'time' => 'Pending', 'state' => 'pending'],
                ],
            ],
            [
                'id' => '#ELX-20504',
                'route_id' => 'ELX-20504',
                'items' => 'Neo Smart TV 55"',
                'status' => 'waiting_for_confirmation',
                'status_class' => 'is-info',
                'summary' => 'The customer call was completed and the order is currently waiting for final confirmation approval.',
                'eta' => 'Confirmation in progress',
                'courier' => 'Not assigned yet',
                'shipment' => 'Awaiting confirmation',
                'address' => 'Banani, Dhaka',
                'last_update' => '20 Mar 2026, 11:10 AM',
                'timeline' => [
                    ['title' => 'waiting_for_call', 'body' => 'The customer was reached successfully for order verification.', 'time' => '20 Mar 2026, 10:42 AM', 'state' => 'complete'],
                    ['title' => 'waiting_for_confirmation', 'body' => 'The order is under final confirmation review.', 'time' => '20 Mar 2026, 11:10 AM', 'state' => 'current'],
                    ['title' => 'ready_to_dispatch', 'body' => 'Dispatch preparation starts after confirmation is completed.', 'time' => 'Pending', 'state' => 'pending'],
                    ['title' => 'in_transit', 'body' => 'Transit updates will appear after courier pickup.', 'time' => 'Pending', 'state' => 'pending'],
                    ['title' => 'success', 'body' => 'Delivery will be marked complete after successful handoff.', 'time' => 'Pending', 'state' => 'pending'],
                ],
            ],
            [
                'id' => '#ELX-20412',
                'route_id' => 'ELX-20412',
                'items' => 'Aurora Watch S2',
                'status' => 'ready_to_dispatch',
                'status_class' => 'is-warning',
                'summary' => 'Your order is confirmed, packed, and waiting for dispatch handoff to the courier.',
                'eta' => 'Tomorrow, 1:00 PM - 5:00 PM',
                'courier' => 'City Parcel',
                'shipment' => 'SV-203102-BD',
                'address' => 'Uttara Sector 11, Dhaka',
                'last_update' => '20 Mar 2026, 11:20 AM',
                'timeline' => [
                    ['title' => 'waiting_for_call', 'body' => 'The initial verification call was completed successfully.', 'time' => '19 Mar 2026, 07:44 PM', 'state' => 'complete'],
                    ['title' => 'waiting_for_confirmation', 'body' => 'The order details were confirmed and approved.', 'time' => '19 Mar 2026, 07:48 PM', 'state' => 'complete'],
                    ['title' => 'ready_to_dispatch', 'body' => 'The item is packed and ready for courier pickup.', 'time' => '20 Mar 2026, 11:20 AM', 'state' => 'current'],
                    ['title' => 'in_transit', 'body' => 'Transit updates will begin after pickup scan.', 'time' => 'Pending', 'state' => 'pending'],
                    ['title' => 'success', 'body' => 'Delivery will be marked complete after successful handoff.', 'time' => 'Pending', 'state' => 'pending'],
                ],
            ],
            [
                'id' => '#ELX-20489',
                'route_id' => 'ELX-20489',
                'items' => 'Vortex ProBook 15 and Orbit Earbuds X',
                'status' => 'in_transit',
                'status_class' => 'is-info',
                'summary' => 'Your package has left the local hub and is currently moving through the delivery network.',
                'eta' => 'Today, 6:00 PM - 9:00 PM',
                'courier' => 'Electro Express',
                'shipment' => 'SV-203490-BD',
                'address' => 'House 12, Road 7, Dhanmondi, Dhaka',
                'last_update' => '20 Mar 2026, 09:00 AM',
                'timeline' => [
                    ['title' => 'waiting_for_call', 'body' => 'The verification call was completed and the order was accepted.', 'time' => '19 Mar 2026, 10:14 AM', 'state' => 'complete'],
                    ['title' => 'waiting_for_confirmation', 'body' => 'Delivery details and product availability were confirmed.', 'time' => '19 Mar 2026, 10:32 AM', 'state' => 'complete'],
                    ['title' => 'ready_to_dispatch', 'body' => 'Items were packed and handed to the dispatch queue.', 'time' => '19 Mar 2026, 03:10 PM', 'state' => 'complete'],
                    ['title' => 'in_transit', 'body' => 'The package is currently with the courier and heading toward the destination area.', 'time' => '20 Mar 2026, 09:00 AM', 'state' => 'current'],
                    ['title' => 'success', 'body' => 'Delivery will be marked complete after successful handoff confirmation.', 'time' => 'Pending', 'state' => 'pending'],
                ],
            ],
            [
                'id' => '#ELX-20187',
                'route_id' => 'ELX-20187',
                'items' => 'StreamBox Mini Hub',
                'status' => 'success',
                'status_class' => 'is-success',
                'summary' => 'The delivery was completed successfully and received at the destination address.',
                'eta' => 'Delivered on 18 Mar 2026',
                'courier' => 'Rapid Dispatch',
                'shipment' => 'SV-201870-BD',
                'address' => 'Zindabazar, Sylhet',
                'last_update' => '18 Mar 2026, 04:25 PM',
                'timeline' => [
                    ['title' => 'waiting_for_call', 'body' => 'The customer verification call was completed.', 'time' => '16 Mar 2026, 09:10 AM', 'state' => 'complete'],
                    ['title' => 'waiting_for_confirmation', 'body' => 'The order was confirmed and approved for processing.', 'time' => '16 Mar 2026, 09:18 AM', 'state' => 'complete'],
                    ['title' => 'ready_to_dispatch', 'body' => 'The item was prepared and handed to dispatch.', 'time' => '16 Mar 2026, 02:15 PM', 'state' => 'complete'],
                    ['title' => 'in_transit', 'body' => 'The parcel reached the destination city and was assigned to a rider.', 'time' => '18 Mar 2026, 09:35 AM', 'state' => 'complete'],
                    ['title' => 'success', 'body' => 'The package was handed over and marked as complete.', 'time' => '18 Mar 2026, 04:25 PM', 'state' => 'current'],
                ],
            ],
        ];
    }

    private function findTrackingOrder(string $orderId): ?array
    {
        $normalizedOrderId = $this->normalizeTrackingOrderId($orderId);

        foreach ($this->trackingOrders() as $order) {
            if ($order['id'] === $normalizedOrderId) {
                return $order;
            }
        }

        return null;
    }

    private function normalizeTrackingOrderId(string $orderId): string
    {
        $normalizedOrderId = strtoupper(trim(rawurldecode($orderId)));
        $normalizedOrderId = preg_replace('/\s+/', '', $normalizedOrderId) ?? $normalizedOrderId;
        $normalizedOrderId = ltrim($normalizedOrderId, '#');
        $normalizedOrderId = preg_replace('/[^A-Z0-9-]/', '', $normalizedOrderId) ?? $normalizedOrderId;

        return '#' . $normalizedOrderId;
    }
}
